<?php
include './adminView/config/dbconnect.php';

// Usar este script para verificar ou gerar hashes de senha
// Acesse-o diretamente via navegador ou linha de comando

// IMPORTANTE: Remova este arquivo após uso ou proteja-o com autenticação

// Senhas para testar
$senha_teste = 'admin123'; // Substitua pela senha que você está tentando usar

// Geração de hash
$hash_novo = password_hash($senha_teste, PASSWORD_DEFAULT);

// Configuração de saída
echo "===== FERRAMENTA DE DIAGNÓSTICO DE SENHAS =====\n";
echo "Senha de teste: " . $senha_teste . "\n";
echo "Novo hash gerado: " . $hash_novo . "\n\n";

// Verificando contra hashes existentes
echo "===== VERIFICAÇÃO COM HASHES DO BANCO DE DADOS =====\n";
$sql = "SELECT id, username, password FROM admins";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Usuário: " . $row["username"] . "\n";
        echo "Hash armazenado: " . $row["password"] . "\n";
        $verificacao = password_verify($senha_teste, $row["password"]);
        echo "Verificação: " . ($verificacao ? "SUCESSO" : "FALHA") . "\n\n";
    }
} else {
    echo "Nenhum usuário encontrado no banco de dados.\n";
}

// Opcional: Atualizar senha de um usuário específico
/*
$username_para_atualizar = 'admin';
$nova_senha = 'admin123';
$novo_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

$sql = "UPDATE admins SET password = ? WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $novo_hash, $username_para_atualizar);

if ($stmt->execute()) {
    echo "Senha atualizada com sucesso para o usuário: " . $username_para_atualizar . "\n";
} else {
    echo "Erro ao atualizar senha: " . $conn->error . "\n";
}
$stmt->close();
*/

$conn->close();
?>