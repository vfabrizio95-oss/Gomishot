<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado, si no lo está, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: ingresar.html");
    exit();
}

// Registrar logout
$usuario = $_SESSION['usuario'] ?? 'Desconocido'; // Si no hay usuario en sesión, usar 'Desconocido'
$log_dir = __DIR__ . '/logs/';

// Verificar si el directorio de logs existe
if (file_exists($log_dir)) {
    $log_message = date('Y-m-d H:i:s') . " - Logout: $usuario\n";
    @file_put_contents($log_dir . 'sistema.log', $log_message, FILE_APPEND);
}

// Destruir sesión
session_unset();  // Elimina todas las variables de sesión
session_destroy(); // Destruye la sesión en el servidor

// Destruir cookie de sesión (si existe)
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/'); // Establece una cookie con un tiempo en el pasado
}

// Redirigir al usuario al login
header("Location: ingresar.html");
exit();
?>