<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['active_tab'])) {
    $_SESSION['active_config_tab'] = $_POST['active_tab'];
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Dados inválidos']);
}
?> 