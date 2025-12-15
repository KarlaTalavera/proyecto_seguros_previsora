<?php
require_once dirname(__DIR__) . '/config/conexion.php';

class ModeloNotificacion {
    private $db;

    public function __construct() {
        try {
            $base = new Base_Datos();
            $this->db = $base->Conexion_Base_Datos();
        } catch (Exception $e) {
            error_log("Error al inicializar DB en ModeloNotificacion: " . $e->getMessage());
            $this->db = null;
        }
    }

    public function crearNotificacion($cedulaDestino, $titulo, $mensaje, $tipo = 'info', $enlace = null) {
        if (!$this->db) {
            error_log("Error: Base de datos no disponible para crear notificación");
            return false;
        }
        
        try {
            $sql = "INSERT INTO notificacion (cedula_destino, titulo, mensaje, tipo, enlace, leida, fecha_creacion)
                    VALUES (?, ?, ?, ?, ?, 0, NOW())";
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([$cedulaDestino, $titulo, $mensaje, $tipo, $enlace]);
            
            if ($resultado) {
                error_log("Notificación creada exitosamente para: $cedulaDestino - $titulo");
            } else {
                error_log("Error al ejecutar statement para notificación: $cedulaDestino");
            }
            
            return $resultado;
        } catch (Exception $e) {
            error_log("Error al crear notificación para $cedulaDestino: " . $e->getMessage());
            error_log("SQL: $sql");
            error_log("Parámetros: " . json_encode([$cedulaDestino, $titulo, $mensaje, $tipo, $enlace]));
            return false;
        }
    }

    public function obtenerNotificacionesUsuario($cedula, $limit = 10, $soloNoLeidas = false, $offset = 0, $tipo = 'todas') {
        if (!$this->db) return ['notificaciones' => [], 'total' => 0, 'total_no_leidas' => 0];
        try {
            $where = "WHERE cedula_destino = ?";
            $params = [$cedula];
            
            if ($soloNoLeidas) {
                $where .= " AND leida = 0";
            }
            
            if ($tipo !== 'todas' && in_array($tipo, ['info', 'success', 'warning', 'danger', 'primary'])) {
                $where .= " AND tipo = ?";
                $params[] = $tipo;
            }
            
            // Obtener notificaciones
            $sql = "SELECT id_notificacion, titulo, mensaje, tipo, enlace, leida, fecha_creacion
                    FROM notificacion
                    $where
                    ORDER BY fecha_creacion DESC
                    LIMIT ? OFFSET ?";
            
            $params[] = (int)$limit;
            $params[] = (int)$offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener totales
            $sqlTotal = "SELECT COUNT(*) as total, 
                                SUM(CASE WHEN leida = 0 THEN 1 ELSE 0 END) as no_leidas
                         FROM notificacion 
                         $where";
            
            $stmtTotal = $this->db->prepare($sqlTotal);
            $stmtTotal->execute(array_slice($params, 0, count($params)-2));
            $totales = $stmtTotal->fetch(PDO::FETCH_ASSOC);
            
            return [
                'notificaciones' => $notificaciones,
                'total' => $totales['total'] ?? 0,
                'total_no_leidas' => $totales['no_leidas'] ?? 0
            ];
        } catch (Exception $e) {
            error_log("Error al obtener notificaciones: " . $e->getMessage());
            return ['notificaciones' => [], 'total' => 0, 'total_no_leidas' => 0];
        }
    }

    public function contarNoLeidas($cedula) {
        if (!$this->db) return 0;
        try {
            $sql = "SELECT COUNT(*) as total FROM notificacion WHERE cedula_destino = ? AND leida = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$cedula]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return isset($result['total']) ? (int)$result['total'] : 0;
        } catch (Exception $e) {
            error_log("Error al contar notificaciones: " . $e->getMessage());
            return 0;
        }
    }

    public function marcarComoLeida($idNotificacion, $cedulaUsuario) {
        if (!$this->db) return false;
        try {
            $sql = "UPDATE notificacion SET leida = 1 WHERE id_notificacion = ? AND cedula_destino = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$idNotificacion, $cedulaUsuario]);
        } catch (Exception $e) {
            error_log("Error al marcar notificación como leída: " . $e->getMessage());
            return false;
        }
    }

