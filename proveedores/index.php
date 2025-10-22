<?php
// Incluir archivo de configuración
require_once '../config.php';

// Conectar a la base de datos
$conexion = conectarDB();

// Obtener todos los proveedores
$sql = "SELECT * FROM proveedores ORDER BY nombre ASC";
$resultado = $conexion->query($sql);

// Obtener mensaje de sesión si existe
$mensaje = obtenerMensaje();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Proveedores</title>
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
            font-size: 0.9rem;
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

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-danger {
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

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #e74a3b;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #858796;
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="logo">📦 Sistema de Compras v1</div>
        <div class="title">Gestión de Proveedores</div>
    </header>

    <div class="main-container">

        <aside class="sidebar">
            <h3 class="sidebar-heading">MÓDULOS</h3>
            
            <div class="sidebar-module">
                <a href="#" class="sidebar-link collapsed" onclick="toggleSubmenu('proveedores', this)">1. Gestión de Proveedores</a>
                <ul class="submenu show" id="submenu-proveedores">
                    <li><a href="index.php" style="background-color: #354e99;">Listado de Proveedores</a></li>
                    <li><a href="agregar.php">Agregar Proveedor</a></li>
                    <li><a href="editar.php">Editar Proveedor</a></li>
                    <li><a href="eliminar.php">Eliminar Proveedor</a></li>
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
                    <h2>📋 Listado de Proveedores</h2>
                    <a href="agregar.php" class="btn-success">➕ Agregar Nuevo Proveedor</a>
                </div>

                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Contacto</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Condiciones de Pago</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($proveedor = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $proveedor['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($proveedor['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($proveedor['contacto']); ?></td>
                                    <td><?php echo htmlspecialchars($proveedor['email']); ?></td>
                                    <td><?php echo htmlspecialchars($proveedor['telefono']); ?></td>
                                    <td><?php echo htmlspecialchars($proveedor['condiciones_pago']); ?></td>
                                    <td>
                                        <?php if ($proveedor['estado'] == 'Activo'): ?>
                                            <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="editar.php?id=<?php echo $proveedor['id']; ?>" class="btn-primary btn-small">✏️ Editar</a>
                                        <a href="eliminar.php?id=<?php echo $proveedor['id']; ?>" class="btn-danger btn-small" onclick="return confirm('¿Está seguro de eliminar este proveedor?')">🗑️ Eliminar</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">
                        <p>No hay proveedores registrados en el sistema.</p>
                        <a href="agregar.php" class="btn-primary">➕ Agregar el primer proveedor</a>
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