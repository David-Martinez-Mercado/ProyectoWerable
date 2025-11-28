<?php
require_once 'BaseModel.php';

class AlertModel extends BaseModel {
    
    public function createMedicalAlert($deviceId, $latitude, $longitude) {
        error_log("🚨 ALERTMODEL: Iniciando createMedicalAlert para dispositivo: $deviceId");
        
        // Primero obtener datos del paciente para la BD pública
        error_log("📋 Obteniendo datos del paciente...");
        $patientData = $this->getPatientDataForPublicAlert($deviceId);
        error_log("📋 Datos del paciente: " . print_r($patientData, true));
        
        // Insertar en BD privada
        error_log("💾 Insertando en BD PRIVADA...");
        $sqlPrivate = "INSERT INTO Log_Alertas (id_dispositivo, tipo_alerta, descripcion, 
                                               ubicacion_lat, ubicacion_lon, estado, fecha_creacion) 
                       VALUES (?, 'medica', 'Emergencia médica activada', ?, ?, 'PENDIENTE', NOW())";
        
        try {
            $this->executeQuery($this->db_private, $sqlPrivate, [$deviceId, $latitude, $longitude]);
            $alertId = $this->db_private->lastInsertId();
            error_log("✅ Alerta privada creada ID: $alertId");
        } catch (Exception $e) {
            error_log("❌ Error en BD privada: " . $e->getMessage());
            throw $e;
        }
        
        // Insertar en BD pública con la estructura correcta
        error_log("🌐 Insertando en BD PÚBLICA alertas_c5...");
        try {
            $sqlPublic = "INSERT INTO alertas_c5 (id_alerta_privada, id_dispositivo, tipo_emergencia,
                                                 nombre_paciente, edad, enfermedades_cronicas, 
                                                 contacto_emergencia, direccion_residencia, direccion_recoleccion,
                                                 ubicacion_lat, ubicacion_lon, estado, fecha_reporte) 
                          VALUES (?, ?, 'Emergencia Médica', ?, ?, ?, ?, ?, ?, ?, ?, 'PENDIENTE', NOW())";
            
            error_log("🔍 SQL Pública: $sqlPublic");
            error_log("🔍 Parámetros: " . print_r([
                $alertId, $deviceId,
                $patientData['nombre_paciente'],
                $patientData['edad'],
                $patientData['enfermedades_cronicas'],
                $patientData['contacto_emergencia'],
                $patientData['direccion_residencia'],
                $patientData['direccion_recoleccion'],
                $latitude,
                $longitude
            ], true));
            
            $this->executeQuery($this->db_public, $sqlPublic, [
                $alertId, $deviceId,
                $patientData['nombre_paciente'],
                $patientData['edad'],
                $patientData['enfermedades_cronicas'],
                $patientData['contacto_emergencia'],
                $patientData['direccion_residencia'],
                $patientData['direccion_recoleccion'],
                $latitude,
                $longitude
            ]);
            
