<?php
session_start();
require 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username) $errors[] = "El usuario es obligatorio.";
    if (!$password) $errors[] = "La contraseña es obligatoria.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Usuario o contraseña incorrectos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Login - MiTorneoApp</title>
<link rel="stylesheet" href="css/style.css">

</head>
<body>

<h2>Iniciar sesión</h2>

<?php if ($errors): ?>
  <div class="errors">
    <ul>
      <?php foreach ($errors as $e): ?>
        <li><?=htmlspecialchars($e)?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="login.php" novalidate>
  <input type="text" name="username" placeholder="Usuario" required value="<?=htmlspecialchars($_POST['username'] ?? '')?>" />
  <input type="password" name="password" placeholder="Contraseña" required />
  <button type="submit">Entrar</button>
</form>

<a href="register.php">¿No tienes cuenta? Regístrate</a>

</body>
</html>
