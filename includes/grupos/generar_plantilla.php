<?php
// Validación de la sesión
session_start();
require_once '../funciones.php';

// Verificar que el usuario tenga rol de administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    // Redireccionar a la página de inicio
    header('Location: ../../index.php');
    exit;
}

// Cargar librerías necesarias para manejar Excel
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Crear un nuevo archivo Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Definir el encabezado de la plantilla
$sheet->setCellValue('A1', 'Nombre del Grupo');

// Dar formato al encabezado (negrita y color de fondo)
$sheet->getStyle('A1')->getFont()->setBold(true);
$sheet->getStyle('A1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
$sheet->getStyle('A1')->getFill()->getStartColor()->setARGB('FFD9EAD3');

// Autoajustar el ancho de la columna
$sheet->getColumnDimension('A')->setAutoSize(true);

// Añadir comentario explicativo
$sheet->getComment('A1')->getText()->createTextRun('Ingrese el nombre completo del grupo. Este campo es obligatorio y no debe estar vacío.');

// Agregar filas de ejemplo
$sheet->setCellValue('A2', 'Grupo de Ejemplo 1');
$sheet->setCellValue('A3', 'Grupo de Ejemplo 2');

// Establecer el nombre de la hoja
$sheet->setTitle('Plantilla de Grupos');

// Crear el archivo para descargar
$writer = new Xlsx($spreadsheet);

// Configurar headers para la descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Plantilla_Importacion_Grupos.xlsx"');
header('Cache-Control: max-age=0');

// Escribir el archivo al output
$writer->save('php://output'); 