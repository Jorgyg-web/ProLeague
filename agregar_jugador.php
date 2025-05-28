<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];
$equipo_id = $_GET['equipo_id'] ?? null;

if (!$equipo_id || !is_numeric($equipo_id)) {
    echo "ID de equipo no válido.";
    exit;
}

// Verificar que el equipo pertenece a un torneo del usuario
$stmt = $pdo->prepare("SELECT e.nombre AS equipo_nombre, t.usuario_id, t.id AS torneo_id FROM equipos e JOIN torneos t ON e.torneo_id = t.id WHERE e.id = ?");
$stmt->execute([$equipo_id]);
$equipo = $stmt->fetch();

if (!$equipo || $equipo['usuario_id'] != $usuario_id) {
    echo "Acceso denegado.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $dorsal = $_POST['dorsal'] ?? null;
    $posicion = $_POST['posicion'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO jugadores (equipo_id, nombre, dorsal, posicion) VALUES (?, ?, ?, ?)");
    $stmt->execute([$equipo_id, $nombre, $dorsal, $posicion]);

    header("Location: jugadores.php?equipo_id=" . $equipo_id);
    exit;
}

include 'header.php';
?>
<link rel="stylesheet" href="css/style.css">
<main class="form-container">
  <h2>Agregar Jugador a "<?= htmlspecialchars($equipo['equipo_nombre']) ?>"</h2>
  <form method="POST">
    <label>Nombre del jugador:</label>
    <input type="text" name="nombre" required>

    <label>Dorsal (opcional):</label>
    <input type="number" name="dorsal" min="0">

    <label>Posición:</label>
    <input type="text" name="posicion">

    <button class="btn-primary" type="submit">Guardar</button>
  </form>

  <a href="jugadores.php?equipo_id=<?= $equipo_id ?>" class="btn-primary" style="margin-top: 1.5rem; display: inline-block;">
    ← Volver al equipo
  </a>
</main>
<?php include 'footer.php'; ?>
