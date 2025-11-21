<?php
/**
 * GOMISHOT 2.0 - Login Simplificado (SIN HASH)
 * ATENCIÓN: Usar solo para pruebas
 */

session_start();

// Obtener datos (ahora usando $_GET en lugar de $_POST)
$usuario = isset($_GET['usuario']) ? trim($_GET['usuario']) : '';
$contrasena = isset($_GET['contrasena']) ? trim($_GET['contrasena']) : '';

// Validar vacíos
if (empty($usuario) || empty($contrasena)) {
    header("Location: ingresar.html?error=campos_vacios");
    exit();
}

// Usuarios simples (SIN HASH para prueba)
$usuarios_validos = array(
    'admin' => '1234',
    'user' => '2025'
);

// Verificar usuario
if (!isset($usuarios_validos[$usuario])) {
    header("Location: ingresar.html?error=credenciales_incorrectas");
    exit();
}

// Verificar contraseña (comparación directa)
if ($contrasena === $usuarios_validos[$usuario]) {
    // LOGIN EXITOSO
    session_regenerate_id(true);

    // Obtener el ID del usuario desde la base de datos (simulado para este caso)
    include 'config.php'; // Conexión a la base de datos

    $sql = "SELECT id_usuario FROM usuarios WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['usuario_id'] = $row['id_usuario'];  // Almacenar el ID del usuario en la sesión
    } else {
        $_SESSION['error'] = "Usuario no encontrado en la base de datos.";
        header("Location: ingresar.html");
        exit();
    }

    $_SESSION['usuario'] = htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8');
    $_SESSION['login_time'] = time();
    $_SESSION['ultimo_acceso'] = time();

    // Log
    $log_dir = __DIR__ . '/logs/';
    if (file_exists($log_dir)) {
        $log = date('Y-m-d H:i:s') . " - Login exitoso: $usuario\n";
        @file_put_contents($log_dir . 'sistema.log', $log, FILE_APPEND);
    }

    header("Location: inventario.php");
    exit();

} else {
    // CONTRASEÑA INCORRECTA
    $log_dir = __DIR__ . '/logs/';
    if (file_exists($log_dir)) {
        $log = date('Y-m-d H:i:s') . " - Login fallido: $usuario\n";
        @file_put_contents($log_dir . 'sistema.log', $log, FILE_APPEND);
    }

    header("Location: ingresar.html?error=credenciales_incorrectas");
    exit();
}
?>