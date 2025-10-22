<?php
// Incluir archivo de configuración
require_once '../config.php';

// Conectar a la base de datos
$conexion = conectarDB();

// Obtener órdenes con saldos pendientes
$sql = "SELECT 
            o.id,
            o.numero_orden,
            o.fecha_emision,
            o.total,
            p.nombre as nombre_proveedor,
            p.condiciones_pago,
            COALESCE(SUM(pag.monto), 0) as pagado,
            (o.total - COALESCE(SUM(pag.monto), 0)) as saldo_pendiente,
            CASE 
                WHEN p.condiciones_pago = 'Contado' THEN DATE_ADD(o.fecha_emision, INTERVAL 0 DAY)
                WHEN p.condiciones_pago = '7 días' THEN DATE_ADD(o.fecha_emision, INTERVAL 7 DAY)
                WHEN p.condiciones_pago = '15 días' THEN DATE_ADD(o.fecha_emision, INTERVAL 15 DAY)
                WHEN p.condiciones_pago = '30 días' THEN DATE_ADD(o.fecha_emision, INTERVAL 30 DAY)
                WHEN p.condiciones_pago = '60 días' THEN DATE_ADD(o.fecha_emision, INTERVAL 60 DAY)
                WHEN p.condiciones_pago = '90 días' THEN DATE_ADD(o.fecha_emision, INTERVAL 90 DAY)
                ELSE DATE_ADD(o.fecha_emision, INTERVAL 30 DAY)
            END as fecha_vencimiento,
            DATEDIFF(CURDATE(), 
                CASE 
                    WHEN p.condiciones_pago = 'Contado' THEN DATE_ADD(o.fecha_emision, INTERVAL 0 DAY)
                    WHEN p.condiciones_pago = '7 días' THEN DATE_ADD(o.fecha_emision, INTERVAL 7 DAY)
                    WHEN p.condiciones_pago = '15 días' THEN DATE_ADD(o.fecha_emision, INTERVAL 15 DAY)
                    WHEN p.condiciones_pago = '30 días' THEN DATE_ADD(o.fecha_emision, INTERVAL 30 DAY)
                    WHEN p.condiciones_pago = '60 días' THEN DATE_ADD(o.fecha_emision, INTERVAL 60 DAY)
                    WHEN p.condiciones_pago = '90 días' THEN DATE_ADD(o.fecha_emision, INTERVAL 90 DAY)
                    ELSE DATE_ADD(o.fecha_emision, INTERVAL 30 DAY)
                END
            ) as dias_vencimiento
        FROM ordenes_compra o
        INNER JOIN proveedores p ON o.id_proveedor = p.id
        LEFT JOIN pagos pag ON o.id = pag.id_orden
        WHERE o.estado = 'Recibida'
        GROUP BY o.id
        HAVING saldo_pendiente > 0
        ORDER BY dias_vencimiento DESC, o.fecha_emision ASC";

$resultado = $conexion->query($sql);

