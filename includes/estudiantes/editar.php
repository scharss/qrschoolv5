<?php
session_start();
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
$nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
$apellidos = filter_var($_POST['apellidos'], FILTER_SANITIZE_STRING);
$documento = filter_var($_POST['documento'], FILTER_SANITIZE_STRING);

if (!$id || !$nombre || !$apellidos || !$documento) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    // Verificar si ya existe otro estudiante con ese documento
    $stmt = $conn->prepare("SELECT id FROM estudiantes WHERE documento = ? AND id != ?");
    $stmt->execute([$documento, $id]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ya existe un estudiante con ese documento']);
        exit;
    }

    // Generar nuevo QR con hash personalizado
    // Generar un identificador único cifrado con SHA-256 usando nombre, apellidos, documento y ID
    $dataToHash = $nombre . $apellidos . $documento . $id;
    $hashedId = hash('sha256', $dataToHash);
    
    // Crear un formato que incluya el ID real y el hash para poder verificarlo después
    $qrData = json_encode([
        'id' => $id,
        'hash' => $hashedId
    ]);

    $options = new QROptions([
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel' => QRCode::ECC_L,
        'scale' => 30,
        'imageBase64' => true,
        'bgColor' => [255, 255, 255],
        'fpColor' => [0, 0, 0],
        'quietzoneSize' => 1
    ]);

    $qrCode = new QRCode($options);
    $qrImage = $qrCode->render($qrData);

    // Actualizar estudiante
    $stmt = $conn->prepare("
        UPDATE estudiantes 
        SET nombre = ?, apellidos = ?, documento = ?, qr_code = ? 
        WHERE id = ?
    ");
    $stmt->execute([$nombre, $apellidos, $documento, $qrImage, $id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Estudiante actualizado exitosamente'
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Estudiante no encontrado'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el estudiante']);
}