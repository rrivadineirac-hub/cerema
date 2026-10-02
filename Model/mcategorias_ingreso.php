<?php
// Model/mcategorias_ingreso.php

class CategoriasIngresoModel {
    private $conn;
    private $table_name = "categorias_ingreso";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las categorías (filtradas por gestión)
    public function getAll($id_gestion = null) {
        if ($id_gestion && $id_gestion !== 'all') {
            $query = "SELECT c.*, g.gestion 
                      FROM " . $this->table_name . " c 
                      LEFT JOIN gestiones g ON c.id_gestion = g.id_gestion 
                      WHERE c.id_gestion = ? OR c.id_gestion IS NULL 
                      ORDER BY c.id_cat_ingreso ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id_gestion);
        } else {
            $query = "SELECT c.*, g.gestion 
                      FROM " . $this->table_name . " c 
                      LEFT JOIN gestiones g ON c.id_gestion = g.id_gestion 
                      ORDER BY c.id_cat_ingreso ASC";
            $stmt = $this->conn->prepare($query);
        }
        $stmt->execute();
        return $stmt;
    }

    // Obtener una categoría por ID
    public function getById($id_cat_ingreso) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_cat_ingreso = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_cat_ingreso);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar el monto sugerido de una categoría
    public function updateMonto($id_cat_ingreso, $monto_sugerido) {
        $query = "UPDATE " . $this->table_name . " 
                  SET monto_sugerido = :monto_sugerido 
                  WHERE id_cat_ingreso = :id_cat_ingreso";
        
        $stmt = $this->conn->prepare($query);

        $monto_sugerido = htmlspecialchars(strip_tags($monto_sugerido));
        $id_cat_ingreso = htmlspecialchars(strip_tags($id_cat_ingreso));

        $stmt->bindParam(':monto_sugerido', $monto_sugerido);
        $stmt->bindParam(':id_cat_ingreso', $id_cat_ingreso);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Crear nueva categoría de ingreso
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_gestion, nombre, requiere_socio, monto_sugerido, descripcion) 
                  VALUES (:id_gestion, :nombre, :requiere_socio, :monto_sugerido, :descripcion)";
        
        $stmt = $this->conn->prepare($query);

        $id_gestion = !empty($data['id_gestion']) ? $data['id_gestion'] : null;
        $nombre = htmlspecialchars(strip_tags($data['nombre']));
        $requiere_socio = htmlspecialchars(strip_tags($data['requiere_socio']));
        $monto_sugerido = htmlspecialchars(strip_tags($data['monto_sugerido']));
        $descripcion = htmlspecialchars(strip_tags($data['descripcion']));

        $stmt->bindParam(":id_gestion", $id_gestion);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":requiere_socio", $requiere_socio);
        $stmt->bindParam(":monto_sugerido", $monto_sugerido);
        $stmt->bindParam(":descripcion", $descripcion);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar categoría de ingreso
    public function delete($id_cat_ingreso) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_cat_ingreso = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_cat_ingreso);
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
