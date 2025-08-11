<?php
require_once __DIR__ . '/../controller/Bling/BlingIntegrationController.php';

function registerUserAndCreateBlingContact($userData, $conn) {
    try {
        $conn->begin_transaction();

        // Insere o usuário no banco de dados
        $stmt = $conn->prepare("INSERT INTO usuarios (name, email, password, cpf, telefone, celular, endereco, numero, complemento, bairro, cep, cidade, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssssss", 
            $userData['name'],
            $userData['email'],
            password_hash($userData['password'], PASSWORD_DEFAULT),
            $userData['cpf'],
            $userData['telefone'],
            $userData['celular'],
            $userData['endereco'],
            $userData['numero'],
            $userData['complemento'],
            $userData['bairro'],
            $userData['cep'],
            $userData['cidade'],
            $userData['estado']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao cadastrar usuário: " . $stmt->error);
        }

        $userId = $conn->insert_id;
        $userData['id'] = $userId;

        // Cria o contato no Bling
        $blingController = new BlingIntegrationController($conn, getenv('BLING_API_KEY'));
        $idExternoBling = $blingController->criarContato($userData);

        $conn->commit();
        return true;

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Erro no processo de registro: " . $e->getMessage());
        throw $e;
    }
}
