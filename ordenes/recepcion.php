<?php
// Incluir archivo de configuración
require_once '../config.php';

// Conectar a la base de datos
$conexion = conectarDB();

// Verificar si se recibió un ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Obtener datos de la orden
    $sql = "SELECT o.*, p.nombre as nombre_proveedor 
            FROM ordenes_compra o 
            INNER JOIN proveedores p ON o.id_proveedor = p.id 
            WHERE o.id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows == 0) {
        mostrarMensaje('Orden de compra no encontrada', 'danger');
        header('Location: index.php');
        exit();
    }
    
    $orden = $resultado->fetch_assoc();
    $stmt->close();
    
    // Obtener detalles de la orden
    $sql_detalle = "SELECT * FROM detalle_orden WHERE id_orden = ?";
    $stmt_detalle = $conexion->prepare($sql_detalle);
    $stmt_detalle->bind_param("i", $id);
    $stmt_detalle->execute();
    $detalles = $stmt_detalle->get_result();
    $stmt_detalle->close();
} else {
    // Obtener órdenes pendientes para recepcionar
    $sql = "SELECT o.*, p.nombre as nombre_proveedor 
            FROM ordenes_compra o 
            INNER JOIN proveedores p ON o.id_proveedor = p.id 
            WHERE o.estado IN ('Pendiente', 'Enviada') 
            ORDER BY o.fecha_emision DESC";
    $ordenes_pendientes = $conexion->query($sql);
}

