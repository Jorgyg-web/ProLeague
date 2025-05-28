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
    echo "ID de torneo no especificado.";
    exit;
}

// Verificar que el torneo pertenece al usuario
$stmt = $pdo->prepare("SELECT nombre, formato, ida_vuelta FROM torneos WHERE id = ? AND usuario_id = ?");
$stmt->execute([$torneo_id, $usuario_id]);
$torneo = $stmt->fetch();

if (!$torneo) {
    echo "Torneo no encontrado o acceso no permitido.";
    exit;
}

// Obtener equipos del torneo
$stmt = $pdo->prepare("SELECT id, nombre FROM equipos WHERE torneo_id = ?");
$stmt->execute([$torneo_id]);
$equipos = $stmt->fetchAll();

include 'header.php';
?>
<link rel="stylesheet" href="css/style.css">

<section id="gestionar-partidos">
  <h2>Gestionar Partidos - Torneo: <?= htmlspecialchars($torneo['nombre']) ?></h2>

  <?php if (count($equipos) < 2): ?>
    <p>Se necesitan al menos 2 equipos para generar partidos. <a href="agregar_equipos.php?id=<?= $torneo_id ?>">Agregar equipos</a></p>
  <?php else: ?>
    <form method="POST" action="generar_partidos.php">
      <input type="hidden" name="torneo_id" value="<?= $torneo_id ?>">

      <?php if ($torneo['formato'] === 'liga'): ?>
        <input type="hidden" name="modo" value="liga">
        <button class="btn-primary" type="submit">
          ⚙️ Generar calendario (Liga <?= $torneo['ida_vuelta'] ? 'ida y vuelta' : 'solo ida' ?>)
        </button>

      <?php elseif ($torneo['formato'] === 'eliminatorias'): ?>
        <input type="hidden" name="modo" value="eliminatorias">
        <button class="btn-primary" type="submit">
          🏆 Generar eliminatorias
        </button>

      <?php elseif ($torneo['formato'] === 'ambos'): ?>
        <input type="hidden" name="modo" value="ambos">
        <button class="btn-primary" type="submit">
          🔁 Generar Liga + Eliminatorias
        </button>
      <?php endif; ?>
    </form>
  <?php endif; ?>

  <div style="margin-top:2rem;">
    <a href="ver_torneo.php?id=<?= $torneo_id ?>" class="btn-primary">← Volver al torneo</a>
  </div>
</section>

<style>
#gestionar-partidos {
  background: white;
  padding: 2rem;
  border-radius: 10px;
  box-shadow: 0 3px 12px rgb(0 0 0 / 0.05);
  max-width: 700px;
  margin: 2rem auto 4rem;
  text-align: center;
}

#gestionar-partidos h2 {
  color: #1e90ff;
  font-weight: 700;
  font-size: 1.8rem;
  margin-bottom: 1.5rem;
}

#gestionar-partidos form button {
  margin-top: 1rem;
  padding: 0.7rem 1.5rem;
  font-size: 1rem;
}
</style>

<?php include 'footer.php'; ?>
