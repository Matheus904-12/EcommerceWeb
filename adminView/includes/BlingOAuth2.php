<?php
// BlingOAuth2.php
// Implementação do fluxo OAuth2 Authorization Code para Bling

class BlingOAuth2 {
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $authUrl = 'https://www.bling.com.br/Api/v3/oauth/authorize';
    private $tokenUrl = 'https://www.bling.com.br/Api/v3/oauth/token';
    private $revokeUrl = 'https://www.bling.com.br/oauth/revoke';

    public function __construct($clientId, $clientSecret, $redirectUri) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
    }

    // 1. Gera a URL de autorização para o usuário
    public function getAuthorizationUrl($state) {
        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'state' => $state
        ]);
        return $this->authUrl . '?' . $params;
    }

    // 2. Troca o authorization_code por access_token e refresh_token
    public function getTokens($authorizationCode) {
        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: 1.0',
            'Authorization: Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret)
        ];
        $body = http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $authorizationCode
        ]);
        $ch = curl_init($this->tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['http_code' => $httpCode, 'response' => json_decode($response, true)];
    }

    // 3. Renova o access_token usando o refresh_token
    public function refreshToken($refreshToken) {
        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: 1.0',
            'Authorization: Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret)
        ];
        $body = http_build_query([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken
        ]);
        $ch = curl_init($this->tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['http_code' => $httpCode, 'response' => json_decode($response, true)];
    }

    // 4. Revoga um access_token ou refresh_token
    public function revokeToken($token, $tokenTypeHint = 'access_token', $revokeAction = null, $revokeTarget = null) {
        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret)
        ];
        $body = [
            'token' => $token,
            'token_type_hint' => $tokenTypeHint
        ];
        if ($revokeAction) $body['revoke_action'] = $revokeAction;
        if ($revokeTarget) $body['revoke_target'] = $revokeTarget;
        $body = http_build_query($body);
        $ch = curl_init($this->revokeUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['http_code' => $httpCode, 'response' => json_decode($response, true)];
    }

    // Gera o link de autorização OAuth2 do Bling com todos os escopos essenciais para emissão de NF-e, pedidos, produtos e contatos.
    public static function generateAuthorizationLink($clientId, $redirectUri, $scopes = []) {
        // Escopos essenciais (adicione outros se necessário)
        $defaultScopes = [
            'contatos',    // Clientes
            'produtos',    // Produtos
            'pedidos',     // Pedidos
            'notasfiscais' // Notas fiscais
        ];
        $scopes = array_unique(array_merge($defaultScopes, $scopes));
        $scopeParam = implode(' ', $scopes);

        $authUrl = 'https://www.bling.com.br/Api/v3/oauth/authorize?response_type=code'
            . '&client_id=' . urlencode($clientId)
            . '&redirect_uri=' . urlencode($redirectUri)
            . '&scope=' . urlencode($scopeParam);

        return $authUrl;
    }
}

// Exemplo de uso estático para gerar o link de autorização
/*
$client_id = 'c3f830af2bce0d80e15f731fe39df3cab7b1d99c'; // Substitua pelo client_id da sua aplicação cadastrada no Bling
$redirect_uri = 'https://cristaisgoldlar.com.br/adminView/includes/callback_bling.php'; // Substitua pela URL de callback configurada no Bling

$auth_url = BlingOAuth2::generateAuthorizationLink($client_id, $redirect_uri);
echo 'Link de Autorização: ' . htmlspecialchars($auth_url);
*/
