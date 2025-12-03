<?php
// Incluir modeloUsuario PRIMERO, antes de cualquier otra cosa
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
require_once dirname(__DIR__) . '/modelo/ModeloAsegurado.php';
require_once dirname(__DIR__) . '/modelo/modeloPoliza.php';

// Ahora incluir la parte superior que probablemente tiene session_start()
require_once 'parte_superior.php';

$modeloAsegurado = new ModeloAsegurado();
$modeloPoliza = new modeloPoliza();

// Determinar si es agente o administrador
$esAgente = false;
$esAdmin = false;
$cedulaUsuario = '';

// Verificar que el objeto de usuario existe y es válido
if (isset($_SESSION['datos_usuario']) && is_object($_SESSION['datos_usuario'])) {
    $usuario = $_SESSION['datos_usuario'];
    
    // Verificar que sea una instancia de modeloUsuario
    if ($usuario instanceof modeloUsuario) {
        if (method_exists($usuario, 'getNombreRol')) {
            $rol = strtolower($usuario->getNombreRol());
            $esAgente = ($rol === 'agente');
            $esAdmin = ($rol === 'administrador');
        }
        
        if (method_exists($usuario, 'getCedula')) {
            $cedulaUsuario = $usuario->getCedula();
        }
    }
}

// Para agentes, restringir a sus propias pólizas
$cedulaAgenteFiltro = $esAgente ? $cedulaUsuario : null;

// Obtener datos
$asegurados = $modeloAsegurado->obtenerAseguradosCompletos($cedulaAgenteFiltro);
$estadisticas = $modeloAsegurado->obtenerEstadisticasAsegurados($cedulaAgenteFiltro);

// Obtener pólizas para el select
if ($esAgente) {
    $polizasDisponibles = $modeloPoliza->obtenerPolizas($cedulaUsuario);
} else {
    $polizasDisponibles = $modeloPoliza->obtenerPolizas();
}

// Mostrar mensajes si existen
if (isset($_SESSION['mensaje'])) {
    $tipo = $_SESSION['tipo_mensaje'] ?? 'info';
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
}


?>

