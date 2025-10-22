<?php
// Incluir archivo de configuración
require_once '../config.php';

// Conectar a la base de datos
$conexion = conectarDB();

// Filtros
$filtro_proveedor = isset($_GET['proveedor']) ? intval($_GET['proveedor']) : 0;
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';

// Construir consulta SQL con filtros
$sql = "SELECT o.*, p.nombre as nombre_proveedor 
        FROM ordenes_compra o 
        INNER JOIN proveedores p ON o.id_proveedor = p.id 
        WHERE 1=1";

if ($filtro_proveedor > 0) {
    $sql .= " AND o.id_proveedor = $filtro_proveedor";
}

if (!empty($filtro_estado)) {
    $sql .= " AND o.estado = '" . $conexion->real_escape_string($filtro_estado) . "'";
}

$sql .= " ORDER BY o.fecha_emision DESC";

$resultado = $conexion->query($sql);

// Obtener proveedores para el filtro
$sql_proveedores = "SELECT id, nombre FROM proveedores WHERE estado = 'Activo' ORDER BY nombre ASC";
$proveedores = $conexion->query($sql_proveedores);

// Obtener mensaje de sesión si existe
$mensaje = obtenerMensaje();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Órdenes de Compra</title>
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

        .filters-box {
            background-color: #f8f9fc;
            padding: 15px;
            border-radius: 0.35rem;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: end;
        }

        .filter-group {
            flex: 1;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            color: #5a5c69;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .filter-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d3e2;
            border-radius: 0.35rem;
            font-size: 0.875rem;
            font-family: 'Nunito', sans-serif;
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
            .filters-box {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="logo">📦 Sistema de Compras v1</div>
        <div class="title">Órdenes de Compra</div>
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
                    <li><a href="index.php" style="background-color: #354e99;">Listado y Seguimiento</a></li>
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
            
            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $mensaje['tipo']; ?>">
                    <?php echo $mensaje['texto']; ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <div class="table-header">
                    <h2>📋 Listado de Órdenes de Compra</h2>
                    <a href="crear.php" class="btn-success">➕ Crear Nueva Orden</a>
                </div>

                <form method="GET" class="filters-box">
                    <div class="filter-group">
                        <label>Filtrar por Proveedor</label>
                        <select name="proveedor" onchange="this.form.submit()">
                            <option value="0">Todos los proveedores</option>
                            <?php while ($prov = $proveedores->fetch_assoc()): ?>
                                <option value="<?php echo $prov['id']; ?>" <?php echo ($filtro_proveedor == $prov['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prov['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Filtrar por Estado</label>
                        <select name="estado" onchange="this.form.submit()">
                            <option value="">Todos los estados</option>
                            <option value="Pendiente" <?php echo ($filtro_estado == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="Enviada" <?php echo ($filtro_estado == 'Enviada') ? 'selected' : ''; ?>>Enviada</option>
                            <option value="Recibida" <?php echo ($filtro_estado == 'Recibida') ? 'selected' : ''; ?>>Recibida</option>
                            <option value="Cancelada" <?php echo ($filtro_estado == 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <a href="index.php" class="btn-info">🔄 Limpiar Filtros</a>
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
                                <th>Acciones</th>
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
                                    <td class="actions-cell">
                                        <a href="ver.php?id=<?php echo $orden['id']; ?>" class="btn-info btn-small">👁️ Ver</a>
                                        <?php if ($orden['estado'] != 'Recibida' && $orden['estado'] != 'Cancelada'): ?>
                                            <a href="recepcion.php?id=<?php echo $orden['id']; ?>" class="btn-success btn-small">✅ Recepcionar</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">
                        <p>No hay órdenes de compra registradas con los filtros seleccionados.</p>
                        <a href="crear.php" class="btn-primary">➕ Crear la primera orden</a>
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