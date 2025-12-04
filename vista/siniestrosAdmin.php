<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/parte_superior.php';
require_once __DIR__ . '/siniestros_estilos.php';
require_once dirname(__DIR__) . '/modelo/modeloSiniestro.php';
require_once dirname(__DIR__) . '/modelo/modeloPoliza.php';

$modeloSiniestro = new ModeloSiniestro();
$modeloPoliza = new ModeloPoliza();

$siniestros = $modeloSiniestro->obtenerTodosSiniestros() ?: [];
$polizasActivas = $modeloSiniestro->obtenerPolizasActivas() ?: [];
$agentesActivos = $modeloSiniestro->obtenerAgentesActivos() ?: [];

$totalSiniestros = count($siniestros);
$totalAbiertos = 0;
$totalCerrados = 0;
$totalMonto = 0.0;

foreach ($siniestros as $registro) {
    $estado = strtoupper($registro['estado'] ?? '');
    $totalMonto += (float)($registro['monto_estimado'] ?? 0);

    if ($estado === 'ABIERTO') {
        $totalAbiertos++;
    } elseif ($estado === 'CERRADO') {
        $totalCerrados++;
    }
}

$estadoConfig = [
  'ABIERTO' => ['variant' => 'warning', 'label' => 'Abierto'],
  'EN PROCESO' => ['variant' => 'info', 'label' => 'En proceso'],
  'CERRADO' => ['variant' => 'success', 'label' => 'Cerrado'],
  'RECHAZADO' => ['variant' => 'danger', 'label' => 'Rechazado'],
];
$estadoConfigJson = json_encode($estadoConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>

<div class="container-fluid">
  <div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Siniestros</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalSiniestros; ?></div>
            </div>
            <div class="col-auto">
              <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-warning shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Abiertos</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalAbiertos; ?></div>
            </div>
            <div class="col-auto">
              <i class="fas fa-clock fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-success shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Cerrados</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalCerrados; ?></div>
            </div>
            <div class="col-auto">
              <i class="fas fa-check-circle fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-info shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Monto Total</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800">$
                <?php echo number_format($totalMonto, 2, ',', '.'); ?>
              </div>
            </div>
            <div class="col-auto">
              <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-0">Gestión de Siniestros</h3>
      <small class="text-muted">Administre reclamos, actualice estados y registre pagos.</small>
    </div>
    <button class="btn-neo btn-neo--primary" data-toggle="modal" data-target="#newClaimModal">
      <i class="fas fa-plus"></i> Añadir Nuevo Siniestro
    </button>
  </div>

  <div class="card">
    <div class="card-body">
      <table id="claimsTable" class="table table-hover siniestros-table w-100">
        <thead>
          <tr>
            <th>ID</th>
            <th>Número</th>
            <th>Póliza</th>
            <th>Cliente</th>
            <th>Fecha Reporte</th>
            <th>Descripción</th>
            <th>Monto Estimado</th>
            <th>Estado</th>
            <th>Agente</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($siniestros)): ?>
            <?php foreach ($siniestros as $siniestro): ?>
            <tr>
              <td><?php echo htmlspecialchars($siniestro['id_siniestro']); ?></td>
              <td><?php echo htmlspecialchars($siniestro['numero_siniestro']); ?></td>
              <td>
                <a href="polizaDetalle.php?id=<?php echo $siniestro['id_poliza']; ?>" target="_blank">
                  <?php echo htmlspecialchars($siniestro['numero_poliza']); ?>
                </a>
              </td>
              <td><?php echo htmlspecialchars($siniestro['nombre_cliente'] ?? 'N/A'); ?></td>
              <td>
                <?php
                $fechaReporte = $siniestro['fecha_reporte'] ?? null;
                echo $fechaReporte ? date('d/m/Y', strtotime($fechaReporte)) : 'Sin fecha';
                ?>
              </td>
              <td>
                <?php
                $descripcion = trim((string)($siniestro['descripcion'] ?? ''));
                if ($descripcion === '') {
                    echo 'Sin descripción';
                } else {
                    $resumen = strlen($descripcion) > 60 ? substr($descripcion, 0, 60) . '...' : $descripcion;
                    echo htmlspecialchars($resumen);
                }
                ?>
              </td>
              <td>$<?php echo number_format((float)($siniestro['monto_estimado'] ?? 0), 2, ',', '.'); ?></td>
              <td>
                <?php
                $estadoRaw = (string)($siniestro['estado'] ?? '');
                $estadoKey = strtoupper(trim($estadoRaw));
                $estadoData = $estadoConfig[$estadoKey] ?? [
                    'variant' => 'neutral',
                    'label' => $estadoRaw !== '' ? $estadoRaw : 'Sin estado'
                ];
                $estadoLabel = $estadoData['label'];
                $estadoVariant = $estadoData['variant'];
                ?>
                <span class="badge-soft" data-variant="<?php echo htmlspecialchars($estadoVariant, ENT_QUOTES, 'UTF-8'); ?>">
                  <?php echo htmlspecialchars($estadoLabel, ENT_QUOTES, 'UTF-8'); ?>
                </span>
              </td>
              <td><?php echo htmlspecialchars($siniestro['nombre_agente'] ?? $siniestro['cedula_agente_gestion'] ?? 'Sin asignar'); ?></td>
              <td>
                <div class="siniestro-actions">
                  <span class="poliza-accion" data-action="detalle" data-id="<?php echo (int)$siniestro['id_siniestro']; ?>" title="Ver detalle">
                    <i class="fas fa-eye"></i>
                  </span>
                  <span class="poliza-accion" data-action="editar" data-id="<?php echo (int)$siniestro['id_siniestro']; ?>" title="Editar">
                    <i class="fas fa-edit"></i>
                  </span>
                  <?php if ($estadoKey !== 'CERRADO'): ?>
                  <span class="poliza-accion" data-action="pago" data-id="<?php echo (int)$siniestro['id_siniestro']; ?>" title="Registrar pago">
                    <i class="fas fa-money-bill"></i>
                  </span>
                  <?php endif; ?>
                  <span class="poliza-accion" data-action="eliminar" data-id="<?php echo (int)$siniestro['id_siniestro']; ?>" title="Eliminar">
                    <i class="fas fa-trash"></i>
                  </span>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="10" class="text-center">No hay siniestros registrados</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Nuevo Siniestro -->
