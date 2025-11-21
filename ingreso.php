<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ingresar.html");
    exit();
}

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Leer los productos de la base de datos
$productos = [];
$sql = "SELECT id_producto, codigo, nombre FROM productos WHERE activo = 1";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $productos[$row['id_producto']] = ['codigo' => $row['codigo'], 'nombre' => $row['nombre']];
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'];  // El código del producto
    $nombre = $_POST['nombre'];  // Nombre del producto
    $cantidad = $_POST['cantidad'];  // Cantidad de productos ingresados
    $fecha = $_POST['fecha'];  // Fecha de ingreso

    // Validar que el producto exista en la base de datos
    $sql_producto = "SELECT id_producto FROM productos WHERE codigo = '$codigo' AND nombre = '$nombre' LIMIT 1";
    $result_producto = $conn->query($sql_producto);

    if ($result_producto->num_rows == 0) {
        $_SESSION['error'] = "Producto no encontrado en la base de datos.";
        header("Location: ingreso.php");
        exit();
    }

    // Obtener el ID del producto
    $row_producto = $result_producto->fetch_assoc();
    $id_producto = $row_producto['id_producto'];

    // Insertar el ingreso en la base de datos
    $sql_ingreso = "INSERT INTO ingresos (id_producto, id_usuario, cantidad, fecha) 
                    VALUES ('$id_producto', '{$_SESSION['usuario_id']}', '$cantidad', '$fecha')";

    if ($conn->query($sql_ingreso) === TRUE) {
        $_SESSION['mensaje'] = "Ingreso registrado exitosamente.";
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
    }
    header("Location: ingreso.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingreso de Productos</title>
    <link rel="stylesheet" href="styles/ingreso.css">
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
            <h2>Registrar Producto</h2>
            <form method="POST" action="ingreso.php" id="formIngreso">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <input type="text" name="codigo" placeholder="Código (ej: JAG001)" 
                       pattern="[A-Z0-9]{3,10}" title="3-10 caracteres: A-Z y 0-9" 
                       maxlength="10" required>
                
                <select name="nombre" required>
                    <option value="">Seleccione el producto</option>
                    <?php foreach ($productos as $id => $producto): ?>
                        <option value="<?php echo htmlspecialchars($producto['nombre']); ?>">
                            <?php echo htmlspecialchars($producto['nombre']); ?> (<?php echo htmlspecialchars($producto['codigo']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="number" name="cantidad" id="cantidadInput" placeholder="Cantidad" min="1" max="10000" step="1" required>
                <input type="date" name="fecha" max="<?php echo date('Y-m-d'); ?>" required>
                
                <div class="boton-group">
                    <button class="boton" type="submit">Registrar</button>
                    <a href="generar_reporte_ingresos.php" class="boton">Generar Reporte</a>
                </div>
            </form>
        </div>

        <div class="tabla">
            <h3>Buscar Producto</h3>
            <input type="text" id="buscar" class="buscar" placeholder="Buscar...">
            
            <table id="tablaProductos">
                <thead>
                    <tr><th>Código</th><th>Nombre</th><th>Cantidad</th><th>Fecha</th></tr>
                </thead>
                <tbody>
                    <?php
                    // Mostrar los productos registrados
                    $sql = "SELECT p.codigo, p.nombre, i.cantidad, i.fecha 
                            FROM ingresos i 
                            JOIN productos p ON i.id_producto = p.id_producto 
                            ORDER BY i.fecha DESC";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['codigo'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['cantidad'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['fecha'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No hay ingresos registrados.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Validación de cantidad
        function validarCantidad(input) {
            const valor = parseFloat(input.value);
            if (valor <= 0 || isNaN(valor)) {
                input.value = '';
                alert('❌ La cantidad debe ser mayor a 0');
                return false;
            }
            if (valor % 1 !== 0) input.value = Math.floor(valor);
            if (valor > 10000) input.value = 10000;
            return true;
        }
        
        // Prevenir números negativos
        function prevenirNegativos(e) {
            if (e.key === '-' || e.key === 'e' || e.key === '+' || e.key === '.') {
                e.preventDefault();
            }
        }
        
        // Aplicar validaciones
        ['cantidadInput'].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('keydown', prevenirNegativos);
                input.addEventListener('change', function() { validarCantidad(this); });
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }
        });
        
        // Búsqueda en tiempo real
        document.getElementById("buscar").addEventListener("keyup", function() {
            const valor = this.value.toLowerCase();
            document.querySelectorAll("#tablaProductos tbody tr").forEach(fila => {
                fila.style.display = fila.textContent.toLowerCase().includes(valor) ? "" : "none";
            });
        });
    </script>
</body>
</html>