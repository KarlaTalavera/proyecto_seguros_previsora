<?php

  class Base_Datos{

    private $Nombre_Servidor = "localhost";
    private $Nombre_Base_Datos = "seguros_la_previsora";
    private $Nombre_Usuario = "root";
    private $Pass = ""; 

    private $Conexion;

    public function Conexion_Base_Datos(){
       $this->Conexion = null;

       try{
             $this-> Conexion = new PDO("mysql:host={$this->Nombre_Servidor};dbname={$this->Nombre_Base_Datos}",
             $this->Nombre_Usuario, //host es lo mismo que el nombre del servidor y dbname nombre base de datos
             $this->Pass
        );
        $this->Conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

       }catch(PDOException $e){
         die ("Error de Conexion:" . $e->getMessage());
       }

       return $this->Conexion;
        
    }

    }

?>