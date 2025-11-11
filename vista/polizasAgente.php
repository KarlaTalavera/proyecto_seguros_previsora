<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
require_once dirname(__DIR__) . '/modelo/modeloPoliza.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';

$modeloPoliza = new ModeloPoliza();
$cedula_agente = $_SESSION['agente_cedula'] ?? 'V-12345678'; 
$polizas = $modeloPoliza->obtenerPolizasDeAgente($cedula_agente) ?: [];
?>

<div class="container-fluid">
  <div class="d-flex justify-content-between mb-3">
    <h3>Mi Cartera de Pólizas</h3> <div>
      <button class="btn btn-secondary mr-2" id="exportCsv">Exportar CSV</button>
      <button class="btn btn-primary" data-toggle="modal" data-target="#cotizacionModal">Crear Cotización</button>
    </div>
  </div>

  <div class="card">
  <div class="card-body">
    <table id="carteraTable" class="table table-striped w-100">
      <thead>
        <tr><th>ID Póliza</th><th>Producto</th><th>Cliente</th><th>Vencimiento</th><th>Prima</th><th>Estado</th><th>Acciones</th></tr>
      </thead>
      <tbody>
        <?php if (!empty($polizas)): ?>
          <?php foreach ($polizas as $poliza): 
              $badge_class = match ($poliza['estado']) {
                  'Activa' => 'badge-success',
                  'Vencida' => 'badge-danger',
                  default => 'badge-warning', 
              };
              $estado_html = '<span class="badge ' . $badge_class . '">' . htmlspecialchars($poliza['estado']) . '</span>';
              $prima_formato = '$' . number_format($poliza['prima'], 2, ',', '.');
          ?>
              <tr>
                  <td><?php echo htmlspecialchars($poliza['id']); ?></td>
                  <td><?php echo htmlspecialchars($poliza['producto']); ?></td>
                  <td><?php echo htmlspecialchars($poliza['cliente']); ?></td>
                  <td><?php echo htmlspecialchars($poliza['vencimiento']); ?></td>
                  <td><?php echo $prima_formato; ?></td>
                  <td><?php echo $estado_html; ?></td>
                  <td>
                      <button class="btn btn-sm btn-outline-primary" data-action="editar" data-id="<?php echo $poliza['id']; ?>">Editar</button>
                      <button class="btn btn-sm btn-outline-secondary" data-action="pdf" data-id="<?php echo $poliza['id']; ?>">PDF</button>
                  </td>
              </tr>
          <?php endforeach; ?>
        <?php endif; ?> </tbody>
    </table>
  </div>
</div>
<!-- Modal Cotización (sin cambios funcionales) -->
<div class="modal fade" id="cotizacionModal">
    <div class="modal-dialog modal-lg modal-dialog-centered"> <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Nueva Póliza</h5>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="crearPolizaForm">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="numero_poliza">Número de Póliza *</label>
                            <input class="form-control" id="numero_poliza" name="numero_poliza" placeholder="Ej: PRED-2025-001" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="id_tipo_poliza">Producto / Tipo de Póliza *</label>
                            <select class="form-control" id="id_tipo_poliza" name="id_tipo_poliza" required>
                                <option value="" disabled selected>Seleccione un producto</option>
                                <?php
                                $tipos_poliza = $modeloPoliza->obtenerTiposPoliza() ?: [];
                                foreach ($tipos_poliza as $tipo): ?>
                                    <option value="<?php echo htmlspecialchars($tipo['id_tipo_poliza']); ?>">
                                        <?php echo htmlspecialchars($tipo['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                    
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="cedula_cliente">Cédula del Cliente *</label>
                            <input class="form-control" id="cedula_cliente" name="cedula_cliente" placeholder="V-12345678" required>
                            <small class="form-text text-muted">Asegúrese de que el cliente esté registrado.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="fecha_vencimiento">Fecha de Vencimiento *</label>
                            <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="prima_anual">Prima Anual ($) *</label>
                            <input class="form-control" id="prima_anual" name="prima_anual" type="number" step="0.01" min="0" placeholder="Ej: 120.00" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="monto_asegurado">Monto Asegurado ($)</label>
                            <input class="form-control" id="monto_asegurado" name="monto_asegurado" type="number" step="0.01" min="0" placeholder="Ej: 50000.00">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="estado_poliza">Estado</label>
                            <select class="form-control" id="estado_poliza" name="estado">
                                <option value="Activa" selected>Activa</option>
                                <option value="Pendiente">Pendiente</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button id="savePoliza" type="submit" class="btn btn-success">Guardar Póliza</button>
                
            </div>
        </div>
    </div>
</div>
<?php
$extra_scripts = <<<EOT
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
\$(function(){
  if (\$.fn.DataTable) {
    // Inicialización de DataTables para ordenar y buscar el contenido ya renderizado por PHP
    \$('#carteraTable').DataTable({ 
      pageLength:10,
      language: { // CLAVE: Traducción DataTables
            "processing": "Procesando...", "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados en la tabla.", "emptyTable": "No hay datos disponibles en la tabla",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros", "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros totales)", "search": "Buscar:", "loadingRecords": "Cargando...",
            "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" },
            "aria": { "sortAscending": ": Activar para ordenar la columna de manera ascendente", "sortDescending": ": Activar para ordenar la columna de manera descendente" }
        }
    });
  }
});
</script>
EOT;
require_once __DIR__ . '/parte_inferior.php';
?>