<div class="modal fade modal-consistent" id="newClaimModal" tabindex="-1" role="dialog" aria-labelledby="newClaimModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newClaimModalLabel">Registrar Nuevo Siniestro</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="claimForm">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="id_poliza">Póliza *</label>
                <select class="form-control" name="id_poliza" id="id_poliza" required>
                  <option value="">Seleccione una póliza...</option>
                  <?php foreach ($polizasActivas as $poliza): ?>
                    <option value="<?php echo (int)$poliza['id_poliza']; ?>">
                      <?php echo htmlspecialchars($poliza['numero_poliza'] . ' - ' . $poliza['nombre_cliente']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="cedula_agente_gestion">Agente responsable *</label>
                <select class="form-control" name="cedula_agente_gestion" id="cedula_agente_gestion" required>
                  <option value="">Seleccione un agente...</option>
                  <?php foreach ($agentesActivos as $agente): ?>
                    <option value="<?php echo htmlspecialchars($agente['cedula_agente']); ?>">
                      <?php echo htmlspecialchars($agente['nombre_completo']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="fecha_incidente">Fecha del incidente *</label>
                <input type="date" class="form-control" name="fecha_incidente" id="fecha_incidente" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="estado">Estado inicial *</label>
                <select class="form-control" name="estado" id="estado" required>
                  <option value="ABIERTO">Abierto</option>
                  <option value="EN PROCESO">En Proceso</option>
                  <option value="CERRADO">Cerrado</option>
                  <option value="RECHAZADO">Rechazado</option>
                </select>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="descripcion">Descripción *</label>
            <textarea class="form-control" name="descripcion" id="descripcion" rows="4" required></textarea>
          </div>

          <div class="form-group">
            <label for="monto_reclamo">Monto estimado ($) *</label>
            <input type="number" step="0.01" min="0" class="form-control" name="monto_reclamo" id="monto_reclamo" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-neo btn-neo--light" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn-neo btn-neo--primary">
            <i class="fas fa-save"></i> Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Ver Siniestro -->
<div class="modal fade modal-consistent" id="viewClaimModal" tabindex="-1" role="dialog" aria-labelledby="viewClaimModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewClaimModalLabel">Detalles del Siniestro</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="viewClaimContent">
        <div class="text-center">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Cargando...</span>
          </div>
          <p>Cargando información...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-neo btn-neo--light" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar Siniestro -->
<div class="modal fade modal-consistent" id="editClaimModal" tabindex="-1" role="dialog" aria-labelledby="editClaimModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editClaimModalLabel">Editar Siniestro</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="editClaimForm">
        <input type="hidden" name="id_siniestro" id="edit_id_siniestro">
        <div class="modal-body" id="editClaimContent">
          <div class="text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="sr-only">Cargando...</span>
            </div>
            <p>Cargando información...</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-neo btn-neo--light" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn-neo btn-neo--primary">
            <i class="fas fa-save"></i> Guardar Cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Registrar Pago -->
<div class="modal fade modal-consistent" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="paymentModalLabel">Registrar Pago</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="paymentForm">
        <input type="hidden" name="id_siniestro" id="payment_id_siniestro">
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Al registrar el pago, el siniestro se marcará como <strong>CERRADO</strong>.
          </div>
          <div class="form-group">
            <label for="monto_pago">Monto pagado ($) *</label>
            <input type="number" class="form-control" name="monto_pago" id="monto_pago" min="0" step="0.01" required>
          </div>
          <div class="form-group">
            <label for="fecha_pago">Fecha de pago *</label>
            <input type="date" class="form-control" name="fecha_pago" id="fecha_pago" required>
          </div>
          <div class="form-group">
            <label for="comentario_pago">Comentario</label>
            <textarea class="form-control" name="comentario_pago" id="comentario_pago" rows="3" placeholder="Observaciones adicionales (opcional)"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-neo btn-neo--light" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn-neo btn-neo--primary">
            <i class="fas fa-check"></i> Registrar Pago
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Confirmar Eliminación -->
<div class="modal fade modal-consistent" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteConfirmModalLabel">Confirmar Eliminación</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>¿Está seguro de eliminar este siniestro?</p>
        <p class="text-danger mb-0"><i class="fas fa-exclamation-triangle"></i> Esta acción no se puede deshacer.</p>
        <input type="hidden" id="delete_id_siniestro">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-neo btn-neo--light" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn-neo btn-neo--primary" id="confirmDelete">
          <i class="fas fa-trash"></i> Eliminar
        </button>
      </div>
    </div>
  </div>
</div>

<?php
$toastrCssCdn = 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css';
$toastrJsCdn = 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js';
$dataTablesResponsiveCss = 'https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css';
$dataTablesResponsiveJs = 'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js';
$dataTablesCore = htmlspecialchars(resolveAssetPath('vendor/datatables/jquery.dataTables.min.js', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js'), ENT_QUOTES, 'UTF-8');
$dataTablesBootstrap = htmlspecialchars(resolveAssetPath('vendor/datatables/dataTables.bootstrap4.min.js', 'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js'), ENT_QUOTES, 'UTF-8');

$script = <<<'SCRIPT'
<script>
(function ($) {
  $(function () {
    const controllerUrl = 'controlador/controladorSiniestro.php';
    const estadoConfig = __ESTADO_CONFIG__;

    function notify(type, message) {
      if (window.toastr && typeof toastr[type] === 'function') {
        toastr[type](message);
      } else if (type === 'success') {
        console.log(message);
      } else {
        console.error(message);
      }
    }

    if (window.toastr) {
      toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 4000
      };
    }

    const $claimsTable = $('#claimsTable');
    if ($claimsTable.length && $.fn.DataTable) {
      try {
        $claimsTable.DataTable({
          language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
          pageLength: 10,
          order: [[0, 'desc']],
          responsive: true,
          autoWidth: false,
          columnDefs: [
            { targets: [0, 1, 2, 4, 8], className: 'text-nowrap align-middle' },
            { targets: 3, className: 'align-middle' },
            { targets: 5, className: 'align-middle' },
            { targets: 6, className: 'text-right align-middle' },
            { targets: 7, className: 'text-nowrap align-middle' },
            { targets: -1, className: 'text-center align-middle', orderable: false, searchable: false }
          ]
        });
      } catch (error) {
        console.error('Error inicializando DataTables:', error);
        notify('error', 'No fue posible inicializar la tabla de siniestros.');
      }
    } else {
      notify('error', 'La tabla interactiva no pudo inicializarse.');
    }

    const today = new Date().toISOString().split('T')[0];
    $('#fecha_incidente').val(today);
    $('#fecha_pago').val(today);

    $('#newClaimModal').on('show.bs.modal', function () {
      const form = document.getElementById('claimForm');
      if (form) {
        form.reset();
      }
      $('#fecha_incidente').val(today);
    });

    $('#paymentModal').on('show.bs.modal', function () {
      const form = document.getElementById('paymentForm');
      if (form) {
        form.reset();
      }
      $('#fecha_pago').val(today);
    });

    function formatDateDisplay(value) {
      if (!value) {
        return 'Sin fecha';
      }
      const date = new Date(value);
      if (Number.isNaN(date.getTime())) {
        return value;
      }
      return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function toISODate(value) {
      if (!value) {
        return '';
      }
      return value.split(' ')[0];
    }

    function renderEstadoBadge(estado) {
      const key = (estado || '').toString().trim().toUpperCase();
      const config = estadoConfig[key] || { variant: 'neutral', label: key || 'Sin estado' };
      return '<span class="badge-soft" data-variant="' + config.variant + '">' + config.label + '</span>';
    }

    function formatCurrency(value) {
      const number = parseFloat(value || 0);
      return '$' + number.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    $(document).on('click', '.poliza-accion[data-action="detalle"]', function () {
      const id = Number($(this).data('id'));
      if (!id) {
        notify('error', 'Identificador de siniestro inválido.');
        return;
      }

      $('#viewClaimContent').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div><p>Cargando información...</p></div>');
      $('#viewClaimModal').modal('show');

      $.ajax({
        url: controllerUrl,
        type: 'GET',
        dataType: 'json',
        data: { accion: 'obtener_siniestro', id_siniestro: id }
      }).done(function (response) {
        if (response && response.success && response.data) {
          const s = response.data;
          let html = '';
          html += '<div class="row">';
          html += '  <div class="col-md-6">';
          html += '    <p><strong>ID:</strong> ' + (s.id_siniestro || '-') + '</p>';
          html += '    <p><strong>Número:</strong> ' + (s.numero_siniestro || '-') + '</p>';
          html += '    <p><strong>Póliza:</strong> ' + (s.numero_poliza || ('POL-' + (s.id_poliza || ''))) + '</p>';
          html += '    <p><strong>Cliente:</strong> ' + (s.nombre_cliente || 'Sin cliente') + '</p>';
          html += '  </div>';
          html += '  <div class="col-md-6">';
          html += '    <p><strong>Fecha reporte:</strong> ' + formatDateDisplay(s.fecha_reporte) + '</p>';
          html += '    <p><strong>Estado:</strong> ' + renderEstadoBadge(s.estado) + '</p>';
          html += '    <p><strong>Monto estimado:</strong> ' + formatCurrency(s.monto_estimado) + '</p>';
          html += '    <p><strong>Agente:</strong> ' + (s.nombre_agente || s.cedula_agente_gestion || 'Sin asignar') + '</p>';
          html += '  </div>';
          html += '</div>';
          html += '<hr>';
          html += '<p><strong>Descripción</strong></p>';
          html += '<div class="card"><div class="card-body">' + (s.descripcion || 'Sin descripción') + '</div></div>';
          $('#viewClaimContent').html(html);
        } else {
          const message = (response && response.message) ? response.message : 'No se pudo obtener la información del siniestro.';
          $('#viewClaimContent').html('<div class="alert alert-danger">' + message + '</div>');
        }
      }).fail(function () {
        $('#viewClaimContent').html('<div class="alert alert-danger">Error al consultar el siniestro.</div>');
      });
    });

    $(document).on('click', '.poliza-accion[data-action="editar"]', function () {
      const id = Number($(this).data('id'));
      if (!id) {
        notify('error', 'Identificador de siniestro inválido.');
        return;
      }

      $('#edit_id_siniestro').val(id);
      $('#editClaimContent').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div><p>Cargando información...</p></div>');
      $('#editClaimModal').modal('show');

      $.ajax({
        url: controllerUrl,
        type: 'GET',
        dataType: 'json',
        data: { accion: 'obtener_siniestro', id_siniestro: id }
      }).done(function (response) {
        if (response && response.success && response.data) {
          const s = response.data;
          const estados = ['ABIERTO', 'EN PROCESO', 'CERRADO', 'RECHAZADO'];
          let html = '';
          html += '<div class="row">';
          html += '  <div class="col-md-6">';
          html += '    <div class="form-group">';
          html += '      <label>Número de siniestro</label>';
          html += '      <input type="text" class="form-control" value="' + (s.numero_siniestro || '-') + '" readonly>';
          html += '    </div>';
          html += '  </div>';
          html += '  <div class="col-md-6">';
          html += '    <div class="form-group">';
          html += '      <label>Póliza</label>';
          html += '      <input type="text" class="form-control" value="' + (s.numero_poliza || ('POL-' + (s.id_poliza || ''))) + '" readonly>';
          html += '    </div>';
          html += '  </div>';
          html += '</div>';
          html += '<div class="row">';
          html += '  <div class="col-md-6">';
          html += '    <div class="form-group">';
          html += '      <label>Fecha del incidente *</label>';
          html += '      <input type="date" class="form-control" name="fecha_incidente" value="' + toISODate(s.fecha_reporte) + '" required>';
          html += '    </div>';
          html += '  </div>';
          html += '  <div class="col-md-6">';
          html += '    <div class="form-group">';
          html += '      <label>Estado *</label>';
          html += '      <select class="form-control" name="estado" required>';
          estados.forEach(function (estado) {
            const selected = s.estado === estado ? ' selected' : '';
            html += '        <option value="' + estado + '"' + selected + '>' + estado + '</option>';
          });
          html += '      </select>';
          html += '    </div>';
          html += '  </div>';
          html += '</div>';
          html += '<div class="form-group">';
          html += '  <label>Descripción *</label>';
          html += '  <textarea class="form-control" name="descripcion" rows="4" required>' + (s.descripcion || '') + '</textarea>';
          html += '</div>';
          html += '<div class="form-group">';
          html += '  <label>Monto estimado ($) *</label>';
          html += '  <input type="number" class="form-control" name="monto_reclamo" step="0.01" min="0" value="' + (s.monto_estimado || 0) + '" required>';
          html += '</div>';
          $('#editClaimContent').html(html);
        } else {
          const message = (response && response.message) ? response.message : 'No se pudo cargar el siniestro para editar.';
          $('#editClaimContent').html('<div class="alert alert-danger">' + message + '</div>');
        }
      }).fail(function () {
        $('#editClaimContent').html('<div class="alert alert-danger">Error al obtener el siniestro.</div>');
      });
    });

    $('#editClaimForm').on('submit', function (event) {
      event.preventDefault();
      const formData = $(this).serialize() + '&accion=actualizar_siniestro';
      const $submitButton = $('#editClaimForm button[type="submit"]');

      $.ajax({
        url: controllerUrl,
        type: 'POST',
        dataType: 'json',
        data: formData,
        beforeSend: function () {
          $submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        }
      }).done(function (response) {
        if (response && response.success) {
          notify('success', response.message || 'Siniestro actualizado correctamente.');
          $('#editClaimModal').modal('hide');
          setTimeout(function () { location.reload(); }, 1200);
        } else {
          const message = (response && response.message) ? response.message : 'No se pudo actualizar el siniestro.';
          notify('error', message);
        }
      }).fail(function () {
        notify('error', 'Error al actualizar el siniestro.');
      }).always(function () {
        $submitButton.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Cambios');
      });
    });

    $('#claimForm').on('submit', function (event) {
      event.preventDefault();
      const formData = $(this).serialize() + '&accion=crear_siniestro';
      const $submitButton = $('#claimForm button[type="submit"]');

      $.ajax({
        url: controllerUrl,
        type: 'POST',
        dataType: 'json',
        data: formData,
        beforeSend: function () {
          $submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        }
      }).done(function (response) {
        if (response && response.success) {
          notify('success', response.message || 'Siniestro registrado correctamente.');
          $('#newClaimModal').modal('hide');
          const form = document.getElementById('claimForm');
          if (form) {
            form.reset();
          }
          setTimeout(function () { location.reload(); }, 1200);
        } else {
          const message = (response && response.message) ? response.message : 'No se pudo registrar el siniestro.';
          notify('error', message);
        }
      }).fail(function () {
        notify('error', 'Error al registrar el siniestro.');
      }).always(function () {
        $submitButton.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
      });
    });

    $(document).on('click', '.poliza-accion[data-action="pago"]', function () {
      const id = Number($(this).data('id'));
      if (!id) {
        notify('error', 'Identificador de siniestro inválido.');
        return;
      }
      $('#payment_id_siniestro').val(id);
      $('#paymentModal').modal('show');
    });

    $('#paymentForm').on('submit', function (event) {
      event.preventDefault();
      const formData = $(this).serialize() + '&accion=registrar_pago';
      const $submitButton = $('#paymentForm button[type="submit"]');

      $.ajax({
        url: controllerUrl,
        type: 'POST',
        dataType: 'json',
        data: formData,
        beforeSend: function () {
          $submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
        }
      }).done(function (response) {
        if (response && response.success) {
          notify('success', response.message || 'Pago registrado correctamente.');
          $('#paymentModal').modal('hide');
          setTimeout(function () { location.reload(); }, 1200);
        } else {
          const message = (response && response.message) ? response.message : 'No se pudo registrar el pago.';
          notify('error', message);
        }
      }).fail(function () {
        notify('error', 'Error al registrar el pago.');
      }).always(function () {
        $submitButton.prop('disabled', false).html('<i class="fas fa-check"></i> Registrar Pago');
      });
    });

    $(document).on('click', '.poliza-accion[data-action="eliminar"]', function () {
      const id = Number($(this).data('id'));
      if (!id) {
        notify('error', 'Identificador de siniestro inválido.');
        return;
      }
      $('#delete_id_siniestro').val(id);
      $('#deleteConfirmModal').modal('show');
    });

    $('#confirmDelete').on('click', function () {
      const id = Number($('#delete_id_siniestro').val());
      const $button = $('#confirmDelete');

      if (!id) {
        notify('error', 'No se pudo determinar el siniestro a eliminar.');
        return;
      }

      $.ajax({
        url: controllerUrl,
        type: 'POST',
        dataType: 'json',
        data: { accion: 'eliminar_siniestro', id_siniestro: id },
        beforeSend: function () {
          $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Eliminando...');
        }
      }).done(function (response) {
        if (response && response.success) {
          notify('success', response.message || 'Siniestro eliminado correctamente.');
          $('#deleteConfirmModal').modal('hide');
          setTimeout(function () { location.reload(); }, 1000);
        } else {
          const message = (response && response.message) ? response.message : 'No se pudo eliminar el siniestro.';
          notify('error', message);
        }
      }).fail(function () {
        notify('error', 'Error al eliminar el siniestro.');
      }).always(function () {
        $button.prop('disabled', false).html('<i class="fas fa-trash"></i> Eliminar');
      });
    });
  });
})(jQuery);
</script>
SCRIPT;

$script = str_replace('__ESTADO_CONFIG__', $estadoConfigJson, $script);

$extra_scripts = <<<EOT
<style>
@import url('{$toastrCssCdn}');
@import url('{$dataTablesResponsiveCss}');
</style>
<script src="{$toastrJsCdn}"></script>
<script src="{$dataTablesCore}"></script>
<script src="{$dataTablesBootstrap}"></script>
<script src="{$dataTablesResponsiveJs}"></script>
{$script}
EOT;

require_once __DIR__ . '/parte_inferior.php';
?>
