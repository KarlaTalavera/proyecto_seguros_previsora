<?php
// Asegúrate de que las rutas sean correctas
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php'; 

/**
 * Clase que maneja la lógica de las peticiones relacionadas con el Usuario (Login, Logout, etc.).
 */
class ControladorUsuario {
    private $modeloUsuario;

    public function __construct() {
        // Iniciar la sesión para poder guardar los datos del usuario
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->modeloUsuario = new UserModel();
    }

    /**
     * Muestra la vista del formulario de inicio de sesión.
     */
    public function mostrarInicioSesion() {
        // Lógica para cargar el HTML/Template del formulario de login
        // require 'views/formulario_inicio_sesion.php'; 
        echo "<h1>Formulario de Inicio de Sesión</h1><p>Implementar aquí la lógica de la Vista (HTML).</p>";
    }

    /**
     * Procesa la solicitud POST del formulario de inicio de sesión.
     */
    public function manejarInicioSesion() {
        // 1. Recibir y validar datos
        $identificador = $_POST['identificador'] ?? ''; // Cédula o Email
        $clave = $_POST['password'] ?? '';

        if (empty($identificador) || empty($clave)) {
            $_SESSION['error'] = "Por favor, complete todos los campos.";
            // header('Location: index.php?controlador=usuario&accion=mostrarFormularioInicioSesion');
            echo "<p style='color:red;'>Error: Por favor, complete todos los campos.</p>";
            return;
        }

        // 2. Llamar al Modelo para autenticar. Retorna un objeto Usuario o false.
        $usuario = $this->modeloUsuario->login($identificador, $clave);

        if ($usuario) {
            // 3. Autenticación exitosa: Guardar datos en la sesión
            $_SESSION['usuario_conectado'] = true;
            
            // Guardamos el objeto Usuario en la sesión para acceso futuro
            $_SESSION['datos_usuario'] = $usuario; 
            
            // Redirigir según el rol del usuario, usando el getter
            switch ($usuario->getNombreRol()) { 
                case 'administrador':
                    $urlRedireccion = 'index.php?controlador=panel&accion=admin';
                    break;
                case 'agente':
                    $urlRedireccion = 'index.php?controlador=panel&accion=agente';
                    break;
                case 'asegurado':
                    $urlRedireccion = 'index.php?controlador=panel&accion=cliente';
                    break;
                default:
                    $urlRedireccion = 'index.php?controlador=inicio';
                    break;
            }
            
            // Redirección simulada
            // header("Location: " . $urlRedireccion);
            echo "<h2>¡Inicio de Sesión Exitoso!</h2>";
            echo "<pre>Bienvenido, {$usuario->getNombre()} ({$usuario->getNombreRol()}). Redirigiendo a: {$urlRedireccion}</pre>";
            
        } else {
            // 4. Autenticación fallida
            $_SESSION['error'] = "Cédula/Email o Contraseña incorrectos.";
            // header('Location: index.php?controlador=usuario&accion=mostrarFormularioInicioSesion');
            echo "<p style='color:red;'>Error: Cédula/Email o Contraseña incorrectos.</p>";
        }
    }
    
    /**
     * Cierra la sesión del usuario.
     */
    public function cerrarSesion() {
        session_destroy();
        // Redirigir al formulario de inicio de sesión
        // header('Location: index.php?controlador=usuario&accion=mostrarFormularioInicioSesion');
        echo "<h2>Sesión Cerrada.</h2>";
    }
}
?>
