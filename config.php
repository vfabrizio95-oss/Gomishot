<?php
$servername = "localhost";    // Dirección del servidor MySQL
$username = "root";           // Tu usuario de MySQL
$password = "";               // Tu contraseña de MySQL (si tiene)
$dbname = "gomishot2";        // Nombre de la base de datos que creaste

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
