<?php
if (session_status() == PHP_SESSION_NONE) {
		session_start();
}
require_once __DIR__ . '/parte_superior.php';
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';

$modeloUsuario = new ModeloUsuario();
$resultadoAgentes = $modeloUsuario->obtenerTodosLosAgentes();
$agentes = [];

if ($resultadoAgentes === false) {
		echo '<div class="alert alert-danger">Error al cargar los agentes.</div>';
} elseif (is_array($resultadoAgentes)) {
		$agentes = array_values(array_filter($resultadoAgentes, function ($usuario) {
				$rol = strtolower($usuario['nombre_rol'] ?? $usuario['rol'] ?? '');
				$activo = isset($usuario['activo']) ? (int) $usuario['activo'] : 1;
				return $rol === 'agente' && $activo === 1;
		}));

		if (empty($agentes)) {
				echo '<div class="alert alert-info">No hay agentes registrados.</div>';
		}
} else {
		echo '<div class="alert alert-info">No hay agentes registrados.</div>';
}
?>

<style>
	:root {
		--perm-modal-header-bg: #93BFC7;
		--perm-modal-header-color: #fff;
		--perm-card-header-bg: #DEDED1;
		--perm-card-header-color: #333;
		--perm-hover-bg: #e9e9e3;
		--modal-hover-bg: #eff3f9;
	}

	#permisosModal .modal-content,
	#modalNuevoAgente .modal-content,
	#modalEditarAgente .modal-content {
		border-radius: 1rem;
		overflow: hidden;
		border: none;
		box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
	}

	#permisosModal .modal-header,
	#modalNuevoAgente .modal-header,
	#modalEditarAgente .modal-header {
		background-color: var(--perm-modal-header-bg);
		color: var(--perm-modal-header-color);
		border-bottom: none;
	}

	#permisosModal .modal-header .close,
	#modalNuevoAgente .modal-header .close,
	#modalEditarAgente .modal-header .close {
		color: var(--perm-modal-header-color);
		opacity: 0.8;
	}

	#permisosModal .modal-title strong {
		font-weight: 500;
	}

	#modalNuevoAgente .modal-body,
	#modalEditarAgente .modal-body {
		padding: 1.5rem;
	}

	#modalNuevoAgente .modal-footer,
	#modalEditarAgente .modal-footer {
		padding: 1rem 1.5rem;
		display: flex;
		gap: 0.5rem;
		justify-content: flex-end;
	}

	#modalNuevoAgente .form-group,
	#modalEditarAgente .form-group {
		margin-bottom: 1rem;
	}

	#modalNuevoAgente .form-row,
	#modalEditarAgente .form-row {
		margin-left: -0.5rem;
		margin-right: -0.5rem;
	}

	#modalNuevoAgente .form-row .form-group,
	#modalEditarAgente .form-row .form-group {
		padding-left: 0.5rem;
		padding-right: 0.5rem;
	}

	.perm-group-card {
		border: none;
		border-radius: 0.75rem;
		box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
		min-height: 100%;
		background-color: #fff;
	}

	.perm-group-card .card-header {
		background-color: var(--perm-card-header-bg);
		color: var(--perm-card-header-color);
		font-weight: 600;
		border-bottom: 1px solid rgba(0, 0, 0, 0.05);
	}

	.perm-group-card .card-body {
		max-height: 260px;
		overflow-y: auto;
		padding: 1rem;
	}

	.perm-check {
		display: flex;
		align-items: center;
		gap: 0.75rem;
		padding: 0.4rem 0.6rem;
		border-radius: 0.5rem;
		transition: background-color 0.2s ease;
	}

	.perm-check:hover {
		background-color: var(--modal-hover-bg);
	}

	.perm-check .form-check-input {
		margin-top: 0;
	}

	.modal-consistent .modal-header {
		background-color: var(--perm-modal-header-bg);
		color: var(--perm-modal-header-color);
	}

	.modal-consistent .modal-header .close {
		color: var(--perm-modal-header-color);
	}

	#respuestaCrearAgente .alert,
	#respuestaEditarAgente .alert {
		margin-bottom: 0;
	}
</style>

