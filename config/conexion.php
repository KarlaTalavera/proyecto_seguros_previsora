<?php
class Base_Datos {
    // Asegúrate de que estos datos sean correctos en tu XAMPP/WAMP
    private $Nombre_Servidor = "localhost";
    private $Nombre_Base_Datos = "seguros_la_previsora";
    private $Nombre_Usuario = "root";
    private $Pass = ""; 
    
    public $Conexion;

    public function Conexion_Base_Datos() {
        $this->Conexion = null;

        try {
            // Añadimos charset utf8 para evitar problemas con acentos y ñ
            $dsn = "mysql:host=" . $this->Nombre_Servidor . ";dbname=" . $this->Nombre_Base_Datos . ";charset=utf8";
            
            $this->Conexion = new PDO($dsn, $this->Nombre_Usuario, $this->Pass);
            
            // Configuración CRÍTICA para ver errores SQL
            $this->Conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->Conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            // En producción no se hace echo directo, pero para depurar ahora sí:
            echo "Error de Conexión: " . $e->getMessage();
            exit;
        }

        return $this->Conexion;
    }
}
?>