<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$torneo_id = $_GET['id'] ?? null;
if (!$torneo_id || !is_numeric($torneo_id)) {
    echo "ID no válido.";
    exit;
}

$usuario_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM torneos WHERE id = ? AND usuario_id = ?");
$stmt->execute([$torneo_id, $usuario_id]);
$torneo = $stmt->fetch();

if (!$torneo) {
    echo "Torneo no encontrado o no autorizado.";
    exit;
}

// Obtener equipos
$stmt = $pdo->prepare("SELECT id, nombre FROM equipos WHERE torneo_id = ?");
$stmt->execute([$torneo_id]);
$equipos = $stmt->fetchAll();

// Clasificación (ordenada por puntos y diferencia de goles)
$stmt = $pdo->prepare("SELECT e.nombre, 
        SUM(CASE WHEN p.equipo_local_id = e.id THEN p.goles_local WHEN p.equipo_visitante_id = e.id THEN p.goles_visitante ELSE 0 END) AS gf,
        SUM(CASE WHEN p.equipo_local_id = e.id THEN p.goles_visitante WHEN p.equipo_visitante_id = e.id THEN p.goles_local ELSE 0 END) AS gc,
        SUM(CASE 
          WHEN p.equipo_local_id = e.id AND p.goles_local > p.goles_visitante THEN 3
          WHEN p.equipo_visitante_id = e.id AND p.goles_visitante > p.goles_local THEN 3
          WHEN (p.equipo_local_id = e.id OR p.equipo_visitante_id = e.id) AND p.goles_local = p.goles_visitante THEN 1
          ELSE 0 END) AS pts
        FROM equipos e
        LEFT JOIN partidos p ON e.id = p.equipo_local_id OR e.id = p.equipo_visitante_id
        WHERE e.torneo_id = ?
        GROUP BY e.id
        ORDER BY pts DESC, (gf - gc) DESC, gf DESC");
$stmt->execute([$torneo_id]);
$clasificacion = $stmt->fetchAll();

// Goleadores (si existe columna 'goleadores')
$stmt = $pdo->prepare("SELECT goleadores FROM partidos WHERE torneo_id = ? AND goleadores IS NOT NULL");
$stmt->execute([$torneo_id]);
$goleadores_raw = $stmt->fetchAll(PDO::FETCH_COLUMN);

$goleadores = [];
foreach ($goleadores_raw as $registro) {
    $datos = explode(';', $registro);
    foreach ($datos as $g) {
        [$nombre, $goles] = explode(':', $g);
        $nombre = trim($nombre);
        $goleadores[$nombre] = ($goleadores[$nombre] ?? 0) + (int)$goles;
    }
}
arsort($goleadores);

include 'header.php';
?>
<link rel="stylesheet" href="css/style.css">

<section id="ver-torneo">
  <h2>🏁 Resumen del Torneo: <?= htmlspecialchars($torneo['nombre']) ?></h2>
  <p><strong>Ciudad:</strong> <?= htmlspecialchars($torneo['ciudad']) ?> | <strong>Fechas:</strong> <?= $torneo['fecha_inicio'] ?> - <?= $torneo['fecha_fin'] ?></p>

  <div class="mt-4">
    <h3>🏆 Clasificación Final</h3>
    <table>
      <thead>
        <tr>
          <th>Equipo</th>
          <th>Puntos</th>
          <th>GF</th>
          <th>GC</th>
          <th>DIF</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($clasificacion as $equipo): ?>
          <tr>
            <td><?= htmlspecialchars($equipo['nombre']) ?></td>
            <td><?= $equipo['pts'] ?></td>
            <td><?= $equipo['gf'] ?></td>
            <td><?= $equipo['gc'] ?></td>
            <td><?= $equipo['gf'] - $equipo['gc'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    <h3>⚽ Máximos Goleadores</h3>
    <ul>
      <?php if (empty($goleadores)): ?>
        <li style="font-style: italic; color: #777;">Sin registros</li>
      <?php else: ?>
        <?php foreach ($goleadores as $jugador => $goles): ?>
          <li><?= htmlspecialchars($jugador) ?> - <?= $goles ?> gol<?= $goles == 1 ? '' : 'es' ?></li>
        <?php endforeach; ?>
      <?php endif; ?>
    </ul>
  </div>

  <div style="text-align:center; margin-top: 2rem;">
    <a href="ver_torneo.php?id=<?= $torneo_id ?>" class="btn-primary">← Volver al torneo</a>
    <button onclick="window.print()" class="btn-primary">🖨️ Descargar resumen</button>
  </div>
</section>

<?php include 'footer.php'; ?>
