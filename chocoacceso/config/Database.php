<?php
class database{
    private $host = "localhost";
    private $user = "root";
    private $password = "";
    private $database = "chocoacceso";
    private $conect;

    public function __construct(){
        $connectionString = "mysql:host=".$this->host.";dbname=".$this->database.";charset=utf8";
        try{
            $this->conect = new PDO($connectionString, $this->user, $this->password);
            $this->conect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo ""; //confirmacion de la conexion, se puede eliminar despues
        } catch (PDOException $e){
            $this->conect = null;
            echo "Conexión fallida: " . $e->getMessage();
        }
    }
    public function Conexion(){
        return $this->conect;
    }
}

$conect = new database();//creada para probar la conexion, se puede eliminar despues
?> 