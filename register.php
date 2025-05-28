<?php
session_start();
require 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (!$username) $errors[] = "El usuario es obligatorio.";
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email inválido.";
    if (strlen($password) < 6) $errors[] = "La contraseña debe tener al menos 6 caracteres.";
    if ($password !== $password2) $errors[] = "Las contraseñas no coinciden.";

    if (empty($errors)) {
        // Comprobar si usuario o email existen
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = "Usuario o email ya registrado.";
        } else {
            // Insertar usuario
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hash]);

            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;

            header('Location: index.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Registro - MiTorneoApp</title>
<style>
  body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; }
  form { background: #f7f7f7; padding: 20px; border-radius: 8px; }
  input { width: 100%; padding: 8px; margin-bottom: 12px; border: 1px solid #ccc; border-radius: 4px; }
  button { width: 100%; padding: 10px; background: #1e90ff; border: none; color: white; font-weight: bold; cursor: pointer; }
  button:hover { background: #0f6cd7; }
  .errors { color: red; margin-bottom: 10px; }
  a { font-size: 0.9rem; display: block; margin-top: 10px; text-align: center; }
</style>
</head>
<body>

<h2>Registro</h2>

<?php if ($errors): ?>
  <div class="errors">
    <ul>
      <?php foreach ($errors as $e): ?>
        <li><?=htmlspecialchars($e)?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="register.php" novalidate>
  <input type="text" name="username" placeholder="Usuario" required value="<?=htmlspecialchars($_POST['username'] ?? '')?>" />
  <input type="email" name="email" placeholder="Correo electrónico" required value="<?=htmlspecialchars($_POST['email'] ?? '')?>" />
  <input type="password" name="password" placeholder="Contraseña" required />
  <input type="password" name="password2" placeholder="Repetir contraseña" required />
  <button type="submit">Registrarse</button>
</form>

<a href="login.php">¿Ya tienes cuenta? Inicia sesión</a>

</body>
</html>
