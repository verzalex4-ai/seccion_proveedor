<?php
require_once '../config.php';
$conexion = conectarDB();

$sql_proveedores = "SELECT id, nombre FROM proveedores WHERE estado = 'Activo' ORDER BY nombre ASC";
$proveedores = $conexion->query($sql_proveedores);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_proveedor = intval($_POST['id_proveedor']);
    $fecha_emision = $_POST['fecha_emision'];
    $fecha_entrega_estimada = $_POST['fecha_entrega_estimada'];
    $observaciones = limpiarDatos($_POST['observaciones']);
    
    $year = date('Y');
    $sql_count = "SELECT COUNT(*) as total FROM ordenes_compra WHERE YEAR(fecha_emision) = $year";
    $result_count = $conexion->query($sql_count);
    $count = $result_count->fetch_assoc()['total'] + 1;
    $numero_orden = "OC-" . $year . "-" . str_pad($count, 3, '0', STR_PAD_LEFT);
    
    $subtotal = 0;
    $productos = $_POST['productos'];
    $cantidades = $_POST['cantidades'];
    $precios = $_POST['precios'];
    
    for ($i = 0; $i < count($productos); $i++) {
        if (!empty($productos[$i]) && !empty($cantidades[$i]) && !empty($precios[$i])) {
            $subtotal += floatval($cantidades[$i]) * floatval($precios[$i]);
        }
    }
    
    $impuestos = $subtotal * 0.21;
    $total = $subtotal + $impuestos;
    
    $sql = "INSERT INTO ordenes_compra (numero_orden, id_proveedor, fecha_emision, fecha_entrega_estimada, subtotal, impuestos, total, observaciones, estado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente')";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sissddds", $numero_orden, $id_proveedor, $fecha_emision, $fecha_entrega_estimada, $subtotal, $impuestos, $total, $observaciones);
    
    if ($stmt->execute()) {
        $id_orden = $conexion->insert_id;
        
        $sql_detalle = "INSERT INTO detalle_orden (id_orden, producto, cantidad, unidad_medida, precio_unitario) VALUES (?, ?, ?, ?, ?)";
        $stmt_detalle = $conexion->prepare($sql_detalle);
        
        for ($i = 0; $i < count($productos); $i++) {
            if (!empty($productos[$i]) && !empty($cantidades[$i]) && !empty($precios[$i])) {
                $producto = limpiarDatos($productos[$i]);
                $cantidad = intval($cantidades[$i]);
                $unidad = limpiarDatos($_POST['unidades'][$i]);
                $precio = floatval($precios[$i]);
                
                $stmt_detalle->bind_param("isisd", $id_orden, $producto, $cantidad, $unidad, $precio);
                $stmt_detalle->execute();
            }
        }
        
        $stmt_detalle->close();
        mostrarMensaje('Orden de compra creada exitosamente: ' . $numero_orden, 'success');
        header('Location: index.php');
        exit();
    } else {
        mostrarMensaje('Error al crear la orden: ' . $conexion->error, 'danger');
    }
    
    $stmt->close();
}

