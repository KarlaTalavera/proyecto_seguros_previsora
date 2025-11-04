<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Solicitudes</h3>
    <div>
      <!-- boton principal para crear solicitud de poliza o siniestro -->
      <button class="btn btn-outline-primary mr-2" data-toggle="modal" data-target="#solicitarPolizaModal"><i class="fas fa-file-alt mr-1"></i> Solicitar Póliza</button>
      <button class="btn btn-outline-danger" data-toggle="modal" data-target="#reportarSiniestroModal"><i class="fas fa-ambulance mr-1"></i> Reportar Siniestro</button>
    </div>
  </div>

  <!-- tabla de solicitudes del cliente -->
  <div class="card">
    <div class="card-body">
      <table id="solicitudesTable" class="table table-striped w-100">
        <thead>
          <tr><th>ID</th><th>Tipo</th><th>Producto / Detalle</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
          <tr>
            <td>S-9001</td>
            <td>Poliza</td>
            <td>RCV - solicitud de cobertura amplia</td>
            <td>2025-09-01</td>
            <td><span class="badge badge-warning">En espera</span></td>
            <td>
              <button class="btn btn-sm btn-info" data-action="ver" data-id="S-9001">Ver</button>
              <button class="btn btn-sm btn-secondary" data-action="cancelar" data-id="S-9001">Cancelar</button>
            </td>
          </tr>
          <tr>
            <td>S-9002</td>
            <td>Siniestro</td>
            <td>Accidente vehicular - solicita cita perito</td>
            <td>2025-08-20</td>
            <td><span class="badge badge-success">Asignado</span></td>
            <td>
              <button class="btn btn-sm btn-info" data-action="ver" data-id="S-9002">Ver</button>
              <button class="btn btn-sm btn-primary" data-action="agendar" data-id="S-9002">Ver cita</button>
            </td>
          </tr>
          <tr>
            <td>S-9003</td>
            <td>Poliza</td>
            <td>Combinado Residencial - cotizacion</td>
            <td>2025-07-15</td>
            <td><span class="badge badge-secondary">Rechazado</span></td>
            <td>
              <button class="btn btn-sm btn-info" data-action="ver" data-id="S-9003">Ver</button>
            </td>
          </tr>
          <!-- mas filas ficticias -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- modal: solicitar poliza -->
<div class="modal fade" id="solicitarPolizaModal"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Solicitar Póliza</h5><button class="close" data-dismiss="modal">&times;</button></div>
  <div class="modal-body">
    <!-- nota: formulario sencillo que simula envio de solicitud -->
    <form id="formSolicitarPoliza">
      <div class="form-group">
        <label>Ramo</label>
        <select class="form-control" name="ramo">
          <option>Personas</option>
          <option>Automóvil</option>
          <option>Patrimoniales</option>
        </select>
      </div>
      <div class="form-group"><label>Producto</label><input class="form-control" name="producto"></div>
      <div class="form-group"><label>Descripcion / comentarios</label><textarea class="form-control" name="descripcion"></textarea></div>
      <div class="form-group"><label>Contacto</label><input class="form-control" name="contacto" placeholder="email o telefono"></div>
    </form>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button id="sendPoliza" class="btn btn-primary">Enviar Solicitud</button></div>
</div></div></div>

<!-- modal: reportar siniestro -->
<div class="modal fade" id="reportarSiniestroModal"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Reportar Siniestro</h5><button class="close" data-dismiss="modal">&times;</button></div>
  <div class="modal-body">
    <!-- nota: este formulario simula la creacion de un siniestro -->
    <form id="formReportarSiniestro">
      <div class="form-group"><label>Poliza asociada</label><input class="form-control" name="poliza"></div>
      <div class="form-group"><label>Tipo de siniestro</label><input class="form-control" name="tipo"></div>
      <div class="form-group"><label>Descripcion</label><textarea class="form-control" name="descripcion"></textarea></div>
      <div class="form-row">
        <div class="form-group col"><label>Fecha</label><input type="date" class="form-control" name="fecha"></div>
        <div class="form-group col"><label>Lugar</label><input class="form-control" name="lugar"></div>
      </div>
    </form>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button id="sendSiniestro" class="btn btn-danger">Reportar</button></div>
</div></div></div>

<?php
$extra_scripts = <<<EOT
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
// inicializacion y manejadores
// nota: usamos datatables para paginar y buscar en la lista de solicitudes
\$(function(){
  if (\$.fn.DataTable) { \$('#solicitudesTable').DataTable({ pageLength: 5 }); }

  // nota: simulamos envio de solicitud de poliza
  \$('#sendPoliza').on('click', function(){
    // en una implementacion real aqui haria ajax hacia backend
    alert('Solicitud de poliza enviada (simulado).');
    \$('#solicitarPolizaModal').modal('hide');
  });

  // nota: simulamos reporte de siniestro
  \$('#sendSiniestro').on('click', function(){
    alert('Siniestro reportado (simulado). Un agente se pondra en contacto.');
    \$('#reportarSiniestroModal').modal('hide');
  });

  // nota: manejo basico de botones en la tabla
  \$('#solicitudesTable').on('click', 'button[data-action]', function(){
    var action = \$(this).data('action'), id = \$(this).data('id');
    if (action === 'ver') { alert('ver solicitud: ' + id + ' (simulado)'); }
    if (action === 'cancelar') { if (confirm('Cancelar solicitud ' + id + '?')) { alert('Solicitud cancelada (simulado).'); } }
    if (action === 'agendar') { alert('mostrar cita / detalles (simulado) para ' + id); }
  });
});
</script>
EOT;
require_once __DIR__ . "/parte_inferior.php";
?>
