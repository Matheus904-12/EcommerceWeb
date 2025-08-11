<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.melhorenvio.com.br/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Erro cURL: ' . curl_error($ch);
} else {
    echo 'Conexão bem-sucedida!<br>';
    echo 'Resposta: <pre>' . htmlspecialchars($result) . '</pre>';
}
curl_close($ch);
?> 