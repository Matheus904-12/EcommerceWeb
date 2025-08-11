<?php
require_once __DIR__ . '/../../models/Configuracoes/Config.php';

class ConfigController
{
    private $config;

    public function __construct($jsonPath)
    {
        $this->config = new Config($jsonPath);
    }

    public function salvarJson($jsonData)
    {
        return $this->config->salvarJson($jsonData);
    }

    public function getConfig()
    {
        return $this->config->getJsonData();
    }
}