<?php
// Iniciar el buffer de salida
ob_start();

// Iniciar sesión al inicio
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar permisos de administrador ANTES de enviar cualquier salida


// Ahora incluir los modelos
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
require_once dirname(__DIR__) . '/modelo/modeloSiniestro.php';
require_once dirname(__DIR__) . '/modelo/modeloPoliza.php';

// Instanciar modelos
$modeloSiniestro = new ModeloSiniestro();
$modeloPoliza = new ModeloPoliza();

require_once __DIR__ . '/parte_superior.php';
// Obtener todos los siniestros para administrador
$siniestros = $modeloSiniestro->obtenerTodosSiniestros();

// Obtener estadísticas
$estadisticas = $modeloSiniestro->obtenerEstadisticas();

// Calcular totales
$totalSiniestros = count($siniestros);
$totalAbiertos = 0;
$totalCerrados = 0;
$totalMonto = 0;

foreach ($siniestros as $siniestro) {
    $totalMonto += $siniestro['monto_estimado'];
    if ($siniestro['estado'] == 'ABIERTO') $totalAbiertos++;
    if ($siniestro['estado'] == 'CERRADO') $totalCerrados++;
}
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
              <td><?php echo htmlspecialchars($siniestro['nombre_agente'] ?? $siniestro['cedula_agente_gestion']); ?></td>
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
                  <button class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $siniestro['id_siniestro']; ?>" title="Eliminar">
                    <i class="fas fa-trash"></i>
                  </button>
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
                  <?php
                  $polizas = $modeloPoliza->obtenerPolizas();
                  foreach ($polizas as $poliza):
                  ?>
                  <option value="<?php echo $poliza['id_poliza']; ?>">
                    <?php echo htmlspecialchars($poliza['numero_poliza']) . ' - ' . htmlspecialchars($poliza['cliente']); ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="cedula_agente_gestion">Agente Gestión *</label>
                <select class="form-control" name="cedula_agente_gestion" id="cedula_agente_gestion" required>
                  <option value="">Seleccionar agente...</option>
                  <?php
                  $agentes = $modeloSiniestro->obtenerAgentesActivos();
                  foreach ($agentes as $agente):
                  ?>
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
                <label for="fecha_incidente">Fecha Incidente *</label>
                <input type="date" class="form-control" name="fecha_incidente" id="fecha_incidente" required>
              </div>
            </div>
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
          </div>
          
          <div class="form-group">
            <label for="descripcion">Descripción *</label>
            <textarea class="form-control" name="descripcion" id="descripcion" rows="4" required placeholder="Describa el siniestro con detalles..."></textarea>
          </div>
          
          <div class="form-group">
            <label for="monto_reclamo">Monto Estimado ($) *</label>
            <input type="number" step="0.01" min="0" class="form-control" name="monto_reclamo" id="monto_reclamo" required>
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

<!-- Modal Confirmar Eliminación -->
<div class="modal fade" id="deleteConfirmModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirmar Eliminación</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p>¿Está seguro de eliminar este siniestro?</p>
        <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Esta acción no se puede deshacer.</p>
        <input type="hidden" id="delete_id_siniestro">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">
          <i class="fas fa-trash"></i> Eliminar
        </button>
      </div>
    </div>
  </div>
</div>

