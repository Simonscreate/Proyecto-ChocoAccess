<?php
class Cita {
    private $conn;
    private $table_name = "citas";

    public $id_cita;
    public $id_usuario;
    public $id_administrador;
    public $fecha_cita;
    public $hora_cita;
    public $departamento_destino;
    public $motivo;
    public $estado;
    public $google_event_id; // Almacena el ID de la nube

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET id_usuario = :id_u, id_administrador = :id_a, fecha_cita = :fecha, 
                    hora_cita = :hora, departamento_destino = :depto, motivo = :motivo, 
                    estado = 'Programada', google_event_id = :google_id";
        
        $stmt = $this->conn->prepare($query);

        $this->departamento_destino = htmlspecialchars(strip_tags($this->departamento_destino));
        $this->motivo = htmlspecialchars(strip_tags($this->motivo));

        $stmt->bindParam(":id_u", $this->id_usuario);
        $stmt->bindParam(":id_a", $this->id_administrador);
        $stmt->bindParam(":fecha", $this->fecha_cita);
        $stmt->bindParam(":hora", $this->hora_cita);
        $stmt->bindParam(":depto", $this->departamento_destino);
        $stmt->bindParam(":motivo", $this->motivo);
        $stmt->bindParam(":google_id", $this->google_event_id);

        return $stmt->execute();
    }

    public function consultarTodos() {
        $query = "SELECT c.*, u.nombre_completo AS nombre_personal, u.cedula, admin.nombre_completo AS nombre_admin
                  FROM " . $this->table_name . " c
                  JOIN usuarios u ON c.id_usuario = u.id_usuario
                  JOIN usuarios admin ON c.id_administrador = admin.id_usuario
                  ORDER BY c.fecha_cita ASC, c.hora_cita ASC";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>