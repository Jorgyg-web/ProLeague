<?php
session_start();
require_once 'db.php';

function resumenFormato($formato) {
    switch ($formato) {
        case 'liga': return 'Liga';
        case 'eliminatorias': return 'Eliminatorias';
        case 'ambos': return 'Liga + Eliminatorias';
        default: return 'Desconocido';
    }
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de torneo no válido.";
    exit;
}

$torneo_id = (int) $_GET['id'];

$sql = "SELECT * FROM torneos WHERE id = ? AND usuario_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$torneo_id, $usuario_id]);
$torneo = $stmt->fetch();

if (!$torneo) {
    echo "Torneo no encontrado.";
    exit;
}

$stmt = $pdo->prepare("SELECT id, nombre FROM equipos WHERE torneo_id = ?");
$stmt->execute([$torneo_id]);
$equipos = $stmt->fetchAll();
$cantidad_equipos = count($equipos);

$stmt = $pdo->prepare("SELECT p.*, el.nombre AS equipo_local, ev.nombre AS equipo_visitante
                       FROM partidos p
                       JOIN equipos el ON p.equipo_local_id = el.id
                       JOIN equipos ev ON p.equipo_visitante_id = ev.id
                       WHERE p.torneo_id = ?
                       ORDER BY COALESCE(p.jornada, 9999), p.id ASC");
$stmt->execute([$torneo_id]);
$partidos = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT p.*, el.nombre AS equipo_local, ev.nombre AS equipo_visitante
                       FROM partidos p
                       JOIN equipos el ON el.id = p.equipo_local_id
                       JOIN equipos ev ON ev.id = p.equipo_visitante_id
                       WHERE p.torneo_id = ? AND p.jornada IS NULL
                       ORDER BY p.id ASC");
$stmt->execute([$torneo_id]);
$eliminatorias = $stmt->fetchAll();

include 'header.php';
?>
<link rel="stylesheet" href="css/style.css">
<section id="ver-torneo">
  <h2><?= htmlspecialchars($torneo['nombre']) ?></h2>

  <button class="btn-primary" onclick="abrirModalCompartir(<?= $torneo['id'] ?>)">
    🔗 Compartir torneo
  </button>

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

  <table>
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Formato</th>
        <th>Categoría</th>
        <th>Subcategoría</th>
        <th>Ciudad</th>
        <th>Fechas</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?= htmlspecialchars($torneo['nombre']) ?></td>
        <td><?= resumenFormato($torneo['formato']) ?></td>
        <td><?= htmlspecialchars($torneo['categoria']) ?></td>
        <td><?= htmlspecialchars($torneo['subcategoria'] ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($torneo['ciudad']) ?></td>
        <td><?= htmlspecialchars($torneo['fecha_inicio']) ?> - <?= htmlspecialchars($torneo['fecha_fin']) ?></td>
        <td>
          <a href="editar_torneo.php?id=<?= $torneo['id'] ?>" class="btn">Editar</a>
          <a href="mis_torneos.php" class="btn">Volver a Mis Torneos</a>
        </td>
      </tr>
    </tbody>
  </table>

  <div class="mt-4">
    <h3>Equipos Participantes</h3>
    <ul>
      <?php if (count($equipos) === 0): ?>
        <li style="font-style: italic; color: #777;">No hay equipos registrados.</li>
      <?php else: ?>
        <?php foreach ($equipos as $equipo): ?>
          <li style="margin-bottom: 0.5rem;">
            <strong><?= htmlspecialchars($equipo['nombre']) ?></strong>
           <li>
            <a href="jugadores.php?equipo_id=<?= $equipo['id'] ?>&torneo_id=<?= $torneo['id'] ?>" class="btn btn-secondary">
              👥 Jugadores
            </a>
          </li>
          </li>
        <?php endforeach; ?>
      <?php endif; ?>
    </ul>
  </div>

  <a href="agregar_equipos.php?id=<?= $torneo['id'] ?>" class="btn-primary" style="margin-bottom: 1rem; display: inline-block;">
    ➕ Gestionar Equipos
  </a>

  <div class="mt-4">
    <h3>Partidos</h3>
    <?php if (count($partidos) > 0): ?>
      <table>
        <thead>
          <tr>
            <th><?= in_array($torneo['formato'], ['liga', 'ambos']) ? 'Jornada' : 'Ronda' ?></th>
            <th>Equipo Local</th>
            <th>Resultado</th>
            <th>Equipo Visitante</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($partidos as $p): ?>
            <tr>
              <td><?= $p['jornada'] ?? 'Eliminatoria' ?></td>
              <td><?= htmlspecialchars($p['equipo_local']) ?></td>
              <td>
                <a href="editar_partido.php?id=<?= $p['id'] ?>" style="text-decoration:none; font-weight:600; color:#1e90ff;">
                  <?= isset($p['goles_local'], $p['goles_visitante']) ? "{$p['goles_local']} - {$p['goles_visitante']}" : 'Editar' ?>
                </a>
              </td>
              <td><?= htmlspecialchars($p['equipo_visitante']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php if (in_array($torneo['formato'], ['liga', 'ambos'])): ?>
        <a href="tabla_clasificacion.php?id=<?= $torneo['id'] ?>" class="btn-primary" style="margin-top: 1.5rem; display: inline-block;">
          📊 Ver Clasificación
        </a>
      <?php endif; ?>

      <?php if (in_array($torneo['formato'], ['eliminatorias', 'ambos']) && count($eliminatorias) > 0): ?>
      <section id="cuadro-eliminatorio" style="margin-top: 3rem;">
        <h3>🏆 Cuadro Eliminatorio</h3>
        <div class="eliminatorias-grid">
          <?php foreach ($eliminatorias as $e): ?>
            <div class="match-card">
              <div><strong><?= htmlspecialchars($e['equipo_local']) ?></strong></div>
              <div><?= is_numeric($e['goles_local']) && is_numeric($e['goles_visitante']) ? "{$e['goles_local']} - {$e['goles_visitante']}" : 'vs' ?></div>
              <div><strong><?= htmlspecialchars($e['equipo_visitante']) ?></strong></div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

    <?php else: ?>
      <p>No hay partidos generados aún.</p>
      <a href="gestionar_partidos.php?id=<?= $torneo_id ?>" class="btn-primary" style="margin-top: 1rem; display: inline-block;">
        ⚙️ Generar partidos
      </a>
    <?php endif; ?>
  </div>

  <a href="mis_torneos.php" class="btn-primary" style="margin-top: 2rem; display: inline-block;">
    ← Volver a Mis Torneos
  </a>
</section>

<?php include 'footer.php'; ?>

<script>
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
