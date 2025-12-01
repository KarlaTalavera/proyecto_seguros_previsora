<?php 
require_once 'parte_superior.php';
$datos = $usuarioActual;
$rol = $datos->getNombreRol();

require_once '../modelo/modeloUsuario.php';
$modeloU = new modeloUsuario();

$infoFresca = $modeloU->obtenerUsuarioPorCedula($datos->getCedula()) ?: []; 
$fotoActual = $infoFresca['foto_perfil'] ?? 'default.png';
?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Editar Mi Perfil</h6>
                </div>
                <div class="card-body">
                    <form id="formPerfil" enctype="multipart/form-data">
                        <input type="hidden" name="accion" value="actualizar_perfil">
                        <input type="hidden" name="cedula" value="<?= $datos->getCedula() ?>">
                        <input type="hidden" name="rol_actual" value="<?= $rol ?>">

                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <label for="foto_perfil" style="cursor: pointer;" title="Cambiar foto">
                                    <img src="../assets/img/usuarios/<?= $fotoActual ?>" 
                                         id="previewFoto" 
                                         class="img-profile rounded-circle img-thumbnail" 
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                    <div class="mt-2 text-xs text-primary"><i class="fas fa-camera"></i> Cambiar Foto</div>
                                </label>
                                <input type="file" id="foto_perfil" name="foto_perfil" style="display: none;" accept="image/*">
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Cédula (No editable)</label>
                                    <input type="text" class="form-control" value="<?= $datos->getCedula() ?>" readonly disabled>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Nombre</label>
                                        <input type="text" class="form-control" name="nombre" value="<?= $datos->getNombre() ?>" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Apellido</label>
                                        <input type="text" class="form-control" name="apellido" value="<?= $datos->getApellido() ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Correo Electrónico</label>
                                <input type="email" class="form-control" name="email" value="<?= $datos->getEmail() ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Teléfono</label>
                                <input type="text" class="form-control" name="telefono" value="<?= $datos->getTelefono() ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Nueva Contraseña <small class="text-muted">(Dejar en blanco si no desea cambiarla)</small></label>
                            <input type="password" class="form-control" name="password" placeholder="********">
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Previsualizar imagen al seleccionar
document.getElementById('foto_perfil').addEventListener('change', function(e) {
    if (e.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewFoto').src = e.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});

// Envio AJAX
$('#formPerfil').on('submit', function(e){
    e.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: '../controlador/controladorUsuario.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(res) {
            if(res.success) {
                alert('✅ ' + res.message);
                location.reload(); // Recargar para ver cambios
            } else {
                alert('❌ ' + res.message);
            }
        },
        error: function() {
            alert('Error del servidor');
        }
    });
});
</script>

<?php require_once 'parte_inferior.php'; ?>