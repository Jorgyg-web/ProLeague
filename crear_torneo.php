<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $formato = $_POST['formato'] ?? '';
    $categoria = trim($_POST['categoria'] ?? '');
    $subcategoria = trim($_POST['subcategoria'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';
$ida_vuelta = isset($_POST['ida_vuelta']) ? (int)$_POST['ida_vuelta'] : 0;

    if (!$nombre || !$formato || !$categoria || !$ciudad || !$fecha_inicio || !$fecha_fin) {
        $error = 'Por favor, completa todos los campos obligatorios.';
    } elseif ($fecha_fin < $fecha_inicio) {
        $error = 'La fecha fin no puede ser anterior a la fecha inicio.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO torneos (nombre, formato, categoria, subcategoria, ciudad, fecha_inicio, fecha_fin, usuario_id, ida_vuelta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $resultado = $stmt->execute([$nombre, $formato, $categoria, $subcategoria, $ciudad, $fecha_inicio, $fecha_fin, $_SESSION['user_id'], $ida_vuelta]);

        if ($resultado) {
            $success = 'Torneo creado correctamente. Puedes verlo en "Mis torneos".';
            $nombre = $formato = $categoria = $subcategoria = $ciudad = $fecha_inicio = $fecha_fin = '';
        } else {
            $error = 'Error al guardar el torneo. Inténtalo de nuevo.';
        }
    }
}
?>

<?php include 'header.php'; ?>
<link rel="stylesheet" href="css/style.css">

<main class="main-container">
  <h2 class="section-title">Crear nuevo torneo</h2>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php elseif ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="POST" action="crear_torneo.php" class="form" novalidate>
    <label for="nombre">Nombre del torneo <span class="required">*</span></label>
    <input type="text" id="nombre" name="nombre" required value="<?= htmlspecialchars($nombre ?? '') ?>" placeholder="Ejemplo: Torneo Verano 2025" />

    <label for="formato">Formato <span class="required">*</span></label>
    <select id="formato" name="formato" required>
      <option value="" disabled <?= empty($formato) ? 'selected' : '' ?>>Selecciona formato</option>
      <option value="liga" <?= ($formato ?? '') === 'liga' ? 'selected' : '' ?>>Liga</option>
      <option value="eliminatorias" <?= ($formato ?? '') === 'eliminatorias' ? 'selected' : '' ?>>Eliminatorias</option>
      <option value="ambos" <?= ($formato ?? '') === 'ambos' ? 'selected' : '' ?>>Liga + Eliminatorias</option>
    </select>
  <div id="ida-vuelta-container" style="display:none;">
    <label>¿Ida y vuelta? (solo para Liga o Liga + Eliminatorias):</label>
    <select name="ida_vuelta">
      <option value="0">Solo ida</option>
      <option value="1">Ida y vuelta</option>
    </select>
  </div>


    <label for="categoria">Categoría <span class="required">*</span></label>
    <input type="text" id="categoria" name="categoria" required value="<?= htmlspecialchars($categoria ?? '') ?>" placeholder="Ejemplo: Adultos, Juvenil" />

    <label for="subcategoria">Subcategoría (opcional)</label>
    <input type="text" id="subcategoria" name="subcategoria" value="<?= htmlspecialchars($subcategoria ?? '') ?>" placeholder="Ejemplo: Masculino, Femenino, Mixto" />

    <label for="ciudad">Ciudad <span class="required">*</span></label>
    <input type="text" id="ciudad" name="ciudad" required value="<?= htmlspecialchars($ciudad ?? '') ?>" placeholder="Ejemplo: Madrid" />

    <label for="fecha_inicio">Fecha inicio <span class="required">*</span></label>
    <input type="date" id="fecha_inicio" name="fecha_inicio" required value="<?= htmlspecialchars($fecha_inicio ?? '') ?>" />

    <label for="fecha_fin">Fecha fin <span class="required">*</span></label>
    <input type="date" id="fecha_fin" name="fecha_fin" required value="<?= htmlspecialchars($fecha_fin ?? '') ?>" />

    <button type="submit" class="btn-primary">Crear torneo</button>
  </form>

  <a href="index.php" class="btn-secondary">← Volver a Inicio</a>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const formatoSelect = document.querySelector('select[name="formato"]');
  const idaVueltaContainer = document.getElementById('ida-vuelta-container');

  function actualizarIdaVuelta() {
    const valor = formatoSelect.value;
    if (valor === 'liga' || valor === 'ambos') {
      idaVueltaContainer.style.display = 'block';
    } else {
      idaVueltaContainer.style.display = 'none';
    }
  }

  formatoSelect.addEventListener('change', actualizarIdaVuelta);
  actualizarIdaVuelta(); // Ejecutar al cargar
});
</script>

<style>
  /* Basado en estilos del index */

  .main-container {
    max-width: 640px;
    margin: 3rem auto 4rem;
    padding: 2rem 1.5rem;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgb(149 157 165 / 0.2);
  }

  .section-title {
    color: #0d6efd;
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 1.8rem;
    text-align: center;
  }

  .form {
    display: flex;
    flex-direction: column;
    gap: 1.2rem;
  }
  body {
    margin-top: 0px!important;
  }

  label {
    font-weight: 600;
    color: #212529;
  }

  .required {
    color: #dc3545;
  }

  input[type="text"],
  input[type="date"],
  select {
    font-size: 1rem;
    padding: 0.55rem 1rem;
    border: 1.5px solid #ced4da;
    border-radius: 8px;
    transition: border-color 0.3s ease;
  }

  input[type="text"]:focus,
  input[type="date"]:focus,
  select:focus {
    outline: none;
    border-color: #0d6efd;
  }

  button.btn-primary {
    margin-top: 1rem;
    background-color: #0d6efd;
    border: none;
    color: white;
    font-weight: 700;
    padding: 0.65rem 1.6rem;
    border-radius: 10px;
    cursor: pointer;
    font-size: 1.1rem;
    transition: background-color 0.3s ease;
  }

  button.btn-primary:hover {
    background-color: #084ecf;
  }

  .btn-secondary {
    display: inline-block;
    margin-top: 1.5rem;
    color: #0d6efd;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
  }

  .btn-secondary:hover {
    text-decoration: underline;
  }

  .alert {
    border-radius: 10px;
    padding: 1rem 1.2rem;
    font-weight: 600;
    margin-bottom: 1.4rem;
    text-align: center;
  }

  .alert-error {
    background-color: #f8d7da;
    color: #842029;
    border: 1px solid #f5c2c7;
  }

  .alert-success {
    background-color: #d1e7dd;
    color: #0f5132;
    border: 1px solid #badbcc;
  }

  @media (max-width: 480px) {
    .main-container {
      margin: 2rem 1rem 3rem;
      padding: 1.5rem 1rem;
    }
  }
</style>
