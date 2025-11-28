<?php
// sync_c5_api.php - Lógica principal de sincronización
require_once 'config.php';

class SincronizadorC5 {
    private $db_privada;
    private $db_publica;
    
    public function __construct() {
        try {
            // Conexión a base PRIVADA
            $this->db_privada = new PDO(
                "mysql:host=" . Config::DB_PRIVATE['host'] . ";dbname=" . Config::DB_PRIVATE['dbname'],
                Config::DB_PRIVATE['username'],
                Config::DB_PRIVATE['password']
            );
            
            // Conexión a base PÚBLICA
            $this->db_publica = new PDO(
                "mysql:host=" . Config::DB_PUBLIC['host'] . ";dbname=" . Config::DB_PUBLIC['dbname'],
                Config::DB_PUBLIC['username'], 
                Config::DB_PUBLIC['password']
            );
            
            $this->db_privada->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db_publica->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch (PDOException $e) {
            error_log("Error conexión BD: " . $e->getMessage());
            throw $e;
        }
    }
    
    // ENVIAR ALERTA DE PRIVADA → PÚBLICA
    public function enviarAlertaC5($idAlertaPrivada) {
        try {
            usleep(500000); // 0.5 segundos
            // Obtener datos de log_alertas + paciente
            $stmt = $this->db_privada->prepare("
                SELECT la.*, p.nombre_paciente, p.edad, p.enfermedades_cronicas, 
                       p.contacto_emergencia, p.direccion_residencia
                FROM log_alertas la
                JOIN pacientes p ON la.id_dispositivo = p.codigo
                WHERE la.id = ? AND la.sincronizado_c5 = 0
            ");
            $stmt->execute([$idAlertaPrivada]);
            $alertaPrivada = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$alertaPrivada) {
                error_log("Alerta no encontrada o ya sincronizada: " . $idAlertaPrivada);
                return false;
            }
            
            // Mapear tipo de alerta
            $tipoEmergencia = ($alertaPrivada['tipo_alerta'] == 'medica') ? 'Emergencia Médica' : 'Persona Extraviada';
            
            // Insertar en alertas_c5
            $stmt = $this->db_publica->prepare("
                INSERT INTO alertas_c5 (
                    id_alerta_privada, id_dispositivo, tipo_emergencia,
                    nombre_paciente, edad, enfermedades_cronicas,
                    contacto_emergencia, direccion_residencia, 
                    ubicacion_lat, ubicacion_lon, estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDIENTE')
            ");
            
            $stmt->execute([
                $alertaPrivada['id'],
                $alertaPrivada['id_dispositivo'],
                $tipoEmergencia,
                $alertaPrivada['nombre_paciente'],
                $alertaPrivada['edad'],
                $alertaPrivada['enfermedades_cronicas'],
                $alertaPrivada['contacto_emergencia'],
                $alertaPrivada['direccion_residencia'],
                $alertaPrivada['ubicacion_lat'],
                $alertaPrivada['ubicacion_lon']
            ]);
            
            $idAlertaPublica = $this->db_publica->lastInsertId();
            
            // Marcar como sincronizado
            $stmt = $this->db_privada->prepare("
                UPDATE log_alertas 
                SET sincronizado_c5 = 1, id_alerta_publica = ?
                WHERE id = ?
            ");
            $stmt->execute([$idAlertaPublica, $idAlertaPrivada]);
            
            // Log
            $this->logSincronizacion($idAlertaPrivada, 'ENVIADO_C5', $alertaPrivada);
            
            return $idAlertaPublica;
            
        } catch (Exception $e) {
            error_log("Error enviando alerta a C5: " . $e->getMessage());
            return false;
        }
    }
    
    // ACTUALIZAR ESTADO DESDE C5 → PRIVADA
    public function actualizarEstadoDesdeC5($idAlertaPublica) {
        try {
            // Obtener estado actualizado de C5
            $stmt = $this->db_publica->prepare("
                SELECT id_alerta_privada, estado, tiempo_respuesta, unidades_asignadas,
                       fecha_actualizacion, notas_actualizacion
                FROM alertas_c5 WHERE id = ?
            ");
            $stmt->execute([$idAlertaPublica]);
            $alertaC5 = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$alertaC5) {
                error_log("Alerta C5 no encontrada: " . $idAlertaPublica);
                return false;
            }
            
            // Actualizar en base privada
            $stmt = $this->db_privada->prepare("
                UPDATE log_alertas 
                SET estado = ?, 
                    notas_c5 = ?,
                    fecha_actualizacion = NOW()
                WHERE id = ? AND estado != ?
            ");
            
            $notas = "C5: " . ($alertaC5['notas_actualizacion'] ?: 'Sin notas') . 
                     " | Unidades: " . ($alertaC5['unidades_asignadas'] ?: 'Ninguna');
            
            $result = $stmt->execute([
                $alertaC5['estado'],
                $notas,
                $alertaC5['id_alerta_privada'],
                $alertaC5['estado']
            ]);
            
            if ($result && $stmt->rowCount() > 0) {
                $this->logSincronizacion($alertaC5['id_alerta_privada'], 'ACTUALIZADO_DESDE_C5', $alertaC5);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error actualizando desde C5: " . $e->getMessage());
            return false;
        }
    }
    
    // SINCRONIZAR PENDIENTES (para cron job)
    public function sincronizarPendientes() {
        // Alertas pendientes de enviar
        $stmt = $this->db_privada->prepare("
            SELECT id FROM log_alertas 
            WHERE sincronizado_c5 = 0 AND estado = 'PENDIENTE'
        ");
        $stmt->execute();
        $pendientes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($pendientes as $idAlerta) {
            $this->enviarAlertaC5($idAlerta);
        }
        
        // Actualizar estados desde C5
        $stmt = $this->db_publica->prepare("
            SELECT id FROM alertas_c5 
            WHERE fecha_actualizacion > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute();
        $actualizadas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($actualizadas as $idAlertaC5) {
            $this->actualizarEstadoDesdeC5($idAlertaC5);
        }
    }
    
    private function logSincronizacion($idAlerta, $accion, $datos) {
        $stmt = $this->db_privada->prepare("
            INSERT INTO logs_sincronizacion_c5 
            (id_alerta_privada, accion, datos_enviados) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$idAlerta, $accion, json_encode($datos)]);
    }
    // LIMPIAR ALERTAS RESUELTAS/CANCELADAS Y ACTUALIZAR ESTADOS
public function limpiarYActualizarAlertas() {
    try {
        echo "🔄 Limpiando y actualizando alertas...\n";
        
        // 1. Obtener alertas actualizadas de C5
        $stmt = $this->db_publica->prepare("
            SELECT id, id_alerta_privada, estado, tiempo_respuesta, unidades_asignadas,
                   fecha_actualizacion, notas_actualizacion
            FROM alertas_c5 
            WHERE fecha_actualizacion > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            AND estado IN ('RESUELTA', 'CANCELADA')
        ");
        $stmt->execute();
        $alertasActualizadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📊 Alertas resueltas/canceladas en C5: " . count($alertasActualizadas) . "\n";
        
        $actualizadas = 0;
        $limpiadas = 0;
        
        foreach ($alertasActualizadas as $alertaC5) {
            // 2. Actualizar estado en base privada
            $stmt = $this->db_privada->prepare("
                UPDATE log_alertas 
                SET estado = ?, 
                    notas_c5 = ?,
                    fecha_actualizacion = NOW()
                WHERE id = ? AND estado != ?
            ");
            
            $notas = "C5: " . ($alertaC5['notas_actualizacion'] ?: 'Sin notas') . 
                     " | Tiempo: " . ($alertaC5['tiempo_respuesta'] ?: 'N/A') . "min" .
                     " | Unidades: " . ($alertaC5['unidades_asignadas'] ?: 'Ninguna');
            
            $result = $stmt->execute([
                $alertaC5['estado'],
                $notas,
                $alertaC5['id_alerta_privada'],
                $alertaC5['estado']
            ]);
            
            if ($result && $stmt->rowCount() > 0) {
                $actualizadas++;
                $this->logSincronizacion($alertaC5['id_alerta_privada'], 'ACTUALIZADO_DESDE_C5', $alertaC5);
                
                // 3. Opcional: Limpiar alerta de C5 después de actualizar
                if ($this->limpiarAlertaC5($alertaC5['id'])) {
                    $limpiadas++;
                }
            }
        }
        
        echo "✅ Alertas actualizadas en privada: $actualizadas\n";
        echo "🗑️  Alertas limpiadas de C5: $limpiadas\n";
        
        return [
            'actualizadas' => $actualizadas,
            'limpiadas' => $limpiadas
        ];
        
    } catch (Exception $e) {
        error_log("Error limpiando/actualizando alertas: " . $e->getMessage());
        echo "❌ Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// LIMPIAR ALERTA ESPECÍFICA DE C5 (mantener histórico si quieres)
private function limpiarAlertaC5($idAlertaC5) {
    try {
        // Opción A: Eliminar completamente
        $stmt = $this->db_publica->prepare("DELETE FROM alertas_c5 WHERE id = ?");
        $result = $stmt->execute([$idAlertaC5]);
        
        // Opción B: O marcar como archivada (recomendado para histórico)
        // $stmt = $this->db_publica->prepare("UPDATE alertas_c5 SET archivada = 1 WHERE id = ?");
        // $result = $stmt->execute([$idAlertaC5]);
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Error limpiando alerta C5 $idAlertaC5: " . $e->getMessage());
        return false;
    }
}
}

?>