// Procesar recepción
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['recepcionar'])) {
    $id_orden = intval($_POST['id_orden']);
    $fecha_recepcion = $_POST['fecha_recepcion'];
    
    $sql = "UPDATE ordenes_compra SET estado = 'Recibida', fecha_recepcion = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("si", $fecha_recepcion, $id_orden);
    
    if ($stmt->execute()) {
        mostrarMensaje('Material recepcionado exitosamente', 'success');
        $stmt->close();
        cerrarDB($conexion);
        header('Location: index.php');
        exit();
    } else {
        mostrarMensaje('Error al recepcionar: ' . $conexion->error, 'danger');
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
    <title>Recepción de Material</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
<style>
    /* ------------------------------------------- */
    /* CONTENEDORES PRINCIPALES (FORMULARIOS Y TABLAS) */
    /* ------------------------------------------- */
    .form-container {
        background-color: white;
        padding: 25px;
        border-radius: 0.35rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        
        /* CORRECCIÓN 1: Centrado y Margen Vertical */
        margin: 20px auto; 
        
        /* CORRECCIÓN 2: Ancho máximo consistente de 1051px (ajustado de 900px) */
        width: 100%;
        max-width: 1051px; 
    }

    .table-container {
        background-color: white;
        padding: 20px;
        border-radius: 0.35rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        
        /* Centrado y Ancho Consistente para contenedores de tabla */
        margin: 20px auto;
        width: 100%;
        max-width: 1051px;
    }

    /* ------------------------------------------- */
    /* ESTILOS DE ENCABEZADO */
    /* ------------------------------------------- */
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
    /* ESTILOS DE CAJA DE INFORMACIÓN */
    /* ------------------------------------------- */
    .info-box {
        background-color: #f8f9fc;
        padding: 15px;
        border-radius: 0.35rem;
        margin-bottom: 20px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #e3e6f0;
        font-size: 0.875rem;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-row strong {
        color: #4e73df;
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
        font-family: 'Nunito', sans-serif;
        
        /* CORRECCIÓN 3: Asegura que el padding/border no desborde */
        box-sizing: border-box;
    }

    /* ------------------------------------------- */
    /* ESTILOS DE TABLA DE PRODUCTOS */
    /* ------------------------------------------- */
    .productos-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }

    .productos-table th {
        background-color: #f8f9fc;
        padding: 10px;
        text-align: left;
        font-size: 0.85rem;
        font-weight: 800;
        color: #4e73df;
        border-bottom: 2px solid #e3e6f0;
    }

    .productos-table td {
        padding: 10px;
        border-bottom: 1px solid #e3e6f0;
        font-size: 0.875rem;
    }

    /* ------------------------------------------- */
    /* ESTILOS DE ACCIONES */
    /* ------------------------------------------- */
    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #e3e6f0;
    }

    /* ------------------------------------------- */
    /* ESTILOS DE ELEMENTOS DE ORDEN (LISTADO) */
    /* ------------------------------------------- */
    .orden-item {
        background-color: #f8f9fc;
        padding: 15px;
        border-radius: 0.35rem;
        margin-bottom: 15px;
        border-left: 4px solid #4e73df;
    }

    .orden-item:hover {
        background-color: #e7f3ff;
    }

    .orden-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .orden-numero {
        font-size: 1.1rem;
        font-weight: bold;
        color: #4e73df;
    }

    .orden-info {
        font-size: 0.875rem;
        color: #5a5c69;
    }
</style>
</head>
<body>

    <header class="navbar">
        <div class="logo">📦 Sistema de Compras v1</div>
        <div class="title">Recepción de Material</div>
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
                    <li><a href="crear.php">Crear Nueva OC</a></li>
                    <li><a href="index.php">Listado y Seguimiento</a></li>
                    <li><a href="recepcion.php" style="background-color: #354e99;">Recepción de Material</a></li>
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
            
            <?php if (isset($orden)): ?>
                <!-- Formulario de recepción específica -->
                <div class="form-container">
                    <div class="form-header">
                        <h2>✅ Recepcionar Orden de Compra</h2>
                    </div>

                    <div class="info-box">
                        <div class="info-row">
                            <span><strong>Número de Orden:</strong></span>
                            <span><?php echo htmlspecialchars($orden['numero_orden']); ?></span>
                        </div>
                        <div class="info-row">
                            <span><strong>Proveedor:</strong></span>
                            <span><?php echo htmlspecialchars($orden['nombre_proveedor']); ?></span>
                        </div>
                        <div class="info-row">
                            <span><strong>Fecha de Emisión:</strong></span>
                            <span><?php echo formatearFecha($orden['fecha_emision']); ?></span>
                        </div>
                        <div class="info-row">
                            <span><strong>Total:</strong></span>
                            <span><?php echo formatearMoneda($orden['total']); ?></span>
                        </div>
                        <div class="info-row">
                            <span><strong>Estado Actual:</strong></span>
                            <span><?php echo $orden['estado']; ?></span>
                        </div>
                    </div>

                    <h3 style="color: #4e73df; margin-bottom: 15px;">Productos/Servicios</h3>
                    <table class="productos-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Unidad</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($detalle = $detalles->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($detalle['producto']); ?></td>
                                    <td><?php echo $detalle['cantidad']; ?></td>
                                    <td><?php echo htmlspecialchars($detalle['unidad_medida']); ?></td>
                                    <td><?php echo formatearMoneda($detalle['precio_unitario']); ?></td>
                                    <td><?php echo formatearMoneda($detalle['subtotal']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <form method="POST" action="">
                        <input type="hidden" name="id_orden" value="<?php echo $orden['id']; ?>">
                        
                        <div class="form-group">
                            <label>Fecha de Recepción *</label>
                            <input type="date" name="fecha_recepcion" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="recepcionar" class="btn-success">✅ Confirmar Recepción</button>
                            <a href="index.php" class="btn-danger">❌ Cancelar</a>
                        </div>
                    </form>
                </div>

            <?php else: ?>
                <!-- Listado de órdenes pendientes -->
                <div class="table-container">
                    <div class="form-header">
                        <h2>📦 Órdenes Pendientes de Recepción</h2>
                    </div>

                    <?php if ($ordenes_pendientes && $ordenes_pendientes->num_rows > 0): ?>
                        <?php while ($orden_pend = $ordenes_pendientes->fetch_assoc()): ?>
                            <div class="orden-item">
                                <div class="orden-header">
                                    <div>
                                        <div class="orden-numero"><?php echo htmlspecialchars($orden_pend['numero_orden']); ?></div>
                                        <div class="orden-info">
                                            <strong>Proveedor:</strong> <?php echo htmlspecialchars($orden_pend['nombre_proveedor']); ?> | 
                                            <strong>Fecha:</strong> <?php echo formatearFecha($orden_pend['fecha_emision']); ?> | 
                                            <strong>Total:</strong> <?php echo formatearMoneda($orden_pend['total']); ?>
                                        </div>
                                    </div>
                                    <div>
                                        <a href="recepcion.php?id=<?php echo $orden_pend['id']; ?>" class="btn-success">✅ Recepcionar</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align: center; padding: 40px; color: #858796;">
                            No hay órdenes pendientes de recepción.
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

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
    </script>

</body>
</html>