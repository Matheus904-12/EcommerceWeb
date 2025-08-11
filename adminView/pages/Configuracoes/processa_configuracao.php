<?php 
require_once '../../controller/Configuracoes/ConfigController.php';

$jsonPath = '../../config_site.json'; 
$controller = new ConfigController($jsonPath);

$uploadDir = '../../uploads/inicio/'; 
$allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif']; 
$allowedVideoTypes = ['video/mp4', 'video/webm', 'video/ogg'];

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $midia = $_POST['midia_url'] ?? '';

    // O arquivo foi enviado corretamente
    if (isset($_FILES['midia_upload']) && $_FILES['midia_upload']['error'] === UPLOAD_ERR_OK) { 
        $fileTmpPath = $_FILES['midia_upload']['tmp_name']; 
        $fileType = mime_content_type($fileTmpPath); 
        $fileName = basename($_FILES['midia_upload']['name']); 
        $fileDestination = $uploadDir . $fileName;

        // Verifica se o formato é válido
        if (in_array($fileType, array_merge($allowedImageTypes, $allowedVideoTypes))) { 
            // Move o arquivo para a pasta de uploads
            if (move_uploaded_file($fileTmpPath, $fileDestination)) { 
                $midia = $fileName; // Apenas o nome do arquivo para salvar no JSON
            } else { 
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar o arquivo.']);
                exit; 
            } 
        } else { 
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Tipo de arquivo não permitido.']);
            exit; 
        } 
    }

    // Atualiza o JSON com a mídia processada
    $data = [ 
        'pagina_inicial' => [ 
            'sobre' => [ 
                'texto' => $_POST['sobre_texto'] ?? '',
                'midia' => $midia 
            ], 
        ], 
        'contato' => [ 
            'whatsapp' => $_POST['whatsapp'] ?? '', 
            'instagram' => $_POST['instagram'] ?? '', 
            'facebook' => $_POST['facebook'] ?? '', 
            'email' => $_POST['email'] ?? '' 
        ] 
    ];

    $resultado = $controller->salvarJson(json_encode($data)); 
    
    if (strpos($resultado, 'sucesso') !== false) {
        echo json_encode(['status' => 'success', 'message' => 'Configurações salvas com sucesso!', 'reload' => true]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar configurações: ' . $resultado]);
    }
} else { 
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método inválido.']); 
}
?>