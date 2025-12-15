<?php
// CORREGIR las rutas en notificaciones.php
require_once dirname(__DIR__) . '/modelo/modeloNotificacion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['datos_usuario'])) {
    header('Location: index.php?vista=login');
    exit;
}

$usuario = $_SESSION['datos_usuario'];

// CORREGIR: Obtener cédula correctamente para objeto o array
if (is_object($usuario) && method_exists($usuario, 'getCedula')) {
    $cedula = $usuario->getCedula();
} elseif (is_array($usuario) && isset($usuario['cedula'])) {
    $cedula = $usuario['cedula'];
} else {
    // Si no se puede obtener, redirigir
    header('Location: index.php?vista=login');
    exit;
}

$modelo = new ModeloNotificacion();

// Obtener parámetros de filtro
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 20;
$offset = ($pagina - 1) * $limite;
$filtroTipo = $_GET['tipo'] ?? 'todas';
$soloNoLeidas = isset($_GET['no_leidas']) ? (bool)$_GET['no_leidas'] : false;

// Obtener notificaciones
$resultado = $modelo->obtenerNotificacionesUsuario($cedula, $limite, $soloNoLeidas, $offset, $filtroTipo);
$notificaciones = $resultado['notificaciones'];
$total = $resultado['total'];
$totalNoLeidas = $resultado['total_no_leidas'];

// Marcar todas como leídas si se solicita
if (isset($_GET['marcar_todas']) && $_GET['marcar_todas'] == 1) {
    $modelo->marcarTodasComoLeidas($cedula);
    header('Location: index.php?vista=notificaciones');
    exit;
}

// Función para formatear fecha (CORREGIDA)
function formatearFecha($fecha) {
    if (empty($fecha)) return 'Fecha desconocida';
    
    try {
        $fechaObj = new DateTime($fecha);
        $hoy = new DateTime();
        $diferencia = $hoy->diff($fechaObj);
        
        if ($diferencia->d == 0) {
            if ($diferencia->h == 0) {
                if ($diferencia->i == 0) {
                    return 'Hace unos segundos';
                }
                return 'Hace ' . $diferencia->i . ' minuto' . ($diferencia->i > 1 ? 's' : '');
            }
            return 'Hace ' . $diferencia->h . ' hora' . ($diferencia->h > 1 ? 's' : '');
        } elseif ($diferencia->d == 1) {
            return 'Ayer a las ' . $fechaObj->format('H:i');
        } elseif ($diferencia->d < 7) {
            return 'Hace ' . $diferencia->d . ' día' . ($diferencia->d > 1 ? 's' : '');
        } else {
            return $fechaObj->format('d/m/Y H:i');
        }
    } catch (Exception $e) {
        return $fecha;
    }
}

// Función para obtener icono según tipo
function obtenerIconoTipo($tipo) {
    switch ($tipo) {
        case 'success': return 'fas fa-check-circle';
        case 'warning': return 'fas fa-exclamation-triangle';
        case 'danger': return 'fas fa-times-circle';
        case 'primary': return 'fas fa-bell';
        default: return 'fas fa-info-circle';
    }
}

// Función para obtener color según tipo
function obtenerColorTipo($tipo) {
    switch ($tipo) {
        case 'success': return 'success';
        case 'warning': return 'warning';
        case 'danger': return 'danger';
        case 'primary': return 'primary';
        default: return 'info';
    }
}

