<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/parte_superior.php';
require_once __DIR__ . '/siniestros_estilos.php';
require_once dirname(__DIR__) . '/modelo/modeloSiniestro.php';
require_once dirname(__DIR__) . '/modelo/modeloPoliza.php';

$modeloSiniestro = new ModeloSiniestro();
$modeloPoliza = new ModeloPoliza();

$siniestros = $modeloSiniestro->obtenerTodosSiniestros() ?: [];

$totales = [
    'total' => count($siniestros),
    'abiertos' => 0,
    'revision' => 0,
    'cerrados' => 0,
    'rechazados' => 0,
    'monto' => 0.0,
];

foreach ($siniestros as $registro) {
    $estado = strtoupper($registro['estado'] ?? '');
    $totales['monto'] += (float)($registro['monto_estimado'] ?? 0);
    switch ($estado) {
        case 'ABIERTO':
            $totales['abiertos']++;
            break;
        case 'EN PROCESO':
        case 'EN REVISION':
        case 'EN REVISIÓN':
            $totales['revision']++;
            break;
        case 'CERRADO':
            $totales['cerrados']++;
            break;
        case 'RECHAZADO':
            $totales['rechazados']++;
            break;
    }
}

$polizasActivas = $modeloSiniestro->obtenerPolizasActivas() ?: [];
$agentesActivos = $modeloSiniestro->obtenerAgentesActivos() ?: [];
?>
<div class="container-fluid">
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="h3 mb-0 text-gray-800">Gestión de Siniestros</h1>
      <p class="text-muted mb-0 small">Administre reclamos, actualice estados y genere reportes consolidados.</p>
    </div>
    <div class="siniestros-toolbar">
      <button type="button" class="btn-neo btn-neo--light" id="exportarSiniestrosCsv">Exportar CSV</button>
      <button type="button" class="btn-main-action" data-toggle="modal" data-target="#registrarSiniestroModal">
        <span class="btn-main-action__label">Registrar Siniestro</span>
        <span class="btn-main-action__icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
        </span>
      </button>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card card-neo h-100 p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted text-uppercase small font-weight-bold">Total siniestros</div>
            <div class="h4 mb-0"><?php echo $totales['total']; ?></div>
          </div>
          <i class="fas fa-clipboard-list text-primary fa-lg"></i>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card card-neo h-100 p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted text-uppercase small font-weight-bold">Abiertos</div>
            <div class="h4 mb-0"><?php echo $totales['abiertos']; ?></div>
          </div>
          <i class="fas fa-exclamation-triangle text-warning fa-lg"></i>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card card-neo h-100 p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted text-uppercase small font-weight-bold">En revisión</div>
            <div class="h4 mb-0"><?php echo $totales['revision']; ?></div>
          </div>
          <i class="fas fa-user-check text-info fa-lg"></i>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card card-neo h-100 p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted text-uppercase small font-weight-bold">Monto total</div>
            <div class="h4 mb-0">$<?php echo number_format($totales['monto'], 2, ',', '.'); ?></div>
          </div>
          <i class="fas fa-coins text-success fa-lg"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="card card-neo">
    <div class="card-body">
      <div class="table-responsive">
        <table id="siniestrosTable" class="table table-striped table-hover align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Número</th>
              <th>Póliza</th>
              <th>Cliente</th>
              <th>Agente</th>
              <th>Fecha Reporte</th>
              <th>Monto</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($siniestros as $siniestro): ?>
              <?php
                $estado = strtoupper($siniestro['estado'] ?? '');
                $badgeVariant = 'neutral';
                if ($estado === 'ABIERTO') {
                    $badgeVariant = 'warning';
                } elseif ($estado === 'CERRADO') {
                    $badgeVariant = 'success';
                } elseif ($estado === 'RECHAZADO') {
                    $badgeVariant = 'danger';
                } elseif ($estado === 'EN PROCESO' || $estado === 'EN REVISION' || $estado === 'EN REVISIÓN') {
                    $badgeVariant = 'info';
                }
                $estadoHtml = '<span class="badge-soft" data-variant="' . $badgeVariant . '">' . htmlspecialchars($siniestro['estado'] ?? 'Pendiente') . '</span>';
              ?>
              <tr>
                <td><?php echo htmlspecialchars($siniestro['id_siniestro']); ?></td>
                <td><?php echo htmlspecialchars($siniestro['numero_siniestro']); ?></td>
                <td><?php echo htmlspecialchars($siniestro['numero_poliza'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($siniestro['nombre_cliente'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($siniestro['nombre_agente'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars(substr($siniestro['fecha_reporte'] ?? '', 0, 10)); ?></td>
                <td>$<?php echo number_format((float)($siniestro['monto_estimado'] ?? 0), 2, ',', '.'); ?></td>
                <td><?php echo $estadoHtml; ?></td>
                <td class="table-action-buttons">
                  <button type="button" class="action-icon action-icon--perm btn-ver-siniestro" data-id="<?php echo (int)$siniestro['id_siniestro']; ?>" title="Ver detalles"><i class="fas fa-eye"></i></button>
                  <button type="button" class="action-icon action-icon--edit btn-editar-siniestro" data-id="<?php echo (int)$siniestro['id_siniestro']; ?>" title="Editar siniestro"><i class="fas fa-edit"></i></button>
                  <?php if ($estado !== 'CERRADO'): ?>
                    <button type="button" class="action-icon action-icon--delete btn-registrar-pago" data-id="<?php echo (int)$siniestro['id_siniestro']; ?>" title="Registrar pago"><i class="fas fa-dollar-sign"></i></button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Registrar Siniestro -->
<div class="modal fade modal-consistent" id="registrarSiniestroModal" tabindex="-1" role="dialog" aria-labelledby="registrarSiniestroModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="registrarSiniestroModalLabel">Registrar Nuevo Siniestro</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="registrarSiniestroForm">
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="id_poliza">Póliza afectada *</label>
              <select class="form-control" id="id_poliza" name="id_poliza" required>
                <option value="">Seleccione una póliza...</option>
                <?php foreach ($polizasActivas as $poliza): ?>
                  <option value="<?php echo (int)$poliza['id_poliza']; ?>">
                    <?php echo htmlspecialchars(($poliza['numero_poliza'] ?? '') . ' · ' . ($poliza['nombre_cliente'] ?? '')); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="cedula_agente_gestion">Agente responsable *</label>
              <select class="form-control" id="cedula_agente_gestion" name="cedula_agente_gestion" required>
                <option value="">Seleccione un agente...</option>
                <?php foreach ($agentesActivos as $agente): ?>
                  <option value="<?php echo htmlspecialchars($agente['cedula_agente']); ?>">
                    <?php echo htmlspecialchars($agente['nombre_completo']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="fecha_incidente">Fecha del incidente *</label>
              <input type="date" class="form-control" id="fecha_incidente" name="fecha_incidente" required>
            </div>
            <div class="form-group col-md-4">
              <label for="estado">Estado inicial *</label>
              <select class="form-control" id="estado" name="estado" required>
                <option value="ABIERTO">Abierto</option>
                <option value="EN PROCESO">En proceso</option>
                <option value="CERRADO">Cerrado</option>
                <option value="RECHAZADO">Rechazado</option>
              </select>
            </div>
            <div class="form-group col-md-4">
              <label for="monto_reclamo">Monto estimado ($) *</label>
              <input type="number" step="0.01" min="0" class="form-control" id="monto_reclamo" name="monto_reclamo" required>
            </div>
          </div>
          <div class="form-group">
            <label for="descripcion">Descripción del siniestro *</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required></textarea>
          </div>
          <div id="registrarSiniestroRespuesta" style="display:none;"></div>
        </div>
        <div class="modal-footer">
          <button class="btn-neo btn-neo--light" type="button" data-dismiss="modal">Cancelar</button>
          <button id="guardarSiniestroBtn" class="btn-neo btn-neo--primary" type="submit">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Detalle Siniestro -->
<div class="modal fade modal-consistent" id="detalleSiniestroModal" tabindex="-1" role="dialog" aria-labelledby="detalleSiniestroModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detalleSiniestroModalLabel">Detalle del Siniestro</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body" id="detalleSiniestroBody">
        <p class="text-center mb-0"><span class="spinner-border spinner-border-sm"></span> Cargando información...</p>
      </div>
      <div class="modal-footer">
        <button class="btn-neo btn-neo--light" type="button" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar Siniestro -->
<div class="modal fade modal-consistent" id="editarSiniestroModal" tabindex="-1" role="dialog" aria-labelledby="editarSiniestroModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editarSiniestroModalLabel">Editar Siniestro</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="editarSiniestroForm">
        <div class="modal-body">
          <input type="hidden" id="id_siniestro_edit" name="id_siniestro">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="numero_siniestro_edit">Número de siniestro</label>
              <input type="text" class="form-control" id="numero_siniestro_edit" readonly>
            </div>
            <div class="form-group col-md-6">
              <label for="poliza_edit">Póliza</label>
              <input type="text" class="form-control" id="poliza_edit" readonly>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="fecha_incidente_edit">Fecha del incidente *</label>
              <input type="date" class="form-control" id="fecha_incidente_edit" name="fecha_incidente" required>
            </div>
            <div class="form-group col-md-4">
              <label for="estado_edit">Estado *</label>
              <select class="form-control" id="estado_edit" name="estado" required>
                <option value="ABIERTO">Abierto</option>
                <option value="EN PROCESO">En proceso</option>
                <option value="CERRADO">Cerrado</option>
                <option value="RECHAZADO">Rechazado</option>
              </select>
            </div>
            <div class="form-group col-md-4">
              <label for="monto_reclamo_edit">Monto estimado ($) *</label>
              <input type="number" step="0.01" min="0" class="form-control" id="monto_reclamo_edit" name="monto_reclamo" required>
            </div>
          </div>
          <div class="form-group">
            <label for="descripcion_edit">Descripción *</label>
            <textarea class="form-control" id="descripcion_edit" name="descripcion" rows="4" required></textarea>
          </div>
          <div id="editarSiniestroRespuesta" style="display:none;"></div>
        </div>
        <div class="modal-footer">
          <button class="btn-neo btn-neo--light" type="button" data-dismiss="modal">Cancelar</button>
          <button id="guardarCambiosSiniestroBtn" class="btn-neo btn-neo--primary" type="submit">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Registrar Pago -->
<div class="modal fade modal-consistent" id="pagoSiniestroModal" tabindex="-1" role="dialog" aria-labelledby="pagoSiniestroModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pagoSiniestroModalLabel">Registrar Pago</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="pagoSiniestroForm">
        <div class="modal-body">
          <input type="hidden" id="id_siniestro_pago" name="id_siniestro">
          <div class="alert alert-info">
            Al registrar el pago, el siniestro cambiará automáticamente a estado <strong>CERRADO</strong>.
          </div>
          <div class="form-group">
            <label for="monto_pago">Monto pagado ($) *</label>
            <input type="number" class="form-control" id="monto_pago" name="monto_pago" min="0" step="0.01" required>
          </div>
          <div class="form-group">
            <label for="fecha_pago">Fecha de pago *</label>
            <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" required>
          </div>
          <div class="form-group">
            <label for="comentario_pago">Comentario</label>
            <textarea class="form-control" id="comentario_pago" name="comentario_pago" rows="3" placeholder="Observaciones adicionales (opcional)"></textarea>
          </div>
          <div id="pagoSiniestroRespuesta" style="display:none;"></div>
        </div>
        <div class="modal-footer">
          <button class="btn-neo btn-neo--light" type="button" data-dismiss="modal">Cancelar</button>
          <button id="registrarPagoBtn" class="btn-neo btn-neo--primary" type="submit">Registrar Pago</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
$extra_scripts = <<<'EOT'
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script>
$(function() {
  var tablaSiniestros = $('#siniestrosTable').DataTable({
    dom: 'Bfrtip',
    buttons: [
      { extend: 'copy', text: '<i class="fas fa-copy"></i> Copiar' },
      { extend: 'csv', text: '<i class="fas fa-file-csv"></i> CSV' },
      { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel' },
      { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir' }
    ],
    language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
    pageLength: 10,
    order: [[0, 'desc']]
  });

  $('#exportarSiniestrosCsv').on('click', function() {
    tablaSiniestros.button('.buttons-csv').trigger('click');
  });

  function mostrarMensaje($target, mensaje, tipo) {
    if (!$target.length) {
      alert(mensaje);
      return;
    }
    var clase = tipo === 'success' ? 'alert-success' : 'alert-danger';
    $target.html('<div class="alert ' + clase + ' mb-0">' + mensaje + '</div>').show();
  }

  function cargarDetalleSiniestro(id, callback) {
    $.ajax({
      url: 'controlador/controladorSiniestro.php',
      type: 'GET',
      dataType: 'json',
      data: { accion: 'obtener_siniestro', id_siniestro: id },
      success: function(respuesta) {
        if (respuesta.success && respuesta.data) {
          callback(respuesta.data);
        } else {
          alert(respuesta.message || 'No se pudo obtener el siniestro.');
        }
      },
      error: function() {
        alert('Error de conexión con el servidor.');
      }
    });
  }

  $('#registrarSiniestroModal').on('show.bs.modal', function() {
    var formulario = $('#registrarSiniestroForm')[0];
    if (formulario) {
      formulario.reset();
    }
    $('#registrarSiniestroRespuesta').hide().empty();
    var hoy = new Date().toISOString().split('T')[0];
    $('#fecha_incidente').val(hoy);
  });

  $('#registrarSiniestroForm').on('submit', function(evento) {
    evento.preventDefault();
    var $boton = $('#guardarSiniestroBtn');
    $('#registrarSiniestroRespuesta').hide().empty();
    $boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');
    $.ajax({
      url: 'controlador/controladorSiniestro.php',
      type: 'POST',
      dataType: 'json',
      data: $(this).serialize() + '&accion=crear_siniestro',
      success: function(respuesta) {
        if (respuesta.success) {
          mostrarMensaje($('#registrarSiniestroRespuesta'), respuesta.message || 'Siniestro registrado correctamente.', 'success');
          setTimeout(function() { window.location.reload(); }, 1200);
        } else {
          mostrarMensaje($('#registrarSiniestroRespuesta'), respuesta.message || 'No se pudo registrar el siniestro.', 'error');
        }
      },
      error: function() {
        mostrarMensaje($('#registrarSiniestroRespuesta'), 'Error de conexión con el servidor.', 'error');
      },
      complete: function() {
        $boton.prop('disabled', false).text('Guardar');
      }
    });
  });

  $(document).on('click', '.btn-ver-siniestro', function() {
    var id = $(this).data('id');
    $('#detalleSiniestroBody').html('<p class="text-center mb-0"><span class="spinner-border spinner-border-sm"></span> Cargando información...</p>');
    $('#detalleSiniestroModal').modal('show');
    cargarDetalleSiniestro(id, function(data) {
      var contenido = '<div class="row">' +
        '<div class="col-md-6">' +
          '<p><strong>Número:</strong> ' + (data.numero_siniestro || '-') + '</p>' +
          '<p><strong>Póliza:</strong> ' + (data.numero_poliza || '-') + '</p>' +
          '<p><strong>Cliente:</strong> ' + (data.nombre_cliente || '-') + '</p>' +
          '<p><strong>Agente:</strong> ' + (data.nombre_agente || '-') + '</p>' +
        '</div>' +
        '<div class="col-md-6">' +
          '<p><strong>Fecha reporte:</strong> ' + (data.fecha_reporte || '-') + '</p>' +
          '<p><strong>Monto estimado:</strong> $' + (parseFloat(data.monto_estimado || 0).toFixed(2)) + '</p>' +
          '<p><strong>Estado:</strong> ' + (data.estado || '-') + '</p>' +
        '</div>' +
      '</div>' +
      '<hr>' +
      '<p><strong>Descripción del siniestro</strong></p>' +
      '<div class="alert alert-light" role="alert">' + (data.descripcion || '-') + '</div>';
      $('#detalleSiniestroBody').html(contenido);
    });
  });

  $(document).on('click', '.btn-editar-siniestro', function() {
    var id = $(this).data('id');
    $('#editarSiniestroRespuesta').hide().empty();
    cargarDetalleSiniestro(id, function(data) {
      $('#id_siniestro_edit').val(data.id_siniestro || id);
      $('#numero_siniestro_edit').val(data.numero_siniestro || '');
      $('#poliza_edit').val(data.numero_poliza || '');
      var fecha = data.fecha_reporte ? data.fecha_reporte.substring(0, 10) : '';
      $('#fecha_incidente_edit').val(fecha);
      $('#estado_edit').val((data.estado || 'ABIERTO').toUpperCase());
      $('#monto_reclamo_edit').val(data.monto_estimado || 0);
      $('#descripcion_edit').val(data.descripcion || '');
      $('#editarSiniestroModal').modal('show');
    });
  });

  $('#editarSiniestroForm').on('submit', function(evento) {
    evento.preventDefault();
    var $boton = $('#guardarCambiosSiniestroBtn');
    $('#editarSiniestroRespuesta').hide().empty();
    $boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');
    $.ajax({
      url: 'controlador/controladorSiniestro.php',
      type: 'POST',
      dataType: 'json',
      data: $(this).serialize() + '&accion=actualizar_siniestro',
      success: function(respuesta) {
        if (respuesta.success) {
          mostrarMensaje($('#editarSiniestroRespuesta'), respuesta.message || 'Siniestro actualizado correctamente.', 'success');
          setTimeout(function() { window.location.reload(); }, 1200);
        } else {
          mostrarMensaje($('#editarSiniestroRespuesta'), respuesta.message || 'No se pudo actualizar el siniestro.', 'error');
        }
      },
      error: function() {
        mostrarMensaje($('#editarSiniestroRespuesta'), 'Error de conexión con el servidor.', 'error');
      },
      complete: function() {
        $boton.prop('disabled', false).text('Guardar Cambios');
      }
    });
  });

  $(document).on('click', '.btn-registrar-pago', function() {
    var id = $(this).data('id');
    $('#id_siniestro_pago').val(id);
    $('#pagoSiniestroRespuesta').hide().empty();
    var hoy = new Date().toISOString().split('T')[0];
    $('#fecha_pago').val(hoy);
    $('#monto_pago').val('');
    $('#comentario_pago').val('');
    $('#pagoSiniestroModal').modal('show');
  });

  $('#pagoSiniestroForm').on('submit', function(evento) {
    evento.preventDefault();
    var $boton = $('#registrarPagoBtn');
    $('#pagoSiniestroRespuesta').hide().empty();
    $boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Procesando...');
    $.ajax({
      url: 'controlador/controladorSiniestro.php',
      type: 'POST',
      dataType: 'json',
      data: $(this).serialize() + '&accion=registrar_pago',
      success: function(respuesta) {
        if (respuesta.success) {
          mostrarMensaje($('#pagoSiniestroRespuesta'), respuesta.message || 'Pago registrado correctamente.', 'success');
          setTimeout(function() { window.location.reload(); }, 1200);
        } else {
          mostrarMensaje($('#pagoSiniestroRespuesta'), respuesta.message || 'No se pudo registrar el pago.', 'error');
        }
      },
      error: function() {
        mostrarMensaje($('#pagoSiniestroRespuesta'), 'Error de conexión con el servidor.', 'error');
      },
      complete: function() {
        $boton.prop('disabled', false).text('Registrar Pago');
      }
    });
  });
});
</script>
EOT;
require_once __DIR__ . '/parte_inferior.php';
?>