cerrarDB($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Orden de Compra</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
<style>
    /* ------------------------------------------- */
    /* ESTILOS DEL CONTENEDOR PRINCIPAL */
    /* ------------------------------------------- */
    .form-container {
        background-color: white;
        padding: 25px;
        border-radius: 0.35rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        
        /* CORRECCIÓN 1: Centrado y Margen Vertical */
        margin: 20px auto; 
        
        /* CORRECCIÓN 2: Ancho máximo de 1051px */
        width: 100%; 
        max-width: 1051px; 
    }

    .form-header {
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e3e6f0;
    }

    .form-header h2 {
        color: #5a5c69;
        font-size: 1.35rem;
        font-weight: 700;
        margin: 0;
    }

    /* ------------------------------------------- */
    /* ESTILOS DE GRUPOS Y CONTROLES (INPUTS) */
    /* ------------------------------------------- */
    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #5a5c69;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d3e2;
        border-radius: 0.35rem;
        font-size: 0.875rem;
        color: #5a5c69;
        font-family: 'Nunito', sans-serif;
        height: 38px;
        
        /* CORRECCIÓN 3: Manejo de Inputs */
        box-sizing: border-box; 
    }

    .form-control:focus {
        outline: none;
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
        height: auto; 
        box-sizing: border-box; 
    }

    /* ------------------------------------------- */
    /* ESTILOS DE SECCIÓN DE PRODUCTOS Y BOTONES */
    /* ------------------------------------------- */
    .productos-section {
        margin: 20px 0;
        padding: 20px;
        background-color: #f8f9fc;
        border-radius: 0.35rem;
    }

    .productos-section h3 {
        color: #4e73df;
        font-size: 1.1rem;
        margin-bottom: 15px;
    }

    .producto-item {
        display: grid;
        grid-template-columns: 3fr 1fr 1fr 1.5fr 50px; 
        gap: 10px;
        margin-bottom: 10px;
        
        /* CORRECCIÓN 4: Alineación del botón y inputs */
        align-items: center; 
    }

    .btn-remove {
        background-color: #e74a3b;
        color: white;
        border: none;
        padding: 8px 10px;
        border-radius: 0.35rem;
        cursor: pointer;
        font-size: 0.875rem;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-remove:hover {
        background-color: #d52a1a;
    }

    .btn-add {
        background-color: #1cc88a;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 0.35rem;
        cursor: pointer;
        font-size: 0.875rem;
        margin-top: 10px;
    }

    .btn-add:hover {
        background-color: #17a673;
    }

    /* ------------------------------------------- */
    /* ESTILOS DE TOTALES */
    /* ------------------------------------------- */
    .totales-box {
        background-color: #e7f3ff;
        padding: 15px;
        border-radius: 0.35rem;
        margin-top: 20px;
    }

    .totales-box .total-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .totales-box .total-row.final {
        font-size: 1.1rem;
        font-weight: bold;
        color: #4e73df;
        padding-top: 8px;
        border-top: 2px solid #4e73df;
    }

    /* ------------------------------------------- */
    /* ESTILOS DE ACCIONES Y MEDIA QUERY */
    /* ------------------------------------------- */
    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #e3e6f0;
    }

    @media (max-width: 768px) {
        .form-row, .producto-item {
            grid-template-columns: 1fr;
        }
    }
