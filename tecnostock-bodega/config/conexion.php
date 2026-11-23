<?php
declare(strict_types=1);

$host = 'localhost';
$database = 'tecnostock';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $exception) {
    error_log('TecnoStock: error de conexión a la base de datos: ' . $exception->getMessage());
    http_response_code(500);
    exit('No se pudo conectar con la base de datos. Verifica la configuración de XAMPP.');
}
