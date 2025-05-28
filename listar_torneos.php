<?php
// listar_torneos.php

$archivo = 'torneos.json';
$torneos = [];

if (file_exists($archivo)) {
    $contenido = file_get_contents($archivo);
    $torneos = json_decode($contenido, true) ?? [];
}

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
  <title>Torneos guardados - MiTorneoApp</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      max-width: 900px;
      margin: 2rem auto;
      padding: 0 1rem;
      background: #f5f8fa;
      color: #222;
    }
    h1 {
      text-align: center;
      color: #1e90ff;
      margin-bottom: 2rem;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
    }
    th, td {
      padding: 12px 15px;
      border-bottom: 1px solid #ddd;
      text-align: center;
    }
    th {
      background: #1e90ff;
      color: white;
    }
    tr:hover {
      background: #f1f9ff;
    }
    a.btn {
      display: inline-block;
      padding: 6px 12px;
      margin: 0 2px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.9rem;
      cursor: pointer;
      user-select: none;
      transition: background 0.3s;
    }
    a.ver {
      background: #4caf50;
      color: white;
    }
    a.ver:hover {
      background: #3a8d3a;
    }
    a.editar {
      background: #ff9800;
      color: white;
    }
    a.editar:hover {
      background: #cc7a00;
    }
    a.borrar {
      background: #f44336;
      color: white;
    }
    a.borrar:hover {
      background: #b71c1c;
    }
    a.crear {
      display: block;
      width: max-content;
      margin: 1.5rem auto;
      background: #2196f3;
      color: white;
      padding: 12px 24px;
      border-radius: 30px;
      font-weight: 700;
      text-align: center;
      text-decoration: none;
    }
    a.crear:hover {
      background: #1769aa;
    }
  </style>
</head>
<body>

  <h1>Torneos guardados</h1>

  <?php if (empty($torneos)): ?>
    <p style="text-align:center; font-weight:700; color:#666;">No hay torneos creados aún.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Equipos</th>
          <th>Formato</th>
          <th>Fechas</th>
          <th>Creado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($torneos as $index => $torneo): ?>
          <tr>
            <td><?=htmlspecialchars($torneo['nombre'])?></td>
            <td><?=count($torneo['equipos'])?></td>
            <td><?=resumenFormato($torneo['formato'])?></td>
            <td><?=htmlspecialchars($torneo['fecha_inicio'])." a ".htmlspecialchars($torneo['fecha_fin'])?></td>
            <td><?=htmlspecialchars($torneo['fecha_creacion'])?></td>
            <td>
              <a href="ver_torneo.php?id=<?=$index?>" class="btn ver">Ver</a>
              <a href="editar_torneo.php?id=<?=$index?>" class="btn editar">Editar</a>
              <a href="borrar_torneo.php?id=<?=$index?>" class="btn borrar" onclick="return confirm('¿Seguro que quieres borrar este torneo?')">Borrar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <a href="crear_torneo.php" class="crear">+ Crear nuevo torneo</a>

</body>
</html>
