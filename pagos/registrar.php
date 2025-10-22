<?php
require_once '../config.php';
$conexion = conectarDB();

$sql_ordenes = "SELECT 
                    o.id,
                    o.numero_orden,
                    o.total,
                    p.nombre as nombre_proveedor,
                    COALESCE(SUM(pag.monto), 0) as pagado,
                    (o.total - COALESCE(SUM(pag.monto), 0)) as saldo_pendiente
                FROM ordenes_compra o
                INNER JOIN proveedores p ON o.id_proveedor = p.id
                LEFT JOIN pagos pag ON o.id = pag.id_orden
                WHERE o.estado = 'Recibida'
                GROUP BY o.id
                HAVING saldo_pendiente > 0
                ORDER BY o.fecha_emision DESC";

$ordenes = $conexion->query($sql_ordenes);

$orden_seleccionada = null;
if (isset($_GET['orden'])) {
    $id_orden = intval($_GET['orden']);
    $sql_orden = "SELECT 
                    o.id,
                    o.numero_orden,
                    o.total,
                    p.nombre as nombre_proveedor,
                    COALESCE(SUM(pag.monto), 0) as pagado,
                    (o.total - COALESCE(SUM(pag.monto), 0)) as saldo_pendiente
                FROM ordenes_compra o
                INNER JOIN proveedores p ON o.id_proveedor = p.id
                LEFT JOIN pagos pag ON o.id = pag.id_orden
                WHERE o.id = ?
                GROUP BY o.id";
    
    $stmt = $conexion->prepare($sql_orden);
    $stmt->bind_param("i", $id_orden);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $orden_seleccionada = $result->fetch_assoc();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['id_orden']) || empty($_POST['id_orden'])) {
        mostrarMensaje('Debe seleccionar una orden de compra', 'danger');
    } else {
        $id_orden = intval($_POST['id_orden']);
        $fecha_pago = $_POST['fecha_pago'];
        $monto = floatval($_POST['monto']);
        $metodo_pago = $_POST['metodo_pago'];
        $numero_comprobante = limpiarDatos($_POST['numero_comprobante']);
        $observaciones = limpiarDatos($_POST['observaciones']);
        
        if ($monto <= 0) {
            mostrarMensaje('El monto debe ser mayor a cero', 'danger');
        } else {
            $sql = "INSERT INTO pagos (id_orden, fecha_pago, monto, metodo_pago, numero_comprobante, observaciones) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("isdsss", $id_orden, $fecha_pago, $monto, $metodo_pago, $numero_comprobante, $observaciones);
            
            if ($stmt->execute()) {
                mostrarMensaje('Pago registrado exitosamente', 'success');
                $stmt->close();
                cerrarDB($conexion);
                header('Location: pendientes.php');
                exit();
            } else {
                mostrarMensaje('Error al registrar el pago: ' . $conexion->error, 'danger');
            }
            
            $stmt->close();
        }
    }
}

