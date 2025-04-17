<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Verificar que el usuario está autenticado y es profesor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'profesor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener los datos del QR escaneado (ahora en formato JSON)
$qrData = $_POST['estudiante_id'] ?? '';

try {
    // Intentar decodificar el JSON
    $decodedData = json_decode($qrData, true);
    
    // Verificar que sea el formato con hash
    if (json_last_error() === JSON_ERROR_NONE && isset($decodedData['id']) && isset($decodedData['hash'])) {
        // Formato con hash
        $estudiante_id = filter_var($decodedData['id'], FILTER_VALIDATE_INT);
        $hash = $decodedData['hash'];
        
        // Verificar que el ID es válido
        if (!$estudiante_id) {
            throw new Exception('ID de estudiante no válido');
        }
    } else {
        // Rechazar cualquier otro formato
        throw new Exception('Formato de QR no válido. Se requiere formato con hash.');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'QR inválido: ' . $e->getMessage()]);
    exit;
}

$profesor_id = $_SESSION['user_id'];

$db = new Database();
$conn = $db->connect();

try {
    // Verificar que el estudiante existe y está activo
    $stmt = $conn->prepare("
        SELECT e.*, g.nombre as grupo_nombre 
        FROM estudiantes e 
        LEFT JOIN grupos g ON e.grupo_id = g.id AND g.activo = 1
        WHERE e.id = ? AND e.activo = 1
    ");
    $stmt->execute([$estudiante_id]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si estamos usando el formato nuevo con hash, verificar que el hash coincida
    if (isset($hash) && !empty($hash)) {
        // Recrear el hash con los datos del estudiante para verificar
        $dataToHash = $estudiante['nombre'] . $estudiante['apellidos'] . $estudiante['documento'] . $estudiante_id;
        $calculatedHash = hash('sha256', $dataToHash);
        
        // Verificar que el hash coincida
        if ($calculatedHash !== $hash) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Código QR inválido o manipulado']);
            exit;
        }
    }

    if (!$estudiante) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Estudiante no encontrado o inactivo']);
        exit;
    }

    // Verificar si ya se registró asistencia en los últimos minutos
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM asistencias 
        WHERE estudiante_id = ? 
        AND fecha_hora >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ");
    $stmt->execute([$estudiante_id]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Ya se registró la asistencia de este estudiante recientemente'
        ]);
        exit;
    }

    // Registrar la asistencia
    $stmt = $conn->prepare("
        INSERT INTO asistencias (estudiante_id, profesor_id, fecha_hora) 
        VALUES (?, ?, NOW())
    ");
    
    if ($stmt->execute([$estudiante_id, $profesor_id])) {
        echo json_encode([
            'success' => true,
            'message' => 'Asistencia registrada correctamente',
            'estudiante' => [
                'nombre' => $estudiante['nombre'] . ' ' . $estudiante['apellidos'],
                'grupo' => $estudiante['grupo_nombre'] ?? 'Sin grupo'
            ]
        ]);
    } else {
        throw new Exception('Error al registrar la asistencia');
    }

} catch (Exception $e) {
    error_log("Error al registrar asistencia: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al registrar la asistencia'
    ]);
}