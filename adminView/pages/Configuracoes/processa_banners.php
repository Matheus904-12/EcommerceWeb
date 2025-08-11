<?php
ob_start();
session_start();
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/banners_errors.log');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    if (!isset($_SESSION['admin_logged_in'])) {
        $msg = 'Acesso não autorizado';
        if (isAjaxRequest()) {
            jsonResponse(['status' => 'error', 'message' => $msg]);
        } else {
            $_SESSION['banner_message'] = $msg;
            header('Location: ../../view/editar_index.php');
        }
        exit;
    }

    require_once '../../config/dbconnect.php';
    require_once '../../controller/Configuracoes/BannerController.php';

    $bannerController = new BannerController($conn);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $msg = 'Método de requisição inválido. Esperado POST.';
        if (isAjaxRequest()) {
            jsonResponse(['status' => 'error', 'message' => $msg]);
        } else {
            error_log('processa_banners.php: método inválido: ' . $_SERVER['REQUEST_METHOD']);
            throw new Exception($msg);
        }
    }

    $action = $_POST['action'] ?? '';
    switch ($action) {
        case 'save_banner':
            $filename = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $filename = $bannerController->processUpload($_FILES['image']);
            } elseif (!empty($_POST['existingImage'])) {
                $filename = $_POST['existingImage'];
            } else {
                if (empty($_POST['id'])) {
                    $msg = 'Imagem é obrigatória para novos banners.';
                    if (isAjaxRequest()) {
                        jsonResponse(['status' => 'error', 'message' => $msg]);
                    } else {
                        $_SESSION['banner_message'] = $msg;
                        header('Location: ../../view/editar_index.php');
                    }
                    exit;
                }
            }

            $dadosBanner = [
                'imagem' => $filename,
                'titulo' => $_POST['titulo'] ?? '',
                'descricao' => $_POST['descricao'] ?? '',
                'link' => $_POST['link'] ?? '',
                'ordem' => (int)($_POST['ordem'] ?? 0)
            ];

            if (!empty($_POST['id'])) {
                $result = $bannerController->updateBanner((int)$_POST['id'], $dadosBanner);
                $msg = $result['success'] ? 'Banner atualizado com sucesso' : $result['message'];
            } else {
                $result = $bannerController->addBanner($dadosBanner);
                $msg = $result['success'] ? 'Banner adicionado com sucesso' : $result['message'];
            }
            if (isAjaxRequest()) {
                jsonResponse([
                    'status' => $result['success'] ? 'success' : 'error',
                    'message' => $msg,
                    'reload' => true
                ]);
            } else {
                $_SESSION['banner_message'] = $msg;
                header('Location: ../../view/editar_index.php');
            }
            exit;

        case 'get_banner':
            if (empty($_POST['id'])) {
                $msg = 'ID do banner não fornecido.';
                if (isAjaxRequest()) {
                    jsonResponse(['status' => 'error', 'message' => $msg]);
                } else {
                    $_SESSION['banner_message'] = $msg;
                    header('Location: ../../view/editar_index.php');
                }
                exit;
            }
            $banner = $bannerController->getBannerById((int)$_POST['id']);
            if ($banner) {
                if (isAjaxRequest()) {
                    jsonResponse(['status' => 'success', 'banner' => $banner]);
                } else {
                    $_SESSION['edit_banner'] = $banner;
                    $_SESSION['banner_message'] = 'Banner carregado para edição';
                    header('Location: ../../view/editar_index.php');
                }
            } else {
                $msg = 'Banner não encontrado';
                if (isAjaxRequest()) {
                    jsonResponse(['status' => 'error', 'message' => $msg]);
                } else {
                    $_SESSION['banner_message'] = $msg;
                    header('Location: ../../view/editar_index.php');
                }
            }
            exit;

        case 'toggle_status':
            if (empty($_POST['id'])) {
                $msg = 'ID do banner não fornecido.';
                if (isAjaxRequest()) {
                    jsonResponse(['status' => 'error', 'message' => $msg]);
                } else {
                    $_SESSION['banner_message'] = $msg;
                    header('Location: ../../view/editar_index.php');
                }
                exit;
            }
            $result = $bannerController->toggleStatus((int)$_POST['id']);
            $msg = $result['message'];
            if (isAjaxRequest()) {
                jsonResponse([
                    'status' => $result['success'] ? 'success' : 'error',
                    'message' => $msg,
                    'reload' => true
                ]);
            } else {
                $_SESSION['banner_message'] = $msg;
                header('Location: ../../view/editar_index.php');
            }
            exit;

        case 'delete_banner':
            if (empty($_POST['id'])) {
                $msg = 'ID do banner não fornecido.';
                if (isAjaxRequest()) {
                    jsonResponse(['status' => 'error', 'message' => $msg]);
                } else {
                    $_SESSION['banner_message'] = $msg;
                    header('Location: ../../view/editar_index.php');
                }
                exit;
            }
            $result = $bannerController->deleteBanner((int)$_POST['id']);
            $msg = $result['success'] ? 'Banner excluído com sucesso' : $result['message'];
            if (isAjaxRequest()) {
                jsonResponse([
                    'status' => $result['success'] ? 'success' : 'error',
                    'message' => $msg,
                    'reload' => true
                ]);
            } else {
                $_SESSION['banner_message'] = $msg;
                header('Location: ../../view/editar_index.php');
            }
            exit;

        default:
            $msg = 'Ação inválida: ' . htmlspecialchars($action);
            if (isAjaxRequest()) {
                jsonResponse(['status' => 'error', 'message' => $msg]);
            } else {
                $_SESSION['banner_message'] = $msg;
                header('Location: ../../view/editar_index.php');
            }
            exit;
    }
} catch (Exception $e) {
    error_log("ERRO: " . $e->getMessage());
    $msg = 'Erro ao processar banner: ' . $e->getMessage();
    if (isAjaxRequest()) {
        jsonResponse(['status' => 'error', 'message' => $msg]);
    } else {
        $_SESSION['banner_message'] = $msg;
        header('Location: ../../view/editar_index.php');
    }
    exit;
}

