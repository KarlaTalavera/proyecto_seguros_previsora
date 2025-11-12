<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
require_once dirname(__DIR__) . '/modelo/modeloSiniestro.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';

$modeloSiniestro = new ModeloSiniestro();
// Usar la cédula de la sesión o valor por defecto para prueba
$cedula_agente = $_SESSION['agente_cedula'] ?? 'V12345678'; 
$siniestros = $modeloSiniestro->obtenerSiniestrosDeAgente($cedula_agente) ?: []; // **MÉTODO CORREGIDO**
?>



<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3">
        <h3>Gestión de Siniestros</h3> 
        <div>
            <button class="btn btn-secondary mr-2" id="exportCsvSiniestros">Exportar CSV</button>
            <button class="btn btn-primary" data-toggle="modal" data-target="#crearSiniestroModal">Registrar Siniestro</button>
        </div>
    </div>

    <div class="card">
    <div class="card-body">
        <table id="siniestrosTable" class="table table-striped w-100">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Póliza</th>
                    <th>Cliente</th>
                    <th>Incidente</th>
                    <th>Reclamo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($siniestros)): ?>
                    <?php foreach ($siniestros as $siniestro): 
                        $badge_class = match ($siniestro['estado']) {
                            'APROBADO' => 'badge-success',
                            'RECHAZADO' => 'badge-danger',
                            'PENDIENTE' => 'badge-warning',
                            default => 'badge-secondary', 
                        };
                        $estado_html = '<span class="badge ' . $badge_class . '">' . htmlspecialchars($siniestro['estado']) . '</span>';
                        $reclamo_formato = '$' . number_format($siniestro['monto_reclamo'] ?? 0, 2, ',', '.');
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($siniestro['id']); ?></td>
                            <td><?php echo htmlspecialchars($siniestro['poliza']); ?></td>
                            <td><?php echo htmlspecialchars($siniestro['cliente']); ?></td>
                            <td><?php echo htmlspecialchars($siniestro['fecha_incidente']); ?></td>
                            <td><?php echo $reclamo_formato; ?></td>
                            <td><?php echo $estado_html; ?></td>
                            <td>
                                <button class="btn btn-sm btn-info btn-edit-siniestro" 
                                        data-id="<?= htmlspecialchars($siniestro['id']) ?>" 
                                        data-toggle="modal" 
                                        data-target="#edicionSiniestroModal" title="Editar Siniestro">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="crearSiniestroModal">
    <div class="modal-dialog modal-lg modal-dialog-centered"> 
        <div class="modal-content modal-create-theme">
            <div class="modal-header bg-info text-white"> 
                <h5 class="modal-title">Registrar Nuevo Siniestro</h5>
                <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="crearSiniestroForm">
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="numero_poliza">Número de Póliza afectada *</label>
                            <input class="form-control" id="numero_poliza" name="numero_poliza" placeholder="Ej: PRED-2025-001" required>
                            <small class="form-text text-muted">Asegúrese que la póliza esté registrada.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="fecha_incidente">Fecha del Incidente *</label>
                            <input type="date" class="form-control" id="fecha_incidente" name="fecha_incidente" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción del Siniestro *</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="monto_reclamo">Monto Reclamado ($) *</label>
                            <input class="form-control" id="monto_reclamo" name="monto_reclamo" type="number" step="0.01" min="0" placeholder="Ej: 5000.00" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="estado">Estado Inicial</label>
                            <select class="form-control" id="estado" name="estado">
                                <option value="Pendiente" selected>Pendiente</option>
                                <option value="Aprobado">Aprobado</option>
                                <option value="Rechazado">Rechazado</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="monto_pago">Monto Pagado ($)</label>
                            <input class="form-control" id="monto_pago" name="monto_pago" type="number" step="0.01" min="0" placeholder="Ej: 0.00">
                        </div>
                        <input type="hidden" id="fecha_pago" name="fecha_pago" value=""> </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button id="saveSiniestro" type="button" class="btn btn-primary">Registrar Siniestro</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="edicionSiniestroModal" tabindex="-1" role="dialog" aria-labelledby="edicionSiniestroModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content modal-edit-theme">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="edicionSiniestroModalLabel">Editar Siniestro</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editarSiniestroForm">
                <div class="modal-body">
                    <input type="hidden" id="id_siniestro_edicion" name="id_siniestro_edicion" value="0">
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Cliente / Póliza</label>
                            <input type="text" class="form-control" id="cliente_poliza_edicion" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Número de Póliza</label>
                            <input type="text" class="form-control" id="numero_poliza_edicion_readonly" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="fecha_incidente_edicion">Fecha del Incidente</label>
                            <input type="date" class="form-control" id="fecha_incidente_edicion" name="fecha_incidente_edicion" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="estado_edicion">Estado</label>
                            <select class="form-control" id="estado_edicion" name="estado_edicion">
                                <option value="Pendiente">Pendiente</option>
                                <option value="Aprobado">Aprobado</option>
                                <option value="Rechazado">Rechazado</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descripcion_edicion">Descripción del Siniestro</label>
                        <textarea class="form-control" id="descripcion_edicion" name="descripcion_edicion" rows="3" required></textarea>
                    </div>

                    <hr>
                    <h6 class="text-secondary">Detalles de Montos y Pago</h6>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="monto_reclamo_edicion">Monto Reclamado ($)</label>
                            <input type="number" class="form-control" id="monto_reclamo_edicion" name="monto_reclamo_edicion" step="0.01" min="0" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="monto_pago_edicion">Monto Pagado ($)</label>
                            <input type="number" class="form-control" id="monto_pago_edicion" name="monto_pago_edicion" step="0.01" min="0">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="fecha_pago_edicion">Fecha de Pago</label>
                            <input type="date" class="form-control" id="fecha_pago_edicion" name="fecha_pago_edicion">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button id="saveEdicionSiniestro" type="submit" class="btn btn-success">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Script de JS con la lógica de DataTables y AJAX, y los estilos CSS
