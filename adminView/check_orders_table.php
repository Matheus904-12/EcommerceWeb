<?php
// Configurações de conexão
$servername = "goldlar_2025.mysql.dbaas.com.br";
$username = "goldlar_2025";
$password = "FNvVuWRZ#1";
$database = "goldlar_2025";

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $database);

// Verifica a conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

echo "Conexão bem-sucedida!\n";

// Consulta a estrutura da tabela orders
$sql = "SHOW COLUMNS FROM orders";
$result = $conn->query($sql);

if ($result === false) {
    echo "Erro ao consultar a tabela: " . $conn->error;
} else {
    if ($result->num_rows > 0) {
        echo "Estrutura da tabela 'orders':\n";
        while($row = $result->fetch_assoc()) {
            echo "Campo: " . $row["Field"] . ", Tipo: " . $row["Type"] . ", Nulo: " . $row["Null"] . "\n";
        }
    } else {
        echo "Nenhum resultado encontrado";
    }
}

// Fecha a conexão
$conn->close();
?>