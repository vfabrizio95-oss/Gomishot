<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ingresar.html");
    exit();
}

<<<<<<< HEAD
// Asegurarse de que el método sea GET
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
=======
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
>>>>>>> abd3fcbfb9c7915689a61aa268e232dc15868d40
    header("Location: ingreso.php");
    exit();
}

<<<<<<< HEAD
// Obtener los datos de la URL (con GET)
$codigo = $_GET["codigo"] ?? '';
$nueva_cantidad = $_GET["cantidad_modificada"] ?? '';
$justificacion = trim($_GET["justificacion"] ?? '');

// Validar la nueva cantidad
=======
$codigo = $_POST["codigo"] ?? '';
$nueva_cantidad = $_POST["cantidad_modificada"] ?? '';
$justificacion = trim($_POST["justificacion"] ?? '');

>>>>>>> abd3fcbfb9c7915689a61aa268e232dc15868d40
if (!is_numeric($nueva_cantidad) || (int)$nueva_cantidad <= 0 || (int)$nueva_cantidad > 10000) {
    $_SESSION['error'] = "Cantidad inválida";
    header("Location: ingreso.php");
    exit();
}

// Conexión a la base de datos
include 'config.php';

// Verificar si el producto existe
$sql_producto = "SELECT id_producto FROM productos WHERE codigo = ? LIMIT 1";
$stmt = $conn->prepare($sql_producto);
$stmt->bind_param("s", $codigo);
$stmt->execute();
$result_producto = $stmt->get_result();

if ($result_producto->num_rows == 0) {
    $_SESSION['error'] = "Código inválido";
    header("Location: ingreso.php");
    exit();
}

// Obtener el ID del producto
$row_producto = $result_producto->fetch_assoc();
$id_producto = $row_producto['id_producto'];

// Verificar si el ingreso existe
$sql_ingreso = "SELECT * FROM ingresos WHERE id_producto = ? AND cantidad > 0 ORDER BY fecha DESC LIMIT 1";
$stmt = $conn->prepare($sql_ingreso);
$stmt->bind_param("i", $id_producto);
$stmt->execute();
$result_ingreso = $stmt->get_result();

if ($result_ingreso->num_rows == 0) {
    $_SESSION['error'] = "No se encontraron registros para este código de producto.";
    header("Location: ingreso.php");
    exit();
}

// Actualizar la cantidad en la base de datos
$sql_update = "UPDATE ingresos SET cantidad = ? WHERE id_producto = ? AND cantidad > 0 ORDER BY fecha DESC LIMIT 1";
$stmt = $conn->prepare($sql_update);
$stmt->bind_param("ii", $nueva_cantidad, $id_producto);

if ($stmt->execute()) {
    $_SESSION['mensaje'] = "Ingreso modificado. Justificación: $justificacion";
} else {
    $_SESSION['error'] = "Error al modificar el ingreso.";
}

header("Location: ingreso.php");
exit();
?>