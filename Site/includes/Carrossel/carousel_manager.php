<?php
// filepath: c:\xampp\htdocs\GoldLar-main\adminView\carousel_manager.php

<?php
require_once '../../../adminView/config/dbconnect.php';
require_once '../../../adminView/controller/Produtos/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productController = new ProductController($conn);
    
    if (isset($_FILES['imagem'])) {
        $titulo = $_POST['titulo'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $link = $_POST['link'] ?? '';
        $ordem = $_POST['ordem'] ?? 0;
        
        $uploadDir = 'uploads/carousel/';
        $fileName = time() . '_' . basename($_FILES['imagem']['name']);
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $targetFile)) {
            $query = "INSERT INTO carousel_images (imagem, titulo, descricao, link, ordem) 
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ssssi', $fileName, $titulo, $descricao, $link, $ordem);
            $stmt->execute();
        }
    }
}

$query = "SELECT * FROM carousel_images ORDER BY ordem ASC";
$result = $conn->query($query);
$carousel_images = $result->fetch_all(MYSQLI_ASSOC);
?>

<!-- HTML do gerenciador de carrossel aqui -->