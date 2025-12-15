<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>

<div class="container-fluid">
  <h3>Reportes de Supervisión</h3>
  <p class="mb-4">Reportes para el seguimiento y control de la operativa del negocio.</p>

  <div class="card mt-3">
    <div class="card-body">
      <div class="row">
        <!-- Card 1: Pólizas por Categoría -->
        <div class="col-md-6 col-xl-4 mb-4">
          <div class="border rounded h-100 p-3 d-flex flex-column">
            <h6 class="mb-1">Pólizas por Categoría</h6>
            <p class="text-muted small mb-3">Detalle de todas las pólizas de la empresa agrupadas por categoría de seguro.</p>
            <div class="mt-auto btn-group btn-group-sm" role="group" aria-label="Exportar pólizas por categoría">
              <a class="btn btn-outline-primary" href="controlador/controladorReporte.php?accion=exportar_grafico&grafico=categoria&formato=pdf"><i class="fas fa-file-pdf mr-1"></i>PDF</a>
              <a class="btn btn-outline-success" href="controlador/controladorReporte.php?accion=exportar_grafico&grafico=categoria&formato=xlsx"><i class="fas fa-file-excel mr-1"></i>Excel</a>
            </div>
          </div>
        </div>

        <!-- Card 2: Pólizas por Estado -->
        <div class="col-md-6 col-xl-4 mb-4">
          <div class="border rounded h-100 p-3 d-flex flex-column">
            <h6 class="mb-1">Pólizas por Estado</h6>
            <p class="text-muted small mb-3">Distribución de todas las pólizas de la empresa según su estado operativo.</p>
            <div class="mt-auto btn-group btn-group-sm" role="group" aria-label="Exportar pólizas por estado">
              <a class="btn btn-outline-primary" href="controlador/controladorReporte.php?accion=exportar_grafico&grafico=estado&formato=pdf"><i class="fas fa-file-pdf mr-1"></i>PDF</a>
              <a class="btn btn-outline-success" href="controlador/controladorReporte.php?accion=exportar_grafico&grafico=estado&formato=xlsx"><i class="fas fa-file-excel mr-1"></i>Excel</a>
            </div>
          </div>
        </div>

        <!-- Card 3: Tendencia de Siniestros -->
        <div class="col-md-6 col-xl-4 mb-4">
          <div class="border rounded h-100 p-3 d-flex flex-column">
            <h6 class="mb-1">Tendencia de Siniestros</h6>
            <p class="text-muted small mb-3">Serie histórica de todos los siniestros de la empresa registrados por mes.</p>
            <div class="mt-auto btn-group btn-group-sm" role="group" aria-label="Exportar tendencia de siniestros">
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