$extra_scripts = <<<EOT
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<style>
    /* Estilos para el Modal de Creación (Thema Azul/info) */
    .modal-create-theme .modal-header {
        background-color: #5bc0de;
        color: white;
        border-bottom: 1px solid #46b8da;
    }
    .modal-create-theme .modal-header .close {
        color: white !important;
        opacity: 0.8;
    }
    .modal-create-theme .modal-header .close:hover {
        opacity: 1;
    }

    /* Estilos para el Modal de Edición (Thema Verde/success) */
    .modal-edit-theme .modal-header {
        background-color: #5cb85c;
        color: white;
        border-bottom: 1px solid #4cae4c;
    }
    .modal-edit-theme .modal-header .close {
        color: white !important;
        opacity: 0.8;
    }
    .modal-edit-theme .modal-header .close:hover {
        opacity: 1;
    }
    .modal-edit-theme.modal-content {
        border-radius: 0.5rem;
        border: 1px solid #4cae4c;
    }
</style>

<script>
\$(function() {
    
    // Inicialización de DataTables
    if ($.fn.DataTable) {
        $('#siniestrosTable').DataTable({ 
            pageLength: 10,
            order: [[ 0, "desc" ]], // Ordenar por ID de siniestro descendente
            // **TRADUCCIÓN AL ESPAÑOL**
            language: {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix": "",
                "sSearch": "Buscar:",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            }
        });
    }

// ==============================================================================
// 1. LÓGICA DE CREACIÓN (Modal: #crearSiniestroModal)
// ==============================================================================

$('#crearSiniestroModal').on('show.bs.modal', function (event) {
    $('#crearSiniestroForm')[0].reset(); 
});

$('#saveSiniestro').on('click', function(e) {
    e.preventDefault();
    
    var form = $('#crearSiniestroForm');
    var saveButton = $(this);
    
    if (!form[0].checkValidity()) {
        form[0].reportValidity();
        return;
    }
    
    var data = form.serialize() + '&accion=crear_siniestro'; 
    
    $.ajax({
        url: 'controlador/controladorSiniestro.php', 
        type: 'POST',
        data: data,
        dataType: 'json',
        beforeSend: function() {
            saveButton.text('Registrando...').prop('disabled', true);
        },
        success: function(response) {
            // Nota: Se adaptan los nombres de respuesta a 'success' y 'message' que son los usados en este script
            if (response.success) {
                alert('✅ Éxito: ' + response.message);
                $('#crearSiniestroModal').modal('hide');
                window.location.reload(); 
            } else {
                alert('❌ Error al registrar: ' + response.message);
                saveButton.text('Registrar Siniestro').prop('disabled', false);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
            alert('❌ Error de comunicación con el servidor al registrar siniestro: ' + textStatus);
            saveButton.text('Registrar Siniestro').prop('disabled', false);
        }
    });
});


// ==============================================================================
// 2. LÓGICA DE EDICIÓN (Modal: #edicionSiniestroModal)
// ==============================================================================

// A. Cargar datos al hacer clic en el botón "Editar" de la tabla
$('#siniestrosTable').on('click', '.btn-edit-siniestro', function(e) {
    e.preventDefault(); 
    
    var id_siniestro = $(this).data('id');
    var editModal = $('#edicionSiniestroModal');

    $('#id_siniestro_edicion').val(id_siniestro); 
    $('#edicionSiniestroModalLabel').text('Cargando Siniestro ID #' + id_siniestro + '...');
    editModal.modal('hide'); 

    // Cargar los datos del siniestro específico vía AJAX (GET)
    $.ajax({
        url: 'controlador/controladorSiniestro.php',
        type: 'GET', 
        data: { accion: 'obtener_siniestro', id_siniestro: id_siniestro }, 
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var data = response.data;
                
                // Llenar los campos
                $('#cliente_poliza_edicion').val(data.nombre_cliente + ' (' + data.cedula_cliente + ')'); 
                $('#numero_poliza_edicion_readonly').val(data.numero_poliza);
                $('#fecha_incidente_edicion').val(data.fecha_incidente);
                $('#descripcion_edicion').val(data.descripcion);
                $('#monto_reclamo_edicion').val(data.monto_reclamo);
                $('#monto_pago_edicion').val(data.monto_pago);
                $('#fecha_pago_edicion').val(data.fecha_pago);
                
                // Mapear estado
                $('#estado_edicion').val(data.estado); 
                
                $('#edicionSiniestroModalLabel').text('Editar Siniestro ID #' + id_siniestro);
                
                // Abrir el modal
                editModal.modal('show'); 
            } else {
                alert('❌ Error al cargar datos: ' + response.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
            alert('❌ Error de comunicación con el servidor al cargar el siniestro: ' + textStatus);
        }
    });
});

// B. El manejo del envío del formulario de Edición (Guardar Cambios)
$('#editarSiniestroForm').on('submit', function(e) {
    e.preventDefault();
    
    var form = $(this); 
    var saveButton = $('#saveEdicionSiniestro');
    
    if (!form[0].checkValidity()) {
        form[0].reportValidity();
        return;
    }
    
    // NOTA: Los nombres de los campos en el formulario de edición tienen _edicion,
    // por lo que el controlador está adaptado para recibirlos así.
    var data = form.serialize() + '&accion=actualizar_siniestro'; 
    
    $.ajax({
        url: 'controlador/controladorSiniestro.php', 
        type: 'POST',
        data: data,
        dataType: 'json',
        beforeSend: function() {
            saveButton.text('Procesando...').prop('disabled', true);
        },
        success: function(response) {
            if (response.success) {
                alert('✅ Éxito: ' + response.message);
                $('#edicionSiniestroModal').modal('hide');
                window.location.reload(); 
            } else {
                alert('❌ Error al actualizar: ' + response.message);
                saveButton.text('Guardar Cambios').prop('disabled', false);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);
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