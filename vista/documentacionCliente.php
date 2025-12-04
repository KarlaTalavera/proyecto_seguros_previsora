<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>

<style>
  .historial-tabs {
    margin-top: -0.5rem;
    margin-left: -0.5rem;
    gap: 0.25rem;
  }

  .historial-tabs .nav-link {
    border-radius: 999px;
    padding: 0.35rem 0.95rem;
    font-weight: 600;
    color: var(--neutral-600, #4d4d4d);
    background-color: rgba(15, 23, 42, 0.04);
    transition: all 0.2s ease;
  }

  .historial-tabs .nav-link:hover {
    background-color: rgba(15, 23, 42, 0.08);
  }

  .historial-tabs .nav-link.active {
    color: #ffffff;
    background-color: var(--primary-500, #4f46e5);
  }
</style>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-0">Historial y documentación</h3>
      <small class="text-muted">Aquí encontrarás tus pólizas vencidas o canceladas, así como el registro documental de tus siniestros.</small>
    </div>
  </div>

  <div class="card card-neo mb-4">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div>
        <span class="font-weight-bold d-block" id="historialTitle">Pólizas históricas</span>
        <span class="badge-soft" id="historialBadge" data-variant="neutral">Solo consulta</span>
      </div>
    </div>
    <div class="card-body">
      <ul class="nav nav-pills historial-tabs mb-3" role="tablist">
        <li class="nav-item">
          <a href="#" class="nav-link active historial-toggle" data-panel="polizas" role="tab">Pólizas históricas</a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link historial-toggle" data-panel="siniestros" role="tab">Siniestros documentados</a>
        </li>
      </ul>
      <div class="historial-panel" data-panel="polizas">
        <div class="table-responsive">
          <table id="historicoPolizas" class="table table-striped table-hover align-middle">
            <thead>
              <tr>
                <th>Número</th>
                <th>Producto</th>
                <th>Estado</th>
                <th>Vencimiento</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>POL-18990</td>
                <td>Vida Integral Familiar</td>
                <td><span class="badge-soft" data-variant="neutral">Expirada</span></td>
                <td>2023-08-30</td>
                <td class="table-action-buttons">
                  <button type="button" class="action-icon action-icon--perm historial-detalle-btn"
                          data-toggle="modal" data-target="#detalleHistorialModal"
                          data-tipo="Póliza"
                          data-numero="POL-18990"
                          data-descripcion="Cobertura de vida integral para núcleo familiar"
                          data-estado="Expirada"
                          data-fecha="2023-08-30"
                          data-monto="$280.00"
                          data-extra="Renovada en 2023 con un plan actualizado.">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button type="button" class="action-icon action-icon--edit descargar-historico"
                          data-numero="POL-18990" title="Descargar PDF">
                    <i class="fas fa-download"></i>
                  </button>
                </td>
              </tr>
              <tr>
                <td>POL-17540</td>
                <td>Comercio Seguro PyME</td>
                <td><span class="badge-soft" data-variant="rechazado">Cancelada</span></td>
                <td>2022-05-12</td>
                <td class="table-action-buttons">
                  <button type="button" class="action-icon action-icon--perm historial-detalle-btn"
                          data-toggle="modal" data-target="#detalleHistorialModal"
                          data-tipo="Póliza"
                          data-numero="POL-17540"
                          data-descripcion="Seguro patrimonial para pequeña empresa"
                          data-estado="Cancelada"
                          data-fecha="2022-05-12"
                          data-monto="$640.00"
                          data-extra="Cancelada a solicitud del asegurado por cese de operaciones.">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button type="button" class="action-icon action-icon--edit descargar-historico"
                          data-numero="POL-17540" title="Descargar PDF">
                    <i class="fas fa-download"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="historial-panel d-none" data-panel="siniestros">
        <div class="table-responsive">
          <table id="historicoSiniestros" class="table table-striped table-hover align-middle">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Póliza</th>
                <th>Estado</th>
                <th>Monto estimado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>2025-07-01</td>
                <td>POL-20745</td>
                <td><span class="badge-soft" data-variant="pendiente">En evaluación</span></td>
                <td>$2,400.00</td>
                <td class="table-action-buttons">
                  <button type="button" class="action-icon action-icon--perm historial-detalle-btn"
                          data-toggle="modal" data-target="#detalleHistorialModal"
                          data-tipo="Siniestro"
                          data-numero="SIN-5021"
                          data-descripcion="Accidente vehicular leve"
                          data-estado="En evaluación"
                          data-fecha="2025-07-01"
                          data-monto="$2,400.00"
                          data-extra="Perito asignado: José Rivas. Revisión programada para el 05/07.">
                    <i class="fas fa-eye"></i>
                  </button>
                </td>
              </tr>
              <tr>
                <td>2024-11-01</td>
                <td>POL-18990</td>
                <td><span class="badge-soft" data-variant="aprobado">Cerrado</span></td>
                <td>$0.00</td>
                <td class="table-action-buttons">
                  <button type="button" class="action-icon action-icon--perm historial-detalle-btn"
                          data-toggle="modal" data-target="#detalleHistorialModal"
                          data-tipo="Siniestro"
                          data-numero="SIN-4410"
                          data-descripcion="Cobertura de gastos funerarios"
                          data-estado="Cerrado"
                          data-fecha="2024-11-01"
                          data-monto="$0.00"
                          data-extra="Liquidación completada sin deducible.">
                    <i class="fas fa-eye"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-consistent" id="detalleHistorialModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle del registro</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <dl class="row mb-0">
          <dt class="col-sm-4">Tipo</dt>
          <dd class="col-sm-8" id="historialTipo">—</dd>

          <dt class="col-sm-4">Referencia</dt>
          <dd class="col-sm-8" id="historialNumero">—</dd>

          <dt class="col-sm-4">Estado</dt>
          <dd class="col-sm-8" id="historialEstado">—</dd>

          <dt class="col-sm-4">Fecha</dt>
          <dd class="col-sm-8" id="historialFecha">—</dd>

          <dt class="col-sm-4">Monto</dt>
          <dd class="col-sm-8" id="historialMonto">—</dd>

          <dt class="col-sm-4">Descripción</dt>
          <dd class="col-sm-8" id="historialDescripcion">—</dd>

          <dt class="col-sm-4">Notas</dt>
          <dd class="col-sm-8" id="historialExtra">—</dd>
        </dl>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-neo btn-neo--light" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn-neo btn-neo--primary" id="descargarDesdeHistorial">Descargar PDF</button>
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
      function initTable(selector){
        if (window.jQuery && \$.fn.DataTable) {
          return \$(selector).DataTable({
            paging: false,
            info: false,
            searching: false,
            order: [[0, 'desc']],
            language: {
              sEmptyTable: 'Sin registros disponibles',
              sZeroRecords: 'Sin resultados',
              sInfo: '',
              sInfoEmpty: '',
              sSearch: 'Buscar:',
              sLoadingRecords: 'Cargando...'
            }
          });
        }
        return null;
      }

      var tablaPolizas = initTable('#historicoPolizas');
      var tablaSiniestros = initTable('#historicoSiniestros');
      var ultimoRegistro = { numero: null, tipo: null };
      var toggles = document.querySelectorAll('.historial-toggle');
      var panels = document.querySelectorAll('.historial-panel');
      var titulo = document.getElementById('historialTitle');
      var badge = document.getElementById('historialBadge');

      function actualizarVista(target){
        panels.forEach(function(panel){
          if (panel.dataset.panel === target) {
            panel.classList.remove('d-none');
          } else {
            panel.classList.add('d-none');
          }
        });

        toggles.forEach(function(btn){
          var isActive = btn.getAttribute('data-panel') === target;
          btn.classList.toggle('active', isActive);
        });

        if (target === 'polizas') {
          titulo.textContent = 'Pólizas históricas';
          badge.textContent = 'Solo consulta';
          badge.setAttribute('data-variant', 'neutral');
          if (tablaPolizas) { tablaPolizas.columns.adjust(); }
        } else {
          titulo.textContent = 'Siniestros documentados';
          badge.textContent = 'Histórico';
          badge.setAttribute('data-variant', 'info');
          if (tablaSiniestros) { tablaSiniestros.columns.adjust(); }
        }
      }

      toggles.forEach(function(btn){
        btn.addEventListener('click', function(event){
          event.preventDefault();
          actualizarVista(btn.getAttribute('data-panel'));
        });
      });

      actualizarVista('polizas');

      \$('.historial-detalle-btn').on('click', function(){
        var btn = \$(this);
        ultimoRegistro.numero = btn.data('numero') || null;
        ultimoRegistro.tipo = btn.data('tipo') || null;
        \$('#historialTipo').text(btn.data('tipo') || '—');
        \$('#historialNumero').text(btn.data('numero') || '—');
        \$('#historialEstado').text(btn.data('estado') || '—');
        \$('#historialFecha').text(btn.data('fecha') || '—');
        \$('#historialMonto').text(btn.data('monto') || '—');
        \$('#historialDescripcion').text(btn.data('descripcion') || '—');
        \$('#historialExtra').text(btn.data('extra') || 'Sin notas adicionales.');
      });

      \$('.descargar-historico').on('click', function(){
        var numero = \$(this).data('numero');
        alert('Descargando documento asociado a ' + numero + ' (simulado).');
      });

      \$('#descargarDesdeHistorial').on('click', function(){
        var numero = ultimoRegistro.numero || 'registro';
        var tipo = ultimoRegistro.tipo || 'documento';
        alert('Descargando ' + tipo.toLowerCase() + ' ' + numero + ' (simulado).');
      });
    });
</script>
EOT;
require_once __DIR__ . "/parte_inferior.php";
?>
</body></html>
