<?php
class Usuario {
    // 1. Atributos: Coinciden exactamente con las columnas de tu SQL
    private $conn; // Para guardar la conexión a la base de datos
    private $table_name = "usuarios";

    public $id_usuario;
    public $cedula;
    public $nombre_completo;
    public $rol;
    public $empresa_externa;
    public $empresa;
    public $activo;
    public $fecha_creacion;

    // 2. El Constructor: Recibe la conexión que configuramos en Database.php
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * MÉTODO: validarCedula
     * Uso: Para el sistema de portería. Verifica si alguien puede entrar.
     */
    public function validarCedula($cedula) {
        // Preparamos la consulta para evitar Inyección SQL
        $query = "SELECT id_usuario, nombre_completo, rol, activo 
                  FROM " . $this->table_name . " 
                  WHERE cedula = :cedula LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        // Limpiamos el dato (Sanitización)
        $this->cedula = htmlspecialchars(strip_tags($cedula));
        $stmt->bindParam(":cedula", $this->cedula);

        $stmt->execute();

        // Si encuentra una fila, retornamos los datos como un arreglo
        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false; // Si no existe, devuelve falso
    }

    /**
     * MÉTODO: crear
     * Uso: Para el panel administrativo (agregar nuevo personal o visitante).
     */
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET cedula=:c, nombre_completo=:n, rol=:r, password=:p, empresa_externa=:e, activo=1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":c", $this->cedula);
        $stmt->bindParam(":n", $this->nombre_completo);
        $stmt->bindParam(":r", $this->rol);
        $stmt->bindParam(":p", $this->password);
        $stmt->bindParam(":e", $this->empresa); // Usamos 'empresa_externa' que sí existe en el SQL

        return $stmt->execute();
    }
}
?>