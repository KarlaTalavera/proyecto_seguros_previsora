<?php
// Activar errores para ver qué pasa
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
// Simular sesión de administrador
$_SESSION['datos_usuario'] = (object)[
    'getNombreRol' => function() { return 'administrador'; },
    'getCedula' => function() { return 'V12345678'; }
];
$_SESSION['usuario_conectado'] = true;

require_once 'modelo/ModeloAsegurado.php';

$modelo = new ModeloAsegurado();

echo "<h1>Prueba de creación de asegurado</h1>";

// Datos de prueba
$datos_prueba = [
    'id_poliza' => 100, // Usa un ID de póliza que exista
    'cedula' => 'V12345679',
    'nombre' => 'Juan',
    'apellido' => 'Pérez',
    'fecha_nacimiento' => '1990-01-15',
    'parentesco' => 'Hijo',
    'sexo' => 'M'
];

echo "<h2>Datos a insertar:</h2>";
echo "<pre>";
print_r($datos_prueba);
echo "</pre>";

// Probar crear
$resultado = $modelo->crearAsegurado($datos_prueba);

echo "<h2>Resultado:</h2>";
echo "<pre>";
print_r($resultado);
echo "</pre>";

// Verificar si hay pólizas
echo "<h2>Pólizas disponibles:</h2>";
$polizas = $modelo->obtenerPolizasParaAsegurado();
echo "<pre>";
print_r($polizas);
echo "</pre>";
?>