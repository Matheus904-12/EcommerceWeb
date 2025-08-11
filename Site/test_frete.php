<?php
// Teste da lógica de frete grátis
function getUfByCep($cep) {
    $cep = preg_replace('/\D/', '', $cep);
    $cep5 = substr($cep, 0, 5);
    $faixas = [
        'SP' => ['01000', '19999'],
        'RJ' => ['20000', '28999'],
        'SC' => ['88000', '89999'],
        'RS' => ['90000', '99999'],
        'PR' => ['80000', '87999'],
        'GO' => ['72800', '76799'],
    ];
    foreach ($faixas as $uf => $range) {
        if ($cep5 >= $range[0] && $cep5 <= $range[1]) return $uf;
    }
    return null;
}

$ceps_teste = ['08501300', '01001000', '20000000', '90000000'];
$subtotal = 450.00; // Valor acima de R$399,99

echo "Teste da lógica de frete grátis:\n";
echo "Subtotal: R$" . number_format($subtotal, 2, ',', '.') . "\n\n";

foreach ($ceps_teste as $cep) {
    $uf = getUfByCep($cep);
    $ufsFreteGratis = ['SP', 'RJ', 'SC', 'RS', 'PR', 'GO'];
    $temFreteGratis = $subtotal >= 399.99 && in_array($uf, $ufsFreteGratis);
    
    echo "CEP: $cep\n";
    echo "UF detectada: " . ($uf ? $uf : 'Não encontrada') . "\n";
    echo "Tem frete grátis: " . ($temFreteGratis ? 'SIM' : 'NÃO') . "\n";
    echo "---\n";
}
?> 