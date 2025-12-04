<?php
require_once 'parte_superior.php';
require_once __DIR__ . '/polizas_estilos.php';
require_once dirname(__DIR__) . '/modelo/modeloPoliza.php';
require_once dirname(__DIR__) . '/modelo/ModeloTipoPoliza.php';

$modeloPoliza = new ModeloPoliza();
$modeloTipoPoliza = new ModeloTipoPoliza();

// Obtener todos los tipos de póliza
$tiposPoliza = $modeloTipoPoliza->obtenerTiposPolizaCompletos();
$categorias = $modeloPoliza->obtenerCategorias();
$estadisticas = $modeloTipoPoliza->obtenerEstadisticasTipos();
?>

<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Gestión de Tipos de Póliza</h1>
    <p class="mb-4">Administra los diferentes tipos y ramos de pólizas disponibles en el sistema.</p>

    <!-- Botón para agregar nuevo tipo -->
    <div class="row mb-4">
        <div class="col-md-12">
            <button class="btn-neo btn-neo--primary" data-toggle="modal" data-target="#modalNuevoTipo">
                <i class="fas fa-plus"></i> Nuevo Tipo de Póliza
            </button>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Estadísticas por Tipo</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTableEstadisticas" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Tipo de Póliza</th>
                                    <th>Categoría</th>
                                    <th>Total Pólizas</th>
                                    <th>Prima Total</th>
                                    <th>Prima Promedio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estadisticas as $estadistica): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($estadistica['tipo_poliza']); ?></td>
                                    <td><span class="badge-soft" data-variant="info"><?php echo htmlspecialchars($estadistica['categoria']); ?></span></td>
                                    <td><span class="badge-soft" data-variant="aprobado"><?php echo (int)$estadistica['total_polizas']; ?></span></td>
                                    <td>$<?php echo number_format($estadistica['prima_total'] ?? 0, 2); ?></td>
                                    <td>$<?php echo number_format($estadistica['prima_promedio'] ?? 0, 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla principal de tipos de póliza -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Tipos de Póliza</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTableTipos" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Tipo</th>
                            <th>Categoría</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tiposPoliza as $tipo): ?>
                        <tr>
                            <td><?php echo $tipo['id_tipo_poliza']; ?></td>
                            <td><?php echo htmlspecialchars($tipo['nombre_tipo']); ?></td>
                            <td>
                                <span class="badge-soft" data-variant="info">
                                    <?php echo htmlspecialchars($tipo['nombre_categoria']); ?>
                                </span>
                            </td>
                            <td>
                                <div>
                                    <span class="poliza-accion" data-action="detalle"
                                          data-id="<?php echo $tipo['id_tipo_poliza']; ?>"
                                          data-nombre="<?php echo htmlspecialchars($tipo['nombre_tipo']); ?>"
                                          title="Ver Coberturas">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                    <span class="poliza-accion" data-action="editar"
                                          data-id="<?php echo $tipo['id_tipo_poliza']; ?>"
                                          data-nombre="<?php echo htmlspecialchars($tipo['nombre_tipo']); ?>"
                                          data-categoria="<?php echo $tipo['id_categoria']; ?>"
                                          title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </span>
                                    <span class="poliza-accion" data-action="eliminar"
                                          data-id="<?php echo $tipo['id_tipo_poliza']; ?>"
                                          data-nombre="<?php echo htmlspecialchars($tipo['nombre_tipo']); ?>"
                                          title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal para nuevo tipo de póliza -->
<div class="modal fade modal-consistent" id="modalNuevoTipo" tabindex="-1" role="dialog" aria-labelledby="modalNuevoTipoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevoTipoLabel">Nuevo Tipo de Póliza</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formNuevoTipo">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nombre_tipo">Nombre del Tipo *</label>
                        <input type="text" class="form-control" id="nombre_tipo" name="nombre_tipo" required>
                    </div>
                    <div class="form-group">
                        <label for="categoria_tipo">Categoría *</label>
                        <select class="form-control" id="categoria_tipo" name="categoria_tipo" required>
                            <option value="">Seleccione una categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo $categoria['id_categoria']; ?>">
                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-neo btn-neo--light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-neo btn-neo--primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar tipo de póliza -->
<div class="modal fade modal-consistent" id="modalEditarTipo" tabindex="-1" role="dialog" aria-labelledby="modalEditarTipoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarTipoLabel">Editar Tipo de Póliza</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditarTipo">
                <div class="modal-body">
                    <input type="hidden" id="edit_id_tipo" name="id_tipo">
                    <div class="form-group">
                        <label for="edit_nombre_tipo">Nombre del Tipo *</label>
                        <input type="text" class="form-control" id="edit_nombre_tipo" name="nombre_tipo" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_categoria_tipo">Categoría *</label>
                        <select class="form-control" id="edit_categoria_tipo" name="categoria_tipo" required>
                            <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo $categoria['id_categoria']; ?>">
                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-neo btn-neo--light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-neo btn-neo--primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ver coberturas -->
