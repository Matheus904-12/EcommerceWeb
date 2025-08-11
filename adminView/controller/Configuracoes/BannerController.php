<?php
require_once __DIR__ . '/../../models/Configuracoes/BannerModel.php';

class BannerController {
    private $bannerModel;
    private $uploadDir = '../../uploads/carousel/';
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $maxSize = 5 * 1024 * 1024; // 5MB

    public function __construct($conn) {
        $this->bannerModel = new BannerModel($conn);
        $this->ensureUploadDirExists();
    }

    private function ensureUploadDirExists() {
        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0777, true)) {
                throw new Exception("Não foi possível criar o diretório de upload.");
            }
            chmod($this->uploadDir, 0777);
        }
    }

    public function processUpload($file) {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Parâmetros de upload inválidos.');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new Exception('Nenhum arquivo enviado.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception('Arquivo excede o tamanho máximo permitido.');
            default:
                throw new Exception('Erro desconhecido no upload.');
        }

        if ($file['size'] > $this->maxSize) {
            throw new Exception('O arquivo excede o tamanho máximo de 5MB.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $this->allowedTypes)) {
            throw new Exception('Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WEBP.');
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('banner_') . '.' . strtolower($ext);
        $destination = $this->uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Falha ao mover o arquivo enviado.');
        }

        chmod($destination, 0644);
        return $filename;
    }

    public function getBanners() {
        return $this->bannerModel->getBanners();
    }

    public function getBannerById($id) {
        return $this->bannerModel->getBannerById($id);
    }

    public function addBanner($data) {
        try {
            $id = $this->bannerModel->addBanner($data);
            return ['success' => true, 'message' => 'Banner adicionado com sucesso', 'id' => $id];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao adicionar banner: ' . $e->getMessage()];
        }
    }

    public function updateBanner($id, $data) {
        try {
            $this->bannerModel->updateBanner($id, $data);
            return ['success' => true, 'message' => 'Banner atualizado com sucesso'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao atualizar banner: ' . $e->getMessage()];
        }
    }

    public function toggleStatus($id) {
        try {
            $newStatus = $this->bannerModel->toggleStatus($id);
            return ['success' => true, 'message' => 'Status do banner alterado para ' . ($newStatus ? 'ativo' : 'inativo')];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao alterar status do banner: ' . $e->getMessage()];
        }
    }

    public function deleteBanner($id) {
        try {
            $banner = $this->bannerModel->getBannerById($id);
            if ($banner && !empty($banner['imagem'])) {
                $filePath = $this->uploadDir . $banner['imagem'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $this->bannerModel->deleteBanner($id);
            return ['success' => true, 'message' => 'Banner excluído com sucesso'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao excluir banner: ' . $e->getMessage()];
        }
    }
}