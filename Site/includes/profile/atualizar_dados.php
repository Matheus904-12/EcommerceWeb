<?php
// includes/atualizar_dados.php
session_start();
require_once '../../../adminView/config/dbconnect.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    echo "Você precisa estar logado para atualizar seus dados.";
    exit;
}

// Processar apenas solicitações POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $cpf = trim($_POST['cpf'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $numeroCasa = trim($_POST['numero_casa'] ?? '');

    // Validar entrada
    if (empty($nome) || empty($email)) {
        echo "Nome e email são obrigatórios.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Por favor, forneça um email válido.";
        exit;
    }

    // Verificar se o email já está em uso por outro usuário
    $query = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $email, $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Este email já está sendo usado por outro usuário.";
        $stmt->close();
        exit;
    }

    // Processar upload da foto de perfil
    $profilePicturePath = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        
        // Validar tipo de arquivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            echo "Por favor, envie apenas arquivos de imagem (JPEG, PNG, GIF, WEBP).";
            exit;
        }
        
        // Validar tamanho (máximo 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            echo "O arquivo deve ter menos de 5MB.";
            exit;
        }
        
        // Criar diretório de uploads se não existir
        $uploadDir = '../../../adminView/uploads/profile_pictures/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Gerar nome único para o arquivo
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'profile_' . $userId . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        // Mover arquivo para o diretório de uploads
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $profilePicturePath = $fileName; // Salvar apenas o nome do arquivo no banco
            
            // Remover foto antiga se existir
            $query = "SELECT profile_picture FROM usuarios WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $oldPicture = $row['profile_picture'];
                if ($oldPicture && file_exists($uploadDir . $oldPicture)) {
                    unlink($uploadDir . $oldPicture);
                }
            }
            $stmt->close();
        } else {
            echo "Erro ao fazer upload da imagem.";
            exit;
        }
    }

    // Atualizar os dados do usuário
    if ($profilePicturePath) {
        // Incluir foto de perfil na atualização
        $query = "UPDATE usuarios SET name = ?, email = ?, cpf = ?, telefone = ?, endereco = ?, cep = ?, numero_casa = ?, profile_picture = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssssi", $nome, $email, $cpf, $telefone, $endereco, $cep, $numeroCasa, $profilePicturePath, $userId);
    } else {
        // Atualizar sem foto de perfil
        $query = "UPDATE usuarios SET name = ?, email = ?, cpf = ?, telefone = ?, endereco = ?, cep = ?, numero_casa = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssssi", $nome, $email, $cpf, $telefone, $endereco, $cep, $numeroCasa, $userId);
    }

    if ($stmt->execute()) {
        // Atualizar os dados na sessão
        $_SESSION['username'] = $nome;
        $_SESSION['user_email'] = $email;
        
        // Atualizar foto de perfil na sessão se foi enviada
        if ($profilePicturePath) {
            $_SESSION['user_picture'] = $profilePicturePath;
        }
        
        echo "Dados atualizados com sucesso!";
    } else {
        echo "Erro ao atualizar os dados: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Método de requisição inválido.";
}

$conn->close();
?>