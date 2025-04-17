<?php
session_start();
require_once '../config/database.php';
require_once 'utils.php';

// Función para obtener la ruta base
function getBasePath() {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    return rtrim(str_replace('/includes', '', $scriptDir), '/');
}

// Habilitar todos los errores para debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log de acceso
error_log("Intento de login de estudiante - " . date('Y-m-d H:i:s'));

// Verificar método y tipo de petición
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleError('Método no permitido', 405);
}

// Obtener y validar datos
$documento = filter_input(INPUT_POST, 'documento', FILTER_SANITIZE_STRING);

error_log("Intento de login para documento de estudiante: " . $documento);

if (!$documento) {
    handleError('Por favor ingrese su número de documento', 400);
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        error_log("Error de conexión a la base de datos");
        handleError('Error de conexión a la base de datos', 500);
    }

    // Debug de la conexión
    error_log("Conexión establecida correctamente");

    // Verificar que el estudiante existe y está activo
    $stmt = $conn->prepare("
        SELECT e.*, g.nombre as grupo_nombre 
        FROM estudiantes e 
        LEFT JOIN grupos g ON e.grupo_id = g.id 
        WHERE e.documento = ? AND e.activo = 1
    ");
    
    if (!$stmt->execute([$documento])) {
        error_log("Error al ejecutar la consulta: " . implode(" ", $stmt->errorInfo()));
        handleError('Error al verificar estudiante', 500);
    }

    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Debug de la consulta
    error_log("Resultado de la consulta: " . ($estudiante ? "Estudiante encontrado" : "Estudiante no encontrado"));
    
    if ($estudiante) {
        // Establecer variables de sesión para el estudiante
        $_SESSION['estudiante_id'] = $estudiante['id'];
        $_SESSION['estudiante_nombre'] = $estudiante['nombre'] . ' ' . $estudiante['apellidos'];
        $_SESSION['estudiante_documento'] = $estudiante['documento'];
        $_SESSION['user_role'] = 'estudiante';
        
        $basePath = getBasePath();
        $redirect = 'pages/estudiante/perfil.php';
        
        error_log("Login exitoso para estudiante: " . $estudiante['nombre'] . ' ' . $estudiante['apellidos']);
        error_log("Redirigiendo a: " . $redirect);
        
        sendJsonResponse([
            'success' => true,
            'redirect' => $redirect
        ]);
    } else {
        error_log("Estudiante no encontrado para documento: " . $documento);
        handleError('Estudiante no encontrado o inactivo', 401);
    }
} catch (PDOException $e) {
    error_log("Error en login_estudiante.php: " . $e->getMessage());
    handleError('Error en el servidor: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    error_log("Error inesperado: " . $e->getMessage());
    handleError('Error inesperado: ' . $e->getMessage(), 500);
} 