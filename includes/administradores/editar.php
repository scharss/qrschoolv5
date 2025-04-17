<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Verificar que el usuario está autenticado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Validar datos
$id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
$nombre = trim(htmlspecialchars($_POST['nombre'] ?? '', ENT_QUOTES, 'UTF-8'));
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID no válido']);
    exit;
}

if (empty($nombre) || empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nombre y email son requeridos']);
    exit;
}

// Validar email con filter_var en lugar de FILTER_SANITIZE_EMAIL
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email no válido']);
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    // Iniciar transacción para asegurar la integridad de los datos
    $conn->beginTransaction();
    
    // Verificar que el ID corresponda a un administrador existente
    $check_stmt = $conn->prepare("SELECT id FROM usuarios WHERE id = ? AND rol_id = 1");
    $check_stmt->execute([$id]);
    if (!$check_stmt->fetch()) {
        $conn->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Administrador no encontrado']);
        exit;
    }
    
    // Verificar si ya existe otro usuario con ese email
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->fetch()) {
        $conn->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ya existe un usuario con este email']);
        exit;
    }

    // Actualizar el administrador
    if (!empty($password)) {
        // Si se proporcionó una nueva contraseña, actualizarla también
        $stmt = $conn->prepare("
            UPDATE usuarios 
            SET nombre = ?, correo = ?, password = ? 
            WHERE id = ? AND rol_id = 1
        ");
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute([$nombre, $email, $hashed_password, $id]);
    } else {
        // Si no se proporcionó contraseña, actualizar solo nombre y email
        $stmt = $conn->prepare("
            UPDATE usuarios 
            SET nombre = ?, correo = ? 
            WHERE id = ? AND rol_id = 1
        ");
        $stmt->execute([$nombre, $email, $id]);
    }

    // Verificar si se realizaron cambios
    if ($stmt->rowCount() > 0) {
        // Confirmar la transacción
        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Administrador actualizado correctamente'
        ]);
    } else {
        // No se realizaron cambios, pero no es un error
        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => 'No se realizaron cambios en el administrador'
        ]);
    }

} catch (PDOException $e) {
    // Si hay una transacción activa, revertirla
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Registrar el error específico para depuración
    error_log("Error al actualizar administrador: " . $e->getMessage());
    
    // Determinar el tipo de error para dar un mensaje más específico
    $errorMessage = 'Error al actualizar el administrador';
    
    // Errores comunes de base de datos
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        $errorMessage = 'Ya existe un usuario con este email';
        http_response_code(400);
    } else {
        http_response_code(500);
    }
    
    echo json_encode([
        'success' => false,
        'message' => $errorMessage
    ]);
}