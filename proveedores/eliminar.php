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

// Verificar si el proveedor existe
$sql = "SELECT nombre FROM proveedores WHERE id = ?";
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

// Verificar si hay órdenes asociadas
$sql_check = "SELECT COUNT(*) as total FROM ordenes_compra WHERE id_proveedor = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("i", $id);
$stmt_check->execute();
$resultado_check = $stmt_check->get_result();
$ordenes = $resultado_check->fetch_assoc();
$stmt_check->close();

// Procesar eliminación si se confirma
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmar'])) {
    if ($ordenes['total'] > 0) {
        mostrarMensaje('No se puede eliminar el proveedor porque tiene órdenes de compra asociadas', 'danger');
    } else {
        $sql = "DELETE FROM proveedores WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            mostrarMensaje('Proveedor eliminado exitosamente', 'success');
            $stmt->close();
            cerrarDB($conexion);
            header('Location: index.php');
            exit();
        } else {
            mostrarMensaje('Error al eliminar el proveedor: ' . $conexion->error, 'danger');
        }
        
        $stmt->close();
    }
}

cerrarDB($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Proveedor</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
<style>
    /* ------------------------------------------- */
    /* ESTILOS DEL CONTENEDOR PRINCIPAL DE ELIMINACIÓN */
    /* ------------------------------------------- */
    .delete-container {
        background-color: white;
        padding: 25px;
        border-radius: 0.35rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        
        /* AJUSTE FINAL: Aumentamos el ancho máximo a 1000px */
        max-width: 1051px; 
        
        /* Centra el contenedor horizontalmente y aplica margen vertical */
        margin: 20px auto; 
        
        /* Asegura que se adapte bien en pantallas pequeñas */
        width: 100%; 
    }

    .delete-header {
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e3e6f0;
    }

    .delete-header h2 {
        color: #e74a3b;
        font-size: 1.35rem;
        font-weight: 700;
        margin: 0;
    }

    /* ------------------------------------------- */
    /* El resto del código se mantiene igual */
    /* ------------------------------------------- */
    .warning-box {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px;
        border-radius: 0.35rem;
        margin-bottom: 20px;
    }

    .warning-box h3 {
        color: #856404;
        font-size: 1rem;
        margin: 0 0 10px 0;
    }

    .warning-box p {
        color: #856404;
        margin: 5px 0;
        font-size: 0.875rem;
    }

    .error-box {
        background-color: #f8d7da;
        border-left: 4px solid #e74a3b;
        padding: 15px;
        border-radius: 0.35rem;
        margin-bottom: 20px;
    }

    .error-box p {
        color: #721c24;
        margin: 5px 0;
        font-size: 0.875rem;
    }

    .info-item {
        padding: 10px;
        margin-bottom: 8px;
        background-color: #f8f9fc;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    .info-item strong {
        color: #4e73df;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #e3e6f0;
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
</style>
</head>
<body>

    <header class="navbar">
        <div class="logo">📦 Sistema de Compras v1</div>
        <div class="title">Eliminar Proveedor</div>
    </header>

    <div class="main-container">

        <aside class="sidebar">
            <h3 class="sidebar-heading">MÓDULOS</h3>
            
            <div class="sidebar-module">
                <a href="#" class="sidebar-link collapsed" onclick="toggleSubmenu('proveedores', this)">1. Gestión de Proveedores</a>
                <ul class="submenu show" id="submenu-proveedores">
                    <li><a href="index.php">Listado de Proveedores</a></li>
                    <li><a href="agregar.php">Agregar Proveedor</a></li>
                    <li><a href="editar.php">Editar Proveedor</a></li>
                    <li><a href="eliminar.php" style="background-color: #354e99;">Eliminar Proveedor</a></li>
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

            <div class="delete-container">
                <div class="delete-header">
                    <h2>🗑️ Eliminar Proveedor</h2>
                </div>

                <?php if ($ordenes['total'] > 0): ?>
                    <div class="error-box">
                        <p><strong>⚠️ No se puede eliminar este proveedor</strong></p>
                        <p>El proveedor tiene <strong><?php echo $ordenes['total']; ?> orden(es) de compra</strong> asociada(s).</p>
                        <p>Debe eliminar primero todas las órdenes relacionadas o considerar cambiar el estado a "Inactivo".</p>
                    </div>
                    
                    <div class="info-item">
                        <strong>Proveedor:</strong> <?php echo htmlspecialchars($proveedor['nombre']); ?>
                    </div>

                    <div class="form-actions">
                        <a href="editar.php?id=<?php echo $id; ?>" class="btn-info">✏️ Editar Proveedor</a>
                        <a href="index.php" class="btn-primary">← Volver al Listado</a>
                    </div>
                    
                <?php else: ?>
                    <div class="warning-box">
                        <h3>⚠️ Confirmación de Eliminación</h3>
                        <p>¿Está seguro de que desea eliminar este proveedor?</p>
                        <p><strong>Esta acción no se puede deshacer.</strong></p>
                    </div>

                    <div class="info-item">
                        <strong>Proveedor:</strong> <?php echo htmlspecialchars($proveedor['nombre']); ?>
                    </div>

                    <form method="POST" action="">
                        <div class="form-actions">
                            <button type="submit" name="confirmar" class="btn-danger">🗑️ Sí, Eliminar Proveedor</button>
                            <a href="index.php" class="btn-primary">❌ Cancelar</a>
                        </div>
                    </form>
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