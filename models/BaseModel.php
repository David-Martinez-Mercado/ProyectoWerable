<?php
require_once 'config/connection.php';

class BaseModel {
    protected $db_private;
    protected $db_public;
    
    public function __construct() {
        $database = new Database();
        $this->db_private = $database->conn_private;
        $this->db_public = $database->conn_public;
    }
    
    protected function executeQuery($conn, $sql, $params = []) {
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            error_log("Error en consulta: " . $e->getMessage());
            throw new Exception("Error en la operación de base de datos");
        }
    }
    
    protected function getSingleResult($conn, $sql, $params = []) {
        $stmt = $this->executeQuery($conn, $sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    protected function getAllResults($conn, $sql, $params = []) {
        $stmt = $this->executeQuery($conn, $sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>