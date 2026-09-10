<?php
// Model/megreso_especial.php

class EgresoEspecialModel {
    private $conn;
    private $table_name = "egresos_especiales";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($id_gestion = null) {
        $query = "SELECT * FROM " . $this->table_name;
        if ($id_gestion) {
            $query .= " WHERE id_gestion = :id_gestion";
        }
        $query .= " ORDER BY fecha_pago DESC, id_egreso_esp DESC";
        $stmt = $this->conn->prepare($query);
        if ($id_gestion) {
            $stmt->bindParam(':id_gestion', $id_gestion);
        }
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_egreso_esp = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_gestion, motivo, detalle, monto, fecha_pago, comprobante, estado) 
                  VALUES (:id_gestion, :motivo, :detalle, :monto, :fecha_pago, :comprobante, :estado)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id_gestion', $data['id_gestion']);
        $stmt->bindParam(':motivo', $data['motivo']);
        $stmt->bindParam(':detalle', $data['detalle']);
        $stmt->bindParam(':monto', $data['monto']);
        $stmt->bindParam(':fecha_pago', $data['fecha_pago']);
        $stmt->bindParam(':comprobante', $data['comprobante']);
        $stmt->bindParam(':estado', $data['estado']);

        return $stmt->execute();
    }

    public function update($data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET motivo = :motivo,
                      detalle = :detalle, 
                      monto = :monto, 
                      fecha_pago = :fecha_pago, 
                      comprobante = :comprobante, 
                      estado = :estado 
                  WHERE id_egreso_esp = :id_egreso_esp";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':motivo', $data['motivo']);
        $stmt->bindParam(':detalle', $data['detalle']);
        $stmt->bindParam(':monto', $data['monto']);
        $stmt->bindParam(':fecha_pago', $data['fecha_pago']);
        $stmt->bindParam(':comprobante', $data['comprobante']);
        $stmt->bindParam(':estado', $data['estado']);
        $stmt->bindParam(':id_egreso_esp', $data['id_egreso_esp']);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_egreso_esp = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }
}
