<?php
/**
 * Archivo de Configuración - Sistema de Gestión de Compras
 * Conexión a Base de Datos MySQL mediante XAMPP
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');      // Servidor (en XAMPP siempre es localhost)
define('DB_USER', 'root');           // Usuario por defecto de XAMPP
define('DB_PASS', '');               // Contraseña vacía por defecto en XAMPP
define('DB_NAME', 'sistema_compras'); // Nombre de tu base de datos

// Configuración de zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Configuración de sesión
session_start();

/**
 * Función para conectar a la base de datos
 * @return mysqli|false Retorna la conexión o false en caso de error
 */
function conectarDB() {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verificar conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    
    // Establecer el charset a UTF-8
    $conexion->set_charset("utf8mb4");
    
    return $conexion;
}

/**
 * Función para cerrar la conexión a la base de datos
 * @param mysqli $conexion
 */
function cerrarDB($conexion) {
    if ($conexion) {
        $conexion->close();
    }
}

/**
 * Función para sanitizar datos de entrada
 * @param string $data
 * @return string
 */
function limpiarDatos($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Función para mostrar mensajes de error o éxito
 * @param string $mensaje
 * @param string $tipo (success, danger, warning, info)
 */
function mostrarMensaje($mensaje, $tipo = 'info') {
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo_mensaje'] = $tipo;
}

/**
 * Función para obtener y limpiar mensaje de sesión
 * @return array|null
 */
function obtenerMensaje() {
    if (isset($_SESSION['mensaje'])) {
        $mensaje = [
            'texto' => $_SESSION['mensaje'],
            'tipo' => $_SESSION['tipo_mensaje'] ?? 'info'
        ];
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
        return $mensaje;
    }
    return null;
}

/**
 * Función para formatear fecha
 * @param string $fecha
 * @return string
 */
function formatearFecha($fecha) {
    if (empty($fecha)) return '';
    $timestamp = strtotime($fecha);
    return date('d/m/Y', $timestamp);
}

/**
 * Función para formatear moneda argentina
 * @param float $monto
 * @return string
 */
function formatearMoneda($monto) {
    return '$' . number_format($monto, 2, ',', '.');
}

// Probar conexión al cargar el archivo (opcional, comentar en producción)
// $test_conn = conectarDB();
// if ($test_conn) {
//     echo "Conexión exitosa a la base de datos!";
//     cerrarDB($test_conn);
// }
?>