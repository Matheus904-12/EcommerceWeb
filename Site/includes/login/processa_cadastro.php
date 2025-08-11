<?php
// Enhanced Registration Processing with Security Features
session_start();
require_once '../../../adminView/config/dbconnect.php';

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Content-Type: application/json; charset=utf-8");

// Rate limiting
$ip = $_SERVER['REMOTE_ADDR'];
$rateLimitKey = "registration_limit_$ip";
$maxAttempts = 3;
$timeWindow = 3600; // 1 hour

if (isset($_SESSION[$rateLimitKey])) {
    $attempts = $_SESSION[$rateLimitKey];
    if ($attempts['count'] >= $maxAttempts && (time() - $attempts['time']) < $timeWindow) {
        http_response_code(429);
        die(json_encode(['status' => 'error', 'message' => 'Muitas tentativas de cadastro. Tente novamente em 1 hora.']));
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validatePassword($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special character
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($pattern, $password);
}


function validateCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11) return false;
    if (preg_match('/^(\d)\1{10}$/', $cpf)) return false;
    $sum = 0;
    for ($i = 0; $i < 9; $i++) $sum += $cpf[$i] * (10 - $i);
    $digit1 = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);
    if ($cpf[9] != $digit1) return false;
    $sum = 0;
    for ($i = 0; $i < 10; $i++) $sum += $cpf[$i] * (11 - $i);
    $digit2 = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);
    return $cpf[10] == $digit2;
}

function validateCNPJ($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    if (strlen($cnpj) != 14) return false;
    if (preg_match('/^(\d)\1{13}$/', $cnpj)) return false;
    $tamanho = strlen($cnpj) - 2;
    $numeros = substr($cnpj, 0, $tamanho);
    $digitos = substr($cnpj, $tamanho);
    $soma = 0;
    $pos = $tamanho - 7;
    for ($i = $tamanho; $i >= 1; $i--) {
        $soma += $numeros[$tamanho - $i] * $pos--;
        if ($pos < 2) $pos = 9;
    }
    $resultado = ($soma % 11 < 2) ? 0 : 11 - ($soma % 11);
    if ($resultado != $digitos[0]) return false;
    $tamanho++;
    $numeros = substr($cnpj, 0, $tamanho);
    $soma = 0;
    $pos = $tamanho - 7;
    for ($i = $tamanho; $i >= 1; $i--) {
        $soma += $numeros[$tamanho - $i] * $pos--;
        if ($pos < 2) $pos = 9;
    }
    $resultado = ($soma % 11 < 2) ? 0 : 11 - ($soma % 11);
    return $resultado == $digitos[1];
}

function validatePhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10 && strlen($phone) <= 11;
}

function logSecurityEvent($event, $ip, $email = null) {
    $logFile = '../../logs/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $event - IP: $ip" . ($email ? " - Email: $email" : "") . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $senha = password_hash(trim($_POST['senha'] ?? ''), PASSWORD_BCRYPT);
    $endereco = $_POST['endereco'] ?? '';
    $cep = $_POST['cep'] ?? '';
    $numero_casa = $_POST['numero_casa'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $bairro = $_POST['bairro'] ?? '';
    $municipio = $_POST['municipio'] ?? '';
    $uf = $_POST['uf'] ?? '';

    // Validação básica
    if (!$nome || !$email || !$cpf || !$senha || !$telefone) {
        echo json_encode(["status" => "error", "message" => "Preencha todos os campos obrigatórios."]);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Email inválido."]);
        exit;
    }
    // Detecta se é CPF ou CNPJ
    $docLimpo = preg_replace('/[^0-9]/', '', $cpf);
    $tipoDoc = '';
    if (strlen($docLimpo) == 11) {
        $tipoDoc = 'CPF';
        if (!validateCPF($docLimpo)) {
            echo json_encode(["status" => "error", "message" => "CPF inválido. Informe um CPF válido."]);
            exit;
        }
    } elseif (strlen($docLimpo) == 14) {
        $tipoDoc = 'CNPJ';
        if (!validateCNPJ($docLimpo)) {
            echo json_encode(["status" => "error", "message" => "CNPJ inválido. Informe um CNPJ válido."]);
            exit;
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Informe um CPF ou CNPJ válido."]);
        exit;
    }
    // Verifica se email já existe
    $query = "SELECT id FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email já cadastrado!"]);
        exit;
    }
    // Verifica se CPF já existe
    $query = "SELECT id FROM usuarios WHERE cpf = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "CPF já cadastrado!"]);
        exit;
    }
    $query = "INSERT INTO usuarios (name, email, password, endereco, cep, numero_casa, telefone, cpf, primeira_compra) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssss", $nome, $email, $senha, $endereco, $cep, $numero_casa, $telefone, $cpf);

    if ($stmt->execute()) {
        $usuario_id = $stmt->insert_id ?? $conn->insert_id;
        require_once 'bling_cliente_sync.php';
        $blingOk = syncClienteBling($usuario_id, $nome, $email, $docLimpo, $telefone, $endereco, $cep, $numero_casa, $bairro, $municipio, $uf, $tipoDoc);
        if ($blingOk) {
            echo json_encode(["status" => "success", "message" => "Conta criada com sucesso!", "show_welcome_modal" => true]);
        } else {
            error_log('Falha ao criar contato no Bling para usuario_id=' . $usuario_id . ', doc=' . $docLimpo);
            echo json_encode(["status" => "warning", "message" => "Conta criada, mas não foi possível sincronizar o contato com o Bling. Verifique o documento ou tente novamente mais tarde.", "show_welcome_modal" => true]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao cadastrar usuário: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método de requisição inválido."]);
}
?> 