// CORREGIR: Incluir correctamente la parte superior
require_once dirname(__DIR__) . '/vista/parte_superior.php';
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Mis Notificaciones</h1>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bell mr-2"></i>Notificaciones
                        <?php if ($totalNoLeidas > 0): ?>
                            <span class="badge badge-danger ml-2"><?php echo $totalNoLeidas; ?> nuevas</span>
                        <?php endif; ?>
                    </h6>
                    <div class="btn-group">
                        <a href="index.php?vista=notificaciones&marcar_todas=1" class="btn btn-sm btn-success">
                            <i class="fas fa-check-double mr-1"></i> Marcar todas como leídas
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <form method="get" class="form-inline">
                                <input type="hidden" name="vista" value="notificaciones">
                                
                                <div class="form-group mr-3">
                                    <label for="tipo" class="mr-2">Tipo:</label>
                                    <select name="tipo" id="tipo" class="form-control form-control-sm" onchange="this.form.submit()">
                                        <option value="todas" <?php echo $filtroTipo == 'todas' ? 'selected' : ''; ?>>Todas</option>
                                        <option value="info" <?php echo $filtroTipo == 'info' ? 'selected' : ''; ?>>Información</option>
                                        <option value="success" <?php echo $filtroTipo == 'success' ? 'selected' : ''; ?>>Éxito</option>
                                        <option value="warning" <?php echo $filtroTipo == 'warning' ? 'selected' : ''; ?>>Advertencia</option>
                                        <option value="danger" <?php echo $filtroTipo == 'danger' ? 'selected' : ''; ?>>Error</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="no_leidas" 
                                               name="no_leidas" value="1" 
                                               <?php echo $soloNoLeidas ? 'checked' : ''; ?>
                                               onchange="this.form.submit()">
                                        <label class="custom-control-label" for="no_leidas">
                                            Solo no leídas
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <div class="col-md-4 text-right">
                            <p class="mb-0 text-muted">
                                Mostrando <?php echo count($notificaciones); ?> de <?php echo $total; ?> notificaciones
                            </p>
                        </div>
                    </div>
                    
                    <!-- Lista de notificaciones -->
                    <div class="notificaciones-list">
                        <?php if (empty($notificaciones)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-bell-slash fa-3x text-gray-300 mb-3"></i>
                                <h5 class="text-gray-500">No hay notificaciones</h5>
                                <p class="text-muted">No tienes notificaciones <?php echo $soloNoLeidas ? 'no leídas' : ''; ?> en este momento.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($notificaciones as $notif): ?>
                                <div class="notificacion-item card mb-3 border-left-<?php echo obtenerColorTipo($notif['tipo']); ?> 
                                    <?php echo $notif['leida'] == 0 ? 'border-left-3' : ''; ?>">
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <i class="<?php echo obtenerIconoTipo($notif['tipo']); ?> 
                                                        text-<?php echo obtenerColorTipo($notif['tipo']); ?> mr-2"></i>
                                                    <h6 class="mb-0 font-weight-bold <?php echo $notif['leida'] == 0 ? 'text-dark' : 'text-gray-700'; ?>">
                                                        <?php echo htmlspecialchars($notif['titulo']); ?>
                                                    </h6>
                                                    <?php if ($notif['leida'] == 0): ?>
                                                        <span class="badge badge-primary badge-pill ml-2">Nueva</span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="mb-1 text-gray-800">
                                                    <?php echo htmlspecialchars($notif['mensaje']); ?>
                                                </p>
                                                <small class="text-muted">
                                                    <i class="far fa-clock mr-1"></i>
                                                    <?php echo formatearFecha($notif['fecha_creacion']); ?>
                                                </small>
                                            </div>
                                            
                                            <div class="ml-3 d-flex flex-column">
                                                <?php if ($notif['enlace']): ?>
                                                    <a href="<?php echo htmlspecialchars($notif['enlace']); ?>" 
                                                       class="btn btn-sm btn-outline-primary mb-1">
                                                        <i class="fas fa-external-link-alt mr-1"></i> Ver
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($notif['leida'] == 0): ?>
                                                    <button class="btn btn-sm btn-outline-success btn-marcar-leida" 
                                                            data-id="<?php echo $notif['id_notificacion']; ?>">
                                                        <i class="fas fa-check mr-1"></i> Marcar como leída
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Paginación -->
                    <?php if ($total > $limite): ?>
                        <nav aria-label="Paginación de notificaciones" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagina > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="?vista=notificaciones&pagina=<?php echo $pagina - 1; ?>&tipo=<?php echo $filtroTipo; ?>&no_leidas=<?php echo $soloNoLeidas ? 1 : 0; ?>">
                                            <i class="fas fa-chevron-left"></i> Anterior
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php 
                                $paginasTotales = ceil($total / $limite);
                                $inicio = max(1, $pagina - 2);
                                $fin = min($paginasTotales, $pagina + 2);
                                
                                for ($i = $inicio; $i <= $fin; $i++):
                                ?>
                                    <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                                        <a class="page-link" 
                                           href="?vista=notificaciones&pagina=<?php echo $i; ?>&tipo=<?php echo $filtroTipo; ?>&no_leidas=<?php echo $soloNoLeidas ? 1 : 0; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagina < $paginasTotales): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="?vista=notificaciones&pagina=<?php echo $pagina + 1; ?>&tipo=<?php echo $filtroTipo; ?>&no_leidas=<?php echo $soloNoLeidas ? 1 : 0; ?>">
                                            Siguiente <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extra_scripts = <<<'EOD'
<script>
$(document).ready(function() {
    // Marcar notificación como leída
    $('.btn-marcar-leida').click(function() {
        var boton = $(this);
        var idNotificacion = boton.data('id');
        
        $.ajax({
            url: 'controlador/controladorNotificacion.php',
            method: 'POST',
            data: {
                accion: 'marcar_leida',
                id_notificacion: idNotificacion
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Actualizar interfaz
                    boton.closest('.notificacion-item').removeClass('border-left-3');
                    boton.closest('.notificacion-item').find('.badge').remove();
                    boton.remove();
                    
                    // Actualizar contador global
                    if (typeof window.sistemaNotificaciones !== 'undefined') {
                        window.sistemaNotificaciones.cargarNotificaciones();
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error marcando notificacion como leida:', xhr.status, xhr.responseText || error);
            }
        });
    });
    
    // Auto-refrescar notificaciones cada 60 segundos
    setInterval(function() {
        if (typeof window.sistemaNotificaciones !== 'undefined') {
            window.sistemaNotificaciones.cargarNotificaciones();
        }
    }, 60000);
});
</script>
EOD;
?>

<style>
.notificacion-item {
    transition: all 0.2s ease;
    border-left-width: 4px !important;
}

.notificacion-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.notificacion-item.border-left-3 {
    border-left-width: 8px !important;
}
</style>

<?php 
// CORREGIDO: Usar 'vista' en lugar de 'vistas'
require_once dirname(__DIR__) . '/vista/parte_inferior.php'; 
?>