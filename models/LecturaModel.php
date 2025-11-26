<?php
require_once 'BaseModel.php';

class LecturaModel extends BaseModel {
    
    public function insertReading($deviceId, $heartRate, $spO2, $temperature, $lat, $lon) {
        $sql = "INSERT INTO Lecturas (id_dispositivo, lectura_FC, lectura_SpO2, 
                                     lectura_temperatura, gps_lat, gps_lon, fecha_lectura) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->executeQuery($this->db_private, $sql, [
            $deviceId, $heartRate, $spO2, $temperature, $lat, $lon
        ]);
        
        // Actualizar última lectura del dispositivo
        $updateSql = "UPDATE Pacientes SET ultima_lectura = NOW() WHERE codigo = ?";
        $this->executeQuery($this->db_private, $updateSql, [$deviceId]);
        
        return $this->db_private->lastInsertId();
    }
    
    public function getLastReading($deviceId) {
        $sql = "SELECT lectura_FC, lectura_SpO2, lectura_temperatura, 
                       gps_lat, gps_lon, fecha_lectura
                FROM Lecturas 
                WHERE id_dispositivo = ? 
                ORDER BY fecha_lectura DESC 
                LIMIT 1";
        
        return $this->getSingleResult($this->db_private, $sql, [$deviceId]);
    }
    
    public function getChartData($deviceId, $hours = 6) {
        $sql = "SELECT lectura_FC, lectura_SpO2, lectura_temperatura,
                       gps_lat, gps_lon, fecha_lectura
                FROM Lecturas 
                WHERE id_dispositivo = ? 
                AND fecha_lectura >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                ORDER BY fecha_lectura ASC";
        
        return $this->getAllResults($this->db_private, $sql, [$deviceId, $hours]);
    }
    
    public function getHistoricalData($deviceId, $startDate, $endDate) {
        $sql = "SELECT lectura_FC, lectura_SpO2, lectura_temperatura,
                       gps_lat, gps_lon, fecha_lectura
                FROM Lecturas 
                WHERE id_dispositivo = ? 
                AND DATE(fecha_lectura) BETWEEN ? AND ?
                ORDER BY fecha_lectura DESC";
        
        return $this->getAllResults($this->db_private, $sql, [$deviceId, $startDate, $endDate]);
    }
    
    public function checkAlertThresholds($deviceId, $heartRate, $spO2, $temperature) {
        $sql = "SELECT umbral_FC_min, umbral_FC_max, umbral_SpO2_min, 
                       umbral_temperatura_min, umbral_temperatura_max
                FROM Umbrales_Alerta 
                WHERE id_dispositivo = ? 
                LIMIT 1";
        
        $thresholds = $this->getSingleResult($this->db_private, $sql, [$deviceId]);
        
        if (!$thresholds) {
            // Umbrales por defecto
            $thresholds = [
                'umbral_FC_min' => 60,
                'umbral_FC_max' => 100,
                'umbral_SpO2_min' => 90,
                'umbral_temperatura_min' => 35.5,
                'umbral_temperatura_max' => 37.5
            ];
        }
        
        $alerts = [];
        
        if ($heartRate < $thresholds['umbral_FC_min'] || $heartRate > $thresholds['umbral_FC_max']) {
            $alerts[] = 'FC';
        }
        
        if ($spO2 < $thresholds['umbral_SpO2_min']) {
            $alerts[] = 'SpO2';
        }
        
        if ($temperature < $thresholds['umbral_temperatura_min'] || $temperature > $thresholds['umbral_temperatura_max']) {
            $alerts[] = 'Temperatura';
        }
        
        return $alerts;
    }
}
?>