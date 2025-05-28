<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];
$torneo_id = $_GET['id'] ?? null;

if (!$torneo_id || !is_numeric($torneo_id)) {
    echo "ID de torneo no válido.";
    exit;
}

// Verificar que el torneo pertenece al usuario
$stmt = $pdo->prepare("SELECT nombre FROM torneos WHERE id = ? AND usuario_id = ?");
$stmt->execute([$torneo_id, $usuario_id]);
$torneo = $stmt->fetch();

if (!$torneo) {
    echo "Torneo no encontrado o no autorizado.";
    exit;
}

// Obtener equipos del torneo
$stmt = $pdo->prepare("SELECT id, nombre FROM equipos WHERE torneo_id = ?");
$stmt->execute([$torneo_id]);
$equipos = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // id => nombre

// Inicializar clasificación
$clasificacion = [];
foreach ($equipos as $id => $nombre) {
    $clasificacion[$id] = [
        'equipo' => $nombre,
        'pj' => 0,
        'pg' => 0,
        'pe' => 0,
        'pp' => 0,
        'gf' => 0,
        'gc' => 0,
        'dg' => 0,
        'pts' => 0
    ];
}

// Obtener partidos jugados
$sql = "SELECT * FROM partidos WHERE torneo_id = ? AND goles_local IS NOT NULL AND goles_visitante IS NOT NULL";
$stmt = $pdo->prepare($sql);
$stmt->execute([$torneo_id]);
$partidos = $stmt->fetchAll();

// Calcular estadísticas
foreach ($partidos as $partido) {
    $local = $partido['equipo_local_id'];
    $visitante = $partido['equipo_visitante_id'];
    $gl = (int)$partido['goles_local'];
    $gv = (int)$partido['goles_visitante'];

    // PJ
    $clasificacion[$local]['pj']++;
    $clasificacion[$visitante]['pj']++;

    // Goles
    $clasificacion[$local]['gf'] += $gl;
    $clasificacion[$local]['gc'] += $gv;
    $clasificacion[$visitante]['gf'] += $gv;
    $clasificacion[$visitante]['gc'] += $gl;

    // Diferencia de gol
    $clasificacion[$local]['dg'] = $clasificacion[$local]['gf'] - $clasificacion[$local]['gc'];
    $clasificacion[$visitante]['dg'] = $clasificacion[$visitante]['gf'] - $clasificacion[$visitante]['gc'];

    // Resultados
    if ($gl > $gv) {
        $clasificacion[$local]['pg']++;
        $clasificacion[$visitante]['pp']++;
        $clasificacion[$local]['pts'] += 3;
    } elseif ($gl < $gv) {
        $clasificacion[$visitante]['pg']++;
        $clasificacion[$local]['pp']++;
        $clasificacion[$visitante]['pts'] += 3;
    } else {
        $clasificacion[$local]['pe']++;
        $clasificacion[$visitante]['pe']++;
        $clasificacion[$local]['pts'] += 1;
        $clasificacion[$visitante]['pts'] += 1;
    }
}

// Ordenar por puntos y diferencia de goles
usort($clasificacion, function ($a, $b) {
    if ($b['pts'] !== $a['pts']) return $b['pts'] - $a['pts'];
    return $b['dg'] - $a['dg'];
});

include 'header.php';
?>
<link rel="stylesheet" href="css/style.css">
<section id="clasificacion">
  <h2>Clasificación - Torneo: <?= htmlspecialchars($torneo['nombre']) ?></h2>

  <?php if (empty($partidos)): ?>
    <p>Aún no hay resultados registrados.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Equipo</th>
          <th>PJ</th>
          <th>PG</th>
          <th>PE</th>
          <th>PP</th>
          <th>GF</th>
          <th>GC</th>
          <th>DG</th>
          <th>PTS</th>
        </tr>
      </thead>
      <tbody>
        <?php $pos = 1; foreach ($clasificacion as $equipo): ?>
          <tr>
            <td><?= $pos++ ?></td>
            <td><?= htmlspecialchars($equipo['equipo']) ?></td>
            <td><?= $equipo['pj'] ?></td>
            <td><?= $equipo['pg'] ?></td>
            <td><?= $equipo['pe'] ?></td>
            <td><?= $equipo['pp'] ?></td>
            <td><?= $equipo['gf'] ?></td>
            <td><?= $equipo['gc'] ?></td>
            <td><?= $equipo['dg'] ?></td>
            <td><strong><?= $equipo['pts'] ?></strong></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <a href="ver_torneo.php?id=<?= $torneo_id ?>" class="btn-primary" style="margin-top: 2rem; display: inline-block;">← Volver al torneo</a>
</section>

<?php include 'footer.php'; ?>
