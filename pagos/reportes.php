<?php
// Incluir archivo de configuración
require_once '../config.php';

// Conectar a la base de datos
$conexion = conectarDB();

// Filtros
$filtro_desde = isset($_GET['desde']) ? $_GET['desde'] : date('Y-m-01');
$filtro_hasta = isset($_GET['hasta']) ? $_GET['hasta'] : date('Y-m-d');

// Reporte de pagos por proveedor
$sql_proveedor = "SELECT 
                    p.nombre as proveedor,
                    COUNT(DISTINCT o.id) as total_ordenes,
                    COALESCE(SUM(o.total), 0) as total_comprado,
                    COALESCE(SUM(pag.monto), 0) as total_pagado,
                    (COALESCE(SUM(o.total), 0) - COALESCE(SUM(pag.monto), 0)) as saldo_pendiente
                FROM proveedores p
                LEFT JOIN ordenes_compra o ON p.id = o.id_proveedor 
                    AND o.fecha_emision BETWEEN ? AND ?
                    AND o.estado = 'Recibida'
                LEFT JOIN pagos pag ON o.id = pag.id_orden
                GROUP BY p.id
                HAVING total_comprado > 0
                ORDER BY total_comprado DESC";

$stmt_prov = $conexion->prepare($sql_proveedor);
$stmt_prov->bind_param("ss", $filtro_desde, $filtro_hasta);
$stmt_prov->execute();
$resultado_proveedor = $stmt_prov->get_result();
$stmt_prov->close();

// Reporte de pagos por método
$sql_metodo = "SELECT 
                metodo_pago,
                COUNT(*) as cantidad,
                SUM(monto) as total
            FROM pagos
            WHERE fecha_pago BETWEEN ? AND ?
            GROUP BY metodo_pago
            ORDER BY total DESC";

$stmt_met = $conexion->prepare($sql_metodo);
$stmt_met->bind_param("ss", $filtro_desde, $filtro_hasta);
$stmt_met->execute();
$resultado_metodo = $stmt_met->get_result();
$stmt_met->close();

// Totales generales
$sql_totales = "SELECT 
                    COUNT(DISTINCT o.id) as total_ordenes,
                    COALESCE(SUM(o.total), 0) as total_compras,
                    COALESCE(SUM(pag.monto), 0) as total_pagos
                FROM ordenes_compra o
                LEFT JOIN pagos pag ON o.id = pag.id_orden
                WHERE o.fecha_emision BETWEEN ? AND ?";

$stmt_tot = $conexion->prepare($sql_totales);
$stmt_tot->bind_param("ss", $filtro_desde, $filtro_hasta);
$stmt_tot->execute();
$totales = $stmt_tot->get_result()->fetch_assoc();
$stmt_tot->close();

cerrarDB($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Financieros</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
<style>
    /* ------------------------------------------- */
    /* ESTILOS DEL CONTENEDOR PRINCIPAL DE LA TABLA */
    /* ------------------------------------------- */
    .table-container {
        background-color: white;
        padding: 20px;
        border-radius: 0.35rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        
        /* CORRECCIÓN 1: Centrado y Margen Vertical (reemplazamos margin-bottom: 20px;) */
        margin: 20px auto; 
        
        /* CORRECCIÓN 2: Ancho máximo de 1051px para consistencia */
        width: 100%; 
        max-width: 1051px; 
    }

    .table-header h2 {
        color: #5a5c69;
        font-size: 1.35rem;
        font-weight: 700;
        margin: 0 0 20px 0;
    }

    /* ------------------------------------------- */
    /* ESTILOS DE CAJA DE FILTROS */
    /* ------------------------------------------- */
    .filters-box {
        background-color: #f8f9fc;
        padding: 15px;
        border-radius: 0.35rem;
        margin-bottom: 20px;
    }

    .filter-row {
        display: grid;
        grid-template-columns: 1fr 1fr auto auto;
        gap: 15px;
        align-items: end;
    }

    .filter-group label {
        display: block;
        margin-bottom: 5px;
        color: #5a5c69;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .filter-group input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d3e2;
        border-radius: 0.35rem;
        font-size: 0.875rem;
        font-family: 'Nunito', sans-serif;
        
        /* CORRECCIÓN 3: Asegura que el padding/border no desborde el 100% del width */
        box-sizing: border-box; 
    }

    /* ------------------------------------------- */
    /* ESTILOS DE ESTADÍSTICAS (CARDS) */
    /* ------------------------------------------- */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
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

    /* ------------------------------------------- */
    /* ESTILOS DE LA TABLA */
    /* ------------------------------------------- */
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

    .no-data {
        text-align: center;
        padding: 40px;
        color: #858796;
    }

    /* ------------------------------------------- */
    /* MEDIA QUERY (RESPONSIVE) */
    /* ------------------------------------------- */
    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: 1fr;
        }
        .filter-row {
            grid-template-columns: 1fr;
        }
    }
