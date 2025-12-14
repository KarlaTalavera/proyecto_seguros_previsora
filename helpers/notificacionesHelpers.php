<?php
require_once dirname(__DIR__) . '/modelo/modeloNotificacion.php';

class NotificacionesHelper {
    private static $modeloNotificacion = null;

    private static function getModelo() {
        if (self::$modeloNotificacion === null) {
            self::$modeloNotificacion = new ModeloNotificacion();
        }
        return self::$modeloNotificacion;
    }

    /**
     * Notificar creación de solicitud de póliza
     */
    public static function notificarSolicitudPoliza($idSolicitud, $clienteCedula, $clienteNombre, $tipoPoliza, $agenteCedula = null) {
        $modelo = self::getModelo();
        return $modelo->notificarSolicitudPoliza($idSolicitud, $clienteCedula, $clienteNombre, $tipoPoliza, $agenteCedula);
    }

    /**
     * Notificar creación de solicitud de siniestro
     */
    public static function notificarSolicitudSiniestro($idSolicitud, $clienteCedula, $clienteNombre, $tipoIncidente, $agenteCedula = null) {
        $modelo = self::getModelo();
        return $modelo->notificarSolicitudSiniestro($idSolicitud, $clienteCedula, $clienteNombre, $tipoIncidente, $agenteCedula);
    }

    /**
     * Notificar cambio de estado de póliza
     */
    public static function notificarCambioEstadoPoliza($polizaNumero, $clienteCedula, $estadoAnterior, $estadoNuevo, $agenteCedula = null) {
        $modelo = self::getModelo();
        return $modelo->notificarCambioEstadoPoliza($polizaNumero, $clienteCedula, $estadoAnterior, $estadoNuevo, $agenteCedula);
    }

    /**
     * Notificar reporte de pago de cuota
     */
    public static function notificarPagoCuota($idReporte, $clienteCedula, $clienteNombre, $monto, $polizaNumero, $agenteCedula) {
        $modelo = self::getModelo();
        return $modelo->notificarPagoCuota($idReporte, $clienteCedula, $clienteNombre, $monto, $polizaNumero, $agenteCedula);
    }

    /**
     * Notificar resultado de revisión de pago
     */
    public static function notificarResultadoPago($idReporte, $clienteCedula, $estado, $polizaNumero, $motivo = '') {
        $modelo = self::getModelo();
        return $modelo->notificarResultadoPago($idReporte, $clienteCedula, $estado, $polizaNumero, $motivo);
    }

    /**
     * Notificar vencimiento próximo de póliza
     */
    public static function notificarVencimientoPoliza($polizaNumero, $clienteCedula, $diasRestantes) {
        $modelo = self::getModelo();
        return $modelo->notificarVencimientoPoliza($polizaNumero, $clienteCedula, $diasRestantes);
    }

    /**
     * Notificar cierre de siniestro
     */
    public static function notificarSiniestroCerrado($idSiniestro, $clienteCedula, $polizaNumero, $montoAprobado) {
        $modelo = self::getModelo();
        return $modelo->notificarSiniestroCerrado($idSiniestro, $clienteCedula, $polizaNumero, $montoAprobado);
    }

    /**
     * Notificar nuevo mensaje
     */
    public static function notificarNuevoMensaje($remitenteCedula, $remitenteNombre, $destinatarioCedula, $asunto) {
        $modelo = self::getModelo();
        return $modelo->notificarNuevoMensaje($remitenteCedula, $remitenteNombre, $destinatarioCedula, $asunto);
    }

    /**
     * Notificar a agentes con permiso específico
     */
    public static function notificarAgentesConPermiso($nombrePermiso, $titulo, $mensaje, $tipo = 'info', $enlace = null) {
        $modelo = self::getModelo();
        return $modelo->notificarAgentesConPermiso($nombrePermiso, $titulo, $mensaje, $tipo, $enlace);
    }

    /**
     * Notificar a varios destinatarios
     */
    public static function notificarVarios($destinatarios, $titulo, $mensaje, $tipo = 'info', $enlace = null) {
        $modelo = self::getModelo();
        return $modelo->notificarVarios($destinatarios, $titulo, $mensaje, $tipo, $enlace);
    }

    /**
     * Crear notificación directa
     */
    public static function crearNotificacion($cedulaDestino, $titulo, $mensaje, $tipo = 'info', $enlace = null) {
        $modelo = self::getModelo();
        return $modelo->crearNotificacion($cedulaDestino, $titulo, $mensaje, $tipo, $enlace);
    }

    /**
     * Notificar asignación de agente a solicitud
     */
    public static function notificarAsignacionAgente($agenteCedula, $agenteNombre, $tipoSolicitud, $clienteNombre, $idSolicitud) {
        $modelo = self::getModelo();
        $titulo = "Nueva asignación de $tipoSolicitud";
        $mensaje = "Se te ha asignado una nueva solicitud de $tipoSolicitud del cliente $clienteNombre";
        $enlace = "index.php?vista=detalleSolicitud&id=$idSolicitud";
        
        return $modelo->crearNotificacion($agenteCedula, $titulo, $mensaje, 'primary', $enlace);
    }

    /**
     * Notificar creación de nueva póliza
     */
    public static function notificarNuevaPoliza($clienteCedula, $polizaNumero, $producto, $agenteCedula = null) {
        $modelo = self::getModelo();
        $titulo = "Nueva póliza creada";
        $mensaje = "Se ha creado una nueva póliza $polizaNumero ($producto)";
        $enlace = "index.php?vista=detallePoliza&numero=$polizaNumero";
        
        // Notificar al cliente
        $modelo->crearNotificacion($clienteCedula, $titulo, $mensaje, 'success', $enlace);
        
        // Notificar al agente si existe
        if ($agenteCedula) {
            $modelo->crearNotificacion($agenteCedula, $titulo, $mensaje, 'info', $enlace);
        }
        
        return true;
    }

    /**
     * Notificar pago pendiente de cuota
     */
    public static function notificarRecordatorioPago($clienteCedula, $polizaNumero, $numeroCuota, $fechaVencimiento, $monto) {
        $modelo = self::getModelo();
        $titulo = "Recordatorio de pago";
        $mensaje = "Tienes un pago pendiente para la póliza $polizaNumero - Cuota #$numeroCuota (Vence: $fechaVencimiento) - Monto: $" . number_format($monto, 2);
        $enlace = "index.php?vista=pagosCuotasCliente";
        
        return $modelo->crearNotificacion($clienteCedula, $titulo, $mensaje, 'warning', $enlace);
    }
}