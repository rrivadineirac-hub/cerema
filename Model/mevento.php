<?php
// Model/mevento.php
class EventoModel {
    private $conn;
    private $table_name = "eventos";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los eventos de una gestión
    public function getByGestionId($id_gestion) {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE id_gestion = ?
                  ORDER BY fecha_evento DESC, hora_evento DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_gestion);
        $stmt->execute();
        return $stmt;
    }

    // Obtener un evento por su ID
    public function getById($id_evento) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_evento = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_evento);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo evento
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_gestion, titulo, tipo_evento, fecha_evento, hora_evento, lugar, descripcion, estado) 
                  VALUES (:id_gestion, :titulo, :tipo_evento, :fecha_evento, :hora_evento, :lugar, :descripcion, :estado)";
        
        $stmt = $this->conn->prepare($query);

        $id_gestion = htmlspecialchars(strip_tags($data['id_gestion']));
        $titulo = htmlspecialchars(strip_tags($data['titulo']));
        $tipo_evento = htmlspecialchars(strip_tags($data['tipo_evento']));
        $fecha_evento = htmlspecialchars(strip_tags($data['fecha_evento']));
        $hora_evento = htmlspecialchars(strip_tags($data['hora_evento']));
        $lugar = htmlspecialchars(strip_tags($data['lugar']));
        $descripcion = htmlspecialchars(strip_tags($data['descripcion']));
        $estado = htmlspecialchars(strip_tags($data['estado'] ?? 'Programado'));

        $stmt->bindParam(":id_gestion", $id_gestion);
        $stmt->bindParam(":titulo", $titulo);
        $stmt->bindParam(":tipo_evento", $tipo_evento);
        $stmt->bindParam(":fecha_evento", $fecha_evento);
        $stmt->bindParam(":hora_evento", $hora_evento);
        $stmt->bindParam(":lugar", $lugar);
        $stmt->bindParam(":descripcion", $descripcion);
        $stmt->bindParam(":estado", $estado);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Actualizar evento
    public function update($data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET titulo = :titulo, 
                      tipo_evento = :tipo_evento, 
                      fecha_evento = :fecha_evento, 
                      hora_evento = :hora_evento, 
                      lugar = :lugar, 
                      descripcion = :descripcion, 
                      estado = :estado
                  WHERE id_evento = :id_evento";

        $stmt = $this->conn->prepare($query);

        $id_evento = htmlspecialchars(strip_tags($data['id_evento']));
        $titulo = htmlspecialchars(strip_tags($data['titulo']));
        $tipo_evento = htmlspecialchars(strip_tags($data['tipo_evento']));
        $fecha_evento = htmlspecialchars(strip_tags($data['fecha_evento']));
        $hora_evento = htmlspecialchars(strip_tags($data['hora_evento']));
        $lugar = htmlspecialchars(strip_tags($data['lugar']));
        $descripcion = htmlspecialchars(strip_tags($data['descripcion']));
        $estado = htmlspecialchars(strip_tags($data['estado'] ?? 'Programado'));

        $stmt->bindParam(":id_evento", $id_evento);
        $stmt->bindParam(":titulo", $titulo);
        $stmt->bindParam(":tipo_evento", $tipo_evento);
        $stmt->bindParam(":fecha_evento", $fecha_evento);
        $stmt->bindParam(":hora_evento", $hora_evento);
        $stmt->bindParam(":lugar", $lugar);
        $stmt->bindParam(":descripcion", $descripcion);
        $stmt->bindParam(":estado", $estado);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar evento
    public function delete($id_evento) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_evento = ?";
        $stmt = $this->conn->prepare($query);
        $id_evento = htmlspecialchars(strip_tags($id_evento));
        $stmt->bindParam(1, $id_evento);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
