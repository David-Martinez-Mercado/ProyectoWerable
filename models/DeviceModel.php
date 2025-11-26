<?php
require_once 'BaseModel.php';

class DeviceModel extends BaseModel {
    
    public function getUserDevices($userId) {
        $sql = "SELECT d.codigo, d.nombre_paciente, d.fecha_nacimiento, d.direccion_residencia, 
                       d.direccion_recoleccion, d.contacto_emergencia, d.edad, d.enfermedades_cronicas, 
                       d.estado, d.fecha_registro, d.ultima_lectura,
                       l.lectura_FC, l.lectura_SpO2, l.lectura_temperatura,
                       l.gps_lat, l.gps_lon, l.fecha_lectura,
                       u.umbral_FC_min, u.umbral_FC_max, u.umbral_SpO2_min, 
                       u.umbral_temperatura_min, u.umbral_temperatura_max
                FROM Pacientes d
                LEFT JOIN Lecturas l ON d.codigo = l.id_dispositivo 
                    AND l.fecha_lectura = (SELECT MAX(fecha_lectura) 
                                         FROM Lecturas 
                                         WHERE id_dispositivo = d.codigo)
                LEFT JOIN Umbrales_Alerta u ON d.codigo = u.id_dispositivo
                WHERE d.id_usuario = ?
                ORDER BY d.fecha_registro DESC";
        
        return $this->getAllResults($this->db_private, $sql, [$userId]);
    }
    
    public function getDevice($deviceCode, $userId) {
        $sql = "SELECT d.codigo, d.nombre_paciente, d.fecha_nacimiento, d.direccion_residencia,
                       d.direccion_recoleccion, d.contacto_emergencia, d.edad, d.enfermedades_cronicas,
                       d.estado, d.fecha_registro, d.ultima_lectura,
                       u.umbral_FC_min, u.umbral_FC_max, u.umbral_SpO2_min, 
                       u.umbral_temperatura_min, u.umbral_temperatura_max
                FROM Pacientes d
                LEFT JOIN Umbrales_Alerta u ON d.codigo = u.id_dispositivo
                WHERE d.codigo = ? AND d.id_usuario = ?";
        
        return $this->getSingleResult($this->db_private, $sql, [$deviceCode, $userId]);
    }
    
    public function addDevice($userId, $deviceCode, $patientName, $birthDate, $address, $collectionAddress, $emergencyContact, $age, $conditions, $thresholds) {
        // Insertar paciente
        $sql = "INSERT INTO Pacientes (codigo, id_usuario, nombre_paciente, fecha_nacimiento, 
                                      direccion_residencia, direccion_recoleccion, contacto_emergencia,
                                      edad, enfermedades_cronicas, estado, fecha_registro) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'offline', NOW())";
        
        $stmt = $this->executeQuery($this->db_private, $sql, [
            $deviceCode, $userId, $patientName, $birthDate, $address, 
            $collectionAddress, $emergencyContact, $age, $conditions
        ]);
        
        // Insertar umbrales personalizados
        $this->addDeviceThresholds($deviceCode, $thresholds);
        
        return $stmt->rowCount();
    }
    
    private function addDeviceThresholds($deviceCode, $thresholds) {
        $sql = "INSERT INTO Umbrales_Alerta (id_dispositivo, umbral_FC_min, umbral_FC_max, 
                                            umbral_SpO2_min, umbral_temperatura_min, umbral_temperatura_max) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $this->executeQuery($this->db_private, $sql, [
            $deviceCode,
            $thresholds['heart_rate_min'] ?? 60,
            $thresholds['heart_rate_max'] ?? 100,
            $thresholds['spO2_min'] ?? 90,
            $thresholds['temp_min'] ?? 35.5,
            $thresholds['temp_max'] ?? 37.5
        ]);
    }
    
    public function updateDeviceThresholds($deviceCode, $thresholds) {
        $sql = "UPDATE Umbrales_Alerta 
                SET umbral_FC_min = ?, umbral_FC_max = ?, umbral_SpO2_min = ?,
                    umbral_temperatura_min = ?, umbral_temperatura_max = ?,
                    fecha_actualizacion = NOW()
                WHERE id_dispositivo = ?";
        
        $stmt = $this->executeQuery($this->db_private, $sql, [
            $thresholds['heart_rate_min'],
            $thresholds['heart_rate_max'],
            $thresholds['spO2_min'],
            $thresholds['temp_min'],
            $thresholds['temp_max'],
            $deviceCode
        ]);
        
        return $stmt->rowCount();
    }
    
    public function deviceExists($deviceCode) {
        $sql = "SELECT codigo FROM Pacientes WHERE codigo = ?";
        $result = $this->getSingleResult($this->db_private, $sql, [$deviceCode]);
        return $result !== false;
    }
    
    public function updateDeviceStatus($deviceCode, $status) {
        $sql = "UPDATE Pacientes SET estado = ?, ultima_lectura = NOW() WHERE codigo = ?";
        $stmt = $this->executeQuery($this->db_private, $sql, [$status, $deviceCode]);
        return $stmt->rowCount();
    }
    
    public function checkDeviceConnection($deviceCode) {
        // Verificar si hay lecturas recientes (últimos 2 minutos)
        $sql = "SELECT COUNT(*) as count FROM Lecturas 
                WHERE id_dispositivo = ? AND fecha_lectura >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)";
        
        $result = $this->getSingleResult($this->db_private, $sql, [$deviceCode]);
        return $result['count'] > 0;
    }
}
?>