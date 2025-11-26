<?php
require_once 'BaseModel.php';

class AlertModel extends BaseModel {
    
    public function createMedicalAlert($deviceId, $latitude, $longitude) {
        // Insertar en BD privada
        $sqlPrivate = "INSERT INTO Log_Alertas (id_dispositivo, tipo_alerta, descripcion, 
                                               ubicacion_lat, ubicacion_lon, estado, fecha_creacion) 
                       VALUES (?, 'medica', 'Emergencia médica activada', ?, ?, 'PENDIENTE', NOW())";
        
        $this->executeQuery($this->db_private, $sqlPrivate, [$deviceId, $latitude, $longitude]);
        $alertId = $this->db_private->lastInsertId();
        
        // Insertar en BD pública para C4/C5
        $sqlPublic = "INSERT INTO Alertas_C5 (id_alerta_privada, id_dispositivo, tipo_emergencia,
                                             ubicacion_lat, ubicacion_lon, estado, fecha_reporte) 
                      VALUES (?, ?, 'Emergencia Médica', ?, ?, 'PENDIENTE', NOW())";
        
        $this->executeQuery($this->db_public, $sqlPublic, [$alertId, $deviceId, $latitude, $longitude]);
        
        return $alertId;
    }
    
    public function createMissingAlert($deviceId, $latitude, $longitude) {
        // Insertar en BD privada
        $sqlPrivate = "INSERT INTO Log_Alertas (id_dispositivo, tipo_alerta, descripcion, 
                                               ubicacion_lat, ubicacion_lon, estado, fecha_creacion) 
                       VALUES (?, 'extravio', 'Paciente extraviado', ?, ?, 'PENDIENTE', NOW())";
        
        $this->executeQuery($this->db_private, $sqlPrivate, [$deviceId, $latitude, $longitude]);
        $alertId = $this->db_private->lastInsertId();
        
        // Insertar en BD pública para C4/C5
        $sqlPublic = "INSERT INTO Alertas_C5 (id_alerta_privada, id_dispositivo, tipo_emergencia,
                                             ubicacion_lat, ubicacion_lon, estado, fecha_reporte) 
                      VALUES (?, ?, 'Persona Extraviada', ?, ?, 'PENDIENTE', NOW())";
        
        $this->executeQuery($this->db_public, $sqlPublic, [$alertId, $deviceId, $latitude, $longitude]);
        
        return $alertId;
    }
    
    public function getActiveAlerts($userId) {
        $sql = "SELECT la.*, p.nombre_paciente 
                FROM Log_Alertas la
                JOIN Pacientes p ON la.id_dispositivo = p.codigo
                WHERE p.id_usuario = ? AND la.estado IN ('PENDIENTE', 'EN PROCESO')
                ORDER BY la.fecha_creacion DESC";
        
        return $this->getAllResults($this->db_private, $sql, [$userId]);
    }
    
    public function getAlertStatus($alertId) {
        $sql = "SELECT estado, fecha_actualizacion, notas_actualizacion
                FROM Alertas_C5 
                WHERE id_alerta_privada = ?";
        
        return $this->getSingleResult($this->db_public, $sql, [$alertId]);
    }
    
    public function updateAlertStatus($alertId, $status, $notes = '') {
        $sqlPrivate = "UPDATE Log_Alertas SET estado = ? WHERE id = ?";
        $this->executeQuery($this->db_private, $sqlPrivate, [$status, $alertId]);
        
        $sqlPublic = "UPDATE Alertas_C5 SET estado = ?, notas_actualizacion = ?, 
                        fecha_actualizacion = NOW() 
                      WHERE id_alerta_privada = ?";
        $this->executeQuery($this->db_public, $sqlPublic, [$status, $notes, $alertId]);
    }
}
?>