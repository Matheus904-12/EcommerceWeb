<?php
session_start();
include './adminView/config/dbconnect.php'; // Conexão com o banco

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verifica se a conexão está ativa
    if (!$conn) {
        die("Erro na conexão com o banco de dados.");
    }

    // Prepara a consulta para evitar SQL Injection
    $sql = "SELECT id, password, role FROM admins WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password, $role);
            $stmt->fetch();

            // Verifica se a senha está correta
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