<?php
session_start();
include './adminView/config/dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Senha padrão de emergência
    $emergency_password = 'admin123';
    $emergency_username = 'admin'; // Usuário padrão
    $emergency_hash = password_hash($emergency_password, PASSWORD_DEFAULT);

    // Verifica se a conexão está ativa
    if (!$conn) {
        die("<script>alert('Erro na conexão com o banco de dados.'); window.location='login.php';</script>");
    }

    // Verifica a senha padrão primeiro
    if ($username === $emergency_username && $password === $emergency_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = 0; // ID 0 para indicar usuário de emergência
        $_SESSION['admin_role'] = 'admin';
        header("Location: ./adminView/pages/index.php");
        exit();
    }

    // Verifica no banco de dados
    $sql = "SELECT id, password, role FROM admins WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password, $role);
            $stmt->fetch();

            // Para depuração
            error_log("Tentativa de login para usuário: " . $username);
            error_log("Senha fornecida: " . $password);
            error_log("Hash armazenado: " . $hashed_password);
            error_log("Resultado da verificação: " . (password_verify($password, $hashed_password) ? "true" : "false"));

            if (password_verify($password, $hashed_password)) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $id;
                $_SESSION['admin_role'] = $role;
                header("Location: ./adminView/pages/index.php");
                exit();
            } else {
                echo "<script>alert('Senha incorreta!'); window.location='login.php';</script>";
            }
        } else {
            echo "<script>alert('Usuário não encontrado!'); window.location='login.php';</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Erro na consulta SQL.'); window.location='login.php';</script>";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="./adminView/assets/images/logo.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="./adminView/assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container" id="loginContainer">
        <div class="logo-animation">
            <img src="adminView/assets/images/logo.png" alt="Logo da Empresa">
            <p class="welcome-message">Bem-vindo!</p>
        </div>
        <div id="loginForm">
            <form action="login.php" method="POST" class="login-form">
                <h2>Acesse sua conta</h2>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Usuário" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Senha" required>
                </div>
                <button type="submit" class="login-button">Entrar</button>
                <div class="forgot-password">
                    <button id="forgotPasswordButton" class="forgot-password-button">Esqueci minha senha</button>
                </div>
            </form>
        </div>
        <div id="forgotPasswordForm" style="display:none;">
            <form action="https://api.web3forms.com/submit" method="POST" class="forgot-password-form">
                <h2>Recuperar senha</h2>
                <p>Informe seus dados para solicitar suporte.</p>
                <input type="hidden" name="access_key" value="6fcc2d3d-5e0e-45d1-8796-744e52a1c64b">
                <input type="hidden" name="subject" value="🔐 Solicitação de Recuperação de Senha">
                <input type="hidden" name="from_name" value="Sistema de Recuperação de Senha">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Seu Nome de Usuário" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Seu Email" required>
                </div>
                <input type="hidden" name="to" value="matheuslucindo904@gmail.com">
                <input type="hidden" name="redirect" value="login.php?success=true">
                <button type="submit" class="login-button">Solicitar Suporte</button>
                <div class="back-to-login">
                    <button id="backToLoginButton" class="back-to-login-button">Voltar para Login</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const forgotPasswordButton = document.getElementById('forgotPasswordButton');
            const backToLoginButton = document.getElementById('backToLoginButton');
            const loginForm = document.getElementById('loginForm');
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            const loginContainer = document.getElementById('loginContainer');

            forgotPasswordButton.addEventListener('click', function(e) {
                e.preventDefault();
                loginForm.style.display = 'none';
                forgotPasswordForm.style.display = 'block';
                loginContainer.style.height = forgotPasswordForm.offsetHeight + loginContainer.querySelector('.logo-animation').offsetHeight + 40 + 'px';
            });

            backToLoginButton.addEventListener('click', function(e) {
                e.preventDefault();
                forgotPasswordForm.style.display = 'none';
                loginForm.style.display = 'block';
                loginContainer.style.height = loginForm.offsetHeight + loginContainer.querySelector('.logo-animation').offsetHeight + 40 + 'px';
            });
        });
    </script>
</body>
</html>