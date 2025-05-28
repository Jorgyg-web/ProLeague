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
    die("ID de torneo no válido.");
}

// Verificar que el torneo es del usuario y de tipo "ambos"
$stmt = $pdo->prepare("SELECT formato FROM torneos WHERE id = ? AND usuario_id = ?");
$stmt->execute([$torneo_id, $usuario_id]);
$torneo = $stmt->fetch();

if (!$torneo || $torneo['formato'] !== 'ambos') {
    die("No autorizado o el torneo no es de tipo liga + eliminatorias.");
}

// Obtener clasificación
$stmt = $pdo->prepare("SELECT id, nombre FROM equipos WHERE torneo_id = ?");
$stmt->execute([$torneo_id]);
$equipos = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$clasificacion = [];
foreach ($equipos as $id => $nombre) {
    $clasificacion[$id] = [
        'equipo' => $nombre,
        'pj' => 0,
        'pg' => 0,
        'pe' => 0,
        'pp' => 0,
        'gf' => 0,
        'gc' => 0,
        'dg' => 0,
        'pts' => 0
    ];
}

$stmt = $pdo->prepare("SELECT * FROM partidos WHERE torneo_id = ? AND goles_local IS NOT NULL AND goles_visitante IS NOT NULL");
$stmt->execute([$torneo_id]);
$partidos = $stmt->fetchAll();

foreach ($partidos as $partido) {
    $l = $partido['equipo_local_id'];
    $v = $partido['equipo_visitante_id'];
    $gl = (int)$partido['goles_local'];
    $gv = (int)$partido['goles_visitante'];

    $clasificacion[$l]['pj']++;
    $clasificacion[$v]['pj']++;
    $clasificacion[$l]['gf'] += $gl;
    $clasificacion[$l]['gc'] += $gv;
    $clasificacion[$v]['gf'] += $gv;
    $clasificacion[$v]['gc'] += $gl;
    $clasificacion[$l]['dg'] = $clasificacion[$l]['gf'] - $clasificacion[$l]['gc'];
    $clasificacion[$v]['dg'] = $clasificacion[$v]['gf'] - $clasificacion[$v]['gc'];

    if ($gl > $gv) {
        $clasificacion[$l]['pg']++;
        $clasificacion[$v]['pp']++;
        $clasificacion[$l]['pts'] += 3;
    } elseif ($gv > $gl) {
        $clasificacion[$v]['pg']++;
        $clasificacion[$l]['pp']++;
        $clasificacion[$v]['pts'] += 3;
    } else {
        $clasificacion[$l]['pe']++;
        $clasificacion[$v]['pe']++;
        $clasificacion[$l]['pts']++;
        $clasificacion[$v]['pts']++;
    }
}

// Ordenar
uasort($clasificacion, function ($a, $b) {
    return [$b['pts'], $b['dg']] <=> [$a['pts'], $a['dg']];
});

// Seleccionar clasificados (automático según cantidad de equipos)
$totalEquipos = count($clasificacion);
if ($totalEquipos <= 4) {
    $clasificados = array_slice(array_keys($clasificacion), 0, 2);
    $fases = ['Final'];
} elseif ($totalEquipos <= 8) {
    $clasificados = array_slice(array_keys($clasificacion), 0, 4);
    $fases = ['Semifinales', 'Final'];
} else {
    $clasificados = array_slice(array_keys($clasificacion), 0, 8);
    $fases = ['Cuartos', 'Semifinales', 'Final'];
}

// Verificar si ya se generaron eliminatorias
$stmt = $pdo->prepare("SELECT COUNT(*) FROM partidos WHERE torneo_id = ? AND jornada IS NULL");
$stmt->execute([$torneo_id]);
if ($stmt->fetchColumn() > 0) {
    die("Ya se han generado partidos de eliminación.");
}

// Crear enfrentamientos directos (en función de posiciones)
$emparejamientos = [];
$mid = count($clasificados) / 2;
for ($i = 0; $i < $mid; $i++) {
    $emparejamientos[] = [$clasificados[$i], $clasificados[count($clasificados) - 1 - $i]];
}

$stmt = $pdo->prepare("INSERT INTO partidos (torneo_id, equipo_local_id, equipo_visitante_id, jornada) VALUES (?, ?, ?, NULL)");
foreach ($emparejamientos as [$local, $visitante]) {
    $stmt->execute([$torneo_id, $local, $visitante]);
}

header("Location: ver_torneo.php?id=" . $torneo_id);
exit;