// Obtener mensaje de sesión si existe
$mensaje = obtenerMensaje();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saldos Pendientes</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
    <style>
        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 20px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-header h2 {
            color: #5a5c69;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-mini {
            background-color: #f8f9fc;
            padding: 15px;
            border-radius: 0.35rem;
            border-left: 4px solid;
        }

        .stat-mini.vencidos {
            border-left-color: #e74a3b;
        }

        .stat-mini.proximos {
            border-left-color: #f6c23e;
        }

        .stat-mini.total {
            border-left-color: #4e73df;
        }

        .stat-mini-label {
            font-size: 0.75rem;
            color: #858796;
            font-weight: 600;
            text-transform: uppercase;
        }

        .stat-mini-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #5a5c69;
            margin-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background-color: #f8f9fc;
        }

        table th {
            padding: 12px;
            text-align: left;
            font-size: 0.85rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #4e73df;
            border-bottom: 2px solid #e3e6f0;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #e3e6f0;
            color: #5a5c69;
            font-size: 0.875rem;
        }

        table tbody tr:hover {
            background-color: #f8f9fc;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-vencido {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-proximo {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-normal {
            background-color: #d4edda;
            color: #155724;
        }

        .btn-small {
            padding: 5px 10px;
            font-size: 0.8rem;
            margin-right: 5px;
            display: inline-block;
            text-decoration: none;
            vertical-align: middle;
        }

        .actions-cell {
            white-space: nowrap;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 0.35rem;
            font-size: 0.9rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #1cc88a;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #858796;
        }

        @media (max-width: 768px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="logo">📦 Sistema de Compras v1</div>
        <div class="title">Saldos Pendientes</div>
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
                    <li><a href="pendientes.php" style="background-color: #354e99;">Saldos Pendientes</a></li>
                    <li><a href="registrar.php">Registrar Pago</a></li>
                    <li><a href="condiciones.php">Condiciones de Pago</a></li>
                    <li><a href="reportes.php">Reportes Financieros</a></li>
                </ul>
            </div>

            <h3 class="sidebar-heading">OTROS</h3>
            <a href="../reportes/index.php" class="sidebar-link">Reportes Generales</a>
            <a href="../index.php" class="sidebar-link">🏠 Volver al Inicio</a>
        </aside>

        <main class="main-content">
            
            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $mensaje['tipo']; ?>">
                    <?php echo $mensaje['texto']; ?>
                </div>
            <?php endif; ?>

            <?php
            // Calcular estadísticas
            $total_vencidos = 0;
            $total_proximos = 0;
            $suma_total = 0;
            
            if ($resultado && $resultado->num_rows > 0) {
                $resultado->data_seek(0);
                while ($row = $resultado->fetch_assoc()) {
                    $suma_total += $row['saldo_pendiente'];
                    if ($row['dias_vencimiento'] > 0) {
                        $total_vencidos++;
                    } elseif ($row['dias_vencimiento'] >= -7) {
                        $total_proximos++;
                    }
                }
                $resultado->data_seek(0);
            }
            ?>

            <div class="stats-row">
                <div class="stat-mini vencidos">
                    <div class="stat-mini-label">Pagos Vencidos</div>
                    <div class="stat-mini-value"><?php echo $total_vencidos; ?></div>
                </div>
                <div class="stat-mini proximos">
                    <div class="stat-mini-label">Vencen en 7 días</div>
                    <div class="stat-mini-value"><?php echo $total_proximos; ?></div>
                </div>
                <div class="stat-mini total">
                    <div class="stat-mini-label">Total Pendiente</div>
                    <div class="stat-mini-value"><?php echo formatearMoneda($suma_total); ?></div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>⏰ Cuentas por Pagar</h2>
                </div>

                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Proveedor</th>
                                <th>Fecha Emisión</th>
                                <th>Vencimiento</th>
                                <th>Total</th>
                                <th>Pagado</th>
                                <th>Saldo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($pago = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($pago['numero_orden']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($pago['nombre_proveedor']); ?></td>
                                    <td><?php echo formatearFecha($pago['fecha_emision']); ?></td>
                                    <td><?php echo formatearFecha($pago['fecha_vencimiento']); ?></td>
                                    <td><?php echo formatearMoneda($pago['total']); ?></td>
                                    <td><?php echo formatearMoneda($pago['pagado']); ?></td>
                                    <td><strong><?php echo formatearMoneda($pago['saldo_pendiente']); ?></strong></td>
                                    <td>
                                        <?php
                                        if ($pago['dias_vencimiento'] > 0) {
                                            echo '<span class="badge badge-vencido">Vencido (' . $pago['dias_vencimiento'] . ' días)</span>';
                                        } elseif ($pago['dias_vencimiento'] >= -7) {
                                            echo '<span class="badge badge-proximo">Vence pronto</span>';
                                        } else {
                                            echo '<span class="badge badge-normal">Normal</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="registrar.php?orden=<?php echo $pago['id']; ?>" class="btn-success btn-small">💵 Registrar Pago</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">
                        <p>✅ No hay saldos pendientes. Todas las cuentas están al día.</p>
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
    </script>

</body>
</html>

<?php
// Cerrar conexión
cerrarDB($conexion);
?>