<?php
// Iniciar sesión al inicio
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// PARA DESARROLLO: Establecer sesión manualmente
if (!isset($_SESSION['rol'])) {
    $_SESSION['rol'] = 'agente';
    $_SESSION['agente_cedula'] = 'V12345678';
    $_SESSION['usuario_nombre'] = 'Santiago Rodriguez';
}

// Ahora incluir los modelos
require_once dirname(__DIR__) . '/modelo/modeloSiniestro.php';
require_once dirname(__DIR__) . '/modelo/modeloPoliza.php';

// Instanciar modelos
$modeloSiniestro = new ModeloSiniestro();
$modeloPoliza = new ModeloPoliza();

// Verificar conexión de manera indirecta - NO usar ->db directamente
// Podemos verificar llamando a un método simple

require_once __DIR__ . '/parte_superior.php';

// Usar la cédula de la sesión del agente
$cedula_agente = $_SESSION['agente_cedula'] ?? 'V12345678';

// Obtener siniestros del agente
$siniestros = $modeloSiniestro->obtenerSiniestrosDeAgente($cedula_agente);
if ($siniestros === false) {
    echo '<div class="alert alert-danger">Error de conexión a la base de datos. No se pudieron obtener los siniestros.</div>';
    $siniestros = [];
}

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
$polizasAgente = $modeloPoliza->obtenerPolizasPorAgente($cedula_agente);
if ($polizasAgente === false || $polizasAgente === null) {
    echo '<div class="alert alert-info">No se encontraron pólizas activas para este agente o hubo un error de conexión.</div>';
    $polizasAgente = [];
}