<!-- Begin Page Content -->
<div class="container-fluid">
	<div class="d-sm-flex align-items-center justify-content-between mb-4">
		<h1 class="h3 mb-0 text-gray-800">Gestión de Agentes</h1>
		<button type="button" class="btn-main-action" data-toggle="modal" data-target="#modalNuevoAgente">
			<span class="btn-main-action__label">Registrar Nuevo Agente</span>
			<span class="btn-main-action__icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<line x1="12" y1="5" x2="12" y2="19"></line>
					<line x1="5" y1="12" x2="19" y2="12"></line>
				</svg>
			</span>
		</button>
	</div>

	<div class="card shadow mb-4">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered" id="agentsTable" width="100%">
					<thead>
						<tr>
							<th>Cédula</th>
							<th>Nombre</th>
							<th>Email</th>
							<th>Teléfono</th>
							<th>Acciones</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($agentes as $agente): ?>
							<?php
								$nombreCompletoAgente = trim(($agente['nombre'] ?? '') . ' ' . ($agente['apellido'] ?? ''));
							?>
							<tr>
								<td><?php echo htmlspecialchars($agente['cedula']); ?></td>
								<td><?php echo htmlspecialchars(($agente['nombre'] ?? '') . ' ' . ($agente['apellido'] ?? '')); ?></td>
								<td><?php echo htmlspecialchars($agente['email'] ?? ''); ?></td>
								<td><?php echo htmlspecialchars($agente['telefono'] ?? ''); ?></td>
								<td class="table-action-buttons">
									<button type="button"
													class="action-icon action-icon--edit editAgentBtn"
													data-cedula="<?php echo htmlspecialchars($agente['cedula']); ?>"
													data-nombre="<?php echo htmlspecialchars($agente['nombre'] ?? ''); ?>"
													data-apellido="<?php echo htmlspecialchars($agente['apellido'] ?? ''); ?>"
													data-email="<?php echo htmlspecialchars($agente['email'] ?? ''); ?>"
													data-telefono="<?php echo htmlspecialchars($agente['telefono'] ?? ''); ?>"
													data-toggle="modal"
													data-target="#modalEditarAgente"
													title="Editar Agente"
													aria-label="Editar agente">
										<i class="fas fa-pencil-alt"></i>
									</button>
									<button type="button"
													class="action-icon action-icon--delete deleteAgentBtn"
													data-cedula="<?php echo htmlspecialchars($agente['cedula']); ?>"
													data-nombre="<?php echo htmlspecialchars($nombreCompletoAgente); ?>"
													title="Desactivar Agente"
													aria-label="Desactivar agente">
										<i class="fas fa-trash"></i>
									</button>
									<button type="button"
													class="action-icon action-icon--perm managePermsBtn"
													data-cedula="<?php echo htmlspecialchars($agente['cedula']); ?>"
													data-nombre="<?php echo htmlspecialchars($nombreCompletoAgente); ?>"
													title="Gestionar Permisos"
													aria-label="Gestionar permisos">
										<i class="fas fa-key"></i>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- /.container-fluid -->

<!-- Modal de Permisos -->
<div class="modal fade modal-consistent" id="permisosModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalLabel">Gestionar Permisos para <strong id="nombreAgentePermisos"></strong></h5>
				<button class="close" type="button" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<form id="permisosForm">
					<input type="hidden" id="cedulaAgentePermisos" name="cedula_agente">
					<div id="listaPermisos" class="container-fluid">
						<!-- Contenido dinámico -->
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button class="btn-neo btn-neo--light" type="button" data-dismiss="modal">Cancelar</button>
				<button id="btnGuardarPermisos" class="btn-neo btn-neo--primary">Guardar Cambios</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal Registrar Nuevo Agente -->
