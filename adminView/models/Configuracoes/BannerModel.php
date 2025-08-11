<?php

class BannerModel {
    private $conn;
    private $table = 'carousel_images';

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Retorna todos os banners, ordenados por ordem crescente.
     * @return array Lista de banners.
     */
    public function getBanners() {
        try {
            $query = "SELECT * FROM {$this->table} ORDER BY ordem ASC";
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $this->conn->error);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $banners = [];
            while ($row = $result->fetch_assoc()) {
                $banners[] = $row;
            }
            $stmt->close();
            return $banners;
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar banners: " . $e->getMessage());
        }
    }

    /**
     * Retorna um banner específico pelo ID.
     * @param int $id ID do banner.
     * @return array|null Dados do banner ou null se não encontrado.
     */
    public function getBannerById($id) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $this->conn->error);
            }
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $banner = $result->fetch_assoc();
            $stmt->close();
            return $banner ?: null;
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar banner: " . $e->getMessage());
        }
    }

    /**
     * Adiciona um novo banner.
     * @param array $data Dados do banner (imagem, titulo, descricao, link, ordem).
     * @return int ID do banner inserido.
     */
    public function addBanner($data) {
        try {
            $query = "INSERT INTO {$this->table} (imagem, titulo, descricao, link, ordem, status, created_at, updated_at) 
                      VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $this->conn->error);
            }
            $status = isset($data['status']) ? (int)$data['status'] : 1;
            $stmt->bind_param('ssssii', 
                $data['imagem'], 
                $data['titulo'], 
                $data['descricao'], 
                $data['link'], 
                $data['ordem'], 
                $status
            );
            if (!$stmt->execute()) {
                throw new Exception("Erro ao inserir banner: " . $stmt->error);
            }
            $insertId = $this->conn->insert_id;
            $stmt->close();
            return $insertId;
        } catch (Exception $e) {
            throw new Exception("Erro ao adicionar banner: " . $e->getMessage());
        }
    }

    /**
     * Atualiza um banner existente.
     * @param int $id ID do banner.
     * @param array $data Dados do banner (imagem, titulo, descricao, link, ordem).
     * @return bool Verdadeiro se atualizado com sucesso.
     */
    public function updateBanner($id, $data) {
        try {
            $query = "UPDATE {$this->table} SET 
                      imagem = ?, 
                      titulo = ?, 
                      descricao = ?, 
                      link = ?, 
                      ordem = ?, 
                      updated_at = NOW()
                      WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $this->conn->error);
            }
            $stmt->bind_param('ssssii', 
                $data['imagem'], 
                $data['titulo'], 
                $data['descricao'], 
                $data['link'], 
                $data['ordem'], 
                $id
            );
            $success = $stmt->execute();
            if (!$success) {
                throw new Exception("Erro ao atualizar banner: " . $stmt->error);
            }
            $stmt->close();
            return $success;
        } catch (Exception $e) {
            throw new Exception("Erro ao atualizar banner: " . $e->getMessage());
        }
    }

    /**
     * Alterna o status (ativo/inativo) de um banner.
     * @param int $id ID do banner.
     * @return bool Novo status (1 para ativo, 0 para inativo).
     */
    public function toggleStatus($id) {
        try {
            $query = "UPDATE {$this->table} SET 
                      status = IF(status = 1, 0, 1), 
                      updated_at = NOW()
                      WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $this->conn->error);
            }
            $stmt->bind_param('i', $id);
            if (!$stmt->execute()) {
                throw new Exception("Erro ao alterar status: " . $stmt->error);
            }
            $query = "SELECT status FROM {$this->table} WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $this->conn->error);
            }
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row['status'];
        } catch (Exception $e) {
            throw new Exception("Erro ao alterar status do banner: " . $e->getMessage());
        }
    }

    /**
     * Exclui um banner pelo ID.
     * @param int $id ID do banner.
     * @return bool Verdadeiro se excluído com sucesso.
     */
    public function deleteBanner($id) {
        try {
            $query = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $this->conn->error);
            }
            $stmt->bind_param('i', $id);
            $success = $stmt->execute();
            if (!$success) {
                throw new Exception("Erro ao excluir banner: " . $stmt->error);
            }
            $stmt->close();
            return $success;
        } catch (Exception $e) {
            throw new Exception("Erro ao excluir banner: " . $e->getMessage());
        }
    }

    /**
     * Reordena um banner (para compatibilidade, se necessário).
     * @param int $id ID do banner.
     * @param string $direction Direção ('up' ou 'down').
     * @return bool Verdadeiro se reordenado com sucesso.
     */
    public function reorderBanner($id, $direction) {
        try {
            $banner = $this->getBannerById($id);
            if (!$banner) {
                throw new Exception("Banner não encontrado.");
            }
            $currentOrder = $banner['ordem'];

            if ($direction === 'up') {
                $query = "SELECT id, ordem FROM {$this->table} 
                          WHERE ordem < ? 
                          ORDER BY ordem DESC LIMIT 1";
            } else {
                $query = "SELECT id, ordem FROM {$this->table} 
                          WHERE ordem > ? 
                          ORDER BY ordem ASC LIMIT 1";
            }

            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $this->conn->error);
            }
            $stmt->bind_param('i', $currentOrder);
            $stmt->execute();
            $result = $stmt->get_result();
            $adjacentBanner = $result->fetch_assoc();
            $stmt->close();

            if ($adjacentBanner) {
                $query = "UPDATE {$this->table} SET ordem = ? WHERE id = ?";
                $stmt = $this->conn->prepare($query);
                if (!$stmt) {
                    throw new Exception("Erro ao preparar consulta: " . $this->conn->error);
                }
                $stmt->bind_param('ii', $adjacentBanner['ordem'], $id);
                if (!$stmt->execute()) {
                    throw new Exception("Erro ao atualizar ordem: " . $stmt->error);
                }
                $stmt->close();

                $query = "UPDATE {$this->table} SET ordem = ? WHERE id = ?";
                $stmt = $this->conn->prepare($query);
                if (!$stmt) {
                    throw new Exception("Erro ao preparar consulta: " . $this->conn->error);
                }
                $stmt->bind_param('ii', $currentOrder, $adjacentBanner['id']);
                if (!$stmt->execute()) {
                    throw new Exception("Erro ao atualizar ordem: " . $stmt->error);
                }
                $stmt->close();
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Erro ao reordenar banner: " . $e->getMessage());
        }
    }
}

?>