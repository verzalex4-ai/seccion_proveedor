<?php
// Incluir archivo de configuración
require_once '../config.php';

// Conectar a la base de datos
$conexion = conectarDB();

// Verificar si se recibió un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    mostrarMensaje('ID de proveedor no especificado', 'danger');
    header('Location: index.php');
    exit();
}

$id = intval($_GET['id']);

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
        // Preparar la consulta SQL de actualización
        $sql = "UPDATE proveedores SET nombre=?, razon_social=?, cuit=?, contacto=?, email=?, telefono=?, direccion=?, condiciones_pago=?, estado=? WHERE id=?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssssssi", $nombre, $razon_social, $cuit, $contacto, $email, $telefono, $direccion, $condiciones_pago, $estado, $id);
        
        if ($stmt->execute()) {
            mostrarMensaje('Proveedor actualizado exitosamente', 'success');
            header('Location: index.php');
            exit();
        } else {
            mostrarMensaje('Error al actualizar el proveedor: ' . $conexion->error, 'danger');
        }
        
        $stmt->close();
    }
}

// Obtener datos del proveedor
$sql = "SELECT * FROM proveedores WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    mostrarMensaje('Proveedor no encontrado', 'danger');
    header('Location: index.php');
    exit();
}

$proveedor = $resultado->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proveedor</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
<style>
    /* ------------------------------------------- */
    /* ESTILOS DEL CONTENEDOR PRINCIPAL */
    /* ------------------------------------------- */
    .form-container {
        background-color: white;
        padding: 25px;
        border-radius: 0.35rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        
        /* CORRECCIÓN 1: Centra el contenedor horizontalmente */
        margin: 20px auto; 
        
        /* CORRECCIÓN 2: Usamos el ancho de 1051px para consistencia */
        width: 100%; 
        max-width: 1051px; /* Ajustado al ancho que elegiste */
    }

    .form-header {
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e3e6f0;
    }

    .form-header h2 {
        color: #5a5c69;
        font-size: 1.35rem;
        font-weight: 700;
        margin: 0;
    }

    /* ------------------------------------------- */
    /* ESTILOS DE GRUPOS Y CONTROLES (INPUTS) */
    /* ------------------------------------------- */
    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #5a5c69;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .form-group label .required {
        color: #e74a3b;
    }

    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d3e2;
        border-radius: 0.35rem;
        font-size: 0.875rem;
        color: #5a5c69;
        transition: border-color 0.15s;
        font-family: 'Nunito', sans-serif;
        
        /* CORRECCIÓN 3: Asegura que el padding/border no desborde el ancho (box-sizing) */
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
        /* Configuración de dos columnas de igual ancho para poner inputs lado a lado */
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        padding-top: 15px;
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
        min-height: 80px;
    }

    /* ------------------------------------------- */
    /* OTROS ESTILOS DE COMPONENTES */
    /* ------------------------------------------- */
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

    .info-badge {
        display: inline-block;
        background-color: #d1ecf1;
        color: #0c5460;
        padding: 5px 10px;
        border-radius: 0.25rem;
        font-size: 0.8rem;
        margin-bottom: 15px;
    }

    /* ------------------------------------------- */
    /* MEDIA QUERY (RESPONSIVE) */
    /* ------------------------------------------- */
    @media (max-width: 768px) {
        .form-row {
            /* En pantallas pequeñas, las filas se convierten en una sola columna */
            grid-template-columns: 1fr;
        }
    }
</style>
</head>
<body>

    <header class="navbar">
        <div class="logo">📦 Sistema de Compras v1</div>
        <div class="title">Editar Proveedor</div>
    </header>

    <div class="main-container">

        <aside class="sidebar">
            <h3 class="sidebar-heading">MÓDULOS</h3>
            
            <div class="sidebar-module">
                <a href="#" class="sidebar-link collapsed" onclick="toggleSubmenu('proveedores', this)">1. Gestión de Proveedores</a>
                <ul class="submenu show" id="submenu-proveedores">
                    <li><a href="index.php">Listado de Proveedores</a></li>
                    <li><a href="agregar.php">Agregar Proveedor</a></li>
                    <li><a href="editar.php" style="background-color: #354e99;">Editar Proveedor</a></li>
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
                    <h2>✏️ Editar Proveedor</h2>
                    <span class="info-badge">ID: <?php echo $proveedor['id']; ?> | Registrado: <?php echo formatearFecha($proveedor['fecha_registro']); ?></span>
                </div>

                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nombre del Proveedor <span class="required">*</span></label>
                            <input type="text" name="nombre" class="form-control" required value="<?php echo htmlspecialchars($proveedor['nombre']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Razón Social</label>
                            <input type="text" name="razon_social" class="form-control" value="<?php echo htmlspecialchars($proveedor['razon_social']); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>CUIT</label>
                            <input type="text" name="cuit" class="form-control" value="<?php echo htmlspecialchars($proveedor['cuit']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Persona de Contacto</label>
                            <input type="text" name="contacto" class="form-control" value="<?php echo htmlspecialchars($proveedor['contacto']); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($proveedor['email']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($proveedor['telefono']); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Dirección</label>
                        <textarea name="direccion" class="form-control"><?php echo htmlspecialchars($proveedor['direccion']); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Condiciones de Pago</label>
                            <select name="condiciones_pago" class="form-control">
                                <option value="Contado" <?php echo ($proveedor['condiciones_pago'] == 'Contado') ? 'selected' : ''; ?>>Contado</option>
                                <option value="7 días" <?php echo ($proveedor['condiciones_pago'] == '7 días') ? 'selected' : ''; ?>>7 días</option>
                                <option value="15 días" <?php echo ($proveedor['condiciones_pago'] == '15 días') ? 'selected' : ''; ?>>15 días</option>
                                <option value="30 días" <?php echo ($proveedor['condiciones_pago'] == '30 días') ? 'selected' : ''; ?>>30 días</option>
                                <option value="60 días" <?php echo ($proveedor['condiciones_pago'] == '60 días') ? 'selected' : ''; ?>>60 días</option>
                                <option value="90 días" <?php echo ($proveedor['condiciones_pago'] == '90 días') ? 'selected' : ''; ?>>90 días</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Estado</label>
                            <select name="estado" class="form-control">
                                <option value="Activo" <?php echo ($proveedor['estado'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                                <option value="Inactivo" <?php echo ($proveedor['estado'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">💾 Actualizar Proveedor</button>
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

<?php
// Cerrar conexión
cerrarDB($conexion);
?>