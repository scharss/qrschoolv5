<?php
session_start();
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

// Verificar que el usuario está autenticado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Aumentar el tiempo máximo de ejecución para archivos grandes
set_time_limit(300); // 5 minutos

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar si se ha subido algún archivo
if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] != UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No se ha subido ningún archivo o ha ocurrido un error en la subida']);
    exit;
}

// Obtener información del archivo
$tempFile = $_FILES['excel_file']['tmp_name'];
$originalName = $_FILES['excel_file']['name'];
$fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

// Variables para almacenar los datos
$rows = [];

try {
    if ($fileExt === 'csv') {
        // Procesar archivo CSV
        $reader = IOFactory::createReader('Csv');
        $spreadsheet = $reader->load($tempFile);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // Eliminar la primera fila (encabezados)
        array_shift($rows);
        error_log("CSV procesado. Filas encontradas: " . count($rows));
    } 
    else if ($fileExt === 'xlsx' || $fileExt === 'xls') {
        // Procesar archivos Excel (tanto XLSX como XLS)
        $reader = IOFactory::createReaderForFile($tempFile);
        $spreadsheet = $reader->load($tempFile);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // Eliminar la primera fila (encabezados)
        array_shift($rows);
        error_log("Excel procesado. Filas encontradas: " . count($rows));
    }
    else {
        throw new Exception('Formato de archivo no compatible. Use archivos .xlsx, .xls o .csv');
    }
    
    // Verificar si hay datos
    if (count($rows) == 0) {
        throw new Exception('El archivo no contiene datos');
    }
    
    // Verificar que haya suficientes columnas
    $primeraFila = $rows[0] ?? [];
    if (count($primeraFila) < 4) {
        throw new Exception('El formato del archivo no es válido. Asegúrese de incluir columnas para Nombre, Apellidos, Correo y Documento.');
    }
    
    // Conectar a la base de datos
    $db = new Database();
    $conn = $db->connect();
    
    // Variables para el seguimiento de resultados
    $profesores_creados = 0;
    $profesores_no_creados = 0;
    $mensajes_error = [];
    
    // Procesar cada fila
    foreach ($rows as $index => $row) {
        // Log para monitorear el progreso
        if ($index % 10 == 0) {
            error_log("Procesando fila $index de " . count($rows));
        }
        
        // Ignorar filas vacías
        if (empty($row[0]) && empty($row[1]) && empty($row[2]) && empty($row[3])) {
            error_log("Fila $index ignorada: fila vacía");
            continue;
        }
        
        // Obtener datos
        $nombre = htmlspecialchars(trim($row[0] ?? ''), ENT_QUOTES, 'UTF-8');
        $apellidos = htmlspecialchars(trim($row[1] ?? ''), ENT_QUOTES, 'UTF-8');
        $correo = filter_var(trim($row[2] ?? ''), FILTER_SANITIZE_EMAIL);
        $documento = htmlspecialchars(trim($row[3] ?? ''), ENT_QUOTES, 'UTF-8');
        
        // Validar datos requeridos
        if (empty($nombre) || empty($apellidos) || empty($correo) || empty($documento)) {
            $profesores_no_creados++;
            $mensaje_error = "Fila " . ($index + 2) . ": Faltan datos requeridos (nombre, apellidos, correo o documento).";
            $mensajes_error[] = $mensaje_error;
            error_log($mensaje_error);
            continue;
        }
        
        // Validar formato de correo
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $profesores_no_creados++;
            $mensaje_error = "Fila " . ($index + 2) . ": El formato de correo no es válido: " . $correo;
            $mensajes_error[] = $mensaje_error;
            error_log($mensaje_error);
            continue;
        }
        
        // Validar formato de documento (solo números)
        if (!preg_match('/^\d+$/', $documento)) {
            $profesores_no_creados++;
            $mensaje_error = "Fila " . ($index + 2) . ": El documento debe contener solo números: " . $documento;
            $mensajes_error[] = $mensaje_error;
            error_log($mensaje_error);
            continue;
        }
        
        try {
            // Verificar si ya existe un usuario con ese correo o documento
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ? OR documento = ?");
            $stmt->execute([$correo, $documento]);
            if ($stmt->fetch()) {
                $profesores_no_creados++;
                $mensaje_error = "Fila " . ($index + 2) . ": Ya existe un usuario con este correo o documento";
                $mensajes_error[] = $mensaje_error;
                error_log($mensaje_error . " (Correo: $correo, Documento: $documento)");
                continue;
            }
            
            // Crear el profesor
            $stmt = $conn->prepare("
                INSERT INTO usuarios (nombre, apellidos, correo, documento, password, rol_id) 
                VALUES (?, ?, ?, ?, ?, 2)
            ");
            
            // Usamos el documento como contraseña inicial (aplicando hash)
            $hashed_password = password_hash($documento, PASSWORD_DEFAULT);
            
            if ($stmt->execute([$nombre, $apellidos, $correo, $documento, $hashed_password])) {
                $profesores_creados++;
                error_log("Profesor creado correctamente: $nombre $apellidos ($correo)");
            } else {
                throw new Exception('Error al ejecutar la consulta');
            }
            
        } catch (Exception $e) {
            $profesores_no_creados++;
            $mensajes_error[] = "Fila " . ($index + 2) . ": Error al crear profesor: " . $e->getMessage();
            error_log("Error al crear profesor: " . $e->getMessage() . " - Trace: " . $e->getTraceAsString());
        }
    }
    
    // Preparar respuesta
    $mensaje_resumen = "Importación completada. Profesores creados: $profesores_creados. Profesores no creados: $profesores_no_creados.";
    error_log($mensaje_resumen);
    
    if ($profesores_no_creados > 0) {
        error_log("Errores durante la importación: " . implode(" | ", $mensajes_error));
    }
    
    echo json_encode([
        'success' => true,
        'message' => $mensaje_resumen,
        'profesores_creados' => $profesores_creados,
        'profesores_no_creados' => $profesores_no_creados,
        'errores' => $mensajes_error
    ]);
    
} catch (Exception $e) {
    $error_message = "Error en importar_excel.php para profesores: " . $e->getMessage();
    error_log($error_message . " - Trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar el archivo: ' . $e->getMessage(),
        'error_details' => $e->getTraceAsString()
    ]);
} 