// Função para detectar AJAX
function isAjaxRequest() {
    return (
        (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    );
}
// Função para resposta JSON
function jsonResponse($data) {
    // Garante que o arquivo de log será criado se não existir
    $logFile = __DIR__.'/debug_output.log';
    if (!file_exists($logFile)) {
        file_put_contents($logFile, "[CRIADO] Arquivo de log criado em ".date('Y-m-d H:i:s')."\n");
    }
    // Loga o conteúdo do buffer antes de limpar
    file_put_contents($logFile, "[ANTES JSON]".ob_get_contents()."\n", FILE_APPEND);
    // Limpa todos os buffers ativos
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    // Garante que o header só será enviado se ainda não foi
    if (!headers_sent()) {
        header('Content-Type: application/json');
    } else {
        file_put_contents($logFile, "[ERRO] Headers já enviados antes do JSON!\n", FILE_APPEND);
    }
    $json = json_encode($data);
    echo $json;
    // Loga o JSON enviado
    file_put_contents($logFile, "[DEPOIS JSON]".$json."\n", FILE_APPEND);
    exit;
}

// Registrar erro fatal e output inesperado
register_shutdown_function(function() {
    $logFile = __DIR__.'/debug_output.log';
    $error = error_get_last();
    if ($error !== null) {
        file_put_contents($logFile, "[FATAL] ".print_r($error, true)."\n", FILE_APPEND);
    }
    // Loga qualquer output que restou
    $output = ob_get_contents();
    if ($output) {
        file_put_contents($logFile, "[SHUTDOWN OUTPUT]".$output."\n", FILE_APPEND);
    }
});
?>