<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Gestión de Asegurados</h1>
    <p class="mb-4">Administra los asegurados adicionales asociados a las pólizas.</p>

    <!-- Mensajes -->
    <?php if (isset($mensaje)): ?>
    <div class="alert alert-<?php echo $tipo; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($mensaje); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php endif; ?>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Asegurados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $estadisticas['total_asegurados'] ?? 0; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Hombres</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $estadisticas['hombres'] ?? 0; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-male fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Mujeres</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $estadisticas['mujeres'] ?? 0; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-female fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pólizas con Asegurados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $estadisticas['polizas_con_asegurados'] ?? 0; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-contract fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón para agregar nuevo asegurado -->
    <div class="row mb-4">
        <div class="col-md-12">
            <button class="btn btn-primary" data-toggle="modal" data-target="#modalNuevoAsegurado">
                <i class="fas fa-user-plus"></i> Nuevo Asegurado
            </button>
        </div>
    </div>

    <!-- Tabla principal de asegurados -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Asegurados</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTableAsegurados" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cédula</th>
                            <th>Nombre Completo</th>
                            <th>Fecha Nacimiento</th>
                            <th>Parentesco</th>
                            <th>Sexo</th>
                            <th>Póliza</th>
                            <th>Cliente Principal</th>
                            <th>Agente</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($asegurados as $asegurado): ?>
                        <tr>
                            <td><?php echo $asegurado['id_asegurado']; ?></td>
                            <td><?php echo $asegurado['cedula'] ?: 'No especificada'; ?></td>
                            <td><?php echo htmlspecialchars($asegurado['nombre'] . ' ' . $asegurado['apellido']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($asegurado['fecha_nacimiento'])); ?></td>
                            <td><?php echo $asegurado['parentesco'] ?: 'No especificado'; ?></td>
                            <td>
                                <?php if ($asegurado['sexo'] == 'M'): ?>
                                    <span class="badge badge-info">Hombre</span>
                                <?php else: ?>
                                    <span class="badge badge-pink">Mujer</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-secondary">
                                    <?php echo htmlspecialchars($asegurado['numero_poliza']); ?>
                                </span><br>
                                <small><?php echo htmlspecialchars($asegurado['tipo_poliza']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($asegurado['cliente_principal']); ?></td>
                            <td>
                                <?php if ($asegurado['agente_nombre']): ?>
                                    <?php echo htmlspecialchars($asegurado['agente_nombre'] . ' ' . $asegurado['agente_apellido']); ?>
                                <?php else: ?>
                                    <span class="text-muted">Sin agente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Botón Editar que abre modal -->
                                <button class="btn btn-sm btn-warning btn-editar" 
                                        data-id="<?php echo $asegurado['id_asegurado']; ?>"
                                        data-cedula="<?php echo htmlspecialchars($asegurado['cedula']); ?>"
                                        data-nombre="<?php echo htmlspecialchars($asegurado['nombre']); ?>"
                                        data-apellido="<?php echo htmlspecialchars($asegurado['apellido']); ?>"
                                        data-fecha-nacimiento="<?php echo $asegurado['fecha_nacimiento']; ?>"
                                        data-parentesco="<?php echo htmlspecialchars($asegurado['parentesco']); ?>"
                                        data-sexo="<?php echo $asegurado['sexo']; ?>"
                                        title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <!-- Formulario para eliminar -->
                                <form method="POST" action="controlador/controladorAsegurado.php" style="display:inline;">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id_asegurado" value="<?php echo $asegurado['id_asegurado']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('¿Está seguro de eliminar a <?php echo htmlspecialchars($asegurado['nombre'] . ' ' . $asegurado['apellido']); ?>?')"
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal para nuevo asegurado -->
<div class="modal fade" id="modalNuevoAsegurado" tabindex="-1" role="dialog" aria-labelledby="modalNuevoAseguradoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevoAseguradoLabel">Nuevo Asegurado</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="controlador/controladorAsegurado.php">
                <input type="hidden" name="accion" value="crear">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nuevo_id_poliza">Póliza *</label>
                                <select class="form-control" id="nuevo_id_poliza" name="id_poliza" required>
                                    <option value="">Seleccione una póliza</option>
                                    <?php foreach ($polizasDisponibles as $poliza): ?>
                                        <?php if (isset($poliza['id_poliza']) && isset($poliza['numero_poliza'])): ?>
                                            <option value="<?php echo $poliza['id_poliza']; ?>">
                                                <?php echo htmlspecialchars($poliza['numero_poliza'] . ' - ' . ($poliza['cliente'] ?? 'Cliente') . ' (' . ($poliza['ramo'] ?? 'Ramo') . ')'); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Selecciona la póliza a la que pertenece este asegurado</small>
                            </div>
                            <div class="form-group">
                                <label for="nuevo_cedula">Cédula</label>
                                <input type="text" class="form-control" id="nuevo_cedula" name="cedula" placeholder="Ej: V12345678">
                            </div>
                            <div class="form-group">
                                <label for="nuevo_nombre">Nombre *</label>
                                <input type="text" class="form-control" id="nuevo_nombre" name="nombre" required>
                            </div>
                            <div class="form-group">
                                <label for="nuevo_apellido">Apellido *</label>
                                <input type="text" class="form-control" id="nuevo_apellido" name="apellido" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nuevo_fecha_nacimiento">Fecha de Nacimiento *</label>
                                <input type="date" class="form-control" id="nuevo_fecha_nacimiento" name="fecha_nacimiento" required>
                            </div>
                            <div class="form-group">
                                <label for="nuevo_parentesco">Parentesco</label>
                                <select class="form-control" id="nuevo_parentesco" name="parentesco">
                                    <option value="">Seleccione parentesco</option>
                                    <option value="Cónyuge">Cónyuge</option>
                                    <option value="Hijo/a">Hijo/a</option>
                                    <option value="Padre">Padre</option>
                                    <option value="Madre">Madre</option>
                                    <option value="Hermano/a">Hermano/a</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nuevo_sexo">Sexo *</label>
                                <select class="form-control" id="nuevo_sexo" name="sexo" required>
                                    <option value="">Seleccione</option>
                                    <option value="M">Hombre</option>
                                    <option value="F">Mujer</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar asegurado -->
<div class="modal fade" id="modalEditarAsegurado" tabindex="-1" role="dialog" aria-labelledby="modalEditarAseguradoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarAseguradoLabel">Editar Asegurado</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="controlador/controladorAsegurado.php">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" id="edit_id_asegurado" name="id_asegurado">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_cedula">Cédula</label>
                                <input type="text" class="form-control" id="edit_cedula" name="cedula">
                            </div>
                            <div class="form-group">
                                <label for="edit_nombre">Nombre *</label>
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_apellido">Apellido *</label>
                                <input type="text" class="form-control" id="edit_apellido" name="apellido" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_fecha_nacimiento">Fecha de Nacimiento *</label>
                                <input type="date" class="form-control" id="edit_fecha_nacimiento" name="fecha_nacimiento" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_parentesco">Parentesco</label>
                                <select class="form-control" id="edit_parentesco" name="parentesco">
                                    <option value="">Seleccione parentesco</option>
                                    <option value="Cónyuge">Cónyuge</option>
                                    <option value="Hijo/a">Hijo/a</option>
                                    <option value="Padre">Padre</option>
                                    <option value="Madre">Madre</option>
                                    <option value="Hermano/a">Hermano/a</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_sexo">Sexo *</label>
                                <select class="form-control" id="edit_sexo" name="sexo" required>
                                    <option value="">Seleccione</option>
                                    <option value="M">Hombre</option>
                                    <option value="F">Mujer</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extra_scripts = <<<HTML
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

<style>
.badge-pink {
    background-color: #e83e8c;
    color: white;
}
</style>

<script>
$(document).ready(function() {
    // Inicializar DataTables
    $("#dataTableAsegurados").DataTable({
        "pageLength": 10,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
        }
    });

    // Mostrar modal de edición
    $(document).on("click", ".btn-editar", function() {
        var id = $(this).data("id");
        var cedula = $(this).data("cedula");
        var nombre = $(this).data("nombre");
        var apellido = $(this).data("apellido");
        var fechaNacimiento = $(this).data("fecha-nacimiento");
        var parentesco = $(this).data("parentesco");
        var sexo = $(this).data("sexo");
        
        $("#edit_id_asegurado").val(id);
        $("#edit_cedula").val(cedula);
        $("#edit_nombre").val(nombre);
        $("#edit_apellido").val(apellido);
        $("#edit_fecha_nacimiento").val(fechaNacimiento);
        $("#edit_parentesco").val(parentesco);
        $("#edit_sexo").val(sexo);
        
        $("#modalEditarAsegurado").modal("show");
    });
});
</script>
HTML;
require_once 'parte_inferior.php';
?>