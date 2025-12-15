<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/parte_superior.php';
?>

<div class="container-fluid">
  <h3>Reportes Gerenciales</h3>
  <p class="mb-4">Reportes de alto nivel para la toma de decisiones estratégicas.</p>
  
  <!-- Fila de Reportes Gerenciales -->
  <div class="row">
    <!-- Card 1: Dashboard Gerencial -->
    <div class="col-12 mb-4">
      <div class="card">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start">
          <div class="mb-3 mb-md-0">
            <h5 class="card-title mb-1">Dashboard Gerencial Completo</h5>
            <p class="card-text text-muted small mb-0">Descargue todos los indicadores y tablas del panel en un único archivo PDF o Excel.</p>
          </div>
          <div class="btn-group" role="group" aria-label="Exportar dashboard gerencial">
            <a class="btn btn-outline-primary" href="controlador/controladorReporte.php?accion=exportar_dashboard&formato=pdf">
              <i class="fas fa-file-pdf mr-1"></i> PDF
            </a>
            <a class="btn btn-outline-success" href="controlador/controladorReporte.php?accion=exportar_dashboard&formato=xlsx">
              <i class="fas fa-file-excel mr-1"></i> Excel
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card mt-3">
    <div class="card-body">
      <h5 class="card-title mb-3">Gráficas Individuales</h5>
      <div class="row">
        <!-- Card 2: Ranking de Productividad -->
        <div class="col-md-6 col-xl-4 mb-4">
          <div class="border rounded h-100 p-3 d-flex flex-column">
            <h6 class="mb-1">Ranking de Productividad</h6>
            <p class="text-muted small mb-3">Top de agentes por pólizas gestionadas y monto de primas en los últimos 12 meses.</p>
            <div class="mt-auto btn-group btn-group-sm" role="group" aria-label="Exportar ranking de productividad">
              <a class="btn btn-outline-primary" href="controlador/controladorReporte.php?accion=exportar_grafico&grafico=ranking&formato=pdf"><i class="fas fa-file-pdf mr-1"></i>PDF</a>
              <a class="btn btn-outline-success" href="controlador/controladorReporte.php?accion=exportar_grafico&grafico=ranking&formato=xlsx"><i class="fas fa-file-excel mr-1"></i>Excel</a>
            </div>
          </div>
        </div>
        
        <!-- Card 3: Balance de Primas vs. Siniestros -->
        <div class="col-md-6 col-xl-4 mb-4">
          <div class="border rounded h-100 p-3 d-flex flex-column">
            <h6 class="mb-1">Balance de Primas vs. Siniestros</h6>
            <p class="text-muted small mb-3">Comparativo mensual de primas cobradas contra siniestros pagados.</p>
            <div class="mt-auto btn-group btn-group-sm" role="group" aria-label="Exportar balance de primas vs. siniestros">
              <a class="btn btn-outline-primary" href="controlador/controladorReporte.php?accion=exportar_grafico&grafico=balance&formato=pdf"><i class="fas fa-file-pdf mr-1"></i>PDF</a>
              <a class="btn btn-outline-success" href="controlador/controladorReporte.php?accion=exportar_grafico&grafico=balance&formato=xlsx"><i class="fas fa-file-excel mr-1"></i>Excel</a>
            </div>
          </div>
        </div>

        <!-- Espacio reservado para un futuro tercer reporte gerencial si se necesita -->
        <div class="col-md-6 col-xl-4 mb-4">
          <!-- Puedes añadir otro reporte aquí -->
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