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
    echo "ID de torneo no especificado.";
    exit;
}

// Obtener datos del torneo
$stmt = $pdo->prepare("SELECT * FROM torneos WHERE id = ? AND usuario_id = ?");
$stmt->execute([$torneo_id, $usuario_id]);
$torneo = $stmt->fetch();

if (!$torneo) {
    echo "Torneo no encontrado o no tienes permisos.";
    exit;
}

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $formato = $_POST['formato'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $subcategoria = $_POST['subcategoria'] ?? null;
    $ciudad = $_POST['ciudad'] ?? '';
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';
$ida_vuelta = isset($_POST['ida_vuelta']) ? (int) $_POST['ida_vuelta'] : 0;

    $sql = "UPDATE torneos 
            SET nombre = ?, formato = ?, categoria = ?, subcategoria = ?, ciudad = ?, fecha_inicio = ?, fecha_fin = ?, ida_vuelta = ? 
            WHERE id = ? AND usuario_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre, $formato, $categoria, $subcategoria, $ciudad, $fecha_inicio, $fecha_fin, $ida_vuelta, $torneo_id, $usuario_id]);


    header("Location: ver_torneo.php?id=" . $torneo_id);
    exit;
}

include 'header.php';
?>
<link rel="stylesheet" href="css/style.css">

<main>
  <section id="crear-torneo" class="form-container">
    <h2>Editar Torneo</h2>
    <form method="POST">
      <label>Nombre del Torneo:</label>
      <input type="text" name="nombre" value="<?= htmlspecialchars($torneo['nombre']) ?>" required>

      <label>Formato:</label>
      <select name="formato" required>
        <option value="liga" <?= $torneo['formato'] === 'liga' ? 'selected' : '' ?>>Liga</option>
        <option value="eliminatorias" <?= $torneo['formato'] === 'eliminatorias' ? 'selected' : '' ?>>Eliminatorias</option>
        <option value="ambos" <?= $torneo['formato'] === 'ambos' ? 'selected' : '' ?>>Liga + Eliminatorias</option>
      </select>
        <label>¿Ida y vuelta? (solo para Liga):</label>
        <select name="ida_vuelta">
        <option value="0" <?= $torneo['ida_vuelta'] == 0 ? 'selected' : '' ?>>Solo ida</option>
        <option value="1" <?= $torneo['ida_vuelta'] == 1 ? 'selected' : '' ?>>Ida y vuelta</option>
        </select>

      <label>Categoría:</label>
      <input type="text" name="categoria" value="<?= htmlspecialchars($torneo['categoria']) ?>" required>

      <label>Subcategoría (opcional):</label>
      <input type="text" name="subcategoria" value="<?= htmlspecialchars($torneo['subcategoria'] ?? '') ?>">

      <label>Ciudad:</label>
      <input type="text" name="ciudad" value="<?= htmlspecialchars($torneo['ciudad']) ?>" required>

      <label>Fecha de Inicio:</label>
      <input type="date" name="fecha_inicio" value="<?= $torneo['fecha_inicio'] ?>" required>

      <label>Fecha de Fin:</label>
      <input type="date" name="fecha_fin" value="<?= $torneo['fecha_fin'] ?>" required>

      <button class="btn-primary" type="submit">Guardar cambios</button>
    </form>
  </section>
  <a href="ver_torneo.php?id=<?= $torneo_id ?>" class="btn-primary" style="margin-top: 2rem; display: inline-block;">
     ← Volver al torneo
    </a>
</main>

<?php include 'footer.php'; ?>
