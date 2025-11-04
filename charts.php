<?php

require_once __DIR__ . "/vista/parte_superior.php";
?>

<h1 class="h3 mb-2 text-gray-800">Charts</h1>
<p class="mb-4">Chart.js demo - ejemplos básicos de gráficas.</p>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Earnings Overview</h6>
            </div>
            <div class="card-body">
                <canvas id="myAreaChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Revenue Sources</h6>
            </div>
            <div class="card-body">
                <canvas id="myPieChart"></canvas>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/vista/parte_inferior.php"; ?>