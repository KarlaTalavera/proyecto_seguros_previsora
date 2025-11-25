<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
require_once __DIR__ . '/agentes_estilos.php';
require_once __DIR__ . '/polizas_estilos.php';

$usuarioActual = $_SESSION['datos_usuario'] ?? null;
$cedulaActual = ($usuarioActual && method_exists($usuarioActual, 'getCedula')) ? $usuarioActual->getCedula() : '';
$nombreActual = ($usuarioActual && method_exists($usuarioActual, 'getNombreCompleto')) ? $usuarioActual->getNombreCompleto() : '';
$permisosActuales = isset($_SESSION['permisos_usuario']) && is_array($_SESSION['permisos_usuario']) ? $_SESSION['permisos_usuario'] : [];
$puedeCrearPoliza = in_array('poliza_crear', $permisosActuales, true);
?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-0">Mis pólizas</h3>
      <small class="text-muted">Consulte y emita pólizas para su cartera de clientes</small>
    </div>
    <?php if ($puedeCrearPoliza): ?>
    <button class="btn btn-primary" data-toggle="modal" data-target="#modalPoliza" id="btnRegistrarPoliza">Registrar póliza</button>
    <?php endif; ?>
  </div>

  <div id="polizaPageAlert"></div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-bordered" id="tablaPolizas">
          <thead class="thead-light">
            <tr>
              <th>Número</th>
              <th>Categoría</th>
              <th>Ramo</th>
              <th>Coberturas</th>
              <th>Cliente</th>
              <th>Agente</th>
              <th>Inicio</th>
              <th>Fin</th>
              <th>Prima total</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-alineada" id="modalPoliza" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <form id="formPoliza" autocomplete="off">
        <div class="modal-header">
          <h5 class="modal-title">Registrar póliza</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="polizaFormAlert" class="alert d-none" role="alert"></div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Número de póliza</label>
              <input type="text" class="form-control" id="numeroPolizaPreview" readonly placeholder="Generando número...">
            </div>
            <div class="form-group col-md-6">
              <label for="estadoPolizaSelect">Estado de la póliza</label>
              <select class="form-control" id="estadoPolizaSelect" disabled>
                <option value="ACTIVA" selected>Activa</option>
                <option value="RENOVAR">Por vencer</option>
                <option value="CANCELADA">Cancelada</option>
              </select>
              <small class="form-text text-muted">Solo se habilita al editar una póliza existente.</small>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="categoriaSelect">Categoría</label>
              <select class="form-control" id="categoriaSelect">
                <option value="">Seleccione...</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="ramoSelect">Ramo / Tipo</label>
              <select class="form-control" id="ramoSelect" disabled>
                <option value="">Seleccione...</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="coberturasContainer">Coberturas asociadas</label>
            <div id="coberturasContainer" class="border rounded p-3 bg-light"></div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6 d-none" id="agenteGroup">
              <label for="agenteSelect">Agente responsable</label>
              <select class="form-control" id="agenteSelect">
                <option value="">Seleccione...</option>
              </select>
            </div>
            <div class="form-group col-md-6" id="agenteResumenWrapper">
              <label>Agente responsable</label>
              <div class="form-control-plaintext font-weight-bold" id="agenteResumenTexto"></div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="clienteSelect">Cliente asegurado</label>
              <select class="form-control" id="clienteSelect">
                <option value="">Seleccione...</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="fechaInicio">Fecha de inicio</label>
              <input type="date" class="form-control" id="fechaInicio">
            </div>
            <div class="form-group col-md-6">
              <label for="fechaFin">Fecha de fin</label>
              <input type="date" class="form-control" id="fechaFin">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="montoPrimaTotal">Prima total</label>
              <input type="number" class="form-control" id="montoPrimaTotal" min="0" step="0.01" placeholder="0,00">
            </div>
            <div class="form-group col-md-4">
              <label for="numeroCuotas">Número de cuotas</label>
              <input type="number" class="form-control" id="numeroCuotas" min="1" value="1">
            </div>
            <div class="form-group col-md-4">
              <label>Cuota estimada</label>
              <div class="form-control-plaintext font-weight-bold" id="montoCuotaResumen">--</div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="frecuenciaPago">Frecuencia de pago</label>
              <select class="form-control" id="frecuenciaPago">
                <option value="MENSUAL">Mensual</option>
                <option value="TRIMESTRAL">Trimestral</option>
                <option value="SEMESTRAL">Semestral</option>
                <option value="ANUAL">Anual</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="fechaPrimerVencimiento">Primer vencimiento</label>
              <input type="date" class="form-control" id="fechaPrimerVencimiento">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="neu-button" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="neu-button neu-primary" id="guardarPolizaBtn">Guardar póliza</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalDetallePoliza" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle de póliza</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <dl class="row mb-0">
          <dt class="col-sm-4">Número</dt>
          <dd class="col-sm-8" id="detalleNumero">—</dd>

          <dt class="col-sm-4">Estado</dt>
          <dd class="col-sm-8" id="detalleEstado">—</dd>

          <dt class="col-sm-4">Categoría</dt>
          <dd class="col-sm-8" id="detalleCategoria">—</dd>

          <dt class="col-sm-4">Ramo</dt>
          <dd class="col-sm-8" id="detalleRamo">—</dd>

          <dt class="col-sm-4">Cliente</dt>
          <dd class="col-sm-8" id="detalleCliente">—</dd>

          <dt class="col-sm-4">Cédula del cliente</dt>
          <dd class="col-sm-8" id="detalleClienteCedula">—</dd>

          <dt class="col-sm-4">Agente responsable</dt>
          <dd class="col-sm-8" id="detalleAgente">—</dd>

          <dt class="col-sm-4">Fecha de inicio</dt>
          <dd class="col-sm-8" id="detalleFechaInicio">—</dd>

          <dt class="col-sm-4">Fecha de fin</dt>
          <dd class="col-sm-8" id="detalleFechaFin">—</dd>

          <dt class="col-sm-4">Prima total</dt>
          <dd class="col-sm-8" id="detallePrimaTotal">—</dd>

          <dt class="col-sm-4">Número de cuotas</dt>
          <dd class="col-sm-8" id="detalleNumeroCuotas">—</dd>

          <dt class="col-sm-4">Monto por cuota</dt>
          <dd class="col-sm-8" id="detalleMontoCuota">—</dd>

          <dt class="col-sm-4">Frecuencia de pago</dt>
          <dd class="col-sm-8" id="detalleFrecuencia">—</dd>

          <dt class="col-sm-4">Primer vencimiento</dt>
          <dd class="col-sm-8" id="detallePrimerVencimiento">—</dd>

          <dt class="col-sm-4">Coberturas</dt>
          <dd class="col-sm-8" id="detalleCoberturas">—</dd>
        </dl>
      </div>
      <div class="modal-footer">
        <button type="button" class="neu-button" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<?php
$configJson = json_encode([
    'rol' => 'agente',
    'cedulaActual' => $cedulaActual,
  'nombreActual' => $nombreActual,
  'permisos' => $permisosActuales
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$extra_scripts = <<<EOT
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="js/polizas.js"></script>
<script>
(function (config) {
  function start() {
    if (typeof initPolizasUI === 'function') {
      initPolizasUI(config);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})($configJson);
</script>
EOT;
require_once __DIR__ . '/parte_inferior.php';
?>
