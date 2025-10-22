<?php
// Incluir archivo de configuración
require_once '../config.php';

// Conectar a la base de datos
$conexion = conectarDB();

// Obtener proveedores con sus condiciones
$sql = "SELECT 
            p.*,
            COUNT(o.id) as total_ordenes,
            COALESCE(SUM(o.total), 0) as monto_total_ordenes
        FROM proveedores p
        LEFT JOIN ordenes_compra o ON p.id = o.id_proveedor
        WHERE p.estado = 'Activo'
        GROUP BY p.id
        ORDER BY p.nombre ASC";

$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Condiciones de Pago</title>
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
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e3e6f0;
        }

        .table-header h2 {
            color: #5a5c69;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .proveedor-card {
            background-color: #f8f9fc;
            padding: 20px;
            border-radius: 0.35rem;
            margin-bottom: 15px;
            border-left: 4px solid #4e73df;
        }

        .proveedor-card:hover {
            background-color: #e7f3ff;
        }

        .proveedor-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .proveedor-nombre {
            font-size: 1.1rem;
            font-weight: bold;
            color: #4e73df;
        }

        .proveedor-contacto {
            font-size: 0.875rem;
            color: #858796;
        }

        .condiciones-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            padding: 15px 0;
            border-top: 1px solid #e3e6f0;
        }

        .condicion-item {
            text-align: center;
        }

        .condicion-label {
            font-size: 0.75rem;
            color: #858796;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .condicion-value {
            font-size: 1.1rem;
            font-weight: bold;
            color: #5a5c69;
        }

        .condicion-value.destacado {
            color: #4e73df;
            font-size: 1.3rem;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #858796;
        }

        @media (max-width: 768px) {
            .condiciones-row {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="logo">📦 Sistema de Compras v1</div>
        <div class="title">Condiciones de Pago</div>
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
                    <li><a href="condiciones.php" style="background-color: #354e99;">Condiciones de Pago</a></li>
                    <li><a href="reportes.php">Reportes Financieros</a></li>
                </ul>
            </div>

            <h3 class="sidebar-heading">OTROS</h3>
            <a href="../reportes/index.php" class="sidebar-link">Reportes Generales</a>
            <a href="../index.php" class="sidebar-link">🏠 Volver al Inicio</a>
        </aside>

        <main class="main-content">
            
            <div class="table-container">
                <div class="table-header">
                    <h2>📋 Condiciones Comerciales por Proveedor</h2>
                </div>

                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while ($proveedor = $resultado->fetch_assoc()): ?>
                        <div class="proveedor-card">
                            <div class="proveedor-header">
                                <div>
                                    <div class="proveedor-nombre"><?php echo htmlspecialchars($proveedor['nombre']); ?></div>
                                    <div class="proveedor-contacto">
                                        📧 <?php echo htmlspecialchars($proveedor['email']); ?> | 
                                        📞 <?php echo htmlspecialchars($proveedor['telefono']); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="condiciones-row">
                                <div class="condicion-item">
                                    <div class="condicion-label">Condiciones de Pago</div>
                                    <div class="condicion-value destacado"><?php echo htmlspecialchars($proveedor['condiciones_pago']); ?></div>
                                </div>

                                <div class="condicion-item">
                                    <div class="condicion-label">Persona de Contacto</div>
                                    <div class="condicion-value"><?php echo htmlspecialchars($proveedor['contacto']); ?></div>
                                </div>

                                <div class="condicion-item">
                                    <div class="condicion-label">Total de Órdenes</div>
                                    <div class="condicion-value"><?php echo $proveedor['total_ordenes']; ?></div>
                                </div>

                                <div class="condicion-item">
                                    <div class="condicion-label">Monto Total</div>
                                    <div class="condicion-value"><?php echo formatearMoneda($proveedor['monto_total_ordenes']); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-data">
                        <p>No hay proveedores activos registrados.</p>
                        <a href="../proveedores/agregar.php" class="btn-primary">➕ Agregar Proveedor</a>
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