</style>
</head>
<body>
    <header class="navbar">
        <div class="logo">📦 Sistema de Compras v1</div>
        <div class="title">Crear Orden de Compra</div>
    </header>
    <div class="main-container">
        <aside class="sidebar">
            <h3 class="sidebar-heading">MÓDULOS</h3>
            <div class="sidebar-module">
                <a href="#" class="sidebar-link collapsed" onclick="toggleSubmenu('proveedores', this)">1. Gestión de Proveedores</a>
                <ul class="submenu" id="submenu-proveedores">
                    <li><a href="../proveedores/index.php">Listado de Proveedores</a></li>
                    <li><a href="../proveedores/agregar.php">Agregar Proveedor</a></li>
                    <li><a href="../proveedores/editar.php">Editar Proveedor</a></li>
                    <li><a href="../proveedores/eliminar.php">Eliminar Proveedor</a></li>
                </ul>
            </div>
            <div class="sidebar-module">
                <a href="#" class="sidebar-link collapsed" onclick="toggleSubmenu('ordenes', this)">2. Órdenes de Compra</a>
                <ul class="submenu show" id="submenu-ordenes">
                    <li><a href="crear.php" style="background-color: #354e99;">Crear Nueva OC</a></li>
                    <li><a href="index.php">Listado y Seguimiento</a></li>
                    <li><a href="recepcion.php">Recepción de Material</a></li>
                    <li><a href="historial.php">Historial de Órdenes</a></li>
                </ul>
            </div>
            <div class="sidebar-module">
                <a href="#" class="sidebar-link collapsed" onclick="toggleSubmenu('pagos', this)">3. Control de Pagos</a>
                <ul class="submenu" id="submenu-pagos">
                    <li><a href="../pagos/pendientes.php">Saldos Pendientes</a></li>
                    <li><a href="../pagos/registrar.php">Registrar Pago</a></li>
                    <li><a href="../pagos/condiciones.php">Condiciones de Pago</a></li>
                    <li><a href="../pagos/reportes.php">Reportes Financieros</a></li>
                </ul>
            </div>
            <h3 class="sidebar-heading">OTROS</h3>
            <a href="../reportes/index.php" class="sidebar-link">Reportes Generales</a>
            <a href="../index.php" class="sidebar-link">🏠 Volver al Inicio</a>
        </aside>
        <main class="main-content">
            <div class="form-container">
                <div class="form-header">
                    <h2>🆕 Crear Nueva Orden de Compra</h2>
                </div>
                <form method="POST" action="" id="formOrden">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Proveedor *</label>
                            <select name="id_proveedor" class="form-control" required>
                                <option value="">Seleccione un proveedor</option>
                                <?php while ($prov = $proveedores->fetch_assoc()): ?>
                                    <option value="<?php echo $prov['id']; ?>"><?php echo htmlspecialchars($prov['nombre']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fecha de Emisión *</label>
                            <input type="date" name="fecha_emision" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Entrega Estimada</label>
                        <input type="date" name="fecha_entrega_estimada" class="form-control">
                    </div>
                    <div class="productos-section">
                        <h3>📦 Productos / Servicios</h3>
                        <div id="productosContainer">
                            <div class="producto-item">
                                <div class="form-group">
                                    <label>Producto/Servicio *</label>
                                    <input type="text" name="productos[]" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Cantidad *</label>
                                    <input type="number" name="cantidades[]" class="form-control cantidad" min="1" value="1" required>
                                </div>
                                <div class="form-group">
                                    <label>Unidad</label>
                                    <input type="text" name="unidades[]" class="form-control" value="Unidad">
                                </div>
                                <div class="form-group">
                                    <label>Precio Unit. *</label>
                                    <input type="number" name="precios[]" class="form-control precio" step="0.01" min="0" value="0" required>
                                </div>
                                <button type="button" class="btn-remove" onclick="removerProducto(this)">🗑️</button>
                            </div>
                        </div>
                        <button type="button" class="btn-add" onclick="agregarProducto()">➕ Agregar Producto</button>
                    </div>
                    <div class="totales-box">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span id="subtotal">$0.00</span>
                        </div>
                        <div class="total-row">
                            <span>IVA (21%):</span>
                            <span id="iva">$0.00</span>
                        </div>
                        <div class="total-row final">
                            <span>TOTAL:</span>
                            <span id="total">$0.00</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea name="observaciones" class="form-control"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-success">💾 Crear Orden de Compra</button>
                        <a href="index.php" class="btn-danger">❌ Cancelar</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <footer class="footer">
        <p>&copy; 2025 Sistema de Gestión de Compras y Proveedores.</p>
    </footer>
    <script>
        function toggleSubmenu(id, element) {
            event.preventDefault();
            const submenu = document.getElementById('submenu-' + id);
            submenu.classList.toggle('show');
            if (submenu.classList.contains('show')) {
                element.classList.remove('collapsed');
                element.innerHTML = element.innerHTML.replace('▼', '▲');
            } else {
                element.classList.add('collapsed');
                element.innerHTML = element.innerHTML.replace('▲', '▼');
            }
        }

        function agregarProducto() {
            const container = document.getElementById('productosContainer');
            const primerItem = container.querySelector('.producto-item');
            const nuevoItem = primerItem.cloneNode(true);
            nuevoItem.querySelectorAll('input').forEach(input => {
                if (input.name === 'unidades[]') {
                    input.value = 'Unidad';
                } else if (input.name === 'cantidades[]') {
                    input.value = '1';
                } else if (input.name === 'precios[]') {
                    input.value = '0';
                } else {
                    input.value = '';
                }
            });
            container.appendChild(nuevoItem);
            calcularTotales();
        }

        function removerProducto(boton) {
            const container = document.getElementById('productosContainer');
            const items = container.querySelectorAll('.producto-item');
            if (items.length > 1) {
                boton.closest('.producto-item').remove();
                calcularTotales();
            } else {
                alert('Debe haber al menos un producto en la orden');
            }
        }

        function calcularTotales() {
            let subtotal = 0;
            const items = document.querySelectorAll('.producto-item');
            items.forEach(item => {
                const cantidad = parseFloat(item.querySelector('.cantidad').value) || 0;
                const precio = parseFloat(item.querySelector('.precio').value) || 0;
                subtotal += cantidad * precio;
            });
            const iva = subtotal * 0.21;
            const total = subtotal + iva;
            document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('iva').textContent = '$' + iva.toFixed(2);
            document.getElementById('total').textContent = '$' + total.toFixed(2);
        }

        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('cantidad') || e.target.classList.contains('precio')) {
                calcularTotales();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            calcularTotales();
        });
    </script>
</body>
</html>