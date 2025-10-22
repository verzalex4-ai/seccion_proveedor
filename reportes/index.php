<?php
// Incluir archivo de configuración
require_once '../config.php';

// Conectar a la base de datos
$conexion = conectarDB();

// Estadísticas generales
$sql_stats = "SELECT 
                (SELECT COUNT(*) FROM proveedores WHERE estado = 'Activo') as total_proveedores,
                (SELECT COUNT(*) FROM ordenes_compra) as total_ordenes,
                (SELECT COUNT(*) FROM ordenes_compra WHERE estado = 'Pendiente') as ordenes_pendientes,
                (SELECT COUNT(*) FROM ordenes_compra WHERE estado = 'Recibida') as ordenes_recibidas,
                (SELECT COALESCE(SUM(total), 0) FROM ordenes_compra) as monto_total_ordenes,
                (SELECT COALESCE(SUM(monto), 0) FROM pagos) as monto_total_pagos";

$stats = $conexion->query($sql_stats)->fetch_assoc();

// Top 5 proveedores por monto
$sql_top_prov = "SELECT 
                    p.nombre,
                    COUNT(o.id) as total_ordenes,
                    COALESCE(SUM(o.total), 0) as monto_total
                FROM proveedores p
                LEFT JOIN ordenes_compra o ON p.id = o.id_proveedor
                GROUP BY p.id
                HAVING monto_total > 0
                ORDER BY monto_total DESC
                LIMIT 5";

$top_proveedores = $conexion->query($sql_top_prov);

// Órdenes recientes
$sql_recientes = "SELECT 
                    o.numero_orden,
                    o.fecha_emision,
                    o.total,
                    o.estado,
                    p.nombre as nombre_proveedor
                FROM ordenes_compra o
                INNER JOIN proveedores p ON o.id_proveedor = p.id
                ORDER BY o.fecha_emision DESC
                LIMIT 10";

$ordenes_recientes = $conexion->query($sql_recientes);

// Pagos recientes
$sql_pagos_rec = "SELECT 
                    pag.fecha_pago,
                    pag.monto,
                    pag.metodo_pago,
                    o.numero_orden,
                    p.nombre as nombre_proveedor
                FROM pagos pag
                INNER JOIN ordenes_compra o ON pag.id_orden = o.id
                INNER JOIN proveedores p ON o.id_proveedor = p.id
                ORDER BY pag.fecha_pago DESC
                LIMIT 10";

$pagos_recientes = $conexion->query($sql_pagos_rec);

cerrarDB($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Generales</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: white;
            padding: 20px;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border-left: 4px solid;
        }

        .stat-card.primary { border-left-color: #4e73df; }
        .stat-card.success { border-left-color: #1cc88a; }
        .stat-card.warning { border-left-color: #f6c23e; }
        .stat-card.info { border-left-color: #36b9cc; }
        .stat-card.danger { border-left-color: #e74a3b; }

        .stat-label {
            font-size: 0.75rem;
            color: #858796;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #5a5c69;
        }

        .content-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .table-container h3 {
            color: #5a5c69;
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e3e6f0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background-color: #f8f9fc;
        }

        table th {
            padding: 10px;
            text-align: left;
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #4e73df;
            border-bottom: 2px solid #e3e6f0;
        }

        table td {
            padding: 10px;
            border-bottom: 1px solid #e3e6f0;
            color: #5a5c69;
            font-size: 0.85rem;
        }

        table tbody tr:hover {
            background-color: #f8f9fc;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 8px;
            font-size: 0.7rem;
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

        @media (max-width: 768px) {
            .content-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="logo">📦 Sistema de Compras v1</div>
        <div class="title">Reportes Generales</div>
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
                <ul class="submenu" id="submenu-pagos">
                    <li><a href="../pagos/pendientes.php">Saldos Pendientes</a></li>
                    <li><a href="../pagos/registrar.php">Registrar Pago</a></li>
                    <li><a href="../pagos/condiciones.php">Condiciones de Pago</a></li>
                    <li><a href="../pagos/reportes.php">Reportes Financieros</a></li>
                </ul>
            </div>

            <h3 class="sidebar-heading">OTROS</h3>
            <a href="index.php" class="sidebar-link" style="background-color: rgba(255, 255, 255, 0.15);">Reportes Generales</a>
            <a href="../index.php" class="sidebar-link">🏠 Volver al Inicio</a>
        </aside>

        <main class="main-content">
            
            <h2 style="color: #5a5c69; margin-bottom: 20px;">📊 Resumen General del Sistema</h2>

            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-label">Proveedores Activos</div>
                    <div class="stat-value"><?php echo $stats['total_proveedores']; ?></div>
                </div>
                <div class="stat-card info">
                    <div class="stat-label">Total Órdenes</div>
                    <div class="stat-value"><?php echo $stats['total_ordenes']; ?></div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-label">Órdenes Pendientes</div>
                    <div class="stat-value"><?php echo $stats['ordenes_pendientes']; ?></div>
                </div>
                <div class="stat-card success">
                    <div class="stat-label">Órdenes Recibidas</div>
                    <div class="stat-value"><?php echo $stats['ordenes_recibidas']; ?></div>
                </div>
                <div class="stat-card primary">
                    <div class="stat-label">Monto Total Compras</div>
                    <div class="stat-value"><?php echo formatearMoneda($stats['monto_total_ordenes']); ?></div>
                </div>
                <div class="stat-card success">
                    <div class="stat-label">Monto Total Pagos</div>
                    <div class="stat-value"><?php echo formatearMoneda($stats['monto_total_pagos']); ?></div>
                </div>
            </div>

            <div class="content-row">
                <div class="table-container">
                    <h3>🏆 Top 5 Proveedores</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Proveedor</th>
                                <th>Órdenes</th>
                                <th>Monto Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($prov = $top_proveedores->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($prov['nombre']); ?></strong></td>
                                    <td><?php echo $prov['total_ordenes']; ?></td>
                                    <td><?php echo formatearMoneda($prov['monto_total']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-container">
                    <h3>💵 Últimos Pagos Registrados</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Orden</th>
                                <th>Monto</th>
                                <th>Método</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($pago = $pagos_recientes->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo formatearFecha($pago['fecha_pago']); ?></td>
                                    <td><?php echo htmlspecialchars($pago['numero_orden']); ?></td>
                                    <td><?php echo formatearMoneda($pago['monto']); ?></td>
                                    <td><?php echo htmlspecialchars($pago['metodo_pago']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-container">
                <h3>📋 Órdenes Recientes</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Número Orden</th>
                            <th>Proveedor</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($orden = $ordenes_recientes->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($orden['numero_orden']); ?></strong></td>
                                <td><?php echo htmlspecialchars($orden['nombre_proveedor']); ?></td>
                                <td><?php echo formatearFecha($orden['fecha_emision']); ?></td>
                                <td><?php echo formatearMoneda($orden['total']); ?></td>
                                <td>
                                    <?php
                                    $clase_badge = 'badge-' . strtolower($orden['estado']);
                                    ?>
                                    <span class="badge <?php echo $clase_badge; ?>"><?php echo $orden['estado']; ?></span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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