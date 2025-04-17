<?php
// Validación de la sesión
session_start();
require_once '../funciones.php';

// Verificar que el usuario tenga rol de administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    // Responder con error
    echo json_encode(['exito' => false, 'mensaje' => 'No tiene permiso para realizar esta acción.']);
    exit;
}

// Verificar que se realizó un POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

// Validaciones del archivo
if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] != 0) {
    echo json_encode(['exito' => false, 'mensaje' => 'Error al subir el archivo.']);
    exit;
}

$archivo = $_FILES['excel_file'];

// Validar la extensión del archivo
$extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
if (!in_array($extension, ['xlsx', 'csv'])) {
    echo json_encode(['exito' => false, 'mensaje' => 'El archivo debe ser Excel (.xlsx) o CSV.']);
    exit;
}

// Cargar librerías necesarias para manejar Excel
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    // Cargar el archivo
    $spreadsheet = IOFactory::load($archivo['tmp_name']);
    $hoja = $spreadsheet->getActiveSheet();
    
    // Obtener filas
    $filas = $hoja->toArray();
    
    // Saltar la primera fila (encabezados)
    array_shift($filas);
    
    // Inicializar contadores
    $gruposCreados = 0;
    $errores = [];
    
    // Conectar a la base de datos
    $mysqli = conectar_db();
    
    // Preparar stmt para verificar si el grupo ya existe
    $stmtVerificar = $mysqli->prepare("SELECT id FROM grupos WHERE nombre = ? AND activo = 1");
    
    // Preparar stmt para insertar nuevo grupo
    $stmtInsertar = $mysqli->prepare("INSERT INTO grupos (nombre, activo) VALUES (?, 1)");
    
    // Procesar filas
    foreach ($filas as $indice => $fila) {
        $numeroFila = $indice + 2; // +2 porque empezamos en 0 y saltamos la fila de encabezados
        
        // Obtener nombre del grupo (primera columna)
        $nombre = trim($fila[0] ?? '');
        
        // Validar que el nombre no esté vacío
        if (empty($nombre)) {
            $errores[] = "Fila $numeroFila: El nombre del grupo está vacío.";
            continue;
        }
        
        // Verificar si el grupo ya existe
        $stmtVerificar->bind_param("s", $nombre);
        $stmtVerificar->execute();
        $resultado = $stmtVerificar->get_result();
        
        if ($resultado->num_rows > 0) {
            $errores[] = "Fila $numeroFila: Ya existe un grupo con el nombre '$nombre'.";
            continue;
        }
        
        // Insertar el nuevo grupo
        $stmtInsertar->bind_param("s", $nombre);
        $exito = $stmtInsertar->execute();
        
        if ($exito) {
            $gruposCreados++;
        } else {
            $errores[] = "Fila $numeroFila: Error al crear el grupo '$nombre'. " . $mysqli->error;
        }
    }
    
    // Cerrar statements y conexión
    $stmtVerificar->close();
    $stmtInsertar->close();
    $mysqli->close();
    
    // Responder según el resultado
    if ($gruposCreados > 0) {
        $mensaje = "Se importaron $gruposCreados grupos correctamente.";
        if (count($errores) > 0) {
            $mensaje .= " Se encontraron " . count($errores) . " errores.";
        }
        
        echo json_encode([
            'exito' => true,
            'mensaje' => $mensaje,
            'grupos_creados' => $gruposCreados,
            'errores' => $errores
        ]);
    } else {
        echo json_encode([
            'exito' => false,
            'mensaje' => "No se pudo importar ningún grupo.",
            'errores' => $errores
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'exito' => false,
        'mensaje' => "Error al procesar el archivo: " . $e->getMessage()
    ]);
} 