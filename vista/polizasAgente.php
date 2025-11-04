<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>

<div class="container-fluid">
  <div class="d-flex justify-content-between mb-3">
    <h3>Mi Cartera</h3>
    <div>
      <button class="btn btn-secondary mr-2" id="exportCsv">Exportar CSV</button>
      <button class="btn btn-primary" data-toggle="modal" data-target="#cotizacionModal">Crear Cotización</button>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <table id="carteraTable" class="table table-striped w-100">
        <thead><tr><th>#</th><th>Producto</th><th>Cliente</th><th>Vencimiento</th><th>Prima</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          <tr><td>2001</td><td>RCV</td><td>Pedro</td><td>2025-12-01</td><td>$120</td><td><span class="badge badge-success">Activa</span></td>
            <td>
              <button class="btn btn-sm btn-outline-primary" data-action="editar" data-id="2001">Editar</button>
              <button class="btn btn-sm btn-outline-secondary" data-action="pdf" data-id="2001">PDF</button>
            </td></tr>
          <tr><td>2002</td><td>Automóvil</td><td>María López</td><td>2025-08-20</td><td>$220</td><td><span class="badge badge-warning">Renovar</span></td>
            <td>
              <button class="btn btn-sm btn-outline-primary" data-action="editar" data-id="2002">Editar</button>
              <button class="btn btn-sm btn-outline-secondary" data-action="pdf" data-id="2002">PDF</button>
            </td></tr>
          <tr><td>2003</td><td>Combinado Residencial</td><td>Empresa XYZ</td><td>2026-02-14</td><td>$450</td><td><span class="badge badge-success">Activa</span></td>
            <td>
              <button class="btn btn-sm btn-outline-primary" data-action="editar" data-id="2003">Editar</button>
              <button class="btn btn-sm btn-outline-secondary" data-action="pdf" data-id="2003">PDF</button>
            </td></tr>
          <tr><td>Q-3001</td><td>RCV (Cot.)</td><td>Carlos Pérez</td><td>—</td><td>$95</td><td><span class="badge badge-info">Cotización</span></td>
            <td>
              <button class="btn btn-sm btn-outline-success" data-action="convertir" data-id="q-3001">Convertir</button>
              <button class="btn btn-sm btn-outline-secondary" data-action="pdf" data-id="q-3001">PDF</button>
            </td></tr>
          <!-- más filas ficticias -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Cotización (sin cambios funcionales) -->
<div class="modal fade" id="cotizacionModal"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Crear Cotización</h5><button class="close" data-dismiss="modal">&times;</button></div>
  <div class="modal-body"><form id="quoteForm"><div class="form-group"><label>Cliente</label><input class="form-control" name="cliente"></div><div class="form-group"><label>Producto</label><input class="form-control" name="producto"></div><div class="form-group"><label>Prima estimada</label><input class="form-control" name="prima"></div></form></div>
  <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button id="saveQuote" class="btn btn-primary">Crear cotización</button></div>
</div></div></div>

<?php
$extra_scripts = <<<EOT
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
\$(function(){
  if (\$.fn.DataTable) {\$('#carteraTable').DataTable({ pageLength:10 });}
  \$('#saveQuote').on('click', function(){ alert('Cotización creada (simulado).'); \$('#cotizacionModal').modal('hide'); });
  \$('#exportCsv').on('click', function(){
    alert('Exportar CSV (simulado).');
  });
});
</script>
EOT;
require_once __DIR__ . "/parte_inferior.php";
?>