<div class="modal fade modal-consistent" id="modalNuevoAgente" tabindex="-1" role="dialog" aria-labelledby="modalLabelNuevoAgente" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalLabelNuevoAgente">Registrar Nuevo Agente</h5>
				<button class="close" type="button" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<form id="nuevoAgenteForm" novalidate>
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="agenteCedula">Cédula <span class="text-danger">*</span></label>
							<div class="input-group">
								<div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-id-card"></i></span></div>
								<input type="text" class="form-control" id="agenteCedula" name="cedula" required placeholder="V12345678">
							</div>
							<div class="invalid-feedback">Cédula requerida (ej: V12345678).</div>
						</div>
						<div class="form-group col-md-6">
							<label for="agenteTelefono">Teléfono <span class="text-danger">*</span></label>
							<div class="input-group">
								<div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
								<input type="text" class="form-control" id="agenteTelefono" name="telefono" required placeholder="0414xxxxxxx">
							</div>
							<div class="invalid-feedback">Teléfono requerido.</div>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="agenteNombre">Nombre <span class="text-danger">*</span></label>
							<input type="text" class="form-control" id="agenteNombre" name="nombre" required placeholder="Nombre">
							<div class="invalid-feedback">Nombre requerido.</div>
						</div>
						<div class="form-group col-md-6">
							<label for="agenteApellido">Apellido <span class="text-danger">*</span></label>
							<input type="text" class="form-control" id="agenteApellido" name="apellido" required placeholder="Apellido">
							<div class="invalid-feedback">Apellido requerido.</div>
						</div>
					</div>
					<div class="form-group">
						<label for="agenteEmail">Email <span class="text-danger">*</span></label>
						<div class="input-group">
							<div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>
							<input type="email" class="form-control" id="agenteEmail" name="email" required placeholder="correo@dominio.tld">
						</div>
						<div class="invalid-feedback">Email válido requerido.</div>
					</div>
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="agentePassword">Contraseña <span class="text-danger">*</span></label>
							<input type="password" class="form-control" id="agentePassword" name="password" required placeholder="Mínimo 8 caracteres">
							<small class="form-text text-muted">La contraseña debe tener al menos 8 caracteres.</small>
							<div class="invalid-feedback">Contraseña requerida (mínimo 8 caracteres).</div>
						</div>
						<div class="form-group col-md-6">
							<label for="agentePasswordConfirm">Confirmar Contraseña <span class="text-danger">*</span></label>
							<input type="password" class="form-control" id="agentePasswordConfirm" required placeholder="Repita la contraseña">
							<div class="invalid-feedback">Las contraseñas deben coincidir.</div>
						</div>
					</div>
				</form>
				<div id="respuestaCrearAgente" style="display:none;" class="mt-2"></div>
			</div>
			<div class="modal-footer">
				<button class="btn-neo btn-neo--light" type="button" data-dismiss="modal">Cancelar</button>
				<button id="btnGuardarAgente" class="btn-neo btn-neo--primary">Crear Agente</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal Editar Agente -->
<div class="modal fade modal-consistent" id="modalEditarAgente" tabindex="-1" role="dialog" aria-labelledby="modalLabelEditarAgente" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalLabelEditarAgente">Editar Agente</h5>
				<button class="close" type="button" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<form id="editarAgenteForm" novalidate>
					<input type="hidden" id="editCedulaOriginal" name="cedula_original">
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="editAgenteCedula">Cédula <span class="text-danger">*</span></label>
							<div class="input-group">
								<div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-id-card"></i></span></div>
								<input type="text" class="form-control" id="editAgenteCedula" name="cedula" required readonly>
							</div>
						</div>
						<div class="form-group col-md-6">
							<label for="editAgenteTelefono">Teléfono <span class="text-danger">*</span></label>
							<div class="input-group">
								<div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
								<input type="text" class="form-control" id="editAgenteTelefono" name="telefono" required placeholder="0414xxxxxxx">
							</div>
							<div class="invalid-feedback">Teléfono requerido.</div>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="editAgenteNombre">Nombre <span class="text-danger">*</span></label>
							<input type="text" class="form-control" id="editAgenteNombre" name="nombre" required placeholder="Nombre">
							<div class="invalid-feedback">Nombre requerido.</div>
						</div>
						<div class="form-group col-md-6">
							<label for="editAgenteApellido">Apellido <span class="text-danger">*</span></label>
							<input type="text" class="form-control" id="editAgenteApellido" name="apellido" required placeholder="Apellido">
							<div class="invalid-feedback">Apellido requerido.</div>
						</div>
					</div>
					<div class="form-group">
						<label for="editAgenteEmail">Email <span class="text-danger">*</span></label>
						<div class="input-group">
							<div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>
							<input type="email" class="form-control" id="editAgenteEmail" name="email" required placeholder="correo@dominio.tld">
						</div>
						<div class="invalid-feedback">Email válido requerido.</div>
					</div>
					<div class="alert alert-info" role="alert">
						Deje los campos de contraseña en blanco si no desea cambiarlos.
					</div>
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="editAgentePassword">Nueva Contraseña</label>
							<input type="password" class="form-control" id="editAgentePassword" name="password" placeholder="Mínimo 8 caracteres">
							<small class="form-text text-muted">Mínimo 8 caracteres si se provee.</small>
							<div class="invalid-feedback">La contraseña debe tener al menos 8 caracteres.</div>
						</div>
						<div class="form-group col-md-6">
							<label for="editAgentePasswordConfirm">Confirmar Nueva Contraseña</label>
							<input type="password" class="form-control" id="editAgentePasswordConfirm" placeholder="Repita la contraseña">
							<div class="invalid-feedback">Las contraseñas deben coincidir.</div>
						</div>
					</div>
				</form>
				<div id="respuestaEditarAgente" style="display:none;" class="mt-2"></div>
			</div>
			<div class="modal-footer">
				<button class="btn-neo btn-neo--light" type="button" data-dismiss="modal">Cancelar</button>
				<button id="btnActualizarAgente" class="btn-neo btn-neo--primary">Guardar Cambios</button>
			</div>
		</div>
	</div>
