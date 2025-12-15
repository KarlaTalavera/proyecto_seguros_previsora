<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>

<div class="container-fluid">
  <h3>Reportes Operacionales</h3>
  <p class="mb-4">Reportes para la gestión de su actividad y cartera individual.</p>

  <div class="card mt-3">
    <div class="card-body">
      <div class="row">
        <!-- Card 1: Pólizas por Categoría (Agente) -->
        <div class="col-md-6 col-xl-4 mb-4">
          <div class="border rounded h-100 p-3 d-flex flex-column">
            <h6 class="mb-1">Mi Cartera por Categoría</h6>
            <p class="text-muted small mb-3">Resumen de su cartera por líneas de negocio para entender su distribución.</p>
            <div class="mt-auto btn-group btn-group-sm" role="group" aria-label="Exportar mis pólizas por categoría">
              <a class="btn btn-outline-primary" href="controlador/controladorReporte.php?accion=exportar_grafico&grafico=categoria&formato=pdf"><i class="fas fa-file-pdf mr-1"></i>PDF</a>
              <a class="btn btn-outline-success" href="controlador/controladorReporte.php?accion=exportar_grafico&grafico=categoria&formato=xlsx"><i class="fas fa-file-excel mr-1"></i>Excel</a>
            </div>
          </div>
        </div>

        <!-- Card 2: Pólizas por Estado (Agente) -->
        <div class="col-md-6 col-xl-4 mb-4">
          <div class="border rounded h-100 p-3 d-flex flex-column">
            <h6 class="mb-1">Mis Pólizas por Estado</h6>
            <p class="text-muted small mb-3">Monitoree cuántas de sus pólizas están activas, por vencer o pendientes.</p>
            <div class="mt-auto btn-group btn-group-sm" role="group" aria-label="Exportar mis pólizas por estado">
              <a class="btn btn-outline-primary" href="controlador/controladorReporte.php?accion=exportar_grafico&grafico=estado&formato=pdf"><i class="fas fa-file-pdf mr-1"></i>PDF</a>
              <a class="btn btn-outline-success" href="controlador/controladorReporte.php?accion=exportar_grafico&grafico=estado&formato=xlsx"><i class="fas fa-file-excel mr-1"></i>Excel</a>
            </div>
          </div>
        </div>

        <!-- Card 3: Tendencia de Siniestros (Agente) -->
        <div class="col-md-6 col-xl-4 mb-4">
          <div class="border rounded h-100 p-3 d-flex flex-column">
            <h6 class="mb-1">Mis Siniestros (Tendencia)</h6>
            <p class="text-muted small mb-3">Histórico de los siniestros gestionados en su cartera personal.</p>
            <div class="mt-auto btn-group btn-group-sm" role="group" aria-label="Exportar mi tendencia de siniestros">
              <a class="btn btn-outline-primary" href="controlador/controladorReporte.php?accion=exportar_grafico&grafico=siniestros&formato=pdf"><i class="fas fa-file-pdf mr-1"></i>PDF</a>
              <a class="btn btn-outline-success" href="controlador/controladorReporte.php?accion=exportar_grafico&grafico=siniestros&formato=xlsx"><i class="fas fa-file-excel mr-1"></i>Excel</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Fin Fila -->

</div>

<?php
$extra_scripts = '';
require_once __DIR__ . "/parte_inferior.php";
?>