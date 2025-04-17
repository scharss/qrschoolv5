<?php
/**
 * Archivo de funciones auxiliares para el sistema
 */

// Cargar el autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Función para conectar a la base de datos usando mysqli
function conectar_db() {
    // Cargar variables de entorno si no están cargadas
    if (!isset($_ENV['DB_SERVER'])) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
    }
    
    $host = $_ENV['DB_SERVER'];
    $username = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'] ?? '';
    $database = $_ENV['DB_NAME'];
    
    // Crear la conexión mysqli
    $mysqli = new mysqli($host, $username, $password, $database);
    
    // Verificar la conexión
    if ($mysqli->connect_error) {
        error_log("Error de conexión a la base de datos: " . $mysqli->connect_error);
        die("Error de conexión: " . $mysqli->connect_error);
    }
    
    // Configurar el juego de caracteres
    $mysqli->set_charset("utf8");
    
    return $mysqli;
} 