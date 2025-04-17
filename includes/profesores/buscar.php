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

if (!isset($_GET['documento']) || empty($_GET['documento'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Se requiere el documento del profesor']);
    exit;
}

$documento = htmlspecialchars(trim($_GET['documento']), ENT_QUOTES, 'UTF-8');

$db = new Database();
$conn = $db->connect();

try {
    // Buscar profesor por documento
    $stmt = $conn->prepare("
        SELECT id, nombre, apellidos, correo, documento, created_at 
        FROM usuarios 
        WHERE documento = ? AND rol_id = 2
    ");
    $stmt->execute([$documento]);
    $profesor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profesor) {
        echo json_encode(['success' => false, 'message' => 'No se encontró ningún profesor con ese documento']);
        exit;
    }

    // Retornar los datos del profesor
    echo json_encode([
        'success' => true, 
        'profesor' => $profesor
    ]);

} catch (PDOException $e) {
    error_log("Error al buscar profesor: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al buscar el profesor']);
} 