<?php
// db.php - conexión PDO para XAMPP

$host = 'localhost';
$db   = 'proleague';   // Cambia por el nombre real de tu BD
$user = 'root';
$pass = '';           // En XAMPP la contraseña por defecto es vacía
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mostrar errores
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Resultado como array asociativo
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Preparar sentencias reales
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
     echo "Error al conectar a la base de datos: " . $e->getMessage();
     exit;
}