cerrarDB($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Pago</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
<style>
    /* ------------------------------------------- */
    /* ESTILOS DEL CONTENEDOR PRINCIPAL DEL FORMULARIO */
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
    /* ESTILOS DE GRUPOS Y CONTROLES (INPUTS/SELECT) */
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
        
        /* CORRECCIÓN 3: Asegura que el padding/border no desborde */
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

    select.form-control {
        cursor: pointer;
        /* box-sizing: border-box ya está aplicado en .form-control */
        height: 38px;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
        height: auto;
        /* box-sizing: border-box ya está aplicado en .form-control */
    }
    
    .help-text {
        color: #858796;
        font-size: 0.75rem;
        margin-top: 5px;
        display: block;
    }

    /* ------------------------------------------- */
    /* ESTILOS DE CAJA DE INFORMACIÓN */
    /* ------------------------------------------- */
    .info-box {
        background-color: #e7f3ff;
        padding: 15px;
        border-radius: 0.35rem;
        margin: 20px 0;
        border-left: 4px solid #4e73df;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 0.875rem;
        border-bottom: 1px solid rgba(78, 115, 223, 0.2);
    }

    .info-row:last-child {
        border-bottom: none;
        padding-top: 12px;
        margin-top: 8px;
        border-top: 2px solid #4e73df;
    }

    .info-row strong {
        color: #4e73df;
    }

    /* ------------------------------------------- */
    /* ESTILOS DE ACCIONES Y ALERTAS */
    /* ------------------------------------------- */
    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #e3e6f0;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 0.35rem;
        font-size: 0.9rem;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid #e74a3b;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid #1cc88a;
    }

    /* ------------------------------------------- */
    /* MEDIA QUERY (RESPONSIVE) */
    /* ------------------------------------------- */
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>
</head>
<body>
    <header class="navbar">
        <div class="logo">📦 Sistema de Compras v1</div>
        <div class="title">Registrar Pago</div>
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
                <ul class="submenu" id="submenu-ordenes">
                    <li><a href="../ordenes/crear.php">Crear Nueva OC</a></li>
                    <li><a href="../ordenes/index.php">Listado y Seguimiento</a></li>
                    <li><a href="../ordenes/recepcion.php">Recepción de Material</a></li>
                    <li><a href="../ordenes/historial.php">Historial de Órdenes</a></li>
                </ul>
            </div>
            <div class="sidebar-module">
                <a href="#" class="sidebar-link collapsed" onclick="toggleSubmenu('pagos', this)">3. Control de Pagos</a>
                <ul class="submenu show" id="submenu-pagos">
                    <li><a href="pendientes.php">Saldos Pendientes</a></li>
                    <li><a href="registrar.php" style="background-color: #354e99;">Registrar Pago</a></li>
                    <li><a href="condiciones.php">Condiciones de Pago</a></li>
                    <li><a href="reportes.php">Reportes Financieros</a></li>
                </ul>
            </div>
            <h3 class="sidebar-heading">OTROS</h3>
            <a href="../reportes/index.php" class="sidebar-link">Reportes Generales</a>
            <a href="../index.php" class="sidebar-link">🏠 Volver al Inicio</a>
        </aside>
        <main class="main-content">
            <?php 
            $mensaje = obtenerMensaje();
            if ($mensaje): 
            ?>
                <div class="alert alert-<?php echo $mensaje['tipo']; ?>">
                    <?php echo $mensaje['texto']; ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <div class="form-header">
                    <h2>💵 Registrar Pago</h2>
                </div>

                <?php if ($ordenes && $ordenes->num_rows > 0): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Seleccionar Orden de Compra *</label>
                        <select name="id_orden" class="form-control" required onchange="actualizarInfo(this)">
                            <option value="">-- Seleccione una orden de compra --</option>
                            <?php 
                            $ordenes->data_seek(0);
                            while ($orden = $ordenes->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $orden['id']; ?>" 
                                        data-total="<?php echo $orden['total']; ?>"
                                        data-pagado="<?php echo $orden['pagado']; ?>"
                                        data-saldo="<?php echo $orden['saldo_pendiente']; ?>"
                                        data-proveedor="<?php echo htmlspecialchars($orden['nombre_proveedor']); ?>"
                                        <?php echo ($orden_seleccionada && $orden_seleccionada['id'] == $orden['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($orden['numero_orden']); ?> - <?php echo htmlspecialchars($orden['nombre_proveedor']); ?> (Saldo: <?php echo formatearMoneda($orden['saldo_pendiente']); ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <small class="help-text">Seleccione la orden a la cual desea registrar el pago</small>
                    </div>

                    <div class="info-box" id="infoOrden" style="display: <?php echo $orden_seleccionada ? 'block' : 'none'; ?>;">
                        <div class="info-row">
                            <span><strong>Proveedor:</strong></span>
                            <span id="infoProveedor"><?php echo $orden_seleccionada ? htmlspecialchars($orden_seleccionada['nombre_proveedor']) : ''; ?></span>
                        </div>
                        <div class="info-row">
                            <span><strong>Total de la Orden:</strong></span>
                            <span id="infoTotal"><?php echo $orden_seleccionada ? formatearMoneda($orden_seleccionada['total']) : ''; ?></span>
                        </div>
                        <div class="info-row">
                            <span><strong>Ya Pagado:</strong></span>
                            <span id="infoPagado"><?php echo $orden_seleccionada ? formatearMoneda($orden_seleccionada['pagado']) : ''; ?></span>
                        </div>
                        <div class="info-row">
                            <span><strong>Saldo Pendiente:</strong></span>
                            <span id="infoSaldo" style="font-size: 1.15rem; font-weight: bold; color: #e74a3b;"><?php echo $orden_seleccionada ? formatearMoneda($orden_seleccionada['saldo_pendiente']) : ''; ?></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Fecha de Pago *</label>
                            <input type="date" name="fecha_pago" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Monto del Pago *</label>
                            <input type="number" name="monto" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Método de Pago *</label>
                            <select name="metodo_pago" class="form-control" required>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Tarjeta">Tarjeta</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Número de Comprobante</label>
                            <input type="text" name="numero_comprobante" class="form-control" placeholder="Ej: 001-00123456">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea name="observaciones" class="form-control" placeholder="Notas adicionales sobre el pago"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-success">💾 Registrar Pago</button>
                        <a href="pendientes.php" class="btn-danger">❌ Cancelar</a>
                    </div>
                </form>

                <?php else: ?>
                <div style="background-color: #fff3cd; padding: 20px; border-radius: 0.35rem; border-left: 4px solid #ffc107;">
                    <h3 style="color: #856404; margin: 0 0 15px 0;">⚠️ No hay órdenes disponibles para pagar</h3>
                    <p style="margin: 0 0 10px 0; font-size: 0.875rem; color: #856404;">Para poder registrar un pago necesitas:</p>
                    <ol style="margin: 10px 0 15px 20px; font-size: 0.875rem; color: #856404;">
                        <li>Crear una orden de compra</li>
                        <li>Recepcionar la orden (marcarla como "Recibida")</li>
                        <li>Que la orden tenga saldo pendiente de pago</li>
                    </ol>
                    <a href="../ordenes/crear.php" class="btn-primary">➕ Crear Orden de Compra</a>
                    <a href="../ordenes/index.php" class="btn-info" style="margin-left: 10px;">📋 Ver Órdenes</a>
                </div>
                <?php endif; ?>
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

        function actualizarInfo(select) {
            const option = select.options[select.selectedIndex];
            const infoBox = document.getElementById('infoOrden');
            
            if (option.value) {
                const total = parseFloat(option.dataset.total);
                const pagado = parseFloat(option.dataset.pagado);
                const saldo = parseFloat(option.dataset.saldo);
                const proveedor = option.dataset.proveedor;
                
                document.getElementById('infoProveedor').textContent = proveedor;
                document.getElementById('infoTotal').textContent = '$' + total.toFixed(2);
                document.getElementById('infoPagado').textContent = '$' + pagado.toFixed(2);
                document.getElementById('infoSaldo').textContent = '$' + saldo.toFixed(2);
                
                infoBox.style.display = 'block';
            } else {
                infoBox.style.display = 'none';
            }
        }

        window.addEventListener('DOMContentLoaded', function() {
            const select = document.querySelector('select[name="id_orden"]');
            if (select && select.value) {
                actualizarInfo(select);
            }
        });
    </script>
</body>
</html>