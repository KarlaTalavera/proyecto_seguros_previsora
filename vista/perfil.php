<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuarioActual = $_SESSION['datos_usuario'] ?? null;
if (!$usuarioActual) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../modelo/modeloUsuario.php';
$modeloU = new modeloUsuario();

$infoFresca = $modeloU->obtenerUsuarioPorCedula($usuarioActual->getCedula()) ?: [];
$rol = $usuarioActual->getNombreRol();
$esAdmin = $rol === 'administrador';
$fotoNombre = $infoFresca['foto_perfil'] ?? 'undraw_profile.svg';
$rutaUsuarios = dirname(__DIR__) . '/assets/img/usuarios/' . $fotoNombre;
$fotoActual = is_file($rutaUsuarios) ? 'assets/img/usuarios/' . $fotoNombre : 'img/' . $fotoNombre;
$nombreActual = $infoFresca['nombre'] ?? $usuarioActual->getNombre();
$apellidoActual = $infoFresca['apellido'] ?? $usuarioActual->getApellido();
$emailActual = $infoFresca['email'] ?? $usuarioActual->getEmail();
$telefonoActual = $infoFresca['telefono'] ?? $usuarioActual->getTelefono();
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
                        <input type="hidden" name="cedula_original" value="<?= htmlspecialchars($usuarioActual->getCedula(), ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (!$esAdmin): ?>
                            <input type="hidden" name="cedula" value="<?= htmlspecialchars($usuarioActual->getCedula(), ENT_QUOTES, 'UTF-8') ?>">
                        <?php endif; ?>
                        <input type="hidden" name="rol_actual" value="<?= htmlspecialchars($rol, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="foto_actual" value="<?= htmlspecialchars($infoFresca['foto_perfil'] ?? 'undraw_profile.svg', ENT_QUOTES, 'UTF-8') ?>">

                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <label for="foto_perfil" style="cursor: pointer;" title="Cambiar foto">
                                     <img src="<?= htmlspecialchars($fotoActual, ENT_QUOTES, 'UTF-8') ?>" 
                                         id="previewFoto" 
                                         class="img-profile rounded-circle img-thumbnail" 
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                    <div class="mt-2 text-xs text-primary"><i class="fas fa-camera"></i> Cambiar Foto</div>
                                </label>
                                <input type="file" id="foto_perfil" name="foto_perfil" style="display: none;" accept="image/*">
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Cédula<?= $esAdmin ? '' : ' (No editable)' ?></label>
                                    <?php if ($esAdmin): ?>
                                        <input type="text" class="form-control" name="cedula" value="<?= htmlspecialchars($usuarioActual->getCedula(), ENT_QUOTES, 'UTF-8') ?>" required>
                                    <?php else: ?>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($usuarioActual->getCedula(), ENT_QUOTES, 'UTF-8') ?>" readonly disabled>
                                    <?php endif; ?>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Nombre</label>
                                        <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($nombreActual, ENT_QUOTES, 'UTF-8') ?>" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Apellido</label>
                                        <input type="text" class="form-control" name="apellido" value="<?= htmlspecialchars($apellidoActual, ENT_QUOTES, 'UTF-8') ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Correo Electrónico</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($emailActual, ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Teléfono</label>
                                <input type="text" class="form-control" name="telefono" value="<?= htmlspecialchars($telefonoActual ?? '', ENT_QUOTES, 'UTF-8') ?>">
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
<?php
$extra_scripts = <<<'EOT'
<script>
$(function(){
    $('#foto_perfil').on('change', function(e){
        const archivo = e.target.files && e.target.files[0];
        if (!archivo) {
            return;
        }
        const reader = new FileReader();
        reader.onload = function(evt){
            $('#previewFoto').attr('src', evt.target.result);
        };
        reader.readAsDataURL(archivo);
    });

    $('#formPerfil').on('submit', function(e){
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: 'controlador/controladorUsuario.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    alert('✅ ' + res.message);
                    location.reload();
                } else {
                    alert('❌ ' + res.message);
                }
            },
            error: function() {
                alert('Error del servidor');
            }
        });
    });
});
</script>
EOT;
?>
