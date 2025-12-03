<?php
// Iniciar sesión al inicio
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario sea agente

// Ahora incluir los modelos
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
require_once dirname(__DIR__) . '/modelo/modeloSiniestro.php';
require_once dirname(__DIR__) . '/modelo/modeloPoliza.php';

// Instanciar modelos
$modeloSiniestro = new ModeloSiniestro();
$modeloPoliza = new ModeloPoliza();

require_once __DIR__ . '/parte_superior.php';

// Usar la cédula de la sesión del agente
$cedula_agente = $_SESSION['agente_cedula'] ?? 'V12345678';

// Obtener siniestros del agente
$siniestros = $modeloSiniestro->obtenerSiniestrosDeAgente($cedula_agente) ?: [];

// Calcular totales
$totalSiniestros = count($siniestros);
$totalAbiertos = 0;
$totalCerrados = 0;
$totalMonto = 0;

foreach ($siniestros as $siniestro) {
    $totalMonto += $siniestro['monto_estimado'] ?? 0;
    if ($siniestro['estado'] == 'ABIERTO') $totalAbiertos++;
    if ($siniestro['estado'] == 'CERRADO') $totalCerrados++;
}

// Obtener pólizas del agente para el select
$polizasAgente = $modeloPoliza->obtenerPolizasPorAgente($cedula_agente) ?: [];
?>

<div class="container-fluid">
  <!-- Tarjetas de estadísticas -->
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
              <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($totalMonto, 2, ',', '.'); ?></div>
            </div>
            <div class="col-auto">
              <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Barra de búsqueda y filtros -->
  <div class="card mb-4">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3">
          <div class="form-group">
            <label for="filtroEstado">Estado</label>
            <select class="form-control" id="filtroEstado">
              <option value="">Todos</option>
              <option value="ABIERTO">Abierto</option>
              <option value="EN PROCESO">En Proceso</option>
              <option value="CERRADO">Cerrado</option>
              <option value="RECHAZADO">Rechazado</option>
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="filtroFechaDesde">Desde</label>
            <input type="date" class="form-control" id="filtroFechaDesde">
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="filtroFechaHasta">Hasta</label>
            <input type="date" class="form-control" id="filtroFechaHasta">
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="filtroPoliza">Póliza</label>
            <input type="text" class="form-control" id="filtroPoliza" placeholder="Número de póliza">
          </div>
        </div>
      </div>
      <button class="btn btn-primary" id="btnBuscar">Buscar</button>
      <button class="btn btn-secondary" id="btnLimpiar">Limpiar</button>
    </div>
  </div>

  <!-- Tabla de siniestros -->
  <div class="d-flex justify-content-between mb-3">
    <h3>Gestión de Siniestros</h3>
    <button class="btn btn-primary" data-toggle="modal" data-target="#newClaimModal">
      <i class="fas fa-plus"></i> Añadir Nuevo Siniestro
    </button>
  </div>

  <div class="card">
    <div class="card-body">
      <table id="claimsTable" class="table table-striped w-100">
        <thead>
          <tr>
            <th>ID</th>
            <th>Número Siniestro</th>
            <th>Póliza</th>
            <th>Cliente</th>
            <th>Fecha Reporte</th>
            <th>Descripción</th>
            <th>Monto Estimado</th>
            <th>Estado</th>
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
              <td><?php echo date('d/m/Y', strtotime($siniestro['fecha_reporte'])); ?></td>
              <td><?php echo htmlspecialchars(substr($siniestro['descripcion'], 0, 50)) . '...'; ?></td>
              <td>$<?php echo number_format($siniestro['monto_estimado'], 2, ',', '.'); ?></td>
              <td>
                <?php
                $badgeClass = 'badge-secondary';
                if ($siniestro['estado'] == 'ABIERTO') $badgeClass = 'badge-warning';
                elseif ($siniestro['estado'] == 'CERRADO') $badgeClass = 'badge-success';
                elseif ($siniestro['estado'] == 'EN PROCESO') $badgeClass = 'badge-info';
                elseif ($siniestro['estado'] == 'RECHAZADO') $badgeClass = 'badge-danger';
                ?>
                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($siniestro['estado']); ?></span>
              </td>
              <td>
                <div class="btn-group" role="group">
                  <button class="btn btn-sm btn-info btn-view" data-id="<?php echo $siniestro['id_siniestro']; ?>" title="Ver">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button class="btn btn-sm btn-warning btn-edit" data-id="<?php echo $siniestro['id_siniestro']; ?>" title="Editar">
                    <i class="fas fa-edit"></i>
                  </button>
                  <?php if ($siniestro['estado'] != 'CERRADO'): ?>
                  <button class="btn btn-sm btn-success btn-payment" data-id="<?php echo $siniestro['id_siniestro']; ?>" title="Registrar Pago">
                    <i class="fas fa-money-bill"></i>
                  </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="text-center">No hay siniestros registrados</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Nuevo Siniestro -->
