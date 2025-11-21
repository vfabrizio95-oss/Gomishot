<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ingresar.html");
    exit();
}

// Verificar si el método es GET
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    $_SESSION['error'] = "Método inválido";
    header("Location: ingreso.php");
    exit();
}

// Obtener los datos de la URL (con GET)
$codigo = trim($_GET['codigo'] ?? '');
$nombre = trim($_GET['nombre'] ?? '');
$cantidad = $_GET['cantidad'] ?? '';
$fecha = trim($_GET['fecha'] ?? '');

$errores = [];

// Validar el código del producto
if (empty($codigo) || !preg_match('/^[A-Z0-9]{3,10}$/', $codigo)) {
    $errores[] = "Código inválido";
}

// Validar el nombre del producto
$productos_permitidos = ['Jagger', 'Whisky Sour', 'Algarrobina', 'Apple Drunk', 'Cuba Libre'];
if (empty($nombre) || !in_array($nombre, $productos_permitidos)) {
    $errores[] = "Producto inválido";
}

// Validar la cantidad
if (!is_numeric($cantidad) || (int)$cantidad <= 0 || (int)$cantidad > 10000) {
    $errores[] = "Cantidad debe ser positiva (1-10000)";
}

// Validar la fecha
$fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha);
if (!$fecha_obj || $fecha_obj->format('Y-m-d') !== $fecha) {
    $errores[] = "Fecha inválida";
}

// Si hay errores, redirigir con el mensaje de error
if (!empty($errores)) {
    $_SESSION['error'] = implode(". ", $errores);
    header("Location: ingreso.php");
    exit();
}

$cantidad = abs((int)$cantidad);
if ($cantidad <= 0) {
    $_SESSION['error'] = "Cantidad inválida";
    header("Location: ingreso.php");
    exit();
}

// Conexión a la base de datos
include 'config.php';

// Verificar si el producto existe en la base de datos
$sql = "SELECT id_producto FROM productos WHERE codigo = ? AND nombre = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $codigo, $nombre);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    $_SESSION['error'] = "Producto no encontrado en la base de datos.";
    header("Location: ingreso.php");
    exit();
}

// Obtener el ID del producto
$row = $result->fetch_assoc();
$id_producto = $row['id_producto'];

// Registrar el ingreso en la base de datos
$sql_ingreso = "INSERT INTO ingresos (id_producto, id_usuario, cantidad, fecha) 
                VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql_ingreso);
$stmt->bind_param("iiis", $id_producto, $_SESSION['usuario_id'], $cantidad, $fecha);

if ($stmt->execute()) {
    $_SESSION['mensaje'] = "Producto registrado: $nombre - $cantidad unidades";
} else {
    $_SESSION['error'] = "Error al registrar el ingreso.";
}

header("Location: ingreso.php");
exit();
?>