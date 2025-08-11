<?php
// Teste de DNS e conectividade SuperFrete
header('Content-Type: text/plain');

echo "=== TESTE DNS SUPERFRETE ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// Teste 1: Verificar se o domínio resolve
echo "1. Testando resolução DNS:\n";
$host = 'api.superfrete.com.br';
$ip = gethostbyname($host);
echo "Host: $host\n";
echo "IP: " . ($ip !== $host ? $ip : 'NÃO RESOLVIDO') . "\n\n";

// Teste 2: Verificar se há conectividade
echo "2. Testando conectividade:\n";
$port = 443;
$connection = @fsockopen($host, $port, $errno, $errstr, 5);
if ($connection) {
    echo "Conexão OK na porta $port\n";
    fclose($connection);
} else {
    echo "Erro de conexão: $errstr ($errno)\n";
}
echo "\n";

// Teste 3: Tentar com IP direto
echo "3. Testando com IP direto:\n";
$ips = ['199.60.103.28', '199.60.103.128']; // IPs do superfrete.com.br

foreach ($ips as $ip) {
    echo "Testando IP: $ip\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://$ip/v1/quote");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['test' => 'test']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Code: $http_code\n";
    echo "Error: " . ($error ?: 'Nenhum') . "\n";
    echo "---\n";
}

// Teste 4: Verificar configuração do PHP
echo "4. Configuração PHP:\n";
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'ON' : 'OFF') . "\n";
echo "curl: " . (function_exists('curl_init') ? 'Disponível' : 'Não disponível') . "\n";
echo "openssl: " . (extension_loaded('openssl') ? 'Carregado' : 'Não carregado') . "\n";

echo "\n=== FIM TESTE ===\n";
?> 