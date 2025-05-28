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

// Obtener partidos del torneo
$sql = "SELECT p.*, 
               el.nombre AS equipo_local, 
               ev.nombre AS equipo_visitante
        FROM partidos p
        JOIN equipos el ON p.equipo_local_id = el.id
        JOIN equipos ev ON p.equipo_visitante_id = ev.id
        WHERE p.torneo_id = ?
        ORDER BY jornada ASC, p.id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$torneo_id]);
$partidos = $stmt->fetchAll();

include 'header.php';
?>

<section id="calendario">
  <h2>Calendario - Torneo: <?= htmlspecialchars($torneo['nombre']) ?></h2>

  <?php if (!$partidos): ?>
    <p>Aún no se han generado partidos.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Jornada</th>
          <th>Equipo Local</th>
          <th>Resultado</th>
          <th>Equipo Visitante</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($partidos as $partido): ?>
          <tr>
            <td><?= htmlspecialchars($partido['jornada']) ?></td>
            <td><?= htmlspecialchars($partido['equipo_local']) ?></td>
            <td>
              <?php if ($partido['goles_local'] !== null && $partido['goles_visitante'] !== null): ?>
                <?= $partido['goles_local'] ?> - <?= $partido['goles_visitante'] ?>
              <?php else: ?>
                vs
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($partido['equipo_visitante']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <a href="ver_torneo.php?id=<?= $torneo_id ?>" class="btn-primary" style="margin-top: 2rem; display: inline-block;">← Volver al torneo</a>
</section>

<style>
#calendario {
  background: white;
  padding: 2rem;
  border-radius: 10px;
  box-shadow: 0 3px 12px rgb(0 0 0 / 0.05);
  max-width: 900px;
  margin: 2rem auto 4rem;
  text-align: center;
}

#calendario h2 {
  color: #1e90ff;
  font-weight: 700;
  font-size: 1.8rem;
  margin-bottom: 1.5rem;
}

#calendario table {
  width: 100%;
  border-collapse: collapse;
  text-align: center;
}

#calendario thead {
  background: #1e90ff;
  color: white;
}

#calendario th, #calendario td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #eee;
  font-size: 0.95rem;
}

#calendario tbody tr:nth-child(even) {
  background: #f9f9f9;
}
</style>

<?php include 'footer.php'; ?>
