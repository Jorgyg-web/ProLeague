<?php
require_once 'db.php';

$torneo_id = $_GET['id'] ?? null;
if (!$torneo_id || !is_numeric($torneo_id)) {
    echo "Torneo no válido.";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM torneos WHERE id = ?");
$stmt->execute([$torneo_id]);
$torneo = $stmt->fetch();

if (!$torneo) {
    echo "Torneo no encontrado.";
    exit;
}

function resumenFormato($formato) {
    return match($formato) {
        'liga' => 'Liga',
        'eliminatorias' => 'Eliminatorias',
        'ambos' => 'Liga + Eliminatorias',
        default => 'Desconocido'
    };
}

$stmt = $pdo->prepare("SELECT id, nombre FROM equipos WHERE torneo_id = ?");
$stmt->execute([$torneo_id]);
$equipos = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT p.*, el.nombre AS local, ev.nombre AS visitante
                       FROM partidos p
                       JOIN equipos el ON p.equipo_local_id = el.id
                       JOIN equipos ev ON p.equipo_visitante_id = ev.id
                       WHERE p.torneo_id = ?
                       ORDER BY jornada ASC, p.id ASC");
$stmt->execute([$torneo_id]);
$partidos = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT p.*, el.nombre AS local, ev.nombre AS visitante
                       FROM partidos p
                       JOIN equipos el ON p.equipo_local_id = el.id
                       JOIN equipos ev ON p.equipo_visitante_id = ev.id
                       WHERE p.torneo_id = ? AND p.jornada IS NULL
                       ORDER BY p.id ASC");
$stmt->execute([$torneo_id]);
$eliminatorias = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($torneo['nombre']) ?> - Torneo Público</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'header.php' ?>
<section id="ver-torneo">
    
  <h2><?= htmlspecialchars($torneo['nombre']) ?></h2>
  <p><strong>Formato:</strong> <?= resumenFormato($torneo['formato']) ?></p>
  <p><strong>Categoría:</strong> <?= htmlspecialchars($torneo['categoria']) ?></p>
  <p><strong>Subcategoría:</strong> <?= htmlspecialchars($torneo['subcategoria'] ?? 'N/A') ?></p>
  <p><strong>Ciudad:</strong> <?= htmlspecialchars($torneo['ciudad']) ?></p>
  <p><strong>Fechas:</strong> <?= htmlspecialchars($torneo['fecha_inicio']) ?> - <?= htmlspecialchars($torneo['fecha_fin']) ?></p>

  <div class="mt-4">
    <h3>Equipos</h3>
    <ul>
      <?php foreach ($equipos as $eq): ?>
        <li><?= htmlspecialchars($eq['nombre']) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>

  <?php if ($torneo['formato'] !== 'eliminatorias'): ?>
  <div class="mt-4">
    <h3>Calendario</h3>
    <table>
      <thead>
        <tr>
          <th>Jornada</th>
          <th>Local</th>
          <th>Resultado</th>
          <th>Visitante</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($partidos as $p): ?>
        <tr>
          <td><?= $p['jornada'] ?></td>
          <td><?= htmlspecialchars($p['local']) ?></td>
          <td><?= is_numeric($p['goles_local']) ? $p['goles_local'] . ' - ' . $p['goles_visitante'] : 'vs' ?></td>
          <td><?= htmlspecialchars($p['visitante']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <?php if ($torneo['formato'] !== 'liga' && count($eliminatorias) > 0): ?>
  <div class="mt-4">
    <h3>Eliminatorias</h3>
    <div class="eliminatorias-grid">
      <?php foreach ($eliminatorias as $e): ?>
        <div class="match-card">
          <div><strong><?= htmlspecialchars($e['local']) ?></strong></div>
          <div><?= is_numeric($e['goles_local']) ? "{$e['goles_local']} - {$e['goles_visitante']}" : 'vs' ?></div>
          <div><strong><?= htmlspecialchars($e['visitante']) ?></strong></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div style="text-align:center; margin-top:2rem;">
    <a href="index.php" class="btn-primary">← Volver a Inicio</a>
    <button class="btn-primary" onclick="window.print()">🖨️ Descargar en PDF</button>
      <button class="btn-primary" onclick="abrirModalCompartir(<?= $torneo['id'] ?>)">
    🔗 Compartir torneo
  </button>

  <!-- Modal de compartir -->
  <div id="modal-compartir" class="modal">
    <div class="modal-content">
      <h3>Compartir torneo</h3>
      <p id="enlace-compartir" style="word-break: break-all;"></p>
      <div class="modal-buttons">
        <button onclick="copiarEnlace()">📋 Copiar</button>
        <a id="whatsapp-link" href="#" target="_blank">🟢 WhatsApp</a>
        <a id="abrir-nueva" href="#" target="_blank">🌐 Nueva pestaña</a>
      </div>
      <button class="cancel" onclick="cerrarModal()">Cancelar</button>
    </div>
  </div>
  <script>
  function copiarEnlaceTorneo(id) {
    const url = `${window.location.origin}/torneo_publico.php?id=${id}`;
    navigator.clipboard.writeText(url)
      .then(() => alert("Enlace copiado al portapapeles:\n" + url))
      .catch(() => alert("Error al copiar el enlace"));
  }
  </script>
  </div>
</section>
<?php include 'footer.php' ?>

<script>
function copiarEnlace() {
  const url = window.location.href;
  navigator.clipboard.writeText(url).then(() => {
    alert("Enlace copiado al portapapeles:");
  }, () => {
    alert("No se pudo copiar el enlace.");
  });
}
function abrirModalCompartir(id) {
  const url = `${window.location.origin}/torneo_publico.php?id=${id}`;
  document.getElementById('enlace-compartir').textContent = url;
  document.getElementById('whatsapp-link').href = `https://wa.me/?text=${encodeURIComponent(url)}`;
  document.getElementById('abrir-nueva').href = url;
  document.getElementById('modal-compartir').classList.add('active');
}
function cerrarModal() {
  document.getElementById('modal-compartir').classList.remove('active');
}
function copiarEnlace() {
  const text = document.getElementById('enlace-compartir').textContent;
  navigator.clipboard.writeText(text)
    .then(() => alert('Enlace copiado al portapapeles'))
    .catch(() => alert('No se pudo copiar'));
}
</script>
</body>
</html>
