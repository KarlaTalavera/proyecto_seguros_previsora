<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/parte_superior.php';
require_once __DIR__ . '/pagosCuotas_estilos.php';

$usuarioActual = $_SESSION['datos_usuario'] ?? null;
$nombreCliente = '';
if (is_object($usuarioActual)) {
    if (method_exists($usuarioActual, 'getNombreCompleto')) {
        $nombreCliente = (string)$usuarioActual->getNombreCompleto();
    } elseif (method_exists($usuarioActual, 'getNombre')) {
        $nombre = $usuarioActual->getNombre();
        $apellido = method_exists($usuarioActual, 'getApellido') ? $usuarioActual->getApellido() : '';
        $nombreCliente = trim($nombre . ' ' . $apellido);
    }
}
?>
<div class="container-fluid">
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
      <h1 class="h3 mb-1 text-gray-800">Pagos de cuotas</h1>
      <p class="text-muted mb-0">Reporta tus pagos con comprobante y consulta el estado de cada validación.</p>
    </div>
    <?php if ($nombreCliente !== ''): ?>
      <span class="badge badge-primary badge-pill py-2 px-3"><i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($nombreCliente, ENT_QUOTES, 'UTF-8'); ?></span>
    <?php endif; ?>
  </div>

  <div class="card shadow-sm mb-4 pagos-cuotas-card">
    <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center">
      <h6 class="m-0 font-weight-bold text-primary">Cuotas de pólizas</h6>
      <small class="text-muted">El monto pendiente se actualiza al aprobarse los pagos reportados.</small>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tablaCuotasCliente" class="table table-striped table-bordered align-middle w-100 pagos-cuotas-table">
          <thead>
            <tr>
              <th>Póliza</th>
              <th>Producto</th>
              <th>Cuota</th>
              <th>Vencimiento</th>
              <th>Monto</th>
              <th>Pagado</th>
              <th>Pendiente</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card shadow-sm pagos-cuotas-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary">Reportes enviados</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tablaReportesCliente" class="table table-sm table-striped table-bordered w-100 pagos-cuotas-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Póliza</th>
              <th>Cuota</th>
              <th>Monto</th>
              <th>Referencia</th>
              <th>Estado</th>
              <th>Comprobante</th>
              <th>Comentarios</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-consistent" id="modalReportePago" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-money-check-alt mr-2"></i>Reportar pago de cuota</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="formReportePago" enctype="multipart/form-data" autocomplete="off">
        <div class="modal-body">
          <input type="hidden" name="id_cuota" id="reporteIdCuota">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Póliza / Producto</label>
              <input type="text" class="form-control" id="reportePoliza" readonly>
            </div>
            <div class="form-group col-md-3">
              <label>Cuota</label>
              <input type="text" class="form-control" id="reporteNumeroCuota" readonly>
            </div>
            <div class="form-group col-md-3">
              <label>Saldo por reportar</label>
              <input type="text" class="form-control" id="reporteSaldoPendiente" readonly>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="reporteMonto">Monto pagado *</label>
              <input type="number" step="0.01" min="0.01" class="form-control" id="reporteMonto" name="monto" required>
              <small class="form-text text-muted">Introduce el monto exacto de tu transferencia o depósito.</small>
            </div>
            <div class="form-group col-md-6">
              <label for="reporteReferencia">Referencia / transacción *</label>
              <input type="text" class="form-control" id="reporteReferencia" name="referencia" required maxlength="100">
            </div>
          </div>
          <div class="form-group">
            <label for="reporteNota">Nota adicional</label>
            <textarea class="form-control" id="reporteNota" name="nota" rows="3" placeholder="Especifica cualquier comentario relevante"></textarea>
          </div>
          <div class="form-group">
            <label for="reporteComprobante">Comprobante de pago *</label>
            <div class="custom-file">
              <input type="file" class="custom-file-input" id="reporteComprobante" name="comprobante" accept="image/*,.pdf" required data-browse="Examinar">
              <label class="custom-file-label" for="reporteComprobante">Selecciona un comprobante...</label>
            </div>
            <small class="form-text text-muted">Formatos permitidos: JPG, PNG, WEBP o PDF. Tamaño máximo 5MB.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-neo btn-neo--light" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn-neo btn-neo--primary" id="btnEnviarReporte">Enviar reporte</button>
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
$pagosJs = 'js/pagosCuotasCliente.js';

$scriptBuffer = function () use ($toastrCss, $toastrJs, $dataTablesCore, $dataTablesBootstrap, $dataTablesResponsive, $pagosJs) {
    ob_start();
    ?>
<link rel="stylesheet" href="<?php echo htmlspecialchars($toastrCss, ENT_QUOTES, 'UTF-8'); ?>">
<script>
  window.PagosCuotasConfig = {
    endpoint: 'controlador/controladorPagoCuota.php'
  };
</script>
<script src="<?php echo htmlspecialchars($dataTablesCore, ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($dataTablesBootstrap, ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($dataTablesResponsive, ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($toastrJs, ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($pagosJs, ENT_QUOTES, 'UTF-8'); ?>"></script>
    <?php
    return ob_get_clean();
};

$extra_scripts = isset($extra_scripts) ? $extra_scripts . $scriptBuffer() : $scriptBuffer();
?>
<?php require_once __DIR__ . '/parte_inferior.php'; ?>
