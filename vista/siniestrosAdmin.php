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
          <i class="fas fa-user-check text-info fa-lg"></i>
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
EOT;

require_once __DIR__ . "/parte_inferior.php";
?>