<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Verificar que el usuario está autenticado con un rol válido
// O que es un estudiante accediendo a sus propias asistencias
$is_student_request = isset($_GET['for_student']) && $_GET['for_student'] === 'true' && 
                     isset($_SESSION['estudiante_id']) && $_SESSION['user_role'] === 'estudiante';

$is_admin_request = isset($_SESSION['user_id']) && in_array($_SESSION['user_role'], ['administrador', 'profesor']);

if (!$is_admin_request && !$is_student_request) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado', 'data' => []]);
    exit;
}

// Verificar que se proporcionó un estudiante_id
if (!isset($_GET['estudiante_id']) || !is_numeric($_GET['estudiante_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de estudiante no válido', 'data' => []]);
    exit;
}

$estudiante_id = (int)$_GET['estudiante_id'];

// Si es un estudiante, verificar que está accediendo a sus propias asistencias
if ($is_student_request && $estudiante_id != $_SESSION['estudiante_id']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado para ver estas asistencias', 'data' => []]);
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    // Primero verificar que el estudiante existe y está activo
    $stmt = $conn->prepare("SELECT id FROM estudiantes WHERE id = ? AND activo = 1");
    $stmt->execute([$estudiante_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Estudiante no encontrado', 'data' => []]);
        exit;
    }

    // Obtener las asistencias
    $stmt = $conn->prepare("
        SELECT 
            a.fecha_hora,
            DATE_FORMAT(a.fecha_hora, '%d/%m/%Y %H:%i:%s') as fecha_hora_format,
            CONCAT(u.nombre, ' ', u.apellidos) as profesor_nombre
        FROM asistencias a
        JOIN usuarios u ON a.profesor_id = u.id
        WHERE a.estudiante_id = ?
        ORDER BY a.fecha_hora DESC
    ");
    $stmt->execute([$estudiante_id]);
    $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $asistencias
    ]);

} catch (PDOException $e) {
    error_log("Error al obtener asistencias: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener las asistencias',
        'data' => []
    ]);
} 