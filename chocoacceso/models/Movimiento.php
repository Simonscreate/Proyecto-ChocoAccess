<?php
class Movimiento {
    private $conn;
    private $table_name = "movimientos";

    public $id_usuario;
    public $id_operador;
    public $tipo_movimiento;
    public $observaciones;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function registrar() {
        $query = "INSERT INTO " . $this->table_name . " SET id_usuario=:id_u, id_operador=:id_o, tipo_movimiento=:tipo, observaciones=:obs";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_u", $this->id_usuario);
        $stmt->bindParam(":id_o", $this->id_operador);
        $stmt->bindParam(":tipo", $this->tipo_movimiento);
        $stmt->bindParam(":obs", $this->observaciones);

        return $stmt->execute();
    }

    public function consultarUltimos($limite = 10) {
        $query = "SELECT m.*, u.nombre_completo FROM " . $this->table_name . " m 
                  JOIN usuarios u ON m.id_usuario = u.id_usuario 
                  ORDER BY m.timestamp_registro DESC LIMIT :limite";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // NUEVO: Obtener quién está en qué departamento actualmente
    public function consultarUbicacionesActuales($filtro = 'TODOS') {
        $query = "SELECT u.nombre_completo, u.rol, e.ubicacion_actual, e.ultima_actualizacion 
                  FROM estado_planta e
                  JOIN usuarios u ON e.id_usuario = u.id_usuario
                  WHERE e.ubicacion_actual != 'FUERA'";
        
        if ($filtro != 'TODOS') {
            $query .= " AND e.ubicacion_actual = :filtro";
        }

        $stmt = $this->conn->prepare($query);
        if ($filtro != 'TODOS') $stmt->bindParam(":filtro", $filtro);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEstadisticasHoy() {
        $query = "SELECT 
                    COUNT(CASE WHEN tipo_movimiento = 'ENTRADA' THEN 1 END) as entradas,
                    COUNT(CASE WHEN tipo_movimiento = 'SALIDA' THEN 1 END) as salidas
                  FROM " . $this->table_name . " WHERE DATE(timestamp_registro) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * MÉTODO: consultarAuditoriaPorDia
     * Recupera todos los movimientos de una fecha específica
     */
    public function consultarAuditoriaGlobal($fecha) {
        // Esta consulta unifica movimientos de acceso y registros de nuevos usuarios
        $query = "
            -- Parte 1: Movimientos de entrada/salida
            SELECT 
                m.timestamp_registro AS fecha_hora,
                u.nombre_completo AS sujeto,
                u.rol AS sujeto_rol,
                m.tipo_movimiento AS accion,
                op.nombre_completo AS responsable,
                m.observaciones AS detalle
            FROM movimientos m
            JOIN usuarios u ON m.id_usuario = u.id_usuario
            JOIN usuarios op ON m.id_operador = op.id_usuario
            WHERE DATE(m.timestamp_registro) = :fecha1

            UNION ALL

            -- Parte 2: Creación de nuevos usuarios
            SELECT 
                u2.fecha_creacion AS fecha_hora,
                u2.nombre_completo AS sujeto,
                u2.rol AS sujeto_rol,
                'REGISTRO_USUARIO' AS accion,
                'ADMIN_SISTEMA' AS responsable, -- O el nombre del admin si tienes el ID
                CONCAT('Nuevo usuario creado: ', u2.cedula) AS detalle
            FROM usuarios u2
            WHERE DATE(u2.fecha_creacion) = :fecha2

            ORDER BY fecha_hora DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fecha1", $fecha);
        $stmt->bindParam(":fecha2", $fecha);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Analítica 1: Cantidad de personas actualmente en cada departamento
     */
    public function obtenerPersonasPorDepartamento() {
        $query = "SELECT ubicacion_actual AS departamento, COUNT(*) AS total 
                FROM estado_planta 
                WHERE ubicacion_actual != 'FUERA'
                GROUP BY ubicacion_actual";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Analítica 2: Veces que un usuario específico ha accedido a los departamentos (Histórico)
     */
    public function obtenerAccesosPorUsuario($id_usuario) {
        $query = "SELECT observaciones AS departamento, COUNT(*) AS accesos 
                FROM movimientos 
                WHERE id_usuario = :id_usuario AND tipo_movimiento = 'ENTRADA'
                GROUP BY observaciones";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_usuario", $id_usuario);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Analítica 3: Datos crudos de accesos diarios generales para calcular Media, Mediana y Moda
     */
    public function obtenerFrecuenciasAccesosDiarios() {
        // Cuenta cuántos accesos de entrada ocurren por día en toda la planta
        $query = "SELECT DATE(timestamp_registro) AS fecha, COUNT(*) AS conteo 
                FROM movimientos 
                WHERE tipo_movimiento = 'ENTRADA'
                GROUP BY DATE(timestamp_registro)
                ORDER BY conteo ASC"; // Ordenado para facilitar la mediana
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN); // Devuelve un array plano de números [2, 5, 5, 8, 12...]
    }
}