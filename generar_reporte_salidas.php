<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ingresar.html");
    exit();
}

// Incluir la conexión a la base de datos
include 'config.php';

// Preparar el archivo CSV para exportación
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Reporte_Salidas_' . date('Y-m-d') . '.csv"');

// Emitir BOM para asegurar que el archivo se abra bien en Excel (con soporte UTF-8)
echo "\xEF\xBB\xBF";

// Consultar las salidas desde la base de datos
$sql = "SELECT p.codigo, p.nombre, s.cantidad, s.fecha, u.username
        FROM salidas s
        JOIN productos p ON s.id_producto = p.id_producto
        JOIN usuarios u ON s.id_usuario = u.id_usuario
        ORDER BY s.fecha DESC";

$result = $conn->query($sql);

// Escribir los encabezados del archivo CSV
echo "Código,Producto,Cantidad,Fecha,Usuario\n";

// Escribir los datos de las salidas
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "{$row['codigo']},{$row['nombre']},{$row['cantidad']},{$row['fecha']},{$row['username']}\n";
    }
} else {
    $_SESSION['error'] = "No hay datos para generar el reporte.";
    header("Location: salidas.php");
    exit();
}

exit();
?>