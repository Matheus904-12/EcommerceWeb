<?php
// callback_bling.php
// Recebe o authorization_code do Bling e troca por tokens

require_once __DIR__ . '/BlingOAuth2.php';

// Defina suas credenciais e redirect_uri
$clientId = 'c3f830af2bce0d80e15f731fe39df3cab7b1d99c';
$clientSecret = 'b157aee379e635941edefade08b36ef1691fcb65c6c4c03e72348043849c';
$redirectUri = 'https://cristaisgoldlar.com.br/adminView/includes/callback_bling.php';

$blingOAuth = new BlingOAuth2($clientId, $clientSecret, $redirectUri);

// Recebe parâmetros do Bling
$code = isset($_GET['code']) ? $_GET['code'] : null;
$state = isset($_GET['state']) ? $_GET['state'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;
$error_description = isset($_GET['error_description']) ? $_GET['error_description'] : null;

if ($error) {
    echo '<h2>Erro na autorização:</h2>';
    echo '<p>' . htmlspecialchars($error_description) . '</p>';
    exit;

if (!$code) {
    echo '<h2>Authorization code não recebido.</h2>';
    $tokensFile = __DIR__ . '/../config/bling_api.json';
    if (file_exists($tokensFile)) {
        $tokens = json_decode(file_get_contents($tokensFile), true);
        echo '<h3>Token salvo em <code>bling_tokens.json</code>:</h3>';
        echo '<pre>' . htmlspecialchars(json_encode($tokens, JSON_PRETTY_PRINT)) . '</pre>';
        // Exibe status do token
        if (isset($tokens['access_token'])) {
            echo '<p><b>Status:</b> <span style="color:green">Token disponível</span></p>';
            if (isset($tokens['expires_in'])) {
                $fileMTime = filemtime($tokensFile);
                $expiraEm = $fileMTime + $tokens['expires_in'];
                $restante = $expiraEm - time();
                if ($restante > 0) {
                    $horas = floor($restante / 3600);
                    $minutos = floor(($restante % 3600) / 60);
                    $segundos = $restante % 60;
                    echo '<p><b>Tempo restante:</b> ' . $horas . 'h ' . $minutos . 'm ' . $segundos . 's</p>';
                } else {
                    echo '<p style="color:red"><b>Token expirado!</b></p>';
                }
            }
            // Botão para renovar token
            if (isset($tokens['refresh_token'])) {
                echo '<form method="post" style="margin-top:20px;">';
                echo '<input type="hidden" name="refresh_token" value="' . htmlspecialchars($tokens['refresh_token']) . '">';
                echo '<button type="submit" name="action" value="refresh">Renovar token (refresh_token)</button>';
                echo '</form>';
            }
        } else {
            echo '<p style="color:red">Token inválido ou ausente.</p>';
        }
    } else {
        echo '<p>Nenhum token salvo encontrado.</p>';
    }
    exit;
}
// Renovação automática do token via refresh_token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'refresh' && isset($_POST['refresh_token'])) {
    $refreshToken = $_POST['refresh_token'];
    $basicAuth = base64_encode($clientId . ':' . $clientSecret);
    $ch = curl_init('https://api.bling.com.br/Api/v3/oauth/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: 1.0',
        'Authorization: Basic ' . $basicAuth
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'refresh_token',
        'refresh_token' => $refreshToken
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $json = json_decode($response, true);
    if ($httpCode === 200 && isset($json['access_token'])) {
        file_put_contents(__DIR__ . '/../config/bling_api.json', json_encode($json, JSON_PRETTY_PRINT));
        echo '<h2>Token renovado com sucesso!</h2>';
        echo '<pre>' . htmlspecialchars(json_encode($json, JSON_PRETTY_PRINT)) . '</pre>';
        echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">Voltar</a>';
    } else {
        echo '<h2>Erro ao renovar token:</h2>';
        echo '<pre>' . htmlspecialchars($response) . '</pre>';
        echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">Voltar</a>';
    }
    exit;
}
}

// Troca o code por tokens

// Gera o access_token conforme recomendação do suporte Bling
$basicAuth = base64_encode($clientId . ':' . $clientSecret);
$ch = curl_init('https://api.bling.com.br/Api/v3/oauth/token');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'Accept: 1.0',
    'Authorization: Basic ' . $basicAuth
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'authorization_code',
    'code' => $code
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$json = json_decode($response, true);
if ($httpCode === 200 && isset($json['access_token'])) {
    file_put_contents(__DIR__ . '/../config/bling_api.json', json_encode($json, JSON_PRETTY_PRINT));
    echo '<h2>Autorização concluída!</h2>';
    echo '<pre>' . htmlspecialchars(json_encode($json, JSON_PRETTY_PRINT)) . '</pre>';
} else {
    echo '<h2>Erro ao obter tokens:</h2>';
    echo '<pre>' . htmlspecialchars($response) . '</pre>';
}
