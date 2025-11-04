<?php
// buttons.php — página que usa la plantilla compartida
// Ajusta $role si necesitas ver el menú de otro rol antes de incluir
// $role = 'Admin';
require_once __DIR__ . "/vista/parte_superior.php";
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Buttons</h1>
    <button class="btn btn-primary" onclick="openModal('Crear Botón','<p>Formulario ejemplo</p>')">Crear</button>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header">Ejemplos de botones</div>
            <div class="card-body">
                <button class="btn btn-primary mr-2">Primary</button>
                <button class="btn btn-success mr-2">Success</button>
                <button class="btn btn-danger mr-2">Danger</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/vista/parte_inferior.php"; ?>