<?php
$extra_scripts = <<<EOT
<!-- Toastr CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script>
$(document).ready(function() {
    // RUTA ABSOLUTAMENTE CORRECTA para tu estructura:
    // XAMPP en C:\\xampp\\htdocs\\proyecto_seguros_previsora\\controlador\\controladorSiniestro.php
    var controladorUrl = '/proyecto_seguros_previsora/controlador/controladorSiniestro.php';
    
    console.log('Ruta CORREGIDA:', controladorUrl);
    console.log('URL completa: http://localhost' + controladorUrl);
    
    // Verificar que el archivo existe
    $.ajax({
        url: controladorUrl,
        type: 'HEAD',
        success: function() {
            console.log('CONTROLADOR ENCONTRADO!');
            inicializarAplicacion();
        },
        error: function() {
            console.error('ERROR: Archivo no encontrado en:', controladorUrl);
            alert('CRÍTICO: El archivo controladorSiniestro.php NO existe en:\\n\\n' +
                  'http://localhost' + controladorUrl + '\\n\\n' +
                  'Pero tú dijiste que está en:\\n' +
                  'C:\\\\xampp\\\\htdocs\\\\mi_proyecto\\\\controlador\\\\controladorSiniestro.php\\n\\n' +
                  '¿Estás seguro que el archivo existe? ¿La carpeta se llama "mi_proyecto" exactamente?');
        }
    });
    
    function inicializarAplicacion() {
        console.log('Inicializando aplicación...');
        
        // 1. Inicializar DataTable
        var table = $('#claimsTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copy', text: '<i class="fas fa-copy"></i> Copiar' },
                { extend: 'csv', text: '<i class="fas fa-file-csv"></i> CSV' },
                { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel' },
                { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF' },
                { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir' }
            ],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']]
        });
        
        // 2. Configurar fechas
        var today = new Date().toISOString().split('T')[0];
        $('#fecha_incidente').val(today);
        $('#fecha_pago').val(today);
        
        // 3. BOTÓN PRINCIPAL: AÑADIR NUEVO SINIESTRO
        $('#claimForm').on('submit', function(e) {
            e.preventDefault();
            
            console.log('ENVIANDO FORMULARIO...');
            
            // Validación simple
            if (!$('#id_poliza').val() || !$('#cedula_agente_gestion').val()) {
                alert('Seleccione una póliza y un agente');
                return;
            }
            
            // Crear FormData
            var formData = new FormData(this);
            formData.append('accion', 'crear_siniestro');
            
            // Mostrar datos en consola
            for (var pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            // Enviar AJAX
            $.ajax({
                url: controladorUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    $('#claimForm button[type="submit"]')
                        .prop('disabled', true)
                        .html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
                },
                success: function(response) {
                    console.log('RESPUESTA:', response);
                    
                    if (response && response.success) {
                        alert('ÉXITO: ' + response.message);
                        $('#newClaimModal').modal('hide');
                        $('#claimForm')[0].reset();
                        
                        // Recargar página
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert('ERROR: ' + (response.message || 'Error desconocido'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('ERROR AJAX:', status, error);
                    alert('ERROR DE CONEXIÓN\\n\\n' +
                          'Status: ' + status + '\\n' +
                          'Error: ' + error + '\\n\\n' +
                          '¿El archivo controlador existe? ¿Tiene permisos?');
                },
                complete: function() {
                    $('#claimForm button[type="submit"]')
                        .prop('disabled', false)
                        .html('<i class="fas fa-save"></i> Guardar');
                }
            });
        });
        
        // 4. Botón de búsqueda
        $('#btnBuscar').on('click', function() {
            var datos = {
                accion: 'buscar_siniestros',
                estado: $('#filtroEstado').val(),
                fecha_desde: $('#filtroFechaDesde').val(),
                fecha_hasta: $('#filtroFechaHasta').val(),
                numero_poliza: $('#filtroPoliza').val()
            };
            
            $.ajax({
                url: controladorUrl,
                type: 'GET',
                data: datos,
                dataType: 'json',
                success: function(response) {
                    console.log('Búsqueda:', response);
                    // Tu código para actualizar la tabla
                }
            });
        });
        
        // 5. Funciones para ver/editar/eliminar
        $('#claimsTable').on('click', '.btn-view', function() {
            var id = $(this).data('id');
            $.ajax({
                url: controladorUrl,
                type: 'GET',
                data: { accion: 'obtener_siniestro', id_siniestro: id },
                dataType: 'json',
                success: function(response) {
                    // Tu código para mostrar detalles
                }
            });
        });
        
        console.log('APLICACIÓN INICIALIZADA CORRECTAMENTE');
    }
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
?>