<div class="modal fade" id="newClaimModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Añadir Nuevo Siniestro</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form id="claimForm">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="id_poliza">Póliza *</label>
                <select class="form-control" name="id_poliza" id="id_poliza" required>
                  <option value="">Seleccionar póliza...</option>
                  <?php if (!empty($polizasAgente)): ?>
                    <?php foreach ($polizasAgente as $poliza): ?>
                    <option value="<?php echo $poliza['id_poliza']; ?>">
                      <?php echo htmlspecialchars($poliza['numero_poliza']) . ' - ' . htmlspecialchars($poliza['nombre_cliente']); ?>
                    </option>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <option value="" disabled>No tiene pólizas activas</option>
                  <?php endif; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="fecha_incidente">Fecha Incidente *</label>
                <input type="date" class="form-control" name="fecha_incidente" id="fecha_incidente" required>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="estado">Estado *</label>
                <select class="form-control" name="estado" id="estado" required>
                  <option value="ABIERTO">Abierto</option>
                  <option value="EN PROCESO">En Proceso</option>
                  <option value="CERRADO">Cerrado</option>
                  <option value="RECHAZADO">Rechazado</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="monto_reclamo">Monto Estimado ($) *</label>
                <input type="number" step="0.01" min="0" class="form-control" name="monto_reclamo" id="monto_reclamo" required>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="descripcion">Descripción *</label>
            <textarea class="form-control" name="descripcion" id="descripcion" rows="4" required placeholder="Describa el siniestro con detalles..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Ver Siniestro -->
