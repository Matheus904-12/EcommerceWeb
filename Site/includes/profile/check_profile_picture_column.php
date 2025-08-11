<?php
// Script para verificar e criar a coluna profile_picture na tabela usuarios
require_once '../../adminView/config/dbconnect.php';

// Verificar se a coluna profile_picture existe
$query = "SHOW COLUMNS FROM usuarios LIKE 'profile_picture'";
$result = $conn->query($query);

if ($result->num_rows === 0) {
    // A coluna não existe, vamos criá-la
    $alterQuery = "ALTER TABLE usuarios ADD COLUMN profile_picture VARCHAR(255) NULL AFTER email";
    
    if ($conn->query($alterQuery)) {
        echo "Coluna 'profile_picture' criada com sucesso na tabela 'usuarios'.\n";
    } else {
        echo "Erro ao criar a coluna 'profile_picture': " . $conn->error . "\n";
    }
} else {
    echo "A coluna 'profile_picture' já existe na tabela 'usuarios'.\n";
}

$conn->close();
?> 