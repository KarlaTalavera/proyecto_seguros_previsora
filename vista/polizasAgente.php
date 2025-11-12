<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
require_once dirname(__DIR__) . '/modelo/modeloPoliza.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';

$modeloPoliza = new ModeloPoliza();
// Se cambia la cédula de prueba a una existente en la BD para ver resultados
$cedula_agente = $_SESSION['agente_cedula'] ?? 'V12345678'; 
$polizas = $modeloPoliza->obtenerPolizasDeAgente($cedula_agente) ?: [];
?>

<div class="container-fluid">
  <div class="d-flex justify-content-between mb-3">
    <h3>Mi Cartera de Pólizas</h3> <div>
      <button class="btn btn-secondary mr-2" id="exportCsv">Exportar CSV</button>
      <button class="btn btn-primary" data-toggle="modal" data-target="#cotizacionModal">Crear Póliza</button>
    </div>
  </div>

  <div class="card">
  <div class="card-body">
    <table id="carteraTable" class="table table-striped w-100">
      <thead>
        <tr><th>ID Póliza</th><th>Producto</th><th>Cliente</th><th>Vencimiento</th><th>Prima</th><th>Estado</th><th>Acciones</th></tr>
      </thead>
      <tbody>
        <?php if (!empty($polizas)): ?>
          <?php foreach ($polizas as $poliza): 
              $badge_class = match ($poliza['estado']) {
                  'ACTIVA' => 'badge-success',
                  'VENCER' => 'badge-warning',
                  'PENDIENTE' => 'badge-info',
                  default => 'badge-warning', 
              };
              $estado_html = '<span class="badge ' . $badge_class . '">' . htmlspecialchars($poliza['estado']) . '</span>';
              $prima_formato = '$' . number_format($poliza['prima'], 2, ',', '.');
          ?>
              <tr>
                  <td><?php echo htmlspecialchars($poliza['id']); ?></td>
                  <td><?php echo htmlspecialchars($poliza['producto']); ?></td>
                  <td><?php echo htmlspecialchars($poliza['cliente']); ?></td>
                  <td><?php echo htmlspecialchars($poliza['vencimiento']); ?></td>
                  <td><?php echo $prima_formato; ?></td>
                  <td><?php echo $estado_html; ?></td>
                  <td>
                      <button class="btn btn-sm btn-info btn-edit-poliza" 
                              data-id="<?= htmlspecialchars($poliza['id']) ?>" 
                              data-toggle="modal" 
                              data-target="#edicionPolizaModal"  title="Editar Póliza">
                          <i class="fas fa-edit"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-secondary" data-action="pdf" data-id="<?php echo $poliza['id']; ?>">PDF</button>
                  </td>
              </tr>
          <?php endforeach; ?>
        <?php endif; ?> </tbody>
    </table>
  </div>
