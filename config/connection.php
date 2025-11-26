<?php
class Database {
    private $host_private = "localhost";
    private $db_private = "vital_monitor_private";
    private $user_private = "root";
    private $pass_private = "";
    
    private $host_public = "localhost";
    private $db_public = "vital_monitor_public";
    private $user_public = "root";
    private $pass_public = "";
    
    public $conn_private;
    public $conn_public;
    
    public function __construct() {
        try {
            // Conexión BD Privada
            $this->conn_private = new PDO(
                "mysql:host=" . $this->host_private . ";dbname=" . $this->db_private . ";charset=utf8",
                $this->user_private,
                $this->pass_private,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
            
            // Conexión BD Pública
            $this->conn_public = new PDO(
                "mysql:host=" . $this->host_public . ";dbname=" . $this->db_public . ";charset=utf8",
                $this->user_public,
                $this->pass_public,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
            
        } catch(PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
            throw new Exception("Error al conectar con la base de datos");
        }
    }
}
?>