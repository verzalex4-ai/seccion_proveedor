<?php
// Incluir archivo de configuración
require_once '../config.php';

// Conectar a la base de datos
$conexion = conectarDB();

// Verificar si se recibió un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    mostrarMensaje('ID de orden no especificado', 'danger');
    header('Location: index.php');
    exit();
}

$id = intval($_GET['id']);

// Obtener datos de la orden
$sql = "SELECT o.*, p.nombre as nombre_proveedor, p.email, p.telefono, p.condiciones_pago
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

// Obtener pagos asociados
$sql_pagos = "SELECT * FROM pagos WHERE id_orden = ? ORDER BY fecha_pago DESC";
$stmt_pagos = $conexion->prepare($sql_pagos);
$stmt_pagos->bind_param("i", $id);
$stmt_pagos->execute();
$pagos = $stmt_pagos->get_result();
$stmt_pagos->close();

// Calcular total pagado
$total_pagado = 0;
$pagos->data_seek(0);
while ($pago = $pagos->fetch_assoc()) {
    $total_pagado += $pago['monto'];
}
$pagos->data_seek(0);

cerrarDB($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Orden de Compra</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
    <style>
        .orden-container {
            background-color: white;
            padding: 25px;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 20px;
        }

        .orden-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e3e6f0;
        }

        .orden-header h2 {
            color: #4e73df;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-pendiente {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-enviada {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .badge-recibida {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-cancelada {
            background-color: #f8d7da;
            color: #721c24;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-section {
            background-color: #f8f9fc;
            padding: 20px;
            border-radius: 0.35rem;
            border-left: 4px solid #4e73df;
        }

        .info-section h3 {
            color: #4e73df;
            font-size: 1rem;
            font-weight: 700;
            margin: 0 0 15px 0;
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
            color: #5a5c69;
        }

        .productos-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .productos-table th {
            background-color: #f8f9fc;
            padding: 12px;
            text-align: left;
            font-size: 0.85rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #4e73df;
            border-bottom: 2px solid #e3e6f0;
        }

        .productos-table td {
            padding: 12px;
            border-bottom: 1px solid #e3e6f0;
            font-size: 0.875rem;
            color: #5a5c69;
        }

        .totales-box {
            background-color: #e7f3ff;
            padding: 20px;
            border-radius: 0.35rem;
            margin: 20px 0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        .total-row.final {
            font-size: 1.2rem;
            font-weight: bold;
            color: #4e73df;
            padding-top: 10px;
            border-top: 2px solid #4e73df;
        }

        .pagos-section {
            margin-top: 30px;
        }

        .pagos-section h3 {
            color: #5a5c69;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .actions-box {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e3e6f0;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="logo">📦 Sistema de Compras v1</div>
        <div class="title">Detalle de Orden</div>
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
            
            <div class="orden-container">
                <div class="orden-header">
                    <h2>Orden: <?php echo htmlspecialchars($orden['numero_orden']); ?></h2>
                    <?php
                    $clase_badge = 'badge-' . strtolower($orden['estado']);
                    ?>
                    <span class="badge <?php echo $clase_badge; ?>"><?php echo $orden['estado']; ?></span>
                </div>

                <div class="info-grid">
                    <div class="info-section">
                        <h3>📋 Información de la Orden</h3>
                        <div class="info-row">
                            <strong>Fecha de Emisión:</strong>
                            <span><?php echo formatearFecha($orden['fecha_emision']); ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Fecha de Entrega:</strong>
                            <span><?php echo formatearFecha($orden['fecha_entrega_estimada']); ?></span>
                        </div>
                        <?php if ($orden['fecha_recepcion']): ?>
                        <div class="info-row">
                            <strong>Fecha de Recepción:</strong>
                            <span><?php echo formatearFecha($orden['fecha_recepcion']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="info-row">
                            <strong>Estado:</strong>
                            <span><?php echo $orden['estado']; ?></span>
                        </div>
                    </div>

                    <div class="info-section">
                        <h3>🏢 Información del Proveedor</h3>
                        <div class="info-row">
                            <strong>Proveedor:</strong>
                            <span><?php echo htmlspecialchars($orden['nombre_proveedor']); ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Email:</strong>
                            <span><?php echo htmlspecialchars($orden['email']); ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Teléfono:</strong>
                            <span><?php echo htmlspecialchars($orden['telefono']); ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Condiciones de Pago:</strong>
                            <span><?php echo htmlspecialchars($orden['condiciones_pago']); ?></span>
                        </div>
                    </div>
                </div>

                <h3 style="color: #4e73df; margin-bottom: 15px;">📦 Productos/Servicios</h3>
                <table class="productos-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Unidad</th>
                            <th>Precio Unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($detalle = $detalles->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($detalle['producto']); ?></strong></td>
                                <td><?php echo htmlspecialchars($detalle['descripcion']); ?></td>
                                <td><?php echo $detalle['cantidad']; ?></td>
                                <td><?php echo htmlspecialchars($detalle['unidad_medida']); ?></td>
                                <td><?php echo formatearMoneda($detalle['precio_unitario']); ?></td>
                                <td><?php echo formatearMoneda($detalle['subtotal']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <div class="totales-box">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span><?php echo formatearMoneda($orden['subtotal']); ?></span>
                    </div>
                    <div class="total-row">
                        <span>IVA (21%):</span>
                        <span><?php echo formatearMoneda($orden['impuestos']); ?></span>
                    </div>
                    <div class="total-row final">
                        <span>TOTAL:</span>
                        <span><?php echo formatearMoneda($orden['total']); ?></span>
                    </div>
                </div>

                <?php if ($orden['observaciones']): ?>
                <div class="info-section">
                    <h3>📝 Observaciones</h3>
                    <p style="margin: 0; color: #5a5c69;"><?php echo nl2br(htmlspecialchars($orden['observaciones'])); ?></p>
                </div>
                <?php endif; ?>

                <?php if ($pagos->num_rows > 0): ?>
                <div class="pagos-section">
                    <h3>💵 Historial de Pagos</h3>
                    <table class="productos-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Comprobante</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($pago = $pagos->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo formatearFecha($pago['fecha_pago']); ?></td>
                                    <td><?php echo formatearMoneda($pago['monto']); ?></td>
                                    <td><?php echo htmlspecialchars($pago['metodo_pago']); ?></td>
                                    <td><?php echo htmlspecialchars($pago['numero_comprobante']); ?></td>
                                    <td><?php echo htmlspecialchars($pago['observaciones']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <div class="info-row" style="margin-top: 15px; padding-top: 15px; border-top: 2px solid #4e73df;">
                        <strong style="font-size: 1.1rem;">Total Pagado:</strong>
                        <strong style="font-size: 1.1rem; color: #1cc88a;"><?php echo formatearMoneda($total_pagado); ?></strong>
                    </div>
                    <div class="info-row">
                        <strong style="font-size: 1.1rem;">Saldo Pendiente:</strong>
                        <strong style="font-size: 1.1rem; color: #e74a3b;"><?php echo formatearMoneda($orden['total'] - $total_pagado); ?></strong>
                    </div>
                </div>
                <?php endif; ?>

                <div class="actions-box">
                    <?php if ($orden['estado'] != 'Recibida' && $orden['estado'] != 'Cancelada'): ?>
                        <a href="recepcion.php?id=<?php echo $orden['id']; ?>" class="btn-success">✅ Recepcionar</a>
                    <?php endif; ?>
                    <?php if (($orden['total'] - $total_pagado) > 0): ?>
                        <a href="../pagos/registrar.php?orden=<?php echo $orden['id']; ?>" class="btn-primary">💵 Registrar Pago</a>
                    <?php endif; ?>
                    <a href="index.php" class="btn-info">← Volver al Listado</a>
                </div>
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
    </script>

</body>
</html>