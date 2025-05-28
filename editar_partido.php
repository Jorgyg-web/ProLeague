<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];
$partido_id = $_GET['id'] ?? null;

if (!$partido_id || !is_numeric($partido_id)) {
    echo "Partido no válido.";
    exit;
}

// Obtener partido y verificar que pertenece al usuario
$sql = "SELECT p.*, t.usuario_id, el.nombre AS equipo_local, ev.nombre AS equipo_visitante
        FROM partidos p
        JOIN torneos t ON p.torneo_id = t.id
        JOIN equipos el ON p.equipo_local_id = el.id
        JOIN equipos ev ON p.equipo_visitante_id = ev.id
        WHERE p.id = ? AND t.usuario_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$partido_id, $usuario_id]);
$partido = $stmt->fetch();

if (!$partido) {
    echo "No autorizado o partido no encontrado.";
    exit;
}

// Si se envió el formulario con resultado y extras
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $goles_local = is_numeric($_POST['goles_local']) ? (int)$_POST['goles_local'] : null;
    $goles_visitante = is_numeric($_POST['goles_visitante']) ? (int)$_POST['goles_visitante'] : null;
    $goleadores = $_POST['goleadores'] ?? '';
    $tarjetas = $_POST['tarjetas'] ?? '';

    $update = $pdo->prepare("UPDATE partidos SET goles_local = ?, goles_visitante = ?, goleadores = ?, tarjetas = ? WHERE id = ?");
    $update->execute([$goles_local, $goles_visitante, $goleadores, $tarjetas, $partido_id]);

    header("Location: ver_torneo.php?id=" . $partido['torneo_id']);
    exit;
}

function generarSiguienteEliminatoria(PDO $pdo, int $torneo_id) {
    // Obtener partidos eliminatorios finalizados
    $stmt = $pdo->prepare("SELECT * FROM partidos 
                           WHERE torneo_id = ? AND jornada IS NULL 
                           AND goles_local IS NOT NULL AND goles_visitante IS NOT NULL 
                           ORDER BY id ASC");
    $stmt->execute([$torneo_id]);
    $finalizados = $stmt->fetchAll();

    $ronda = [];
    foreach ($finalizados as $p) {
        $ganador = null;
        if ($p['goles_local'] > $p['goles_visitante']) {
            $ganador = $p['equipo_local_id'];
        } elseif ($p['goles_visitante'] > $p['goles_local']) {
            $ganador = $p['equipo_visitante_id'];
        }

        if ($ganador) {
            $ronda[] = $ganador;
        }
    }

    // Si hay pares de ganadores, generar la siguiente ronda
    if (count($ronda) % 2 === 0 && count($ronda) >= 2) {
        $stmtInsert = $pdo->prepare("INSERT INTO partidos (torneo_id, equipo_local_id, equipo_visitante_id, jornada) VALUES (?, ?, ?, NULL)");

        for ($i = 0; $i < count($ronda); $i += 2) {
            $stmtInsert->execute([$torneo_id, $ronda[$i], $ronda[$i + 1]]);
        }
    }
}


include 'header.php';
?>
<link rel="stylesheet" href="css/style.css">

<main>
  <section id="editar-partido" class="form-container">
    <h2>Editar Resultado</h2>
    <p><strong><?= $partido['equipo_local'] ?></strong> vs <strong><?= $partido['equipo_visitante'] ?></strong></p>

    <form id="form-partido" method="POST">
      <label><?= $partido['equipo_local'] ?> (goles):</label>
      <input type="number" name="goles_local" min="0" value="<?= htmlspecialchars($partido['goles_local']) ?>">

      <label><?= $partido['equipo_visitante'] ?> (goles):</label>
      <input type="number" name="goles_visitante" min="0" value="<?= htmlspecialchars($partido['goles_visitante']) ?>">

      <div class="seccion-extra">
        <label>Goleadores (opcional):</label>
        <textarea name="goleadores" rows="3" placeholder="Ej: Juan x2, Pedro"><?= htmlspecialchars($partido['goleadores'] ?? '') ?></textarea>

        <label>Tarjetas / Incidencias (opcional):</label>
        <textarea name="tarjetas" rows="3" placeholder="Ej: Luis (amarilla), Jorge (roja)"><?= htmlspecialchars($partido['tarjetas'] ?? '') ?></textarea>
      </div>

      <button type="button" class="finalizar-btn" onclick="mostrarModal()">✅ Finalizar partido</button>
    </form>

    <a href="ver_torneo.php?id=<?= $partido['torneo_id'] ?>" class="btn-primary" style="margin-top:1.5rem; display:inline-block;">← Volver al torneo</a>
  </section>

  <!-- Modal de confirmación -->
  <div class="modal" id="modal-finalizar">
    <div class="modal-content">
      <h3>¿Confirmar resultado del partido?</h3>
      <p>Una vez confirmado, los datos se guardarán.</p>
      <div class="modal-buttons">
        <button class="confirm" onclick="document.getElementById('form-partido').submit()">Confirmar</button>
        <button class="cancel" onclick="cerrarModal()">Cancelar</button>
      </div>
    </div>
  </div>
</main>

<script>
function mostrarModal() {
  document.getElementById('modal-finalizar').classList.add('active');
}

function cerrarModal() {
  document.getElementById('modal-finalizar').classList.remove('active');
}
</script>

<?php include 'footer.php'; ?>
