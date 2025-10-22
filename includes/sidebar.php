<aside class="sidebar">
            <h3 class="sidebar-heading">MÓDULOS</h3>
            
            <div class="sidebar-module">
                <a href="#" class="sidebar-link collapsed" onclick="toggleSubmenu('proveedores', this)">1. Gestión de Proveedores</a>
                <ul class="submenu <?php echo (isset($active_module) && $active_module == 'proveedores') ? 'show' : ''; ?>" id="submenu-proveedores">
                    <li><a href="<?php echo isset($base_path) ? $base_path : '../'; ?>proveedores/index.php" <?php echo (isset($active_page) && $active_page == 'proveedores-index') ? 'style="background-color: #354e99;"' : ''; ?>>Listado de Proveedores</a></li>
                    <li><a href="<?php echo isset($base_path) ? $base_path : '../'; ?>proveedores/agregar.php" <?php echo (isset($active_page) && $active_page == 'proveedores-agregar') ? 'style="background-color: #354e99;"' : ''; ?>>Agregar Proveedor</a></li>
                    <li><a href="<?php echo isset($base_path) ? $base_path : '../'; ?>proveedores/editar.php" <?php echo (isset($active_page) && $active_page == 'proveedores-editar') ? 'style="background-color: #354e99;"' : ''; ?>>Editar Proveedor</a></li>
                    <li><a href="<?php echo isset($base_path) ? $base_path : '../'; ?>proveedores/eliminar.php" <?php echo (isset($active_page) && $active_page == 'proveedores-eliminar') ? 'style="background-color: #354e99;"' : ''; ?>>Eliminar Proveedor</a></li>
                </ul>
            </div>
            
            <div class="sidebar-module">
                <a href="#" class="sidebar-link collapsed" onclick="toggleSubmenu('ordenes', this)">2. Órdenes de Compra</a>
                <ul class="submenu <?php echo (isset($active_module) && $active_module == 'ordenes') ? 'show' : ''; ?>" id="submenu-ordenes">
                    <li><a href="<?php echo isset($base_path) ? $base_path : '../'; ?>ordenes/crear.php" <?php echo (isset($active_page) && $active_page == 'ordenes-crear') ? 'style="background-color: #354e99;"' : ''; ?>>Crear Nueva OC</a></li>
                    <li><a href="<?php echo isset($base_path) ? $base_path : '../'; ?>ordenes/index.php" <?php echo (isset($active_page) && $active_page == 'ordenes-index') ? 'style="background-color: #354e99;"' : ''; ?>>Listado y Seguimiento</a></li>
                    <li><a href="<?php echo isset($base_path) ? $base_path : '../'; ?>ordenes/recepcion.php" <?php echo (isset($active_page) && $active_page == 'ordenes-recepcion') ? 'style="background-color: #354e99;"' : ''; ?>>Recepción de Material</a></li>
                    <li><a href="<?php echo isset($base_path) ? $base_path : '../'; ?>ordenes/historial.php" <?php echo (isset($active_page) && $active_page == 'ordenes-historial') ? 'style="background-color: #354e99;"' : ''; ?>>Historial de Órdenes</a></li>
                </ul>
            </div>

            <div class="sidebar-module">
                <a href="#" class="sidebar-link collapsed" onclick="toggleSubmenu('pagos', this)">3. Control de Pagos</a>
                <ul class="submenu <?php echo (isset($active_module) && $active_module == 'pagos') ? 'show' : ''; ?>" id="submenu-pagos">
                    <li><a href="<?php echo isset($base_path) ? $base_path : '../'; ?>pagos/pendientes.php" <?php echo (isset($active_page) && $active_page == 'pagos-pendientes') ? 'style="background-color: #354e99;"' : ''; ?>>Saldos Pendientes</a></li>
                    <li><a href="<?php echo isset($base_path) ? $base_path : '../'; ?>pagos/registrar.php" <?php echo (isset($active_page) && $active_page == 'pagos-registrar') ? 'style="background-color: #354e99;"' : ''; ?>>Registrar Pago</a></li>
                    <li><a href="<?php echo isset($base_path) ? $base_path : '../'; ?>pagos/condiciones.php" <?php echo (isset($active_page) && $active_page == 'pagos-condiciones') ? 'style="background-color: #354e99;"' : ''; ?>>Condiciones de Pago</a></li>
                    <li><a href="<?php echo isset($base_path) ? $base_path : '../'; ?>pagos/reportes.php" <?php echo (isset($active_page) && $active_page == 'pagos-reportes') ? 'style="background-color: #354e99;"' : ''; ?>>Reportes Financieros</a></li>
                </ul>
            </div>

            <h3 class="sidebar-heading">OTROS</h3>
            <a href="<?php echo isset($base_path) ? $base_path : '../'; ?>reportes/index.php" class="sidebar-link" <?php echo (isset($active_page) && $active_page == 'reportes') ? 'style="background-color: rgba(255, 255, 255, 0.15);"' : ''; ?>>Reportes Generales</a>
            <a href="<?php echo isset($base_path) ? $base_path : ''; ?>index.php" class="sidebar-link">🏠 Volver al Inicio</a>
        </aside>

        <main class="main-content">