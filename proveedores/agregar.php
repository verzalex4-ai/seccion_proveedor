<?php
// Incluir archivo de configuración
require_once '../config.php';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Conectar a la base de datos
    $conexion = conectarDB();
    
    // Obtener y limpiar datos del formulario
    $nombre = limpiarDatos($_POST['nombre']);
    $razon_social = limpiarDatos($_POST['razon_social']);
    $cuit = limpiarDatos($_POST['cuit']);
    $contacto = limpiarDatos($_POST['contacto']);
    $email = limpiarDatos($_POST['email']);
    $telefono = limpiarDatos($_POST['telefono']);
    $direccion = limpiarDatos($_POST['direccion']);
    $condiciones_pago = limpiarDatos($_POST['condiciones_pago']);
    $estado = $_POST['estado'];
    
    // Validar campos obligatorios
    if (empty($nombre)) {
        mostrarMensaje('El nombre del proveedor es obligatorio', 'danger');
    } else {
        // Preparar la consulta SQL
        $sql = "INSERT INTO proveedores (nombre, razon_social, cuit, contacto, email, telefono, direccion, condiciones_pago, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssssss", $nombre, $razon_social, $cuit, $contacto, $email, $telefono, $direccion, $condiciones_pago, $estado);
        
        if ($stmt->execute()) {
            mostrarMensaje('Proveedor agregado exitosamente', 'success');
            header('Location: index.php');
            exit();
        } else {
            mostrarMensaje('Error al agregar el proveedor: ' . $conexion->error, 'danger');
        }
        
        $stmt->close();
    }
    
    cerrarDB($conexion);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Proveedor</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
    <style>
    /* ------------------------------------------- */
    /* ESTILOS DEL CONTENEDOR PRINCIPAL DEL FORMULARIO */
    /* ------------------------------------------- */
    .form-container {
        background-color: white;
        padding: 30px;
        border-radius: 0.35rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        
        /* CORRECCIÓN 1: Centra el contenedor y establece el margen vertical */
        margin: 20px auto; 
        
        /* CORRECCIÓN 2: Define el ancho máximo y responsivo */
        max-width: 1051px; /* Usamos el ancho consistente de 1051px */
        width: 100%; 
    }

    .form-header {
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e3e6f0;
    }

    .form-header h2 {
        color: #5a5c69;
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
    }

    /* ------------------------------------------- */
    /* ESTILOS DE GRUPOS Y CONTROLES (INPUTS) */
    /* ------------------------------------------- */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #5a5c69;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .form-group label .required {
        color: #e74a3b;
    }

    .form-control {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #d1d3e2;
        border-radius: 0.35rem;
        font-size: 0.9rem;
        color: #5a5c69;
        transition: border-color 0.15s;
        
        /* CORRECCIÓN 3: Asegura que el padding/border no desborde el width: 100% */
        box-sizing: border-box; 
    }

    .form-control:focus {
        outline: none;
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    /* ------------------------------------------- */
    /* ESTILOS DE FILAS (GRID) */
    /* ------------------------------------------- */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e3e6f0;
    }

    /* ------------------------------------------- */
    /* ESTILOS ESPECÍFICOS DE ELEMENTOS */
    /* ------------------------------------------- */
    select.form-control {
        cursor: pointer;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 0.35rem;
        font-size: 0.9rem;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid #e74a3b;
    }

    /* ------------------------------------------- */
    /* MEDIA QUERY (RESPONSIVE) */
    /* ------------------------------------------- */
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>
</head>
<body>

    <header class="navbar">
        <div class="logo">📦 Sistema de Compras v1</div>
        <div class="title">Agregar Proveedor</div>
    </header>

    <div class="main-container">

        <aside class="sidebar">
            <h3 class="sidebar-heading">MÓDULOS</h3>
            
            <div class="sidebar-module">
                <a href="#" class="sidebar-link collapsed" onclick="toggleSubmenu('proveedores', this)">1. Gestión de Proveedores</a>
                <ul class="submenu show" id="submenu-proveedores">
                    <li><a href="index.php">Listado de Proveedores</a></li>
                    <li><a href="agregar.php" style="background-color: #354e99;">Agregar Proveedor</a></li>
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
            
            <?php 
            $mensaje = obtenerMensaje();
            if ($mensaje): 
            ?>
                <div class="alert alert-<?php echo $mensaje['tipo']; ?>">
                    <?php echo $mensaje['texto']; ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <div class="form-header">
                    <h2>➕ Agregar Nuevo Proveedor</h2>
                </div>

                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nombre del Proveedor <span class="required">*</span></label>
                            <input type="text" name="nombre" class="form-control" required placeholder="Ej: Proveedor ABC S.A.">
                        </div>

                        <div class="form-group">
                            <label>Razón Social</label>
                            <input type="text" name="razon_social" class="form-control" placeholder="Razón social completa">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>CUIT</label>
                            <input type="text" name="cuit" class="form-control" placeholder="XX-XXXXXXXX-X">
                        </div>

                        <div class="form-group">
                            <label>Persona de Contacto</label>
                            <input type="text" name="contacto" class="form-control" placeholder="Nombre del contacto">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" placeholder="email@proveedor.com">
                        </div>

                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" class="form-control" placeholder="0387-XXXXXXX">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Dirección</label>
                        <textarea name="direccion" class="form-control" placeholder="Dirección completa del proveedor"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Condiciones de Pago</label>
                            <select name="condiciones_pago" class="form-control">
                                <option value="Contado">Contado</option>
                                <option value="7 días">7 días</option>
                                <option value="15 días">15 días</option>
                                <option value="30 días">30 días</option>
                                <option value="60 días">60 días</option>
                                <option value="90 días">90 días</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Estado</label>
                            <select name="estado" class="form-control">
                                <option value="Activo" selected>Activo</option>
                                <option value="Inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-success">💾 Guardar Proveedor</button>
                        <a href="index.php" class="btn-danger">❌ Cancelar</a>
                    </div>
                </form>
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