</div>
<div class="modal fade" id="cotizacionModal">
    <div class="modal-dialog modal-lg modal-dialog-centered"> <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Nueva Póliza</h5>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="crearPolizaForm">
                  
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="numero_poliza">Número de Póliza *</label>
                            <input class="form-control" id="numero_poliza" name="numero_poliza" placeholder="Ej: PRED-2025-001" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="id_tipo_poliza">Producto / Tipo de Póliza *</label>
                            <select class="form-control" id="id_tipo_poliza" name="id_tipo_poliza" required>
                                <option value="" disabled selected>Seleccione un producto</option>
                                <?php
                                $tipos_poliza = $modeloPoliza->obtenerTiposPoliza() ?: [];
                                foreach ($tipos_poliza as $tipo): ?>
                                    <option value="<?php echo htmlspecialchars($tipo['id_tipo_poliza']); ?>">
                                        <?php echo htmlspecialchars($tipo['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                    
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="cedula_cliente">Cédula del Cliente *</label>
                            <input class="form-control" id="cedula_cliente" name="cedula_cliente" placeholder="V-12345678" required>
                            <small class="form-text text-muted">Asegúrese de que el cliente esté registrado.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="fecha_vencimiento">Fecha de Vencimiento *</label>
                            <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="prima_anual">Prima Anual ($) *</label>
                            <input class="form-control" id="prima_anual" name="prima_anual" type="number" step="0.01" min="0" placeholder="Ej: 120.00" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="monto_asegurado">Monto Asegurado ($)</label>
                            <input class="form-control" id="monto_asegurado" name="monto_asegurado" type="number" step="0.01" min="0" placeholder="Ej: 50000.00">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="estado_poliza">Estado</label>
                            <select class="form-control" id="estado_poliza" name="estado">
                                <option value="Activa" selected>Activa</option>
                                <option value="Pendiente">Pendiente</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button id="savePoliza" type="button" class="btn btn-success">Guardar Póliza</button>
                
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="edicionPolizaModal" tabindex="-1" role="dialog" aria-labelledby="edicionPolizaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edicionPolizaModalLabel">Editar Póliza</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editarPolizaForm">
                <div class="modal-body">
                    <input type="hidden" id="id_poliza_edicion" name="id_poliza_edicion" value="0">
                    
                    <div class="form-group">
                        <label>Cliente</label>
                        <input type="text" class="form-control" id="cliente_poliza_edicion" readonly>
                    </div>

                    <div class="form-group">
                        <label for="numero_poliza_edicion">Número de Póliza</label>
                        <input type="text" class="form-control" id="numero_poliza_edicion" name="numero_poliza" required>
                    </div>

                    <div class="form-group">
                        <label for="fecha_vencimiento_edicion">Fecha de Vencimiento</label>
                        <input type="date" class="form-control" id="fecha_vencimiento_edicion" name="fecha_vencimiento" required>
                    </div>

                    <div class="form-group">
                        <label for="prima_anual_edicion">Prima Anual (Monto)</label>
                        <input type="number" class="form-control" id="prima_anual_edicion" name="prima_anual" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="estado_edicion">Estado</label>
                        <select class="form-control" id="estado_edicion" name="estado">
                            <option value="Activa">Activa</option>
                            <option value="Pendiente">Pendiente</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button id="saveEdicionPoliza" type="submit" class="btn btn-success">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
// Se añade el script de manejo de formulario AJAX al final.
$extra_scripts = <<<EOT
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
\$(function() {
    
    // Asegúrate de que tu DataTables se inicialice aquí si aún no lo está
    if ($.fn.DataTable) {
        // Inicialización de DataTables para ordenar y buscar el contenido ya renderizado por PHP
        $('#carteraTable').DataTable({ 
            pageLength: 10,
            // ... (resto de tus opciones de DataTables) ...
        });
    }

// ==============================================================================
// 1. LÓGICA DE CREACIÓN (Modal: #cotizacionModal)
// ==============================================================================

// Limpia el formulario de Creación cada vez que se abre
$('#cotizacionModal').on('show.bs.modal', function (event) {
    $('#crearPolizaForm')[0].reset(); 
});

// Maneja el envío del formulario de Creación
$('#savePoliza').on('click', function(e) {
    e.preventDefault();
    
    var form = $('#crearPolizaForm');
    var saveButton = $(this);
    
    if (!form[0].checkValidity()) {
        form[0].reportValidity();
        return;
    }
    
    // Acción Fija para el modal de Creación: crear_poliza
    var data = form.serialize() + '&accion=crear_poliza'; 
    
    $.ajax({
        url: 'controlador/controladorPoliza.php', 
        type: 'POST',
        data: data,
        dataType: 'json',
        beforeSend: function() {
            saveButton.text('Guardando...').prop('disabled', true);
        },
        success: function(response) {
            if (response.success) {
                alert('✅ Éxito: ' + response.message);
                $('#cotizacionModal').modal('hide');
                window.location.reload(); // Recargar la tabla para mostrar la nueva póliza
            } else {
                alert('❌ Error al crear: ' + response.message);
                saveButton.text('Guardar Póliza').prop('disabled', false);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('❌ Error de comunicación con el servidor al crear: ' + textStatus);
            saveButton.text('Guardar Póliza').prop('disabled', false);
        }
    });
});


// ==============================================================================
// 2. LÓGICA DE EDICIÓN (Modal: #edicionPolizaModal)
// ==============================================================================

// A. Cargar datos al hacer clic en el botón "Editar" de la tabla
$('#carteraTable').on('click', '.btn-edit-poliza', function(e) {
    e.preventDefault(); 
    
    var id_poliza = $(this).data('id');
    var editModal = $('#edicionPolizaModal'); // <-- Referencia correcta al modal

    // Muestra un mensaje de carga
    $('#id_poliza_edicion').val(id_poliza); 
    $('#edicionPolizaModalLabel').text('Cargando Póliza ID #' + id_poliza + '...');

    // Oculta el modal, en caso de que haya habido conflicto al eliminar data-toggle
    editModal.modal('hide'); 

    // Cargar los datos de la póliza específica vía AJAX (GET)
    $.ajax({
        url: 'controlador/controladorPoliza.php',
        type: 'GET', 
        data: { accion: 'obtener_poliza', id_poliza: id_poliza }, 
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var data = response.data;
                
                // 1. Llenar los campos (Asegúrate que los IDs de los campos son correctos)
                $('#cliente_poliza_edicion').val(data.nombre_cliente + ' (' + data.cedula_cliente + ')'); 
                $('#numero_poliza_edicion').val(data.numero_poliza);
                $('#fecha_vencimiento_edicion').val(data.fecha_vencimiento);
                $('#prima_anual_edicion').val(data.prima_anual);
                $('#estado_edicion').val(data.estado); 
                
                $('#edicionPolizaModalLabel').text('Editar Póliza ID #' + id_poliza);
                
                // 🔑 PASO CLAVE: ABRIR EL MODAL SOLO EN ÉXITO
                editModal.modal('show'); 
            } else {
                // Caso de falla: Muestra el error
                alert('❌ Error al cargar datos: ' + response.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('❌ Error de comunicación con el servidor al cargar la póliza: ' + textStatus);
        }
    });
});

// B. El manejo del envío del formulario de Edición (Guardar Cambios)
// Este código permanece igual, ya que solo se ejecuta después de abrir el modal y hacer clic en Guardar.
$('#editarPolizaForm').on('submit', function(e) {
    e.preventDefault();
    
    var form = $(this); 
    var saveButton = $('#saveEdicionPoliza');
    
    if (!form[0].checkValidity()) {
        form[0].reportValidity();
        return;
    }
    
    // Acción Fija para el modal de Edición: actualizar_poliza
    var data = form.serialize() + '&accion=actualizar_poliza'; 
    
    $.ajax({
        url: 'controlador/controladorPoliza.php', 
        type: 'POST',
        data: data,
        dataType: 'json',
        beforeSend: function() {
            saveButton.text('Procesando...').prop('disabled', true);
        },
        success: function(response) {
            if (response.success) {
                alert('✅ Éxito: ' + response.message);
                $('#edicionPolizaModal').modal('hide');
                window.location.reload(); 
            } else {
                alert('❌ Error al actualizar: ' + response.message);
                saveButton.text('Guardar Cambios').prop('disabled', false);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('❌ Error de comunicación con el servidor al guardar cambios: ' + textStatus);
            saveButton.text('Guardar Cambios').prop('disabled', false);
        }
    });
});

});
</script>
EOT;
require_once __DIR__ . '/parte_inferior.php';
?>