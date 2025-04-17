<?php
session_start();
require_once '../../config/database.php';
require_once '../utils.php';

header('Content-Type: application/json');

// Verificar que el usuario está autenticado y es profesor o administrador
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'profesor' && $_SESSION['user_role'] !== 'administrador')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener y validar los datos
$currentPassword = $_POST['currentPassword'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';

// Validar que los campos no estén vacíos
if (empty($currentPassword) || empty($newPassword)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
    exit;
}

// Validar la longitud de la nueva contraseña
if (strlen($newPassword) < 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 8 caracteres']);
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    // Obtener la información del usuario actual
    $stmt = $conn->prepare("SELECT id, password FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }

    // Verificar que la contraseña actual sea correcta
    if (!password_verify($currentPassword, $usuario['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
        exit;
    }

    // Generar hash para la nueva contraseña
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // Actualizar la contraseña en la base de datos
    $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
    $result = $stmt->execute([$passwordHash, $_SESSION['user_id']]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente'
        ]);
    } else {
        throw new Exception('Error al actualizar la contraseña');
    }
} catch (PDOException $e) {
    error_log("Error al cambiar contraseña: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error inesperado: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error inesperado: ' . $e->getMessage()
    ]);
} 