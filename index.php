<?php
session_start();
require 'db.php';

function resumenFormato($formato) {
    switch ($formato) {
        case 'liga': return 'Liga';
        case 'eliminatorias': return 'Eliminatorias';
        case 'ambos': return 'Liga + Eliminatorias';
        default: return 'Desconocido';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Organiza Torneos de Fútbol Fácil | MiTorneoApp</title>
<link rel="stylesheet" href="css/style.css">

</head>
<body>
<?php include 'header.php'; ?>
<main>

<?php
// Si el usuario está logueado mostramos la lista de torneos y buscador
if (isset($_SESSION['user_id'])) {
    $usuario_id = $_SESSION['user_id'];
    $buscarCiudad = $_GET['ciudad'] ?? '';

    $sql = "SELECT * FROM torneos WHERE usuario_id = ?";
    $params = [$usuario_id];

    if ($buscarCiudad) {
        $sql .= " AND ciudad LIKE ?";
        $params[] = "%$buscarCiudad%";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $torneos = $stmt->fetchAll();
?>



  <section class="hero" id="inicio">
    <div class="hero-text">
      <h1>Organiza torneos de fútbol sin complicaciones</h1>
      <p>Con nuestra herramienta, controla horarios, clasificaciones y cruces en un solo lugar.<br>¡Todo fácil, rápido y profesional!</p>
      <a href="#crear" class="btn-primary">Crear mi torneo</a>
    </div>
    <div class="hero-image">
      <img src="https://images.unsplash.com/photo-1502904550040-1e50dbecf4e9?auto=format&fit=crop&w=600&q=80" alt="Fútbol amistoso" />
    </div>
  </section>

  <section class="benefits" id="beneficios">
    <h2>¿Por qué usar nuestra herramienta?</h2>
    <div class="benefits-list">
      <div class="benefit-item">
        <svg viewBox="0 0 24 24"><path d="M12 2L2 7v7c0 5 10 9 10 9s10-4 10-9V7l-10-5z"/></svg>
        <p>Organización clara y sin errores</p>
      </div>
      <div class="benefit-item">
        <svg viewBox="0 0 24 24"><path d="M12 22c5-3 9-10 9-15S17 2 12 2 3 7 3 12s4 10 9 10z"/></svg>
        <p>Ahorro de tiempo en cálculos y cruces</p>
      </div>
      <div class="benefit-item">
        <svg viewBox="0 0 24 24"><path d="M12 1C6 1 1 5 1 11s5 10 11 10 11-4 11-10S18 1 12 1z"/></svg>
        <p>Ideal para compartir con los equipos</p>
      </div>
    </div>
  </section>

  <section class="steps" id="pasos">
    <h2>Guía rápida para organizar tu torneo</h2>
    <div class="step-list">
      <div class="step-item">
        <h3>✅ Paso 1: Define el formato del torneo</h3>
        <p>¿Cuántos equipos? ¿Liga o eliminatorias? ¿Mixto o categorías? Si tienes pocos equipos (4-6), haz liga y final. Si son muchos (12-20), grupos + eliminatorias.</p>
      </div>
      <div class="step-item">
        <h3>📅 Paso 2: Establece fechas y lugar</h3>
        <p>Define días y horas, reserva campos y avisa con tiempo. Evita sobrecargar jornadas: 2-3 partidos por día, 2-3 jornadas por semana.</p>
      </div>
      <div class="step-item">
        <h3>📝 Paso 3: Crea el torneo en la app</h3>
        <p>Introduce nombre, formato, equipos y deja que la app genere calendario, puntos y cruces automáticamente.</p>
      </div>
      <div class="step-item">
        <h3>⚽ Paso 4: Anota resultados en vivo</h3>
        <p>Registra resultados, goleadores, tarjetas y la app actualizará clasificaciones y fases automáticamente.</p>
      </div>
      <div class="step-item">
        <h3>🏅 Paso 5: Disfruta el cierre</h3>
        <p>Descarga el resumen, comparte la tabla final y prepárate para la próxima edición reutilizando equipos y estructuras.</p>
      </div>
    </div>
  </section>

  <section id="crear" style="text-align:center; margin-bottom: 4rem;">
    <a href="./crear_torneo.php" class="btn-primary" style="font-size:1.3rem; padding: 1rem 3rem;">¡Comenzar a crear mi torneo!</a>
  </section>

  <section id="mis-torneos">
    <h2>Mis torneos</h2>

    <form method="GET" id="search" action="#mis-torneos" aria-label="Buscar torneos por ciudad">
      <input type="text" name="ciudad" placeholder="Buscar torneos por ciudad" value="<?=htmlspecialchars($buscarCiudad)?>" />
      <button class="btn-primary search" type="submit">Buscar</button>
    </form>

    <?php if (!$torneos): ?>
      <p>No tienes torneos creados.</p>
    <?php else: ?>
      <table aria-describedby="mis-torneos">
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
          <?php foreach ($torneos as $torneo): ?>
            <tr>
              <td><?=htmlspecialchars($torneo['nombre'])?></td>
              <td><?=resumenFormato($torneo['formato'])?></td>
              <td><?=htmlspecialchars($torneo['categoria'])?></td>
              <td><?=htmlspecialchars($torneo['subcategoria'] ?? '')?></td>
              <td><?=htmlspecialchars($torneo['ciudad'])?></td>
              <td><?=htmlspecialchars($torneo['fecha_inicio'])?> - <?=htmlspecialchars($torneo['fecha_fin'])?></td>
              <td>
                <a href="ver_torneo.php?id=<?= $torneo['id'] ?>">Ver</a> |
                <a href="editar_torneo.php?id=<?= $torneo['id'] ?>">Editar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

  </section>

<?php
} else {
  // Usuario no logueado: mostramos la landing pública original, sin lista ni búsqueda
  ?>

    <section class="hero" id="inicio">
      <div class="hero-text">
        <h1>Organiza torneos de fútbol sin complicaciones</h1>
        <p>Con nuestra herramienta, controla horarios, clasificaciones y cruces en un solo lugar.<br>¡Todo fácil, rápido y profesional!</p>
        <a href="#crear" class="btn-primary">Crear mi torneo</a>
      </div>
      <div class="hero-image">
        <img src="https://images.unsplash.com/photo-1502904550040-1e50dbecf4e9?auto=format&fit=crop&w=600&q=80" alt="Fútbol amistoso" />
      </div>
    </section>

    <section class="benefits" id="beneficios">
      <h2>¿Por qué usar nuestra herramienta?</h2>
      <div class="benefits-list">
        <div class="benefit-item">
          <svg viewBox="0 0 24 24"><path d="M12 2L2 7v7c0 5 10 9 10 9s10-4 10-9V7l-10-5z"/></svg>
          <p>Organización clara y sin errores</p>
        </div>
        <div class="benefit-item">
          <svg viewBox="0 0 24 24"><path d="M12 22c5-3 9-10 9-15S17 2 12 2 3 7 3 12s4 10 9 10z"/></svg>
          <p>Ahorro de tiempo en cálculos y cruces</p>
        </div>
        <div class="benefit-item">
          <svg viewBox="0 0 24 24"><path d="M12 1C6 1 1 5 1 11s5 10 11 10 11-4 11-10S18 1 12 1z"/></svg>
          <p>Ideal para compartir con los equipos</p>
        </div>
      </div>
    </section>

    <section class="steps" id="pasos">
      <h2>Guía rápida para organizar tu torneo</h2>
      <div class="step-list">
        <div class="step-item">
          <h3>✅ Paso 1: Define el formato del torneo</h3>
          <p>¿Cuántos equipos? ¿Liga o eliminatorias? ¿Mixto o categorías? Si tienes pocos equipos (4-6), haz liga y final. Si son muchos (12-20), grupos + eliminatorias.</p>
        </div>
        <div class="step-item">
          <h3>📅 Paso 2: Establece fechas y lugar</h3>
          <p>Define días y horas, reserva campos y avisa con tiempo. Evita sobrecargar jornadas: 2-3 partidos por día, 2-3 jornadas por semana.</p>
        </div>
        <div class="step-item">
          <h3>📝 Paso 3: Crea el torneo en la app</h3>
          <p>Introduce nombre, formato, equipos y deja que la app genere calendario, puntos y cruces automáticamente.</p>
        </div>
        <div class="step-item">
          <h3>⚽ Paso 4: Anota resultados en vivo</h3>
          <p>Registra resultados, goleadores, tarjetas y la app actualizará clasificaciones y fases automáticamente.</p>
        </div>
        <div class="step-item">
          <h3>🏅 Paso 5: Disfruta el cierre</h3>
          <p>Descarga el resumen, comparte la tabla final y prepárate para la próxima edición reutilizando equipos y estructuras.</p>
        </div>
      </div>
    </section>

    <section id="crear" style="text-align:center; margin-bottom: 4rem;">
      <a href="./crear_torneo.php" class="btn-primary" style="font-size:1.3rem; padding: 1rem 3rem;">¡Comenzar a crear mi torneo!</a>
    </section>


  <?php
}
?>

  </main>
<?php
include 'footer.php';
?>
  </body>
  </html>