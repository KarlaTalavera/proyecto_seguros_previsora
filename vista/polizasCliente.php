<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-0">Mis pólizas vigentes</h3>
      <small class="text-muted">Consulta tus coberturas activas, revisa los detalles y descarga la documentación al instante.</small>
    </div>
    <button id="descargarResumen" type="button" class="btn-neo btn-neo--primary">
      Descargar resumen
    </button>
  </div>

  <div class="card card-neo">
    <div class="card-body">
      <div class="table-responsive">
        <table id="myPolicies" class="table table-striped table-hover align-middle">
          <thead>
            <tr>
              <th>Número</th>
              <th>Producto</th>
              <th>Vigencia</th>
              <th>Estado</th>
              <th>Prima anual</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>POL-20301</td>
              <td>Combinado Residencial</td>
              <td>2026-01-10</td>
              <td><span class="badge-soft" data-variant="pendiente">Por vencer</span></td>
              <td>$350.00</td>
              <td class="table-action-buttons">
                <button type="button" class="action-icon action-icon--perm poliza-detalle-btn"
                        data-toggle="modal"
                        data-target="#detallePolizaModal"
                        data-numero="POL-20301"
                        data-producto="Combinado Residencial"
                        data-inicio="2025-01-11"
                        data-vencimiento="2026-01-10"
                        data-estado="Por vencer"
                        data-prima="$350.00"
                        data-cuotas="12 cuotas de $29.17"
                        data-coberturas="Incendio estructural, Robo contenido, Responsabilidad civil familiar">
                  <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="action-icon action-icon--edit poliza-descargar-btn"
                        data-numero="POL-20301" title="Descargar póliza">
                  <i class="fas fa-download"></i>
                </button>
              </td>
            </tr>
            <tr>
              <td>POL-20745</td>
              <td>Auto Integral Premium</td>
              <td>2025-09-18</td>
              <td><span class="badge-soft" data-variant="aprobado">Vigente</span></td>
              <td>$580.00</td>
              <td class="table-action-buttons">
                <button type="button" class="action-icon action-icon--perm poliza-detalle-btn"
                        data-toggle="modal"
                        data-target="#detallePolizaModal"
                        data-numero="POL-20745"
                        data-producto="Auto Integral Premium"
                        data-inicio="2024-09-19"
                        data-vencimiento="2025-09-18"
                        data-estado="Vigente"
                        data-prima="$580.00"
                        data-cuotas="4 cuotas de $145.00"
                        data-coberturas="Daños a terceros, Cobertura amplia, Grúa y auxilio vial 24/7">
                  <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="action-icon action-icon--edit poliza-descargar-btn"
                        data-numero="POL-20745" title="Descargar póliza">
                  <i class="fas fa-download"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-consistent" id="detallePolizaModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle de póliza</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <dl class="row mb-0">
          <dt class="col-sm-4">Número</dt>
          <dd class="col-sm-8" id="detalleNumero">—</dd>

          <dt class="col-sm-4">Producto</dt>
          <dd class="col-sm-8" id="detalleProducto">—</dd>

          <dt class="col-sm-4">Estado</dt>
          <dd class="col-sm-8" id="detalleEstado">—</dd>

          <dt class="col-sm-4">Inicio de cobertura</dt>
          <dd class="col-sm-8" id="detalleInicio">—</dd>

          <dt class="col-sm-4">Vencimiento</dt>
          <dd class="col-sm-8" id="detalleVencimiento">—</dd>

          <dt class="col-sm-4">Prima anual</dt>
          <dd class="col-sm-8" id="detallePrima">—</dd>

          <dt class="col-sm-4">Plan de pago</dt>
          <dd class="col-sm-8" id="detalleCuotas">—</dd>

          <dt class="col-sm-4">Coberturas incluidas</dt>
          <dd class="col-sm-8" id="detalleCoberturas">—</dd>
        </dl>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-neo btn-neo--light" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn-neo btn-neo--primary" id="descargarDesdeModal">Descargar póliza</button>
      </div>
    </div>
  </div>
</div>

<?php
$extra_scripts = <<<EOT
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
		document.addEventListener('DOMContentLoaded', function(){
			if (window.jQuery && \$.fn.DataTable) {
				\$('#myPolicies').DataTable({
					pageLength: 5,
					order: [[2, 'asc']],
					language: {
						sProcessing: 'Procesando...',
						sLengthMenu: 'Mostrar _MENU_ registros',
						sZeroRecords: 'No se encontraron resultados',
						sEmptyTable: 'Ningún dato disponible en esta tabla',
						sInfo: 'Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros',
						sInfoEmpty: 'Mostrando registros del 0 al 0 de un total de 0 registros',
						sInfoFiltered: '(filtrado de un total de _MAX_ registros)',
						sSearch: 'Buscar:',
						sLoadingRecords: 'Cargando...',
						oPaginate: {
							sFirst: 'Primero',
							sLast: 'Último',
							sNext: 'Siguiente',
							sPrevious: 'Anterior'
						},
						oAria: {
							sSortAscending: ': Activar para ordenar la columna de manera ascendente',
							sSortDescending: ': Activar para ordenar la columna de manera descendente'
						}
					}
				});
			}

			var ultimoNumeroSeleccionado = null;

			\$('.poliza-detalle-btn').on('click', function(){
				var btn = \$(this);
				ultimoNumeroSeleccionado = btn.data('numero') || null;
				\$('#detalleNumero').text(btn.data('numero') || '—');
				\$('#detalleProducto').text(btn.data('producto') || '—');
				\$('#detalleEstado').text(btn.data('estado') || '—');
				\$('#detalleInicio').text(btn.data('inicio') || '—');
				\$('#detalleVencimiento').text(btn.data('vencimiento') || '—');
				\$('#detallePrima').text(btn.data('prima') || '—');
				\$('#detalleCuotas').text(btn.data('cuotas') || '—');
				\$('#detalleCoberturas').text(btn.data('coberturas') || '—');
			});

			\$('.poliza-descargar-btn').on('click', function(){
				var numero = \$(this).data('numero');
				alert('Descargando póliza ' + numero + ' (simulado).');
			});

			\$('#descargarResumen').on('click', function(){
				alert('Generando resumen completo de pólizas (simulado).');
			});

			\$('#descargarDesdeModal').on('click', function(){
				var numero = ultimoNumeroSeleccionado || 'sin-identificar';
				alert('Descargando póliza ' + numero + ' (simulado).');
			});
		});
</script>
EOT;
require_once __DIR__ . "/parte_inferior.php";
?>
</body></html>
