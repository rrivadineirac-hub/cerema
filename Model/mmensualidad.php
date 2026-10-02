<?php
// Model/mmensualidad.php
class MensualidadModel {
    private $conn;
    private $table_name = "mensualidad";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las mensualidades de un socio
    public function getBySocioId($id_socio) {
        $query = "SELECT m.*, s.nombre, s.ap_paterno, s.ap_materno 
                  FROM " . $this->table_name . " m
                  JOIN asociados s ON m.id_socio = s.id_socio
                  WHERE m.id_socio = ?
                  ORDER BY m.anio DESC, 
                           FIELD(m.mes, 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre') DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_socio);
        $stmt->execute();
        return $stmt;
    }

    // Obtener una mensualidad por su ID
    public function getById($id_mensualidad) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_mensualidad = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_mensualidad);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function checkExists($id_socio, $numero_accion, $mes, $anio) {
        $query = "SELECT estado FROM " . $this->table_name . " WHERE id_socio = ? AND numero_accion = ? AND mes = ? AND anio = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_socio);
        $stmt->bindParam(2, $numero_accion);
        $stmt->bindParam(3, $mes);
        $stmt->bindParam(4, $anio);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener la lista de meses ya pagados para un socio, acción y año específicos
    public function getPaidMonthsByYear($id_socio, $numero_accion, $anio) {
        $query = "SELECT mes FROM " . $this->table_name . " 
                  WHERE id_socio = ? AND numero_accion = ? AND anio = ? AND (estado = 'Pagado' OR estado IS NULL OR estado = '')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_socio);
        $stmt->bindParam(2, $numero_accion);
        $stmt->bindParam(3, $anio);
        $stmt->execute();
        
        $months = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $months[] = $row['mes'];
        }
        return $months;
    }

    // Obtener todos los meses pagados por año para un socio y acción
    public function getAllPaidMonthsAllYears($id_socio, $numero_accion = 1) {
        if ($id_socio === 'all' || empty($id_socio)) {
            return [];
        }

        $query = "SELECT anio, mes FROM " . $this->table_name . " 
                  WHERE id_socio = :id_socio ";
        
        if ($numero_accion !== 'all') {
            $query .= " AND numero_accion = :numero_accion ";
        }
        $query .= " AND (estado = 'Pagado' OR estado IS NULL OR estado = '') ORDER BY anio ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_socio", $id_socio);
        if ($numero_accion !== 'all') {
            $stmt->bindParam(":numero_accion", $numero_accion);
        }
        $stmt->execute();

        $paid = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $y = $row['anio'];
            $m = $row['mes'];
            if (!isset($paid[$y])) {
                $paid[$y] = [];
            }
            if (!in_array($m, $paid[$y])) {
                $paid[$y][] = $m;
            }
        }
        return $paid;
    }

    // Crear una nueva mensualidad
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_socio, numero_accion, numero_recibo, mes, anio, monto, fecha_pago, estado) 
                  VALUES (:id_socio, :numero_accion, :numero_recibo, :mes, :anio, :monto, :fecha_pago, :estado)";
        
        $stmt = $this->conn->prepare($query);

        $id_socio = htmlspecialchars(strip_tags($data['id_socio']));
        $numero_accion = htmlspecialchars(strip_tags($data['numero_accion'] ?? 1));
        $numero_recibo = htmlspecialchars(strip_tags($data['numero_recibo'] ?? ''));
        $mes = htmlspecialchars(strip_tags($data['mes']));
        $anio = htmlspecialchars(strip_tags($data['anio']));
        $monto = htmlspecialchars(strip_tags($data['monto']));
        $fecha_pago = htmlspecialchars(strip_tags($data['fecha_pago']));
        $estado = htmlspecialchars(strip_tags($data['estado']));

        $stmt->bindParam(":id_socio", $id_socio);
        $stmt->bindParam(":numero_accion", $numero_accion);
        $stmt->bindParam(":numero_recibo", $numero_recibo);
        $stmt->bindParam(":mes", $mes);
        $stmt->bindParam(":anio", $anio);
        $stmt->bindParam(":monto", $monto);
        $stmt->bindParam(":fecha_pago", $fecha_pago);
        $stmt->bindParam(":estado", $estado);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Actualizar mensualidad
    public function update($data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET numero_accion = :numero_accion,
                      numero_recibo = :numero_recibo,
                      mes = :mes, 
                      anio = :anio, 
                      monto = :monto, 
                      fecha_pago = :fecha_pago, 
                      estado = :estado
                  WHERE id_mensualidad = :id_mensualidad";

        $stmt = $this->conn->prepare($query);

        $id_mensualidad = htmlspecialchars(strip_tags($data['id_mensualidad']));
        $numero_accion = htmlspecialchars(strip_tags($data['numero_accion'] ?? 1));
        $numero_recibo = htmlspecialchars(strip_tags($data['numero_recibo'] ?? ''));
        $mes = htmlspecialchars(strip_tags($data['mes']));
        $anio = htmlspecialchars(strip_tags($data['anio']));
        $monto = htmlspecialchars(strip_tags($data['monto']));
        $fecha_pago = htmlspecialchars(strip_tags($data['fecha_pago']));
        $estado = htmlspecialchars(strip_tags($data['estado']));

        $stmt->bindParam(":id_mensualidad", $id_mensualidad);
        $stmt->bindParam(":numero_accion", $numero_accion);
        $stmt->bindParam(":numero_recibo", $numero_recibo);
        $stmt->bindParam(":mes", $mes);
        $stmt->bindParam(":anio", $anio);
        $stmt->bindParam(":monto", $monto);
        $stmt->bindParam(":fecha_pago", $fecha_pago);
        $stmt->bindParam(":estado", $estado);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar mensualidad
    public function delete($id_mensualidad) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_mensualidad = ?";
        $stmt = $this->conn->prepare($query);
        $id_mensualidad = htmlspecialchars(strip_tags($id_mensualidad));
        $stmt->bindParam(1, $id_mensualidad);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
