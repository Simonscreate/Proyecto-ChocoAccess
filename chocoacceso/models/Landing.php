<?php
class Landing {
    private $conn;
    private $table_name = "landing_content";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todo el contenido indexado por sección para cargarlo eficientemente
    public function obtenerTodoContenido() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $contenido = [];
        foreach ($resultados as $row) {
            $contenido[$row['seccion']] = $row;
        }
        return $contenido;
    }

    // Actualizar una sección con medidas preventivas de sanitización
    public function actualizarSeccion($seccion, $titulo, $subtitulo, $contenido, $imagen_url = null) {
        $query = "UPDATE " . $this->table_name . " 
                  SET titulo = :titulo, subtitulo = :subtitulo, contenido = :contenido" . 
                  ($imagen_url ? ", imagen_url = :imagen" : "") . " 
                  WHERE seccion = :seccion";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindValue(":titulo", htmlspecialchars(strip_tags($titulo)));
        $stmt->bindValue(":subtitulo", $subtitulo ? htmlspecialchars(strip_tags($subtitulo)) : null);
        $stmt->bindValue(":contenido", htmlspecialchars(strip_tags($contenido)));
        $stmt->bindValue(":seccion", $seccion);
        if ($imagen_url) {
            $stmt->bindValue(":imagen", $imagen_url);
        }

        return $stmt->execute();
    }
}