<div class="modal fade" id="viewClaimModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalles del Siniestro</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
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
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar Siniestro -->
<div class="modal fade" id="editClaimModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar Siniestro</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
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
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Guardar Cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Registrar Pago -->
<div class="modal fade" id="paymentModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registrar Pago de Siniestro</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form id="paymentForm">
        <input type="hidden" name="id_siniestro" id="id_siniestro_pago">
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Al registrar un pago, el siniestro será marcado como <strong>CERRADO</strong>.
          </div>
          
          <div class="form-group">
            <label for="monto_pago">Monto del Pago ($) *</label>
            <input type="number" step="0.01" min="0" class="form-control" name="monto_pago" id="monto_pago" required>
          </div>
          
          <div class="form-group">
            <label for="fecha_pago">Fecha de Pago *</label>
            <input type="date" class="form-control" name="fecha_pago" id="fecha_pago" required>
          </div>
          
          <div class="form-group">
            <label for="comentario_pago">Comentario</label>
            <textarea class="form-control" name="comentario_pago" id="comentario_pago" rows="3" placeholder="Observaciones sobre el pago..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-check"></i> Registrar Pago
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
$extra_scripts = <<<EOT
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script>
$(document).ready(function() {
    // Inicializar DataTable con opciones avanzadas
    var table = $('#claimsTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copiar'
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV'
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimir'
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 25,
        order: [[0, 'desc']]
    });

    // Configurar fecha por defecto en formularios
    var today = new Date().toISOString().split('T')[0];
    $('#fecha_incidente').val(today);
    $('#fecha_pago').val(today);

    // Manejar búsqueda con filtros
    $('#btnBuscar').on('click', function() {
        var estado = $('#filtroEstado').val();
        var fechaDesde = $('#filtroFechaDesde').val();
        var fechaHasta = $('#filtroFechaHasta').val();
        var poliza = $('#filtroPoliza').val();

        $.ajax({
            url: '../controlador/controladorSiniestro.php',
            type: 'GET',
            data: {
                accion: 'buscar_siniestros',
                estado: estado,
                fecha_desde: fechaDesde,
                fecha_hasta: fechaHasta,
                numero_poliza: poliza
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Limpiar tabla
                    table.clear();
                    
                    // Agregar nuevos datos (solo los siniestros del agente)
                    var agentSiniestros = response.siniestros.filter(function(siniestro) {
                        // Aquí deberías filtrar por el agente actual si es necesario
                        // Pero como la búsqueda es general, y el agente solo ve los suyos, 
                        // podrías hacer una búsqueda específica para el agente en el controlador.
                        // Por ahora, asumimos que la búsqueda ya retorna solo los del agente.
                        return true;
                    });
                    
                    $.each(agentSiniestros, function(index, siniestro) {
                        var badgeClass = 'badge-secondary';
                        if (siniestro.estado == 'ABIERTO') badgeClass = 'badge-warning';
                        else if (siniestro.estado == 'CERRADO') badgeClass = 'badge-success';
                        else if (siniestro.estado == 'EN PROCESO') badgeClass = 'badge-info';
                        else if (siniestro.estado == 'RECHAZADO') badgeClass = 'badge-danger';
                        
                        table.row.add([
                            siniestro.id_siniestro,
                            siniestro.numero_siniestro,
                            '<a href="polizaDetalle.php?id=' + siniestro.id_poliza + '" target="_blank">' + 
                                (siniestro.numero_poliza || 'POL-' + siniestro.id_poliza) + 
                            '</a>',
                            siniestro.nombre_cliente || 'N/A',
                            new Date(siniestro.fecha_reporte).toLocaleDateString('es-ES'),
                            siniestro.descripcion.substring(0, 50) + '...',
                            '$' + parseFloat(siniestro.monto_estimado).toFixed(2).replace(/\\./g, ','),
                            '<span class="badge ' + badgeClass + '">' + siniestro.estado + '</span>',
                            '<div class="btn-group" role="group">' +
                                '<button class="btn btn-sm btn-info btn-view" data-id="' + siniestro.id_siniestro + '" title="Ver"><i class="fas fa-eye"></i></button>' +
                                '<button class="btn btn-sm btn-warning btn-edit" data-id="' + siniestro.id_siniestro + '" title="Editar"><i class="fas fa-edit"></i></button>' +
                                (siniestro.estado != 'CERRADO' ? 
                                    '<button class="btn btn-sm btn-success btn-payment" data-id="' + siniestro.id_siniestro + '" title="Registrar Pago"><i class="fas fa-money-bill"></i></button>' : 
                                    '') +
                            '</div>'
                        ]);
                    });
                    
                    table.draw();
                    toastr.success('Búsqueda completada. Se encontraron ' + agentSiniestros.length + ' siniestros.');
                } else {
                    toastr.error('Error: ' + response.message);
                }
            },
            error: function() {
                toastr.error('Error al realizar la búsqueda');
            }
        });
    });

    // Limpiar filtros
    $('#btnLimpiar').on('click', function() {
        $('#filtroEstado').val('');
        $('#filtroFechaDesde').val('');
        $('#filtroFechaHasta').val('');
        $('#filtroPoliza').val('');
        location.reload();
    });

    // Manejar creación de nuevo siniestro
    $('#claimForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        formData += '&accion=crear_siniestro';
        
        $.ajax({
            url: '../controlador/controladorSiniestro.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#claimForm button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#newClaimModal').modal('hide');
                    $('#claimForm')[0].reset();
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                toastr.error('Error al comunicarse con el servidor: ' + error);
            },
            complete: function() {
                $('#claimForm button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
            }
        });
    });

    // Manejar botón Ver
    $('#claimsTable').on('click', '.btn-view', function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: '../controlador/controladorSiniestro.php',
            type: 'GET',
            data: {
                accion: 'obtener_siniestro',
                id_siniestro: id
            },
            dataType: 'json',
            beforeSend: function() {
                $('#viewClaimContent').html(
                    '<div class="text-center">' +
                    '<div class="spinner-border text-primary" role="status">' +
                    '<span class="sr-only">Cargando...</span>' +
                    '</div>' +
                    '<p>Cargando información...</p>' +
                    '</div>'
                );
            },
            success: function(response) {
                if (response.success) {
                    var siniestro = response.data;
                    var html = '<div class="row">';
                    html += '<div class="col-md-6">';
                    html += '<p><strong>ID Siniestro:</strong> ' + siniestro.id_siniestro + '</p>';
                    html += '<p><strong>Número Siniestro:</strong> ' + siniestro.numero_siniestro + '</p>';
                    html += '<p><strong>Póliza:</strong> ' + (siniestro.numero_poliza || 'POL-' + siniestro.id_poliza) + '</p>';
                    html += '<p><strong>Cliente:</strong> ' + siniestro.nombre_cliente + '</p>';
                    html += '<p><strong>Cédula Cliente:</strong> ' + siniestro.cedula_cliente + '</p>';
                    html += '</div>';
                    html += '<div class="col-md-6">';
                    html += '<p><strong>Fecha Reporte:</strong> ' + new Date(siniestro.fecha_reporte).toLocaleDateString('es-ES') + '</p>';
                    html += '<p><strong>Estado:</strong> <span class="badge ' + getEstadoClass(siniestro.estado) + '">' + siniestro.estado + '</span></p>';
                    html += '<p><strong>Monto Estimado:</strong> $' + parseFloat(siniestro.monto_estimado).toFixed(2).replace(/\\./g, ',') + '</p>';
                    html += '</div>';
                    html += '</div>';
                    html += '<div class="row mt-3">';
                    html += '<div class="col-12">';
                    html += '<p><strong>Descripción:</strong></p>';
                    html += '<div class="card"><div class="card-body">' + siniestro.descripcion + '</div></div>';
                    html += '</div>';
                    html += '</div>';
                    
                    $('#viewClaimContent').html(html);
                    $('#viewClaimModal').modal('show');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Error al cargar los detalles');
            }
        });
    });

    // Manejar botón Editar
    $('#claimsTable').on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $('#edit_id_siniestro').val(id);
        
        $.ajax({
            url: '../controlador/controladorSiniestro.php',
            type: 'GET',
            data: {
                accion: 'obtener_siniestro',
                id_siniestro: id
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var siniestro = response.data;
                    var html = '<div class="row">';
                    
                    html += '<div class="col-md-6">';
                    html += '<div class="form-group">';
                    html += '<label>Número Siniestro</label>';
                    html += '<input type="text" class="form-control" value="' + siniestro.numero_siniestro + '" readonly>';
                    html += '</div>';
                    html += '</div>';
                    
                    html += '<div class="col-md-6">';
                    html += '<div class="form-group">';
                    html += '<label>Póliza</label>';
                    html += '<input type="text" class="form-control" value="' + (siniestro.numero_poliza || 'POL-' + siniestro.id_poliza) + '" readonly>';
                    html += '</div>';
                    html += '</div>';
                    
                    html += '</div>';
                    
                    html += '<div class="row">';
                    html += '<div class="col-md-6">';
                    html += '<div class="form-group">';
                    html += '<label for="edit_fecha_incidente">Fecha Incidente *</label>';
                    html += '<input type="date" class="form-control" name="fecha_incidente" id="edit_fecha_incidente" value="' + siniestro.fecha_reporte.split(' ')[0] + '" required>';
                    html += '</div>';
                    html += '</div>';
                    
                    html += '<div class="col-md-6">';
                    html += '<div class="form-group">';
                    html += '<label for="edit_estado">Estado *</label>';
                    html += '<select class="form-control" name="estado" id="edit_estado" required>';
                    html += '<option value="ABIERTO"' + (siniestro.estado == 'ABIERTO' ? ' selected' : '') + '>Abierto</option>';
                    html += '<option value="EN PROCESO"' + (siniestro.estado == 'EN PROCESO' ? ' selected' : '') + '>En Proceso</option>';
                    html += '<option value="CERRADO"' + (siniestro.estado == 'CERRADO' ? ' selected' : '') + '>Cerrado</option>';
                    html += '<option value="RECHAZADO"' + (siniestro.estado == 'RECHAZADO' ? ' selected' : '') + '>Rechazado</option>';
                    html += '</select>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                    
                    html += '<div class="form-group">';
                    html += '<label for="edit_descripcion">Descripción *</label>';
                    html += '<textarea class="form-control" name="descripcion" id="edit_descripcion" rows="4" required>' + siniestro.descripcion + '</textarea>';
                    html += '</div>';
                    
                    html += '<div class="form-group">';
                    html += '<label for="edit_monto_reclamo">Monto Estimado ($) *</label>';
                    html += '<input type="number" step="0.01" min="0" class="form-control" name="monto_reclamo" id="edit_monto_reclamo" value="' + siniestro.monto_estimado + '" required>';
                    html += '</div>';
                    
                    $('#editClaimContent').html(html);
                    $('#editClaimModal').modal('show');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Error al cargar los datos para editar');
            }
        });
    });

    // Manejar formulario de edición
    $('#editClaimForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        formData += '&accion=actualizar_siniestro';
        
        $.ajax({
            url: '../controlador/controladorSiniestro.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#editClaimForm button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#editClaimModal').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                toastr.error('Error al comunicarse con el servidor: ' + error);
            },
            complete: function() {
                $('#editClaimForm button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Cambios');
            }
        });
    });

    // Manejar botón Registrar Pago
    $('#claimsTable').on('click', '.btn-payment', function() {
        var id = $(this).data('id');
        $('#id_siniestro_pago').val(id);
        
        // Obtener monto estimado del siniestro
        $.ajax({
            url: '../controlador/controladorSiniestro.php',
            type: 'GET',
            data: {
                accion: 'obtener_siniestro',
                id_siniestro: id
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var montoEstimado = response.data.monto_estimado;
                    $('#monto_pago').val(montoEstimado).attr('max', montoEstimado);
                }
            }
        });
        
        $('#paymentModal').modal('show');
    });

    // Manejar formulario de pago
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        formData += '&accion=registrar_pago';
        
        $.ajax({
            url: '../controlador/controladorSiniestro.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#paymentForm button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#paymentModal').modal('hide');
                    $('#paymentForm')[0].reset();
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                toastr.error('Error al comunicarse con el servidor: ' + error);
            },
            complete: function() {
                $('#paymentForm button[type="submit"]').prop('disabled', false).html('<i class="fas fa-check"></i> Registrar Pago');
            }
        });
    });

    // Función auxiliar para obtener clase CSS según estado
    function getEstadoClass(estado) {
        switch(estado) {
            case 'ABIERTO': return 'badge-warning';
            case 'CERRADO': return 'badge-success';
            case 'EN PROCESO': return 'badge-info';
            case 'RECHAZADO': return 'badge-danger';
            default: return 'badge-secondary';
        }
    }

    // Configurar toastr para notificaciones
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "5000"
    };
});
</script>
<style>
.dataTables_wrapper .dt-buttons {
    margin-bottom: 10px;
}
.btn-group {
    display: flex;
    flex-wrap: nowrap;
}
.btn-group .btn {
    margin: 0 2px;
}
.badge {
    font-size: 85%;
    padding: 0.4em 0.6em;
}
.card {
    border-radius: 10px;
}
.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
</style>
EOT;

require_once __DIR__ . "/parte_inferior.php";