    public function marcarTodasComoLeidas($cedulaUsuario) {
        if (!$this->db) return false;
        try {
            $sql = "UPDATE notificacion SET leida = 1 WHERE cedula_destino = ? AND leida = 0";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$cedulaUsuario]);
        } catch (Exception $e) {
            error_log("Error al marcar todas como leídas: " . $e->getMessage());
            return false;
        }
    }

    public function limpiarNotificacionesAntiguas() {
        if (!$this->db) return false;
        try {
            $sql = "DELETE FROM notificacion WHERE fecha_creacion < DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al limpiar notificaciones antiguas: " . $e->getMessage());
            return false;
        }
    }

    public function notificarVarios($destinatarios, $titulo, $mensaje, $tipo = 'info', $enlace = null) {
        $success = true;
        foreach ($destinatarios as $cedula) {
            if (!$this->crearNotificacion($cedula, $titulo, $mensaje, $tipo, $enlace)) {
                $success = false;
            }
        }
        return $success;
    }

    // Métodos específicos para diferentes eventos del sistema
    public function notificarSolicitudPoliza($idSolicitud, $clienteCedula, $clienteNombre, $tipoPoliza, $agenteCedula = null) {
        $titulo = "Nueva solicitud de póliza";
        $mensaje = "$clienteNombre ha solicitado una póliza de $tipoPoliza";
        $enlace = "index.php?vista=detalleSolicitud&id=$idSolicitud";
        
        if ($agenteCedula) {
            return $this->crearNotificacion($agenteCedula, $titulo, $mensaje, 'info', $enlace);
        } else {
            // Notificar a todos los agentes con permiso de gestionar solicitudes
            return $this->notificarAgentesConPermiso('solicitud_gestionar', $titulo, $mensaje, 'info', $enlace);
        }
    }

    public function notificarSolicitudSiniestro($idSolicitud, $clienteCedula, $clienteNombre, $tipoIncidente, $agenteCedula = null) {
        $titulo = "Nuevo reporte de siniestro";
        $mensaje = "$clienteNombre ha reportado un siniestro: $tipoIncidente";
        $enlace = "index.php?vista=detalleSolicitudSiniestro&id=$idSolicitud";
        
        if ($agenteCedula) {
            return $this->crearNotificacion($agenteCedula, $titulo, $mensaje, 'warning', $enlace);
        } else {
            return $this->notificarAgentesConPermiso('solicitud_gestionar', $titulo, $mensaje, 'warning', $enlace);
        }
    }

    public function notificarCambioEstadoPoliza($polizaNumero, $clienteCedula, $estadoAnterior, $estadoNuevo, $agenteCedula = null) {
        $titulo = "Cambio de estado en póliza";
        $mensaje = "La póliza $polizaNumero ha cambiado de estado: $estadoAnterior → $estadoNuevo";
        $enlace = "index.php?vista=detallePoliza&numero=$polizaNumero";
        
        // Notificar al cliente
        $this->crearNotificacion($clienteCedula, $titulo, $mensaje, 'info', $enlace);
        
        // Notificar al agente si está asignado
        if ($agenteCedula) {
            $this->crearNotificacion($agenteCedula, $titulo, $mensaje, 'info', $enlace);
        }
        
        return true;
    }

    public function notificarPagoCuota($idReporte, $clienteCedula, $clienteNombre, $monto, $polizaNumero, $agenteCedula) {
        $titulo = "Nuevo pago reportado";
        $mensaje = "$clienteNombre ha reportado un pago de $" . number_format($monto, 2) . " para la póliza $polizaNumero";
        $enlace = "index.php?vista=detalleReportePago&id=$idReporte";
        
        return $this->crearNotificacion($agenteCedula, $titulo, $mensaje, 'success', $enlace);
    }

    public function notificarResultadoPago($idReporte, $clienteCedula, $estado, $polizaNumero, $motivo = '') {
        $titulo = $estado === 'APROBADO' ? "Pago aprobado" : "Pago rechazado";
        $mensaje = $estado === 'APROBADO' 
            ? "Tu pago para la póliza $polizaNumero ha sido aprobado"
            : "Tu pago para la póliza $polizaNumero ha sido rechazado. Motivo: $motivo";
        $enlace = "index.php?vista=pagosCuotasCliente";
        
        return $this->crearNotificacion($clienteCedula, $titulo, $mensaje, 
            $estado === 'APROBADO' ? 'success' : 'danger', $enlace);
    }

    public function notificarAgentesConPermiso($nombrePermiso, $titulo, $mensaje, $tipo = 'info', $enlace = null) {
        if (!$this->db) return false;
        
        try {
            // Obtener agentes con el permiso específico
            $sql = "SELECT ap.cedula_agente 
                    FROM agente_permiso ap 
                    JOIN permiso p ON ap.id_permiso = p.id_permiso 
                    WHERE p.nombre_permiso = ? AND ap.tiene_permiso = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nombrePermiso]);
            $agentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $success = true;
            foreach ($agentes as $cedulaAgente) {
                if (!$this->crearNotificacion($cedulaAgente, $titulo, $mensaje, $tipo, $enlace)) {
                    $success = false;
                }
            }
            return $success;
        } catch (Exception $e) {
            error_log("Error al notificar agentes con permiso: " . $e->getMessage());
            return false;
        }
    }

    public function notificarVencimientoPoliza($polizaNumero, $clienteCedula, $diasRestantes) {
        $titulo = "Vencimiento de póliza próximo";
        $mensaje = "Tu póliza $polizaNumero vencerá en $diasRestantes días. Renuévala a tiempo.";
        $enlace = "index.php?vista=polizasCliente";
        
        return $this->crearNotificacion($clienteCedula, $titulo, $mensaje, 'warning', $enlace);
    }

    public function notificarSiniestroCerrado($idSiniestro, $clienteCedula, $polizaNumero, $montoAprobado) {
        $titulo = "Siniestro cerrado";
        $mensaje = "El siniestro de la póliza $polizaNumero ha sido cerrado. Monto aprobado: $" . number_format($montoAprobado, 2);
        $enlace = "index.php?vista=detalleSiniestro&id=$idSiniestro";
        
        return $this->crearNotificacion($clienteCedula, $titulo, $mensaje, 'success', $enlace);
    }

    public function notificarNuevoMensaje($remitenteCedula, $remitenteNombre, $destinatarioCedula, $asunto) {
        $titulo = "Nuevo mensaje de $remitenteNombre";
        $mensaje = "Tienes un nuevo mensaje: $asunto";
        $enlace = "index.php?vista=mensajes";
        
        return $this->crearNotificacion($destinatarioCedula, $titulo, $mensaje, 'primary', $enlace);
    }
}