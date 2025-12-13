<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/parte_superior.php';
require_once __DIR__ . '/pagosCuotas_estilos.php';

$usuarioActual = $_SESSION['datos_usuario'] ?? null;
$nombreUsuario = '';
$rolUsuario = '';
if (is_object($usuarioActual)) {
    if (method_exists($usuarioActual, 'getNombreCompleto')) {
        $nombreUsuario = (string)$usuarioActual->getNombreCompleto();
    } elseif (method_exists($usuarioActual, 'getNombre')) {
        $nombre = $usuarioActual->getNombre();
        $apellido = method_exists($usuarioActual, 'getApellido') ? $usuarioActual->getApellido() : '';
        $nombreUsuario = trim($nombre . ' ' . $apellido);
    }
    if (method_exists($usuarioActual, 'getRolNombre')) {
        $rolUsuario = (string)$usuarioActual->getRolNombre();
    } elseif (!empty($_SESSION['rol'])) {
        $rolUsuario = (string)$_SESSION['rol'];
    }
}
?>
<div class="container-fluid">
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
      <h1 class="h3 mb-1 text-gray-800">Gestión de pagos de cuotas</h1>
      <p class="text-muted mb-0">Valida la documentación remitida, controla los pendientes y deja trazabilidad de cada decisión.</p>
    </div>
    <div class="text-right">
      <?php if ($nombreUsuario !== ''): ?>
        <div class="badge badge-info badge-pill py-2 px-3 mb-2"><i class="fas fa-user-shield mr-2"></i><?php echo htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>
      <?php if ($rolUsuario !== ''): ?>
        <div class="text-muted small text-uppercase">Rol: <?php echo htmlspecialchars($rolUsuario, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="row pagos-cuotas-resumen">
    <div class="col-lg-4 col-md-6 mb-4">
      <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pendientes por revisar</div>
              <div class="h4 mb-0 font-weight-bold text-gray-800" id="totalPendientes">—</div>
            </div>
            <div class="col-auto"><i class="fas fa-clipboard-list fa-2x text-gray-300"></i></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
      <div class="card border-left-success shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Aprobados hoy</div>
              <div class="h4 mb-0 font-weight-bold text-gray-800" id="totalAprobadosHoy">—</div>
            </div>
            <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-md-12 mb-4">
      <div class="card border-left-danger shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rechazados hoy</div>
              <div class="h4 mb-0 font-weight-bold text-gray-800" id="totalRechazadosHoy">—</div>
            </div>
            <div class="col-auto"><i class="fas fa-times-circle fa-2x text-gray-300"></i></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm mb-4 pagos-cuotas-card">
    <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center">
      <h6 class="m-0 font-weight-bold text-primary">Reportes recibidos</h6>
      <div class="form-inline">
        <label for="filtroEstado" class="mr-2 mb-0">Estado</label>
        <select id="filtroEstado" class="custom-select custom-select-sm">
          <option value="pendiente" selected>Pendientes</option>
          <option value="aprobado">Aprobados</option>
          <option value="rechazado">Rechazados</option>
          <option value="todos">Todos</option>
        </select>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tablaReportesGestion" class="table table-striped table-bordered table-hover align-middle w-100 pagos-cuotas-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Cliente</th>
              <th>Póliza</th>
              <th>Cuota</th>
              <th>Monto</th>
              <th>Estado</th>
              <th>Referencia</th>
              <th>Comprobante</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-consistent" id="modalDetalleReporte" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-search-dollar mr-2"></i>Detalle del reporte</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <dl class="row mb-0">
              <dt class="col-sm-5">Cliente</dt>
              <dd class="col-sm-7" id="detalleCliente">—</dd>
              <dt class="col-sm-5">Contacto</dt>
              <dd class="col-sm-7" id="detalleContacto">—</dd>
              <dt class="col-sm-5">Póliza</dt>
              <dd class="col-sm-7" id="detallePoliza">—</dd>
              <dt class="col-sm-5">Producto</dt>
              <dd class="col-sm-7" id="detalleProducto">—</dd>
              <dt class="col-sm-5">Cuota</dt>
              <dd class="col-sm-7" id="detalleCuota">—</dd>
              <dt class="col-sm-5">Vencimiento</dt>
              <dd class="col-sm-7" id="detalleVencimiento">—</dd>
            </dl>
          </div>
          <div class="col-md-6">
            <dl class="row mb-0">
              <dt class="col-sm-5">Monto reportado</dt>
              <dd class="col-sm-7" id="detalleMonto">—</dd>
              <dt class="col-sm-5">Monto pendiente</dt>
              <dd class="col-sm-7" id="detallePendiente">—</dd>
              <dt class="col-sm-5">Referencia</dt>
              <dd class="col-sm-7" id="detalleReferencia">—</dd>
              <dt class="col-sm-5">Estado</dt>
              <dd class="col-sm-7" id="detalleEstado">—</dd>
              <dt class="col-sm-5">Fecha de reporte</dt>
              <dd class="col-sm-7" id="detalleFecha">—</dd>
            </dl>
          </div>
        </div>
        <div class="mt-3">
          <h6>Comentario del cliente</h6>
          <p id="detalleNota" class="small text-muted border rounded p-3">Sin comentarios</p>
        </div>
        <div class="mt-3">
          <h6>Comprobante</h6>
          <a id="detalleComprobante" href="#" target="_blank" class="btn-neo btn-neo--light"><i class="fas fa-external-link-alt mr-2"></i>Abrir comprobante</a>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-neo btn-neo--light" data-dismiss="modal">Cerrar</button>
        <div class="ml-auto d-flex">
          <button type="button" class="btn btn-outline-danger mr-2" id="btnAbrirRechazo"><i class="fas fa-times mr-2"></i>Rechazar</button>
          <button type="button" class="btn-neo btn-neo--primary" id="btnAprobarReporte"><i class="fas fa-check mr-2"></i>Aprobar</button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalRechazoReporte" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Motivo de rechazo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="formRechazoReporte">
        <div class="modal-body">
          <input type="hidden" id="rechazoIdReporte" name="id_reporte">
          <div class="form-group">
            <label for="rechazoMotivo">Describe brevemente el motivo *</label>
            <textarea id="rechazoMotivo" name="motivo" class="form-control" rows="4" minlength="10" maxlength="300" required></textarea>
            <small class="form-text text-muted">Se notificará al cliente con este comentario.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-neo btn-neo--light" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Confirmar rechazo</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
$toastrCss = 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css';
$toastrJs = 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js';
$dataTablesCore = resolveAssetPath('vendor/datatables/jquery.dataTables.min.js', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js');
$dataTablesBootstrap = resolveAssetPath('vendor/datatables/dataTables.bootstrap4.min.js', 'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js');
$dataTablesResponsive = 'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js';
$pagosGestionJs = 'js/pagosCuotasGestion.js';

$scriptBuffer = function () use ($toastrCss, $toastrJs, $dataTablesCore, $dataTablesBootstrap, $dataTablesResponsive, $pagosGestionJs) {
    ob_start();
    ?>
<link rel="stylesheet" href="<?php echo htmlspecialchars($toastrCss, ENT_QUOTES, 'UTF-8'); ?>">
<script>
  window.PagosCuotasGestionConfig = {
    endpoint: 'controlador/controladorPagoCuota.php'
  };
</script>
<script src="<?php echo htmlspecialchars($dataTablesCore, ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($dataTablesBootstrap, ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($dataTablesResponsive, ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($toastrJs, ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($pagosGestionJs, ENT_QUOTES, 'UTF-8'); ?>"></script>
    <?php
    return ob_get_clean();
};

$extra_scripts = isset($extra_scripts) ? $extra_scripts . $scriptBuffer() : $scriptBuffer();
?>
<?php require_once __DIR__ . '/parte_inferior.php'; ?>
