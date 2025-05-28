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
include 'header.php';
?>

<style>
  body {
  font-family: 'Segoe UI', sans-serif;
  background: #f4f6f8;
  margin: 0;
  padding: 0;
  line-height: 1.6;
}

a {
  text-decoration: none;
  color: inherit;
}
#mis-torneos {
  background: white;
  padding: 2rem;
  border-radius: 10px;
  box-shadow: 0 3px 12px rgb(0 0 0 / 0.05);
  max-width: 1100px;
  margin: 20px auto 4rem;
}

#mis-torneos h2 {
  color: #1e90ff;
  font-weight: 700;
  font-size: 2rem;
  margin-bottom: 1.5rem;
  text-align: center;
}

#mis-torneos form {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

#mis-torneos form input[type="text"] {
  padding: 0.5rem 1rem;
  border: 1.5px solid #ddd;
  border-radius: 25px;
  font-size: 1rem;
  width: 250px;
  transition: border-color 0.3s;
}

#mis-torneos form input[type="text"]:focus {
  outline: none;
  border-color: #1e90ff;
}

#mis-torneos form button {
  background: #ff6f61;
  color: white;
  border: none;
  padding: 0.55rem 1.5rem;
  border-radius: 25px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.3s;
}

#mis-torneos form button:hover {
  background: #ff3b2f;
}

#mis-torneos table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

#mis-torneos thead {
  background: #1e90ff;
  color: white;
}

#mis-torneos thead th {
  padding: 0.75rem 1rem;
  font-weight: 600;
}

#mis-torneos tbody tr:nth-child(even) {
  background: #f9f9f9;
}

#mis-torneos tbody td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #eee;
  vertical-align: middle;
  font-size: 0.95rem;
  color: #333;
}

#mis-torneos tbody td a {
  color: #1e90ff;
  text-decoration: none;
  font-weight: 600;
  margin: 0 0.3rem;
  transition: color 0.3s;
}

#mis-torneos tbody td a:hover {
  color: #ff6f61;
}

#mis-torneos p {
  text-align: center;
  color: #666;
  font-style: italic;
  margin-top: 1rem;
}
</style>
<section id="mis-torneos">
    <h2>Mis torneos</h2>

    <form method="GET" action="#mis-torneos" aria-label="Buscar torneos por ciudad">
      <input type="text" name="ciudad" placeholder="Buscar torneos por ciudad" value="<?=htmlspecialchars($buscarCiudad)?>" />
      <button type="submit">Buscar</button>
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

<?php include 'footer.php'; ?>