?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
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
        // Obtener la ruta base del proyecto
        function getBaseUrl() {
            var pathArray = window.location.pathname.split('/');
            var basePath = '';
            // Construir la ruta base
            for (var i = 0; i < pathArray.length - 1; i++) {
                if (pathArray[i]) {
                    basePath += '/' + pathArray[i];
                }
            }
            return basePath || '/';
        }
        
        var baseUrl = getBaseUrl();
        var controllerUrl = baseUrl + '/controlador/controladorSiniestro.php';
        console.log('Ruta del controlador:', controllerUrl);

        // Inicializar DataTable
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

        // Configurar fecha por defecto
        var today = new Date().toISOString().split('T')[0];
        $('#fecha_incidente').val(today);
        $('#fecha_pago').val(today);

        // Configurar toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000"
        };

        // --- FUNCIONES AUXILIARES ---
        function formatFecha(fechaString) {
            if (!fechaString) return 'N/A';
            try {
                var fecha = new Date(fechaString);
                return fecha.toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            } catch (e) {
                return fechaString;
            }
        }
        
        function getEstadoClass(estado) {
            if (!estado) return 'badge-secondary';
            switch(estado.toUpperCase()) {
                case 'ABIERTO': return 'badge-warning';
                case 'CERRADO': return 'badge-success';
                case 'EN PROCESO': return 'badge-info';
                case 'RECHAZADO': return 'badge-danger';
                default: return 'badge-secondary';
            }
        }

        // --- MANEJAR BOTONES ---
        $(document).on('click', '.btn-view', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var id = $(this).data('id');
            if (!id) {
                toastr.error('ID de siniestro no válido');
                return;
            }
            
            $('#viewClaimContent').html(
                '<div class="text-center">' +
                '<div class="spinner-border text-primary" role="status">' +
                '<span class="sr-only">Cargando...</span>' +
                '</div>' +
                '<p>Cargando información...</p>' +
                '</div>'
            );
            
            $('#viewClaimModal').modal('show');
            
            $.ajax({
                url: controllerUrl,
                type: 'GET',
                data: {
                    accion: 'obtener_siniestro',
                    id_siniestro: id
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Respuesta:', response);
                    if (response.success && response.data) {
                        var s = response.data;
                        var html = '<div class="row">';
                        html += '<div class="col-md-6">';
                        html += '<p><strong>ID:</strong> ' + s.id_siniestro + '</p>';
                        html += '<p><strong>Número:</strong> ' + s.numero_siniestro + '</p>';
                        html += '<p><strong>Póliza:</strong> ' + (s.numero_poliza || 'POL-' + s.id_poliza) + '</p>';
                        html += '<p><strong>Cliente:</strong> ' + (s.nombre_cliente || 'N/A') + '</p>';
                        html += '</div>';
                        html += '<div class="col-md-6">';
                        html += '<p><strong>Fecha:</strong> ' + formatFecha(s.fecha_reporte) + '</p>';
                        html += '<p><strong>Estado:</strong> <span class="badge ' + getEstadoClass(s.estado) + '">' + s.estado + '</span></p>';
                        html += '<p><strong>Monto:</strong> $' + (s.monto_estimado ? parseFloat(s.monto_estimado).toFixed(2).replace(/\./g, ',') : '0,00') + '</p>';
                        html += '</div>';
                        html += '</div>';
                        html += '<div class="row mt-3">';
                        html += '<div class="col-12">';
                        html += '<p><strong>Descripción:</strong></p>';
                        html += '<div class="card"><div class="card-body">' + (s.descripcion || 'Sin descripción') + '</div></div>';
                        html += '</div>';
                        html += '</div>';
                        $('#viewClaimContent').html(html);
                    } else {
                        $('#viewClaimContent').html(
                            '<div class="alert alert-danger">' +
                            (response.message || 'Error al cargar datos') +
                            '</div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    toastr.error('Error: ' + error);
                    $('#viewClaimContent').html(
                        '<div class="alert alert-danger">Error de conexión. Verifique la consola.</div>'
                    );
                }
            });
        });

        $(document).on('click', '.btn-edit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var id = $(this).data('id');
            $('#edit_id_siniestro').val(id);
            
            $('#editClaimContent').html(
                '<div class="text-center">' +
                '<div class="spinner-border text-primary" role="status">' +
                '<span class="sr-only">Cargando...</span>' +
                '</div>' +
                '<p>Cargando...</p>' +
                '</div>'
            );
            
            $('#editClaimModal').modal('show');
            
            $.ajax({
                url: controllerUrl,
                type: 'GET',
                data: {
                    accion: 'obtener_siniestro',
                    id_siniestro: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        var s = response.data;
                        var html = '<div class="row">';
                        html += '<div class="col-md-6">';
                        html += '<div class="form-group">';
                        html += '<label>Número Siniestro</label>';
                        html += '<input type="text" class="form-control" value="' + s.numero_siniestro + '" readonly>';
                        html += '</div>';
                        html += '</div>';
                        html += '<div class="col-md-6">';
                        html += '<div class="form-group">';
                        html += '<label>Póliza</label>';
                        html += '<input type="text" class="form-control" value="' + (s.numero_poliza || 'POL-' + s.id_poliza) + '" readonly>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        html += '<div class="row">';
                        html += '<div class="col-md-6">';
                        html += '<div class="form-group">';
                        html += '<label>Fecha Incidente *</label>';
                        html += '<input type="date" class="form-control" name="fecha_incidente" value="' + s.fecha_reporte.split(' ')[0] + '" required>';
                        html += '</div>';
                        html += '</div>';
                        html += '<div class="col-md-6">';
                        html += '<div class="form-group">';
                        html += '<label>Estado *</label>';
                        html += '<select class="form-control" name="estado" required>';
                        html += '<option value="ABIERTO"' + (s.estado == 'ABIERTO' ? ' selected' : '') + '>Abierto</option>';
                        html += '<option value="EN PROCESO"' + (s.estado == 'EN PROCESO' ? ' selected' : '') + '>En Proceso</option>';
                        html += '<option value="CERRADO"' + (s.estado == 'CERRADO' ? ' selected' : '') + '>Cerrado</option>';
                        html += '<option value="RECHAZADO"' + (s.estado == 'RECHAZADO' ? ' selected' : '') + '>Rechazado</option>';
                        html += '</select>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        html += '<div class="form-group">';
                        html += '<label>Descripción *</label>';
                        html += '<textarea class="form-control" name="descripcion" rows="4" required>' + (s.descripcion || '') + '</textarea>';
                        html += '</div>';
                        html += '<div class="form-group">';
                        html += '<label>Monto Estimado ($) *</label>';
                        html += '<input type="number" step="0.01" min="0" class="form-control" name="monto_reclamo" value="' + (s.monto_estimado || '0') + '" required>';
                        html += '</div>';
                        $('#editClaimContent').html(html);
                    } else {
                        $('#editClaimContent').html(
                            '<div class="alert alert-danger">' + (response.message || 'Error') + '</div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('Error: ' + error);
                    $('#editClaimContent').html('<div class="alert alert-danger">Error de conexión</div>');
                }
            });
        });

        $('#editClaimForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize() + '&accion=actualizar_siniestro';
            
            $.ajax({
                url: controllerUrl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    $('#editClaimForm button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'Actualizado correctamente');
                        $('#editClaimModal').modal('hide');
                        setTimeout(function() { location.reload(); }, 1500);
                    } else {
                        toastr.error(response.message || 'Error');
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('Error: ' + error);
                },
                complete: function() {
                    $('#editClaimForm button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
                }
            });
        });

        $('#claimForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize() + '&accion=crear_siniestro';
            
            $.ajax({
                url: controllerUrl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    $('#claimForm button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
                },
                success: function(response) {
                    console.log('Respuesta creación:', response);
                    if (response.success) {
                        toastr.success(response.message);
                        $('#newClaimModal').modal('hide');
                        $('#claimForm')[0].reset();
                        setTimeout(function() { location.reload(); }, 1500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('Error: ' + error);
                },
                complete: function() {
                    $('#claimForm button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
                }
            });
        });

        $(document).on('click', '.btn-payment', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            $('#id_siniestro_pago').val(id);
            $('#paymentModal').modal('show');
        });

        $('#paymentForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize() + '&accion=registrar_pago';
            
            $.ajax({
                url: controllerUrl,
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
                        setTimeout(function() { location.reload(); }, 1500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('Error: ' + error);
                },
                complete: function() {
                    $('#paymentForm button[type="submit"]').prop('disabled', false).html('<i class="fas fa-check"></i> Registrar Pago');
                }
            });
        });

        console.log('JavaScript inicializado correctamente');
    });
</script>
<style>
.dataTables_wrapper .dt-buttons { margin-bottom: 10px; }
.btn-group { display: flex; flex-wrap: nowrap; }
.btn-group .btn { margin: 0 2px; }
.badge { font-size: 85%; padding: 0.4em 0.6em; }
.card { border-radius: 10px; }
.modal-header { background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; }
</style>
EOT;

require_once __DIR__ . "/parte_inferior.php";

