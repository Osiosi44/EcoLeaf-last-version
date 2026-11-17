<?php
$config = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'database' => getenv('DB_NAME') ?: 'ecoleaf',
    'username' => getenv('DB_USER') ?: 'ecoleaf',
    'password' => getenv('DB_PASSWORD') ?: '',
    'port' => (int) (getenv('DB_PORT') ?: 3306),
];
$localConfig = __DIR__ . '/config.local.php';
if (is_file($localConfig)) {
    $config = array_replace($config, require $localConfig);
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $con = new mysqli($config['host'], $config['username'], $config['password'], $config['database'], (int) $config['port']);
    $con->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    error_log('EcoLeaf database connection failed: ' . $exception->getMessage());
    http_response_code(503);
    exit('The database is unavailable. Please check the database settings.');
}
