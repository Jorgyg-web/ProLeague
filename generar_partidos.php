<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];
$torneo_id = $_POST['torneo_id'] ?? null;
$modo = $_POST['modo'] ?? 'liga';

if (!$torneo_id || !is_numeric($torneo_id)) {
    echo "Torneo no especificado.";
    exit;
}

// Verificar torneo y obtener datos
$stmt = $pdo->prepare("SELECT formato, ida_vuelta FROM torneos WHERE id = ? AND usuario_id = ?");
$stmt->execute([$torneo_id, $usuario_id]);
$torneo = $stmt->fetch();

if (!$torneo) {
    echo "No autorizado.";
    exit;
}

$formato = $torneo['formato'];
$ida_vuelta = (int) $torneo['ida_vuelta'];

// Obtener equipos
$stmt = $pdo->prepare("SELECT id FROM equipos WHERE torneo_id = ?");
$stmt->execute([$torneo_id]);
$equipos = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (count($equipos) < 2) {
    echo "Se necesitan al menos 2 equipos para generar partidos.";
    exit;
}

// Verificar si ya hay partidos generados
$stmt = $pdo->prepare("SELECT COUNT(*) FROM partidos WHERE torneo_id = ?");
$stmt->execute([$torneo_id]);
$ya_existen = $stmt->fetchColumn();

if ($ya_existen > 0) {
    echo "Los partidos ya fueron generados para este torneo.";
    exit;
}

function generarLiga($pdo, $torneo_id, $equipos, $ida_vuelta) {
    $emparejamientos = [];
    for ($i = 0; $i < count($equipos); $i++) {
        for ($j = $i + 1; $j < count($equipos); $j++) {
            $emparejamientos[] = [$equipos[$i], $equipos[$j]];
        }
    }
    shuffle($emparejamientos);

    $contador_locales = array_fill_keys($equipos, 0);
    $jornada = 1;
    $stmt = $pdo->prepare("INSERT INTO partidos (torneo_id, equipo_local_id, equipo_visitante_id, jornada) VALUES (?, ?, ?, ?)");

    foreach ($emparejamientos as [$a, $b]) {
        if ($contador_locales[$a] < $contador_locales[$b]) {
            $local = $a;
            $visitante = $b;
        } elseif ($contador_locales[$b] < $contador_locales[$a]) {
            $local = $b;
            $visitante = $a;
        } else {
            [$local, $visitante] = rand(0, 1) ? [$a, $b] : [$b, $a];
        }

        $contador_locales[$local]++;
        $stmt->execute([$torneo_id, $local, $visitante, $jornada++]);

        if ($ida_vuelta === 1) {
            $contador_locales[$visitante]++;
            $stmt->execute([$torneo_id, $visitante, $local, $jornada++]);
        }
    }
}

function generarEliminatorias($pdo, $torneo_id, $equipos, $ida_vuelta) {
    shuffle($equipos);
    $numEquipos = count($equipos);
    if ($numEquipos % 2 !== 0) {
        $equipos[] = null;
        $numEquipos++;
    }

    $stmt = $pdo->prepare("INSERT INTO partidos (torneo_id, equipo_local_id, equipo_visitante_id, jornada) VALUES (?, ?, ?, NULL)");

    for ($i = 0; $i < $numEquipos; $i += 2) {
        $a = $equipos[$i];
        $b = $equipos[$i + 1];
        if ($a !== null && $b !== null) {
            $stmt->execute([$torneo_id, $a, $b]);
            if ($ida_vuelta) {
                $stmt->execute([$torneo_id, $b, $a]);
            }
        }
    }
}

switch ($modo) {
    case 'liga':
        generarLiga($pdo, $torneo_id, $equipos, $ida_vuelta);
        break;
    case 'eliminatorias':
        generarEliminatorias($pdo, $torneo_id, $equipos, $ida_vuelta);
        break;
    case 'ambos':
        generarLiga($pdo, $torneo_id, $equipos, $ida_vuelta);
        generarEliminatorias($pdo, $torneo_id, $equipos, $ida_vuelta);
        break;
    default:
        echo "Modo no válido.";
        exit;
}

header("Location: ver_torneo.php?id=" . $torneo_id);
exit;
