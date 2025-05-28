<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];
$torneo_id = $_GET['id'] ?? null;

if (!$torneo_id) {
    echo "Torneo no especificado.";
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

// Agregar equipo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_equipo'])) {
    $nombre_equipo = trim($_POST['nombre_equipo']);
    if ($nombre_equipo !== '') {
        $stmt = $pdo->prepare("INSERT INTO equipos (torneo_id, nombre) VALUES (?, ?)");
        $stmt->execute([$torneo_id, $nombre_equipo]);
    }
}

// Eliminar equipo
if (isset($_GET['eliminar'])) {
    $equipo_id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM equipos WHERE id = ? AND torneo_id = ?");
    $stmt->execute([$equipo_id, $torneo_id]);
}

// Obtener equipos del torneo
$stmt = $pdo->prepare("SELECT * FROM equipos WHERE torneo_id = ?");
$stmt->execute([$torneo_id]);
$equipos = $stmt->fetchAll();

include 'header.php';
?>
<link rel="stylesheet" href="css/style.css">

<main>
  <section id="agregar-equipos">
    <h2>Equipos del Torneo: <?= htmlspecialchars($torneo['nombre']) ?></h2>

    <form method="POST">
      <label>Nombre del Equipo:</label>
      <input type="text" name="nombre_equipo" required>
      <button class="btn-primary" type="submit">Agregar Equipo</button>
    </form>

    <?php if ($equipos): ?>
      <h3>Equipos Registrados:</h3>
      <ul>
        <?php foreach ($equipos as $equipo): ?>
          <li>
            <?= htmlspecialchars($equipo['nombre']) ?>
            <a href="jugadores.php?id=<?= $equipo['id'] ?>" class="btn">👥 Jugadores</a>
            <a href="agregar_equipos.php?id=<?= $torneo_id ?>&eliminar=<?= $equipo['id'] ?>" onclick="return confirm('¿Eliminar equipo?')">🗑️</a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>Aún no se han agregado equipos.</p>
    <?php endif; ?>
  </section>
      <a href="ver_torneo.php?id=<?= $torneo_id ?>" class="btn-primary" style="margin-top: 2rem; display: inline-block;">
     ← Volver al torneo
    </a>
</main>

<?php include 'footer.php'; ?>