            $publicAlertId = $this->db_public->lastInsertId();
            error_log("✅✅ Alerta pública creada exitosamente. ID: $publicAlertId");
            
        } catch (Exception $e) {
            error_log("❌❌ ERROR CRÍTICO - No se pudo insertar en BD pública: " . $e->getMessage());
            error_log("❌❌ Archivo: " . $e->getFile() . " Línea: " . $e->getLine());
            // No lanzar excepción, continuar con el proceso pero loggear el error
        }
        
        return $alertId;
    }
    
    public function createMissingAlert($deviceId, $latitude, $longitude) {
        error_log("🚨 ALERTMODEL: Iniciando createMissingAlert para dispositivo: $deviceId");
        
        // Primero obtener datos del paciente para la BD pública
        $patientData = $this->getPatientDataForPublicAlert($deviceId);
        
        // Insertar en BD privada
        $sqlPrivate = "INSERT INTO Log_Alertas (id_dispositivo, tipo_alerta, descripcion, 
                                               ubicacion_lat, ubicacion_lon, estado, fecha_creacion) 
                       VALUES (?, 'extravio', 'Paciente extraviado', ?, ?, 'PENDIENTE', NOW())";
        
        $this->executeQuery($this->db_private, $sqlPrivate, [$deviceId, $latitude, $longitude]);
        $alertId = $this->db_private->lastInsertId();
        
        error_log("✅ Alerta privada creada ID: $alertId");
        
        // Insertar en BD pública con la estructura correcta
        try {
            $sqlPublic = "INSERT INTO alertas_c5 (id_alerta_privada, id_dispositivo, tipo_emergencia,
                                                 nombre_paciente, edad, enfermedades_cronicas, 
                                                 contacto_emergencia, direccion_residencia, direccion_recoleccion,
                                                 ubicacion_lat, ubicacion_lon, estado, fecha_reporte) 
                          VALUES (?, ?, 'Persona Extraviada', ?, ?, ?, ?, ?, ?, ?, ?, 'PENDIENTE', NOW())";
            
            $this->executeQuery($this->db_public, $sqlPublic, [
                $alertId, $deviceId,
                $patientData['nombre_paciente'],
                $patientData['edad'],
                $patientData['enfermedades_cronicas'],
                $patientData['contacto_emergencia'],
                $patientData['direccion_residencia'],
                $patientData['direccion_recoleccion'],
                $latitude,
                $longitude
            ]);
            
            $publicAlertId = $this->db_public->lastInsertId();
            error_log("✅✅ Alerta pública creada exitosamente. ID: $publicAlertId");
            
        } catch (Exception $e) {
            error_log("❌❌ ERROR CRÍTICO - No se pudo insertar en BD pública: " . $e->getMessage());
            error_log("❌❌ Archivo: " . $e->getFile() . " Línea: " . $e->getLine());
        }
        
        return $alertId;
    }
    
    private function getPatientDataForPublicAlert($deviceId) {
        $sql = "SELECT nombre_paciente, edad, enfermedades_cronicas, 
                       contacto_emergencia, direccion_residencia, direccion_recoleccion
                FROM Pacientes 
                WHERE codigo = ?";
        
        $result = $this->getSingleResult($this->db_private, $sql, [$deviceId]);
        
        if (!$result) {
            error_log("❌ No se encontraron datos del paciente para: $deviceId");
            return [
                'nombre_paciente' => 'Desconocido',
                'edad' => 0,
                'enfermedades_cronicas' => 'No especificado',
                'contacto_emergencia' => 'No especificado',
                'direccion_residencia' => 'No especificado',
                'direccion_recoleccion' => 'No especificado'
            ];
        }
        
        error_log("✅ Datos del paciente encontrados: " . $result['nombre_paciente']);
        
        // Asegurarse de que ningún campo sea null
        return [
            'nombre_paciente' => $result['nombre_paciente'] ?? 'Desconocido',
            'edad' => $result['edad'] ?? 0,
            'enfermedades_cronicas' => $result['enfermedades_cronicas'] ?? 'No especificado',
            'contacto_emergencia' => $result['contacto_emergencia'] ?? 'No especificado',
            'direccion_residencia' => $result['direccion_residencia'] ?? 'No especificado',
            'direccion_recoleccion' => $result['direccion_recoleccion'] ?? 'No especificado'
        ];
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
                FROM alertas_c5 
                WHERE id_alerta_privada = ?";
        
        return $this->getSingleResult($this->db_public, $sql, [$alertId]);
    }
    
    public function updateAlertStatus($alertId, $status, $notes = '') {
        $sqlPrivate = "UPDATE Log_Alertas SET estado = ? WHERE id = ?";
        $this->executeQuery($this->db_private, $sqlPrivate, [$status, $alertId]);
        
        $sqlPublic = "UPDATE alertas_c5 SET estado = ?, notas_actualizacion = ?, 
                        fecha_actualizacion = NOW() 
                      WHERE id_alerta_privada = ?";
        $this->executeQuery($this->db_public, $sqlPublic, [$status, $notes, $alertId]);
    }
}
?>