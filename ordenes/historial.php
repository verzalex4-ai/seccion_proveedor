<?php
// Incluir archivo de configuración
require_once '../config.php';

// Conectar a la base de datos
$conexion = conectarDB();

// Filtros
$filtro_desde = isset($_GET['desde']) ? $_GET['desde'] : date('Y-m-01');
$filtro_hasta = isset($_GET['hasta']) ? $_GET['hasta'] : date('Y-m-d');
$filtro_proveedor = isset($_GET['proveedor']) ? intval($_GET['proveedor']) : 0;

// Construir consulta SQL con filtros
$sql = "SELECT o.*, p.nombre as nombre_proveedor 
        FROM ordenes_compra o 
        INNER JOIN proveedores p ON o.id_proveedor = p.id 
        WHERE o.fecha_emision BETWEEN ? AND ?";

if ($filtro_proveedor > 0) {
    $sql .= " AND o.id_proveedor = $filtro_proveedor";
}

$sql .= " ORDER BY o.fecha_emision DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ss", $filtro_desde, $filtro_hasta);
$stmt->execute();
$resultado = $stmt->get_result();

// Obtener proveedores para el filtro
$sql_proveedores = "SELECT id, nombre FROM proveedores ORDER BY nombre ASC";
$proveedores = $conexion->query($sql_proveedores);

// Calcular totales
$total_ordenes = 0;
$suma_total = 0;

if ($resultado->num_rows > 0) {
    $resultado->data_seek(0);
    while ($row = $resultado->fetch_assoc()) {
        $total_ordenes++;
        $suma_total += $row['total'];
    }
    $resultado->data_seek(0);
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Órdenes</title>
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
        
        /* CORRECCIÓN 1: Centrado y Margen Vertical */
        margin: 20px auto; 
        
        /* CORRECCIÓN 2: Ancho máximo de 1051px para consistencia */
        width: 100%; 
        max-width: 1051px; 
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
        /* La configuración de columnas se mantiene para el diseño de filtros */
        grid-template-columns: 1fr 1fr 1fr 1fr auto;
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

    .filter-group input,
    .filter-group select {
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
    /* ESTILOS DE ESTADÍSTICAS (MINI CARDS) */
    /* ------------------------------------------- */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }

    .stat-mini {
        background-color: #f8f9fc;
        padding: 15px;
        border-radius: 0.35rem;
        border-left: 4px solid #4e73df;
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

    /* ------------------------------------------- */
    /* ESTILOS DE BADGES */
    /* ------------------------------------------- */
    .badge {
        padding: 4px 10px;
        border-radius: 10px;
        font-size: 0.75rem;
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

    .no-data {
        text-align: center;
        padding: 40px;
        color: #858796;
    }

    /* ------------------------------------------- */
    /* MEDIA QUERY (RESPONSIVE) */
    /* ------------------------------------------- */
    @media (max-width: 768px) {
        .filter-row {
            grid-template-columns: 1fr;
        }
        .stats-row {
            grid-template-columns: 1fr;
        }
    }
</style>
</head>
<body>

    <header class="navbar">
        <div class="logo">📦 Sistema de Compras v1</div>
        <div class="title">Historial de Órdenes</div>
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
                    <li><a href="historial.php" style="background-color: #354e99;">Historial de Órdenes</a></li>
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
            
            <div class="stats-row">
                <div class="stat-mini">
                    <div class="stat-mini-label">Total de Órdenes</div>
                    <div class="stat-mini-value"><?php echo $total_ordenes; ?></div>
                </div>
                <div class="stat-mini">
                    <div class="stat-mini-label">Monto Total</div>
                    <div class="stat-mini-value"><?php echo formatearMoneda($suma_total); ?></div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>🔍 Historial de Órdenes de Compra</h2>
                </div>

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
                            <label>Proveedor</label>
                            <select name="proveedor">
                                <option value="0">Todos</option>
                                <?php while ($prov = $proveedores->fetch_assoc()): ?>
                                    <option value="<?php echo $prov['id']; ?>" <?php echo ($filtro_proveedor == $prov['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($prov['nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <button type="submit" class="btn-primary">🔍 Filtrar</button>
                        </div>

                        <div class="filter-group">
                            <a href="historial.php" class="btn-info">🔄 Limpiar</a>
                        </div>
                    </div>
                </form>

                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Número Orden</th>
                                <th>Proveedor</th>
                                <th>Fecha Emisión</th>
                                <th>Fecha Entrega</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($orden = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($orden['numero_orden']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($orden['nombre_proveedor']); ?></td>
                                    <td><?php echo formatearFecha($orden['fecha_emision']); ?></td>
                                    <td><?php echo formatearFecha($orden['fecha_entrega_estimada']); ?></td>
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
                <?php else: ?>
                    <div class="no-data">
                        <p>No hay órdenes en el rango de fechas seleccionado.</p>
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