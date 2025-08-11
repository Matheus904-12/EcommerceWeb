<?php
// Sincroniza cliente cadastrado com o painel de clientes/contatos da Bling
require_once '../../../adminView/models/BlingIntegration.php';
require_once '../../../adminView/config/dbconnect.php';

function syncClienteBling($usuario_id, $nome, $email, $cpf, $telefone, $endereco, $cep, $numero_casa, $bairro = '', $municipio = '', $uf = '', $tipoDoc = 'CPF') {
    global $pdo;
    try {
        $bling = new BlingIntegration();
        $docLimpo = preg_replace('/[^0-9]/', '', $cpf);
        $isValido = false;
        $tipo = 'F';
        if ($tipoDoc === 'CPF') {
            // Validação CPF
            $isValido = (function($cpf) {
                if (strlen($cpf) != 11 || preg_match('/^(\d)\1{10}$/', $cpf)) return false;
                $sum = 0;
                for ($i = 0; $i < 9; $i++) $sum += $cpf[$i] * (10 - $i);
                $digit1 = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);
                if ($cpf[9] != $digit1) return false;
                $sum = 0;
                for ($i = 0; $i < 10; $i++) $sum += $cpf[$i] * (11 - $i);
                $digit2 = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);
                return $cpf[10] == $digit2;
            })($docLimpo);
            $tipo = 'F';
        } elseif ($tipoDoc === 'CNPJ') {
            // Validação CNPJ
            $isValido = (function($cnpj) {
                if (strlen($cnpj) != 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) return false;
                $tamanho = strlen($cnpj) - 2;
                $numeros = substr($cnpj, 0, $tamanho);
                $digitos = substr($cnpj, $tamanho);
                $soma = 0;
                $pos = $tamanho - 7;
                for ($i = $tamanho; $i >= 1; $i--) {
                    $soma += $numeros[$tamanho - $i] * $pos--;
                    if ($pos < 2) $pos = 9;
                }
                $resultado = ($soma % 11 < 2) ? 0 : 11 - ($soma % 11);
                if ($resultado != $digitos[0]) return false;
                $tamanho++;
                $numeros = substr($cnpj, 0, $tamanho);
                $soma = 0;
                $pos = $tamanho - 7;
                for ($i = $tamanho; $i >= 1; $i--) {
                    $soma += $numeros[$tamanho - $i] * $pos--;
                    if ($pos < 2) $pos = 9;
                }
                $resultado = ($soma % 11 < 2) ? 0 : 11 - ($soma % 11);
                return $resultado == $digitos[1];
            })($docLimpo);
            $tipo = 'J';
        }
        if (!$isValido) {
            error_log('Documento inválido ao tentar sincronizar com Bling: ' . $docLimpo);
            return false;
        }
        $clienteData = [
            'usuario_id' => $usuario_id,
            'nome' => $nome,
            'email' => $email,
            'numeroDocumento' => $docLimpo,
            'telefone' => $telefone,
            'tipo' => $tipo,
            'indicadorIe' => 9,
            'endereco' => [
                'geral' => [
                    'endereco' => $endereco,
                    'numero' => $numero_casa,
                    'bairro' => $bairro,
                    'cep' => preg_replace('/[^0-9]/', '', $cep),
                    'municipio' => $municipio,
                    'uf' => $uf
                ]
            ]
        ];
        $bling->criarContato($clienteData, $pdo);
        return true;
    } catch (Exception $e) {
        error_log('Erro ao sincronizar cliente com Bling: ' . $e->getMessage());
        return false;
    }
}
?>
