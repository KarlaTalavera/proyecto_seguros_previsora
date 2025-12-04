<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>

<div class="container-fluid">
  <h3>Reportes y exportaciones</h3>

  <div class="card mt-3">
    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start">
      <div class="mb-3 mb-md-0">
        <h5 class="card-title mb-1">Dashboard gerencial completo</h5>
        <p class="card-text text-muted small mb-0">Descargue todos los indicadores y tablas del panel en un único archivo PDF o Excel.</p>
      </div>
      <div class="btn-group" role="group" aria-label="Exportar dashboard gerencial">
        <a class="btn btn-outline-primary" href="controlador/controladorReporte.php?accion=exportar_dashboard&amp;formato=pdf">
          <i class="fas fa-file-pdf mr-1"></i> PDF
        </a>
        <a class="btn btn-outline-success" href="controlador/controladorReporte.php?accion=exportar_dashboard&amp;formato=xlsx">
          <i class="fas fa-file-excel mr-1"></i> Excel
        </a>
      </div>
    </div>
  </div>

  <div class="card mt-3">
    <div class="card-body">
      <h5 class="card-title mb-3">Descargar gráficas individuales</h5>
      <p class="text-muted small mb-4">Seleccione la gráfica y el formato deseado para compartir únicamente el análisis que necesita.</p>
      <div class="row">
        <div class="col-md-6 col-xl-3 mb-4">
          <div class="border rounded h-100 p-3 d-flex flex-column">
            <h6 class="mb-1">Pólizas por ramo</h6>
            <p class="text-muted small mb-3">Detalle de pólizas agrupadas por ramo de seguros.</p>
            <div class="mt-auto btn-group btn-group-sm" role="group" aria-label="Exportar pólizas por ramo">
              <a class="btn btn-outline-primary" href="controlador/controladorReporte.php?accion=exportar_grafico&amp;grafico=ramo&amp;formato=pdf"><i class="fas fa-file-pdf mr-1"></i>PDF</a>
              <a class="btn btn-outline-success" href="controlador/controladorReporte.php?accion=exportar_grafico&amp;grafico=ramo&amp;formato=xlsx"><i class="fas fa-file-excel mr-1"></i>Excel</a>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
          <div class="border rounded h-100 p-3 d-flex flex-column">
            <h6 class="mb-1">Pólizas por estado</h6>
            <p class="text-muted small mb-3">Distribución de pólizas según su estado operativo.</p>
            <div class="mt-auto btn-group btn-group-sm" role="group" aria-label="Exportar pólizas por estado">
              <a class="btn btn-outline-primary" href="controlador/controladorReporte.php?accion=exportar_grafico&amp;grafico=estado&amp;formato=pdf"><i class="fas fa-file-pdf mr-1"></i>PDF</a>
              <a class="btn btn-outline-success" href="controlador/controladorReporte.php?accion=exportar_grafico&amp;grafico=estado&amp;formato=xlsx"><i class="fas fa-file-excel mr-1"></i>Excel</a>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
          <div class="border rounded h-100 p-3 d-flex flex-column">
            <h6 class="mb-1">Ranking de productividad</h6>
            <p class="text-muted small mb-3">Top de agentes por pólizas gestionadas y monto de primas.</p>
            <div class="mt-auto btn-group btn-group-sm" role="group" aria-label="Exportar ranking de productividad">
              <a class="btn btn-outline-primary" href="controlador/controladorReporte.php?accion=exportar_grafico&amp;grafico=ranking&amp;formato=pdf"><i class="fas fa-file-pdf mr-1"></i>PDF</a>
              <a class="btn btn-outline-success" href="controlador/controladorReporte.php?accion=exportar_grafico&amp;grafico=ranking&amp;formato=xlsx"><i class="fas fa-file-excel mr-1"></i>Excel</a>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
          <div class="border rounded h-100 p-3 d-flex flex-column">
            <h6 class="mb-1">Tendencia de siniestros</h6>
            <p class="text-muted small mb-3">Serie histórica de siniestros registrados por mes.</p>
            <div class="mt-auto btn-group btn-group-sm" role="group" aria-label="Exportar tendencia de siniestros">
              <a class="btn btn-outline-primary" href="controlador/controladorReporte.php?accion=exportar_grafico&amp;grafico=siniestros&amp;formato=pdf"><i class="fas fa-file-pdf mr-1"></i>PDF</a>
              <a class="btn btn-outline-success" href="controlador/controladorReporte.php?accion=exportar_grafico&amp;grafico=siniestros&amp;formato=xlsx"><i class="fas fa-file-excel mr-1"></i>Excel</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$extra_scripts = '';
require_once __DIR__ . "/parte_inferior.php";
?>
