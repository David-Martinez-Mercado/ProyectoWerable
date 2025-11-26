<?php
require_once 'BaseModel.php';

class UserModel extends BaseModel {
    
    public function createUser($name, $email, $password, $userType = 'familiar') {
        $sql = "INSERT INTO USUARIOS (nombre, email, password, tipo_usuario, fecha_creacion) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->executeQuery($this->db_private, $sql, [
            $name, $email, $hashedPassword, $userType
        ]);
        
        return $this->db_private->lastInsertId();
    }
    
    public function getUserByEmail($email) {
        $sql = "SELECT id, nombre, email, password, tipo_usuario, estado 
                FROM USUARIOS 
                WHERE email = ? AND estado = 'activo'";
        
        return $this->getSingleResult($this->db_private, $sql, [$email]);
    }
    
    public function getUserById($id) {
        $sql = "SELECT id, nombre, email, tipo_usuario, fecha_creacion 
                FROM USUARIOS 
                WHERE id = ? AND estado = 'activo'";
        
        return $this->getSingleResult($this->db_private, $sql, [$id]);
    }
    
    public function updateUser($userId, $name, $email) {
        $sql = "UPDATE USUARIOS 
                SET nombre = ?, email = ? 
                WHERE id = ?";
        
        $stmt = $this->executeQuery($this->db_private, $sql, [$name, $email, $userId]);
        return $stmt->rowCount();
    }
}
?>