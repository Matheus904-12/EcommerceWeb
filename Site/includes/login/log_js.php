<?php
// log_js.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['message'])) {
        $msg = date('Y-m-d H:i:s') . ' - ' . $data['message'] . "\n";
        file_put_contents(__DIR__ . '/log_js.txt', $msg, FILE_APPEND | LOCK_EX);
    }
}
// Não retorna nada para o JS 