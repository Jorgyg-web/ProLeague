<?php
// jugadores.php
session_start();
require_once 'db.php';

$equipo_id = $_GET['equipo_id'] ?? null;
if (!$equipo_id) {
    echo "Equipo no especificado.";
    exit;
}
$torneo_id = $_GET['torneo_id'] ?? null;

// Obtener nombre del equipo
$stmt = $pdo->prepare("SELECT nombre FROM equipos WHERE id = ?");
$stmt->execute([$equipo_id]);
$equipo = $stmt->fetch();

// Obtener jugadores
$stmt = $pdo->prepare("SELECT * FROM jugadores WHERE equipo_id = ?");
$stmt->execute([$equipo_id]);
$jugadores = $stmt->fetchAll();

include 'header.php';
?>
<link rel="stylesheet" href="css/style.css">
<section class="contenido">
  <h2>Jugadores de <?= htmlspecialchars($equipo['nombre']) ?></h2>
  <a href="agregar_jugador.php?equipo_id=<?= $equipo_id ?>" class="btn-primary">➕ Agregar Jugador</a>
  <ul class="jugadores-lista">
    <?php foreach ($jugadores as $j): ?>
      <li><strong><?= htmlspecialchars($j['nombre']) ?></strong> (<?= $j['dorsal'] ?> - <?= $j['posicion'] ?>)</li>
    <?php endforeach; ?>
    <?php if (empty($jugadores)): ?>
      <li style="color: gray; font-style: italic;">No hay jugadores registrados.</li>
    <?php endif; ?>
  </ul>
    <a href="ver_torneo.php?id=<?= $torneo_id ?>" class="btn-primary">← Volver al torneo</a>
</section>
<?php include 'footer.php'; ?>