</div>

<?php
require_once __DIR__ . '/parte_inferior.php';

$dataTablesCore = resolveAssetPath(
	'vendor/datatables/jquery.dataTables.min.js',
	'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js'
);
$dataTablesBootstrap = resolveAssetPath(
	'vendor/datatables/dataTables.bootstrap4.min.js',
	'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js'
);
?>

<!-- Page level plugins -->
<script src="<?php echo htmlspecialchars($dataTablesCore, ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($dataTablesBootstrap, ENT_QUOTES, 'UTF-8'); ?>"></script>

<!-- Page level custom scripts -->
<script>
$(document).ready(function() {
	$('#agentsTable').DataTable({
		language: {
			url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json'
		}
	});

	function formatPermisoLabel(permiso) {
		if (!permiso) {
			return '';
		}
		if (typeof permiso === 'string') {
			const parts = permiso.split('_');
			if (parts.length > 1) {
				const action = parts.slice(1).join(' ');
				return action.charAt(0).toUpperCase() + action.slice(1);
			}
			return permiso;
		}
		if (permiso.descripcion) {
			return permiso.descripcion;
		}
		return formatPermisoLabel(permiso.nombre_permiso || '');
	}

	function formatGrupoLabel(nombreGrupo) {
		const mapa = {
			polizas: 'Planes y pólizas',
			siniestros: 'Reportes de siniestros',
			solicitud: 'Solicitudes de pólizas y siniestros',
			general: 'Configuración general'
		};
		if (Object.prototype.hasOwnProperty.call(mapa, nombreGrupo)) {
			return mapa[nombreGrupo];
		}
		if (!nombreGrupo) {
			return 'Permisos';
		}
		return nombreGrupo.charAt(0).toUpperCase() + nombreGrupo.slice(1);
	}

	function buildControllerUrl(rutaRelativa) {
		var origin = window.location.origin || '';
		var path = window.location.pathname || '';
		var vistaIndex = path.indexOf('/vista/');
		var base = '';
		if (vistaIndex !== -1) {
			base = path.substring(0, vistaIndex);
		} else {
			var lastSlash = path.lastIndexOf('/');
			base = lastSlash > -1 ? path.substring(0, lastSlash) : '';
		}
		base = base.replace(/\/+$/, '');
		if (!rutaRelativa.startsWith('/')) {
			rutaRelativa = '/' + rutaRelativa;
		}
		return origin + base + rutaRelativa;
	}

	const controladorPermisoUrl = buildControllerUrl('controlador/controladorPermisoAgente.php');

	$(document).on('click', '.managePermsBtn', function() {
		const cedula = $(this).data('cedula');
		const nombre = $(this).data('nombre');
		$('#nombreAgentePermisos').text(nombre);
		$('#cedulaAgentePermisos').val(cedula);
		$('#permisosModal').modal('show');
		$('#listaPermisos').html('<div class="text-center"><div class="spinner-border text-primary"></div></div>');

		$.ajax({
			url: controladorPermisoUrl,
			type: 'GET',
			data: { accion: 'obtener_permisos_agente', cedula_agente: cedula },
			dataType: 'json',
			success: function(respuesta) {
				if (respuesta.estado === 'exito' && Array.isArray(respuesta.permisos) && respuesta.permisos.length > 0) {
					const grupos = {};

					respuesta.permisos.forEach(function(permiso) {
						const nombrePermiso = permiso.nombre_permiso || '';
						const parts = nombrePermiso.split('_');
						const grupo = parts[0] || 'general';
						if (!grupos[grupo]) {
							grupos[grupo] = [];
						}
						grupos[grupo].push(permiso);
					});

					let html = '<div class="row">';
					for (const nombreGrupo in grupos) {
						if (!Object.prototype.hasOwnProperty.call(grupos, nombreGrupo)) {
							continue;
						}
						html += '\n              <div class="col-md-6 mb-4">\n                <div class="card perm-group-card">\n                  <div class="card-header">' + formatGrupoLabel(nombreGrupo) + '</div>\n                  <div class="card-body">';

						grupos[nombreGrupo].forEach(function(permiso) {
							const isChecked = permiso.activo ? 'checked' : '';
							const label = formatPermisoLabel(permiso);
							html += '\n                <div class="form-check perm-check">\n                  <input type="checkbox" class="form-check-input" name="permisos[]" value="' + permiso.id_permiso + '" id="perm-' + permiso.id_permiso + '" ' + isChecked + '>\n                  <label class="form-check-label w-100" for="perm-' + permiso.id_permiso + '">\n                    ' + label + '\n                  </label>\n                </div>';
						});

						html += '\n                  </div>\n                </div>\n              </div>';
					}
					html += '\n            </div>';
					$('#listaPermisos').html(html);
				} else if (Array.isArray(respuesta.permisos) && respuesta.permisos.length === 0) {
					$('#listaPermisos').html('<p class="text-center">No hay permisos definidos en el sistema.</p>');
				} else {
					$('#listaPermisos').html('<p class="text-danger text-center">Error: ' + (respuesta.mensaje || 'No fue posible cargar los permisos.') + '</p>');
				}
			},
			error: function(xhr, status, error) {
				console.error('Permisos AJAX (obtener)', status, error, xhr && xhr.responseText);
				$('#listaPermisos').html('<p class="text-danger text-center">Error de conexión al cargar los permisos.</p>');
			}
		});
	});

	$(document).on('click', '.deleteAgentBtn', function() {
		const button = $(this);
		const cedula = button.data('cedula');
		const nombre = button.data('nombre') || '';
		if (!cedula) {
			alert('No se pudo identificar al agente.');
			return;
		}
		const mensajeConfirmacion = nombre ? '¿Desea desactivar al agente ' + nombre + '?' : '¿Desea desactivar a este agente?';
		if (!confirm(mensajeConfirmacion)) {
			return;
		}

		button.prop('disabled', true);

		$.ajax({
			url: 'controlador/controladorUsuario.php',
			type: 'POST',
			dataType: 'json',
			data: { accion: 'desactivar_usuario', cedula: cedula },
			success: function(res) {
				if (res.success) {
					alert(res.message || 'Agente desactivado correctamente.');
					location.reload();
				} else {
					alert(res.message || 'No se pudo desactivar el agente.');
				}
			},
			error: function() {
				alert('Error de conexión al servidor.');
			},
			complete: function() {
				button.prop('disabled', false);
			}
		});
	});

	$('#btnGuardarPermisos').on('click', function() {
		const form = $('#permisosForm');
		const boton = $(this);
		boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');

		$.ajax({
			url: controladorPermisoUrl,
			type: 'POST',
			data: form.serialize() + '&accion=actualizar_permisos_agente',
			dataType: 'json',
			success: function(respuesta) {
				if (respuesta.estado === 'exito') {
					$('#permisosModal').modal('hide');
					alert(respuesta.mensaje || 'Permisos actualizados correctamente.');
				} else {
					alert('Error al guardar: ' + (respuesta.mensaje || 'Operación no completada.'));
				}
			},
			error: function(xhr, status, error) {
				console.error('Permisos AJAX (guardar)', status, error, xhr && xhr.responseText);
				alert('Error de conexión al guardar los permisos.');
			},
			complete: function() {
				boton.prop('disabled', false).text('Guardar Cambios');
			}
		});
	});

	$('#btnGuardarAgente').on('click', function() {
		const form = $('#nuevoAgenteForm');
		const boton = $(this);
		$('#respuestaCrearAgente').hide().html('');
		boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');

		const cedula = $('#agenteCedula').val().trim();
		const nombre = $('#agenteNombre').val().trim();
		const apellido = $('#agenteApellido').val().trim();
		const email = $('#agenteEmail').val().trim();
		const password = $('#agentePassword').val() || '';
		const passwordConfirm = $('#agentePasswordConfirm').val() || '';
		const telefono = $('#agenteTelefono').val().trim();

		const rePersona = /^V\d{7,8}$/i;
		const reEntidad = /^(J|G|E|EM)\d{7,8}-\d{1}$/i;
		const reEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		const reTelefono = /^[0-9\-\s\+]{7,20}$/;

		function showCreateError(msg) {
			$('#respuestaCrearAgente').show().html('<div class="alert alert-danger">' + msg + '</div>');
			boton.prop('disabled', false).text('Crear Agente');
		}

		if (!cedula) { showCreateError('Complete la cédula.'); return; }
		if (!nombre) { showCreateError('Complete el nombre.'); return; }
		if (!apellido) { showCreateError('Complete el apellido.'); return; }
		if (!email) { showCreateError('Complete el email.'); return; }
		if (!telefono) { showCreateError('Complete el teléfono.'); return; }
		if (!(rePersona.test(cedula) || reEntidad.test(cedula))) { showCreateError('Formato de cédula inválido. Ej: V12345678 o J12345678-9'); return; }
		if (!reEmail.test(email)) { showCreateError('Email inválido.'); return; }
		if (!reTelefono.test(telefono)) { showCreateError('Teléfono inválido.'); return; }
		if (!password || password.length < 8) { showCreateError('La contraseña debe tener al menos 8 caracteres.'); return; }
		if (password !== passwordConfirm) { showCreateError('Las contraseñas no coinciden.'); return; }

		$.ajax({
			url: 'controlador/controladorUsuario.php',
			type: 'POST',
			data: form.serialize() + '&accion=crear_usuario',
			dataType: 'json',
			success: function(res) {
				if (res.success) {
					$('#modalNuevoAgente').modal('hide');
					let msg = res.message || 'Agente creado correctamente.';
					if (res.password) {
						msg += '\nContraseña generada: ' + res.password;
					}
					alert(msg);
					location.reload();
				} else {
					$('#respuestaCrearAgente').show().html('<div class="alert alert-danger">' + (res.message || 'Error al crear agente.') + '</div>');
				}
			},
			error: function() {
				$('#respuestaCrearAgente').show().html('<div class="alert alert-danger">Error de conexión al servidor.</div>');
			},
			complete: function() {
				boton.prop('disabled', false).text('Crear Agente');
			}
		});
	});

	$(document).on('click', '.editAgentBtn', function() {
		const button = $(this);
		const cedula = button.data('cedula') || '';
		const nombre = button.data('nombre') || '';
		const apellido = button.data('apellido') || '';
		const email = button.data('email') || '';
		const telefono = button.data('telefono') || '';
		const nombreCompleto = [nombre, apellido].filter(Boolean).join(' ').trim();

		const formElement = document.getElementById('editarAgenteForm');
		if (formElement) {
			formElement.reset();
		}

		$('#modalLabelEditarAgente').text(nombreCompleto ? 'Editar Agente: ' + nombreCompleto : 'Editar Agente');
		$('#editCedulaOriginal').val(cedula);
		$('#editAgenteCedula').val(cedula);
		$('#editAgenteNombre').val(nombre);
		$('#editAgenteApellido').val(apellido);
		$('#editAgenteEmail').val(email);
		$('#editAgenteTelefono').val(telefono);
		$('#editAgentePassword').val('');
		$('#editAgentePasswordConfirm').val('');

		$('#respuestaEditarAgente').hide().html('');
		$('#editarAgenteForm .is-invalid').removeClass('is-invalid');
	});

	$('#btnActualizarAgente').on('click', function() {
		const form = $('#editarAgenteForm');
		const boton = $(this);
		$('#respuestaEditarAgente').hide().html('');
		boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');

		const cedulaOriginal = $('#editCedulaOriginal').val();
		const cedula = $('#editAgenteCedula').val().trim();
		const nombre = $('#editAgenteNombre').val().trim();
		const apellido = $('#editAgenteApellido').val().trim();
		const email = $('#editAgenteEmail').val().trim();
		const telefono = $('#editAgenteTelefono').val().trim();
		const password = $('#editAgentePassword').val() || '';
		const passwordConfirm = $('#editAgentePasswordConfirm').val() || '';

		const rePersona = /^V\d{7,8}$/i;
		const reEntidad = /^(J|G|E|EM)\d{7,8}-\d{1}$/i;
		const reEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		const reTelefono = /^[0-9\-\s\+]{7,20}$/;

		function showEditError(msg) {
			$('#respuestaEditarAgente').show().html('<div class="alert alert-danger">' + msg + '</div>');
			boton.prop('disabled', false).text('Guardar Cambios');
		}

		if (!cedula) { showEditError('Complete la cédula.'); return; }
		if (!nombre) { showEditError('Complete el nombre.'); return; }
		if (!apellido) { showEditError('Complete el apellido.'); return; }
		if (!email) { showEditError('Complete el email.'); return; }
		if (!telefono) { showEditError('Complete el teléfono.'); return; }
		if (!(rePersona.test(cedula) || reEntidad.test(cedula))) { showEditError('Formato de cédula inválido. Ej: V12345678 o J12345678-9'); return; }
		if (!reEmail.test(email)) { showEditError('Email inválido.'); return; }
		if (!reTelefono.test(telefono)) { showEditError('Teléfono inválido.'); return; }
		if (password && password.length < 8) { showEditError('La nueva contraseña debe tener al menos 8 caracteres.'); return; }
		if (password && password !== passwordConfirm) { showEditError('Las contraseñas no coinciden.'); return; }

		const formData = new FormData();
		formData.append('accion', 'actualizar_usuario');
		formData.append('cedula_original', cedulaOriginal);
		formData.append('cedula', cedula);
		formData.append('nombre', nombre);
		formData.append('apellido', apellido);
		formData.append('email', email);
		formData.append('telefono', telefono);
		if (password) {
			formData.append('password', password);
		}

		$.ajax({
			url: 'controlador/controladorUsuario.php',
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			dataType: 'json',
			success: function(res) {
				if (res.success) {
					$('#modalEditarAgente').modal('hide');
					alert(res.message || 'Agente actualizado correctamente.');
					location.reload();
				} else {
					$('#respuestaEditarAgente').show().html('<div class="alert alert-danger">' + (res.message || 'Error al actualizar agente.') + '</div>');
				}
			},
			error: function() {
				$('#respuestaEditarAgente').show().html('<div class="alert alert-danger">Error de conexión al servidor.</div>');
			},
			complete: function() {
				boton.prop('disabled', false).text('Guardar Cambios');
			}
		});
	});

	$('#nuevoAgenteForm input').on('blur', function() {
		const field = $(this);
		if (field.is('#agentePassword') || field.is('#agentePasswordConfirm')) {
			validatePasswords();
		} else if (field.is('#agenteCedula')) {
			validateCedula(field.val().trim());
		} else if (field.is('#agenteEmail')) {
			validateEmail(field.val().trim());
		}
	});

	function validatePasswords() {
		const pass = $('#agentePassword').val();
		const confirm = $('#agentePasswordConfirm').val();
		if (pass && confirm && pass !== confirm) {
			$('#agentePasswordConfirm').addClass('is-invalid');
			$('#agentePasswordConfirm').next('.invalid-feedback').text('Las contraseñas no coinciden.');
		} else {
			$('#agentePasswordConfirm').removeClass('is-invalid');
		}
	}

	function validateCedula(cedula) {
		const rePersona = /^V\d{7,8}$/i;
		const reEntidad = /^(J|G|E|EM)\d{7,8}-\d{1}$/i;
		if (cedula && !(rePersona.test(cedula) || reEntidad.test(cedula))) {
			$('#agenteCedula').addClass('is-invalid');
		} else {
			$('#agenteCedula').removeClass('is-invalid');
		}
	}

	function validateEmail(email) {
		const reEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		if (email && !reEmail.test(email)) {
			$('#agenteEmail').addClass('is-invalid');
		} else {
			$('#agenteEmail').removeClass('is-invalid');
		}
	}
});
</script>
