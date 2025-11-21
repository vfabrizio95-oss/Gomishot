<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ingresar.html");
    exit();
}

// Incluir la conexión a la base de datos
include 'config.php';

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Leer productos y su stock desde la base de datos
$productos = [];
$sql = "SELECT id_producto, codigo, nombre FROM productos WHERE activo = 1";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $productos[$row['id_producto']] = [
        'codigo' => $row['codigo'],
        'nombre' => $row['nombre']
    ];
}

// Función para obtener el stock disponible de un producto
function obtenerStock($codigo, $conn) {
    // Obtener el stock de ingresos
    $sql_ingresos = "SELECT SUM(cantidad) AS stock_ingreso FROM ingresos WHERE id_producto = (SELECT id_producto FROM productos WHERE codigo = '$codigo')";
    $result_ingresos = $conn->query($sql_ingresos);
    $stock_ingreso = $result_ingresos->fetch_assoc()['stock_ingreso'];

    // Obtener el stock de salidas
    $sql_salidas = "SELECT SUM(cantidad) AS stock_salida FROM salidas WHERE id_producto = (SELECT id_producto FROM productos WHERE codigo = '$codigo')";
    $result_salidas = $conn->query($sql_salidas);
    $stock_salida = $result_salidas->fetch_assoc()['stock_salida'];

    return ($stock_ingreso - $stock_salida);
}

// Registrar salida
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["registrar"])) {
    $codigo = trim($_GET['codigo'] ?? '');
    $cantidad = $_GET['cantidad'] ?? '';
    $fecha = trim($_GET['fecha'] ?? '');

    // Verificar si el producto existe
    $sql_producto = "SELECT id_producto FROM productos WHERE codigo = '$codigo' LIMIT 1";
    $result_producto = $conn->query($sql_producto);
    if ($result_producto->num_rows == 0) {
        $_SESSION['error'] = "Código inválido";
        header("Location: salidas.php");
        exit();
    }

    // Obtener el stock actual
    $stock_actual = obtenerStock($codigo, $conn);
    
    // Validar cantidad
    $cantidad = (int)$cantidad;
    if ($cantidad <= 0) {
        $_SESSION['error'] = "Cantidad inválida";
        header("Location: salidas.php");
        exit();
    }

    if ($cantidad > $stock_actual) {
        $_SESSION['error'] = "Stock insuficiente. Disponible: $stock_actual";
        header("Location: salidas.php");
        exit();
    }

    // Obtener el ID del producto
    $row_producto = $result_producto->fetch_assoc();
    $id_producto = $row_producto['id_producto'];

    // Insertar la salida en la base de datos
    $sql_salida = "INSERT INTO salidas (id_producto, id_usuario, cantidad, fecha) 
                   VALUES ('$id_producto', '{$_SESSION['usuario_id']}', '$cantidad', '$fecha')";

    if ($conn->query($sql_salida) === TRUE) {
        $_SESSION['mensaje'] = "Salida registrada con éxito. Stock restante: " . ($stock_actual - $cantidad);
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
    }

    header("Location: salidas.php");
    exit();
}

// Descargar reporte
if (isset($_GET['descargar'])) {
    // Puedes incluir un reporte similar en formato CSV si lo necesitas
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Reporte_Salidas_' . date('Y-m-d') . '.csv"');

    $sql = "SELECT s.codigo, s.nombre, s.cantidad, s.fecha 
            FROM salidas s
            JOIN productos p ON s.id_producto = p.id_producto
            ORDER BY s.fecha DESC";
    $result = $conn->query($sql);

    echo "\xEF\xBB\xBF";  // Forzar UTF-8
    echo "Código,Producto,Cantidad,Fecha\n";
    while ($row = $result->fetch_assoc()) {
        echo "{$row['codigo']},{$row['nombre']},{$row['cantidad']},{$row['fecha']}\n";
    }
    exit();
}

$salidas = [];
$sql = "SELECT s.codigo, s.nombre, s.cantidad, s.fecha
        FROM salidas s
        JOIN productos p ON s.id_producto = p.id_producto
        ORDER BY s.fecha DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $salidas[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salidas de Producto</title>
    <link rel="stylesheet" href="styles/salida.css">
</head>
<body>
    <div class="top-bar">
        <div class="logo-text">Gomi<span class="highlight">Shots</span></div>
        <div>
            <a href="inventario.php" class="volver-link">Volver al Panel</a>
            <a href="logout.php" class="logout-link">Cerrar sesión</a>
        </div>
    </div>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div style="background:#004400; color:#00ff00; padding:15px; margin:20px; border-radius:5px; text-align:center;">
            ✅ <?php echo htmlspecialchars($_SESSION['mensaje'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['mensaje']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="background:#440000; color:#ff6666; padding:15px; margin:20px; border-radius:5px; text-align:center;">
            ❌ <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="panel">
        <div class="formulario">
            <h2>Registrar Salida</h2>
            <form method="GET" action="salidas.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <select name="codigo" id="selectProducto" required>
                    <option value="">Seleccione el producto</option>
                    <?php foreach ($productos as $id => $producto): ?>
                        <option value="<?php echo htmlspecialchars($producto['codigo']); ?>" data-stock="<?php echo obtenerStock($producto['codigo'], $conn); ?>">
                            <?php echo htmlspecialchars($producto['nombre']); ?> (<?php echo htmlspecialchars($producto['codigo']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <div id="stockInfo" style="display:none; background:#1a1a1a; padding:10px; margin:10px 0; border-left:3px solid #ffa500;">
                    <strong>Stock disponible:</strong> <span id="stockDisponible">0</span> unidades
                </div>
                
                <input type="number" name="cantidad" id="cantidadSalida" placeholder="Cantidad" min="1" required>
                
                <input type="date" name="fecha" max="<?php echo date('Y-m-d'); ?>" required>
                
                <div class="boton-group">
                    <button type="submit" name="registrar" class="boton">Registrar</button>
                    <a class="boton" href="?descargar=1">Generar Reporte</a>
                </div>
            </form>

            <h2>Salidas Registradas</h2>
            <table>
                <thead>
                    <tr><th>Código</th><th>Producto</th><th>Cantidad</th><th>Fecha</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($salidas)): ?>
                        <tr><td colspan="4">No hay salidas registradas</td></tr>
                    <?php else: ?>
                        <?php foreach ($salidas as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($row['cantidad']); ?></td>
                                <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('selectProducto').addEventListener('change', function() {
            const stockInfo = document.getElementById('stockInfo');
            const stockDisponible = document.getElementById('stockDisponible');
            const cantidadInput = document.getElementById('cantidadSalida');
            
            if (this.value) {
                const stock = this.options[this.selectedIndex].dataset.stock;
                stockDisponible.textContent = stock;
                stockInfo.style.display = 'block';
                cantidadInput.max = stock;
            } else {
                stockInfo.style.display = 'none';
                cantidadInput.removeAttribute('max');
            }
        });
        
        document.getElementById('cantidadSalida').addEventListener('input', function() {
            const max = parseInt(this.max);
            const valor = parseInt(this.value);
            
            if (max && valor > max) {
                this.value = max;
                alert('La cantidad no puede exceder el stock disponible (' + max + ')');
            }
        });
    </script>
</body>
</html>