<div class="modal fade modal-consistent" id="modalCoberturas" tabindex="-1" role="dialog" aria-labelledby="modalCoberturasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCoberturasLabel">Coberturas del Tipo: <span id="nombreTipoCoberturas"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="tablaCoberturas" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoCoberturas">
                            <!-- Las coberturas se cargarán aquí dinámicamente -->
                        </tbody>
                    </table>
                </div>
                <div id="sinCoberturas" class="alert alert-info" style="display: none;">
                    Este tipo de póliza no tiene coberturas asociadas.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-neo btn-neo--light" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php
$dataTablesCore = htmlspecialchars(
    resolveAssetPath(
        'vendor/datatables/jquery.dataTables.min.js',
        'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js'
    ),
    ENT_QUOTES,
    'UTF-8'
);
$dataTablesBootstrap = htmlspecialchars(
    resolveAssetPath(
        'vendor/datatables/dataTables.bootstrap4.min.js',
        'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js'
    ),
    ENT_QUOTES,
    'UTF-8'
);

$extra_scripts = <<<EOT
<script src="{$dataTablesCore}"></script>
<script src="{$dataTablesBootstrap}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Inicializar DataTables
    $("#dataTableTipos").DataTable({
        "pageLength": 10,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
        }
    });
    
    $("#dataTableEstadisticas").DataTable({
        "pageLength": 5,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
        },
        "order": [[2, "desc"]]
    });

    // Enviar formulario de nuevo tipo
    $("#formNuevoTipo").submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "controlador/controladorTipoPoliza.php",
            type: "POST",
            data: {
                accion: "crear",
                nombre: $("#nombre_tipo").val(),
                id_categoria: $("#categoria_tipo").val()
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: "success",
                        title: "¡Éxito!",
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        $("#modalNuevoTipo").modal("hide");
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: response.message
                    });
                }
            }
        });
    });

    // Mostrar modal de edición
    $(document).on("click", ".poliza-accion[data-action='editar']", function() {
        var id = $(this).data("id");
        var nombre = $(this).data("nombre");
        var categoria = $(this).data("categoria");
        
        $("#edit_id_tipo").val(id);
        $("#edit_nombre_tipo").val(nombre);
        $("#edit_categoria_tipo").val(categoria);
        $("#modalEditarTipo").modal("show");
    });

    // Enviar formulario de edición
    $("#formEditarTipo").submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "controlador/controladorTipoPoliza.php",
            type: "POST",
            data: {
                accion: "actualizar",
                id_tipo_poliza: $("#edit_id_tipo").val(),
                nombre: $("#edit_nombre_tipo").val(),
                id_categoria: $("#edit_categoria_tipo").val()
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: "success",
                        title: "¡Éxito!",
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        $("#modalEditarTipo").modal("hide");
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: response.message
                    });
                }
            }
        });
    });

    // Eliminar tipo de póliza
    $(document).on("click", ".poliza-accion[data-action='eliminar']", function() {
        var id = $(this).data("id");
        var nombre = $(this).data("nombre");
        
        Swal.fire({
            title: "¿Estás seguro?",
            text: "Vas a eliminar el tipo: " + nombre,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "controlador/controladorTipoPoliza.php",
                    type: "POST",
                    data: {
                        accion: "eliminar",
                        id_tipo_poliza: id
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: "success",
                                title: "¡Eliminado!",
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: response.message
                            });
                        }
                    }
                });
            }
        });
    });

    // Ver coberturas
    $(document).on("click", ".poliza-accion[data-action='detalle']", function() {
        var id = $(this).data("id");
        var nombre = $(this).data("nombre");
        
        $("#nombreTipoCoberturas").text(nombre);
        $("#cuerpoCoberturas").empty();
        
        $.ajax({
            url: "controlador/controladorTipoPoliza.php",
            type: "GET",
            data: {
                accion: "coberturas",
                id_tipo_poliza: id
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    $("#sinCoberturas").hide();
                    $.each(response.data, function(index, cobertura) {
                        var row = "<tr>" +
                            "<td>" + cobertura.id_cobertura + "</td>" +
                            "<td>" + cobertura.nombre + "</td>" +
                            "<td>" + (cobertura.detalle || "Sin detalle") + "</td>" +
                            "</tr>";
                        $("#cuerpoCoberturas").append(row);
                    });
                } else {
                    $("#sinCoberturas").show();
                }
                $("#modalCoberturas").modal("show");
            }
        });
    });
});
</script>
EOT;

require_once 'parte_inferior.php';
?>