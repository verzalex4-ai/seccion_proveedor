<?php
require_once 'config.php';
$conexion = conectarDB();
$stats = ['total_proveedores' => 0, 'ordenes_pendientes' => 0, 'ordenes_mes' => 0, 'pagos_vencidos' => 0];
try {
    $sql_stats = "SELECT 
        (SELECT COUNT(*) FROM proveedores WHERE estado = 'Activo') as total_proveedores,
        (SELECT COUNT(*) FROM ordenes_compra WHERE estado IN ('Pendiente', 'Enviada')) as ordenes_pendientes,
        (SELECT COUNT(*) FROM ordenes_compra WHERE MONTH(fecha_emision) = MONTH(CURDATE()) AND YEAR(fecha_emision) = YEAR(CURDATE())) as ordenes_mes,
        (SELECT COUNT(DISTINCT o.id) FROM ordenes_compra o LEFT JOIN (SELECT id_orden, SUM(monto) as pagado FROM pagos GROUP BY id_orden) p ON o.id = p.id_orden INNER JOIN proveedores prov ON o.id_proveedor = prov.id WHERE o.estado = 'Recibida' AND (o.total - COALESCE(p.pagado, 0)) > 0 AND DATEDIFF(CURDATE(), CASE WHEN prov.condiciones_pago = 'Contado' THEN DATE_ADD(o.fecha_emision, INTERVAL 0 DAY) WHEN prov.condiciones_pago = '7 días' THEN DATE_ADD(o.fecha_emision, INTERVAL 7 DAY) WHEN prov.condiciones_pago = '15 días' THEN DATE_ADD(o.fecha_emision, INTERVAL 15 DAY) WHEN prov.condiciones_pago = '30 días' THEN DATE_ADD(o.fecha_emision, INTERVAL 30 DAY) WHEN prov.condiciones_pago = '60 días' THEN DATE_ADD(o.fecha_emision, INTERVAL 60 DAY) WHEN prov.condiciones_pago = '90 días' THEN DATE_ADD(o.fecha_emision, INTERVAL 90 DAY) ELSE DATE_ADD(o.fecha_emision, INTERVAL 30 DAY) END) > 0) as pagos_vencidos";
    $resultado = $conexion->query($sql_stats);
    if ($resultado) {
        $stats = $resultado->fetch_assoc();
    } else {
        $error_db = "Error en la consulta: " . $conexion->error;
    }
} catch (Exception $e) {
    $error_db = "Error de conexión: " . $e->getMessage();
}
cerrarDB($conexion);
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Compras y Proveedores</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="navbar">
        <div class="logo">📦 Sistema de Compras v1</div>
        <div class="title">Panel de Gestión</div>
    </header>
    <div class="main-container">
        <aside class="sidebar">
            <h3 class="sidebar-heading">MÓDULOS</h3>
            <div class="sidebar-module">
                <a href="#" class="sidebar-link collapsed" onclick="toggleSubmenu('proveedores', this)">1. Gestión de Proveedores</a>
                <ul class="submenu" id="submenu-proveedores">
                    <li><a href="proveedores/index.php">Listado de Proveedores</a></li>
                    <li><a href="proveedores/agregar.php">Agregar Proveedor</a></li>
                    <li><a href="proveedores/editar.php">Editar Proveedor</a></li>
                    <li><a href="proveedores/eliminar.php">Eliminar Proveedor</a></li>
                </ul>
            </div>
            <div class="sidebar-module">
                <a href="#" class="sidebar-link collapsed" onclick="toggleSubmenu('ordenes', this)">2. Órdenes de Compra</a>
                <ul class="submenu" id="submenu-ordenes">
                    <li><a href="ordenes/crear.php">Crear Nueva OC</a></li>
                    <li><a href="ordenes/index.php">Listado y Seguimiento</a></li>
                    <li><a href="ordenes/recepcion.php">Recepción de Material</a></li>
                    <li><a href="ordenes/historial.php">Historial de Órdenes</a></li>
                </ul>
            </div>
            <div class="sidebar-module">
                <a href="#" class="sidebar-link collapsed" onclick="toggleSubmenu('pagos', this)">3. Control de Pagos</a>
                <ul class="submenu" id="submenu-pagos">
                    <li><a href="pagos/pendientes.php">Saldos Pendientes</a></li>
                    <li><a href="pagos/registrar.php">Registrar Pago</a></li>
                    <li><a href="pagos/condiciones.php">Condiciones de Pago</a></li>
                    <li><a href="pagos/reportes.php">Reportes Financieros</a></li>
                </ul>
            </div>
            <h3 class="sidebar-heading">OTROS</h3>
            <a href="reportes/index.php" class="sidebar-link">Reportes Generales</a>
        </aside>
        <main class="main-content">
            <?php if (isset($error_db)): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 0.35rem; margin-bottom: 20px; border-left: 4px solid #e74a3b;">
                    <strong>⚠️ Error:</strong> <?php echo $error_db; ?>
                </div>
            <?php endif; ?>
            <section class="stat-grid">
                <div class="stat-card primary">
                    <p class="stat-title">Proveedores Activos</p>
                    <p class="stat-value"><?php echo $stats['total_proveedores']; ?></p>
                </div>
                <div class="stat-card danger">
                    <p class="stat-title">Órdenes Pendientes</p>
                    <p class="stat-value"><?php echo $stats['ordenes_pendientes']; ?></p>
                </div>
                <div class="stat-card success">
                    <p class="stat-title">Órdenes del Mes</p>
                    <p class="stat-value"><?php echo $stats['ordenes_mes']; ?></p>
                </div>
                <div class="stat-card info">
                    <p class="stat-title">Pagos Vencidos</p>
                    <p class="stat-value"><?php echo $stats['pagos_vencidos']; ?></p>
                </div>
            </section>
            <section class="info-box">
                <h2>Seguimiento Rápido</h2>
                <p>Accede directamente a las funciones más utilizadas del sistema.</p>
                <button class="btn-primary" onclick="window.location.href='ordenes/index.php'">Ir a Listado de OC</button>
                <button class="btn-danger" onclick="window.location.href='pagos/pendientes.php'">Revisar Cuentas por Pagar</button>
            </section>
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