</style>
</head>
<body>

    <header class="navbar">
        <div class="logo">📦 Sistema de Compras v1</div>
        <div class="title">Reportes Financieros</div>
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
                    <li><a href="registrar.php">Registrar Pago</a></li>
                    <li><a href="condiciones.php">Condiciones de Pago</a></li>
                    <li><a href="reportes.php" style="background-color: #354e99;">Reportes Financieros</a></li>
                </ul>
            </div>

            <h3 class="sidebar-heading">OTROS</h3>
            <a href="../reportes/index.php" class="sidebar-link">Reportes Generales</a>
            <a href="../index.php" class="sidebar-link">🏠 Volver al Inicio</a>
        </aside>

        <main class="main-content">
            
            <form method="GET" class="filters-box">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Desde</label>
                        <input type="date" name="desde" value="<?php echo $filtro_desde; ?>">
                    </div>

                    <div class="filter-group">
                        <label>Hasta</label>
                        <input type="date" name="hasta" value="<?php echo $filtro_hasta; ?>">
                    </div>

                    <div class="filter-group">
                        <button type="submit" class="btn-primary">🔍 Generar Reporte</button>
                    </div>

                    <div class="filter-group">
                        <a href="reportes.php" class="btn-info">🔄 Limpiar</a>
                    </div>
                </div>
            </form>

            <div class="stats-row">
                <div class="stat-card primary">
                    <div class="stat-label">Total Órdenes</div>
                    <div class="stat-value"><?php echo $totales['total_ordenes']; ?></div>
                </div>
                <div class="stat-card success">
                    <div class="stat-label">Total Compras</div>
                    <div class="stat-value"><?php echo formatearMoneda($totales['total_compras']); ?></div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-label">Total Pagos</div>
                    <div class="stat-value"><?php echo formatearMoneda($totales['total_pagos']); ?></div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>📊 Reporte por Proveedor</h2>
                </div>

                <?php if ($resultado_proveedor && $resultado_proveedor->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Proveedor</th>
                                <th>Órdenes</th>
                                <th>Total Comprado</th>
                                <th>Total Pagado</th>
                                <th>Saldo Pendiente</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $resultado_proveedor->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['proveedor']); ?></strong></td>
                                    <td><?php echo $row['total_ordenes']; ?></td>
                                    <td><?php echo formatearMoneda($row['total_comprado']); ?></td>
                                    <td><?php echo formatearMoneda($row['total_pagado']); ?></td>
                                    <td><strong><?php echo formatearMoneda($row['saldo_pendiente']); ?></strong></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">
                        <p>No hay datos en el período seleccionado.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>💳 Reporte por Método de Pago</h2>
                </div>

                <?php if ($resultado_metodo && $resultado_metodo->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Método de Pago</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $resultado_metodo->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['metodo_pago']); ?></strong></td>
                                    <td><?php echo $row['cantidad']; ?></td>
                                    <td><?php echo formatearMoneda($row['total']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">
                        <p>No hay pagos registrados en el período seleccionado.</p>
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