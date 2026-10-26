<?php
// Configure these variables in the PHP runtime/hosting environment.
// Do not commit real database credentials to the repository.
$servername = getenv('DB_HOST');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$database = getenv('DB_DATABASE');

if ($servername === false || $username === false || $password === false || $database === false ||
    $servername === '' || $username === '' || $database === '') {
    error_log('Database environment variables are not fully configured.');
    http_response_code(500);
    die('Database configuration is missing. Configure DB_HOST, DB_USERNAME, DB_PASSWORD and DB_DATABASE.');
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    error_log('MySQL connection failed: ' . $conn->connect_error);
    http_response_code(500);
    die('Database connection failed.');
}

try {
    $dsn = "mysql:host={$servername};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    error_log('PDO database connection failed: ' . $e->getMessage());
    http_response_code(500);
    die('Database connection failed.');
}
?>