<?php

class Config
{
    private $jsonPath;

    public function __construct($jsonPath)
    {
        $this->jsonPath = $jsonPath;
    }

    public function getJsonPath()
    {
        return $this->jsonPath;
    }

    public function getJsonData()
    {
        if (!file_exists($this->jsonPath)) {
            throw new Exception("Arquivo JSON não encontrado: " . $this->jsonPath);
        }
        
        $jsonContent = file_get_contents($this->jsonPath);
        $data = json_decode($jsonContent);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Erro ao decodificar JSON: " . json_last_error_msg());
        }
        
        return $data;
    }

    public function salvarJson($jsonData)
    {
        try {
            if (!is_writable(dirname($this->jsonPath))) {
                error_log("Diretório não é gravável: " . dirname($this->jsonPath));
                return "Erro: Diretório não tem permissão de escrita";
            }
            
            if (file_put_contents($this->jsonPath, $jsonData) === false) {
                throw new Exception("Falha ao escrever no arquivo JSON");
            }
            return "JSON salvo com sucesso!";
        } catch (Exception $e) {
            error_log("Erro ao salvar JSON: " . $e->getMessage());
            return "Erro: " . $e->getMessage();
        }
    }
}