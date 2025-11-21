<?php
session_start();

<<<<<<< HEAD
if (!isset($_SESSION['usuario_id'])) {
=======
if (!isset($_SESSION['usuario'])) {
>>>>>>> abd3fcbfb9c7915689a61aa268e232dc15868d40
    header("Location: ingresar.html");
    exit();
}

<<<<<<< HEAD
// Verificar si el método es GET
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
=======
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
>>>>>>> abd3fcbfb9c7915689a61aa268e232dc15868d40
    $_SESSION['error'] = "Método inválido";
    header("Location: ingreso.php");
    exit();
}

<<<<<<< HEAD
// Obtener los datos de la URL (con GET)
$codigo = trim($_GET['codigo'] ?? '');
$nombre = trim($_GET['nombre'] ?? '');
$cantidad = $_GET['cantidad'] ?? '';
$fecha = trim($_GET['fecha'] ?? '');

$errores = [];

// Validar el código del producto
=======
$codigo = trim($_POST['codigo'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$cantidad = $_POST['cantidad'] ?? '';
$fecha = trim($_POST['fecha'] ?? '');

$errores = [];

>>>>>>> abd3fcbfb9c7915689a61aa268e232dc15868d40
if (empty($codigo) || !preg_match('/^[A-Z0-9]{3,10}$/', $codigo)) {
    $errores[] = "Código inválido";
}

<<<<<<< HEAD
// Validar el nombre del producto
=======
>>>>>>> abd3fcbfb9c7915689a61aa268e232dc15868d40
$productos_permitidos = ['Jagger', 'Whisky Sour', 'Algarrobina', 'Apple Drunk', 'Cuba Libre'];
if (empty($nombre) || !in_array($nombre, $productos_permitidos)) {
    $errores[] = "Producto inválido";
}

<<<<<<< HEAD
// Validar la cantidad
=======
>>>>>>> abd3fcbfb9c7915689a61aa268e232dc15868d40
if (!is_numeric($cantidad) || (int)$cantidad <= 0 || (int)$cantidad > 10000) {
    $errores[] = "Cantidad debe ser positiva (1-10000)";
}

<<<<<<< HEAD
// Validar la fecha
=======
>>>>>>> abd3fcbfb9c7915689a61aa268e232dc15868d40
$fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha);
if (!$fecha_obj || $fecha_obj->format('Y-m-d') !== $fecha) {
    $errores[] = "Fecha inválida";
}

<<<<<<< HEAD
// Si hay errores, redirigir con el mensaje de error
=======
>>>>>>> abd3fcbfb9c7915689a61aa268e232dc15868d40
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

<<<<<<< HEAD
// Verificar si el producto existe en la base de datos
=======
// Verificar si el producto existe
>>>>>>> abd3fcbfb9c7915689a61aa268e232dc15868d40
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

<<<<<<< HEAD
// Obtener el ID del producto
=======
>>>>>>> abd3fcbfb9c7915689a61aa268e232dc15868d40
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