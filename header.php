<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_logged_in = isset($_SESSION['user_id']);
?>

<header>
  <div class="logo">MiTorneoApp</div>
  <nav>
    <a href="./inicio">Inicio</a>
    <?php if ($user_logged_in): ?>
      <a href="./mis_torneos.php">Mis torneos</a>
      <a href="./crear_torneo.php">Crear torneo</a>
      <a href="logout.php" style="margin-left:20px;">Cerrar sesión</a>
    <?php else: ?>
      <a href="#pasos">Cómo funciona</a>
      <a href="login.php" style="margin-left:20px;">Iniciar sesión</a>
    <?php endif; ?>
  </nav>
</header>

<style>
  header {
    background: #1e90ff;
    color: white;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
  }

  header .logo {
    font-weight: 700;
    font-size: 1.5rem;
    letter-spacing: 1px;
  }

  nav {
    display: flex;
    gap: 1.2rem;
    flex-wrap: wrap;
    align-items: center;
  }

  nav a {
    color: white;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s;
  }

  nav a:hover {
    color: #cce6ff;
  }

  @media (max-width: 600px) {
    header {
      flex-direction: column;
      align-items: flex-start;
    }

    nav {
      flex-direction: column;
      gap: 0.5rem;
      margin-top: 0.5rem;
    }
  }
</style>
