<?php
// usaremos session_start() solo si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../modelo/modeloUsuario.php'; 
require_once '../modelo/modeloPermiso.php';

// Inicializa la variable de error
$error = '';
$usuario_ingresado = '';

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Recibir y limpiar datos
    // Usamos 'identificador' y 'password' como claves del POST para coincidir con la lógica del modelo
    $identificador = trim($_POST['identificador'] ?? ''); // Cédula o Email
    $clave = trim($_POST['clave'] ?? ''); // Contraseña

    $usuario_ingresado = htmlspecialchars($identificador); // Para rellenar el campo en caso de error

    // 2. Revisar si los campos están vacíos
    if (empty($identificador) || empty($clave)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // 3. Crear el modelo y buscar/autenticar al usuario
        $modelo = new modeloUsuario();
        // El método login retorna un objeto Usuario si es exitoso
        $usuario = $modelo->login($identificador, $clave); 

        // 4. Si la autenticación es exitosa
        if ($usuario) {
            
            // 5. Autenticación exitosa: Guardar datos esenciales en la sesión
            $_SESSION['usuario_conectado'] = true;
            // Guardamos el objeto Usuario completo para acceso futuro
            $_SESSION['datos_usuario'] = $usuario; 
            $_SESSION['permisos_usuario'] = [];
            
           
            $rol = $usuario->getNombreRol();

            if ($rol === 'agente') {
                try {
                    $permisoModelo = new ModeloPermiso();
                    $permisosActivos = $permisoModelo->obtenerNombresPermisosDeAgente($usuario->getCedula());
                    if (is_array($permisosActivos)) {
                        $_SESSION['permisos_usuario'] = $permisosActivos;
                    }
                } catch (Exception $ex) {
                    error_log('No se pudieron cargar los permisos del agente: ' . $ex->getMessage());
                    $_SESSION['permisos_usuario'] = [];
                }
            }

            switch ($rol) {
                case 'administrador':
                    header('Location: ../index.php?modulo=admin');
                    break;
                case 'agente':
                    header('Location: ../index.php?modulo=agente');
                    break;
                case 'asegurado':
                    header('Location: ../index.php?modulo=cliente');
                    break;
                default:
                    $error = "Rol de usuario no reconocido. Contacte a soporte.";
                    break;
            }
            exit();

        } else {
            // 7. Autenticación fallida
            $error = "Cédula/Email o Contraseña incorrectos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión - La Previsora</title>
    <!-- Incluye Bootstrap (opcional, si se usa solo para el header) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Iconos LINI ICONS (de tu ejemplo) -->
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet">
    <!-- Iconos Font Awesome (de tu ejemplo) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* Estilos del Segundo Archivo */
        body {
            background-image: url(../img/fondologin.svg);
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            margin: 0;
            min-height: 100vh; /* Ajustado a 100vh */
            font-family: 'Poppins', sans-serif;
        }
        .login-center-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card-horizontal {
            display: flex;
            flex-direction: row;
            background: #c4c4d4ff; 
            border-radius: 22px;
            box-shadow: 0 6px 32px 0 rgba(0,0,0,0.10);
            overflow: hidden;
            min-width: 600px;
            max-width: 800px;
            width: 80vw;
        }
        .login-card-photo {
            background: #c4c4d4ff; 
            display: flex;
            align-items: center; /* Alineado al centro para la imagen */
            justify-content: center;
            min-width: 400px;
            max-width: 420px;
            padding: 0;
            height: 100%;
        }
        .login-card-photo img {
            width: 90%; /* Ajuste para que la imagen no se estire totalmente */
            height: auto;
            max-height: 100%;
            display: block;
            object-fit: contain;
            padding: 1rem;
        }
        .login-card-form {
            flex: 1 1 0;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 2.5rem;
        }
        .login-formbox {
            width: 100%;
            max-width: 340px;
            background: #c4c4d4ff; 
            border-radius: 16px;
            padding: 2rem 1.5rem 1.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
        }
        .login-error {
            color: #d12e2eff; /* Rojo más visible para errores */
            background-color: #ffe5e5;
            border: 1px solid #d12e2eff;
            padding: 0.5rem;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .login-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 600;
            color: #1c1d49ff;
            margin-bottom: 0.7rem;
            letter-spacing: 1px;
        }
        .login-inputbox {
            position: relative;
            margin-bottom: 0.3rem;
            width: 100%;
            overflow: visible;
        }

        .login-input:focus {
            border-bottom: 2px solid #334da3ff;
        }

        .login-inputbox i {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: #838383ff;
            font-size: 1.2rem;
            pointer-events: none;
        }


        .login-input {
            width: 100%;
            border: none;
            border-bottom: 2px solid #6e6d6dff;
            background: transparent;
            padding: 1.2rem 2.5rem 0.3rem 0.2rem;
            font-size: 1rem;
            color: #3a2c2c;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.2s;
            height: 3rem;
        }

        .login-label {
            position: absolute;
            left: 0.2rem;
            top: 1.2rem; 
            font-size: 1rem;
            color: #575656ff;
            pointer-events: none;
            transition: 0.2s;
            z-index: 2;
            background: transparent;
        }

        .login-input:focus ~ .login-label,
        .login-input:not(:placeholder-shown) ~ .login-label {
            top: -0.2rem;
            left: 0;
            font-size: 0.85rem;
            color: #3e33a3ff;
            background: #c4c4d4ff;
            padding: 0 0.3rem;
            border-radius: 8px;
            z-index: 2;
        }
        .login-btn {
            width: 100%;
            padding: 0.9rem 0;
            background: #2a3083ff;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 1rem;
        }
        .login-btn:hover {
            background: #4648beff;
        }
        
        /* Estilos del Header */
        .header-transparente {
            background-color: rgba(33, 37, 41, 0.8);
            position: absolute;
            top: 0;
            width: 100%;
            z-index: 1030;
            transition: background-color 0.3s ease-in-out;
        }

        .header-transparente .nav-link {
            color: white !important;
        }

        .header-transparente .nav-link:hover {
            color: #007bff !important;
            text-decoration: none;
        }

        .logo img {
            height: 40px; 
        }

        /* Responsive */
        @media (max-width: 900px) {
            .login-card-horizontal {
                flex-direction: column;
                min-width: 320px;
                max-width: 98vw;
            }
            .login-card-photo {
                /* Ocultar la foto en móviles o reducir su altura */
                display: none; 
                /* Si se quisiera mostrar: min-height: 150px; */
            }
            .login-card-form {
                min-width: 100vw;
                max-width: 100vw;
                padding: 2rem 0.5rem;
            }
            .login-formbox {
                max-width: 95vw;
                padding: 2rem 1.5rem 1.5rem 1.5rem;
            }
        }
    </style>
</head>
    <body>
        
        <div class="login-center-container">
            <div class="login-card-horizontal">
                <!-- Se ha corregido la ruta de la imagen según tu segundo ejemplo -->
                <div class="login-card-photo">
                    <img src="../img/iconos-17.svg" alt="Logo La Previsora" >
                </div>
                
                <div class="login-card-form">
                    <!-- El formulario envía los datos al mismo archivo -->
                    <form method="POST" class="login-formbox" autocomplete="off">
                        <div class="login-title">Inicio de Sesión</div>
                        
                        <!-- Muestra el error si existe -->
                        <?php if ($error): ?>
                            <div class="login-error"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <div class="login-inputbox">
                            <!-- Nombre del campo cambiado a 'identificador' para el modelo -->
                            <input type="text" id="identificador" name="identificador" class="login-input" 
                                   required autocomplete="username" placeholder=" " 
                                   value="<?php echo $usuario_ingresado; ?>" />
                            <label for="identificador" class="login-label">Cédula o Email</label>
                            <i class="lni lni-user"></i>
                        </div>
                        <div class="login-inputbox">
                            <!-- Nombre del campo cambiado a 'clave' para el modelo -->
                            <input type="password" name="clave" id="clave" class="login-input" 
                                   required autocomplete="current-password" placeholder=" " />
                            <label for="clave" class="login-label">Contraseña</label>
                            <i class="lni lni-lock"></i>
                        </div>
                        <button type="submit" class="login-btn">Iniciar Sesión</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>