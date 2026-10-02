<?php
// Model/motro_gasto.php
class OtroGastoModel {
    private $conn;
    private $table_name = "otros_gastos";

    public function __construct($db) {
        $this->conn = $db;
        $this->ensureColumnsExist();
    }

    private function ensureColumnsExist() {
        try { $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN nombre_gasto VARCHAR(255) DEFAULT NULL"); } catch (Exception $e) {}
        try { $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN unidad_medida VARCHAR(50) DEFAULT ''"); } catch (Exception $e) {}
        try { $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN cantidad DECIMAL(10,2) DEFAULT 1.00"); } catch (Exception $e) {}
        try { $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN precio DECIMAL(10,2) DEFAULT NULL"); } catch (Exception $e) {}
    }

    // Obtener lista única de conceptos para el Combobox
    public function getListaDetalles() {
        $query = "SELECT DISTINCT COALESCE(NULLIF(nombre_gasto, ''), detalle) AS concepto FROM " . $this->table_name . " WHERE (nombre_gasto IS NOT NULL AND nombre_gasto != '') OR (detalle IS NOT NULL AND detalle != '') ORDER BY concepto ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Obtener todos los gastos de una gestión
    public function getByGestionId($id_gestion) {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE id_gestion = ?
                  ORDER BY fecha_pago DESC, id_otro_gasto DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_gestion);
        $stmt->execute();
        return $stmt;
    }

    // Obtener por ID
    public function getById($id_otro_gasto) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_otro_gasto = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_otro_gasto);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo registro
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_gestion, nombre_gasto, detalle, unidad_medida, cantidad, precio, monto, fecha_pago, comprobante, estado) 
                  VALUES (:id_gestion, :nombre_gasto, :detalle, :unidad_medida, :cantidad, :precio, :monto, :fecha_pago, :comprobante, :estado)";
        
        $stmt = $this->conn->prepare($query);

        $id_gestion = htmlspecialchars(strip_tags($data['id_gestion']));
        $nombre_gasto = htmlspecialchars(strip_tags($data['nombre_gasto'] ?? $data['detalle'] ?? ''));
        $detalle = htmlspecialchars(strip_tags($data['detalle'] ?? ''));
        $unidad_medida = htmlspecialchars(strip_tags($data['unidad_medida'] ?? ''));
        $cantidad = !empty($data['cantidad']) ? floatval($data['cantidad']) : 1.00;
        $monto = floatval($data['monto']);
        $precio = !empty($data['precio']) ? floatval($data['precio']) : ($cantidad > 0 ? $monto / $cantidad : $monto);
        $fecha_pago = htmlspecialchars(strip_tags($data['fecha_pago']));
        $comprobante = htmlspecialchars(strip_tags($data['comprobante'] ?? ''));
        $estado = htmlspecialchars(strip_tags($data['estado'] ?? 'Pagado'));

        $stmt->bindParam(":id_gestion", $id_gestion);
        $stmt->bindParam(":nombre_gasto", $nombre_gasto);
        $stmt->bindParam(":detalle", $detalle);
        $stmt->bindParam(":unidad_medida", $unidad_medida);
        $stmt->bindParam(":cantidad", $cantidad);
        $stmt->bindParam(":precio", $precio);
        $stmt->bindParam(":monto", $monto);
        $stmt->bindParam(":fecha_pago", $fecha_pago);
        $stmt->bindParam(":comprobante", $comprobante);
        $stmt->bindParam(":estado", $estado);

        return $stmt->execute();
    }

    // Actualizar un registro
    public function update($data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre_gasto = :nombre_gasto,
                      detalle = :detalle, 
                      unidad_medida = :unidad_medida,
                      cantidad = :cantidad,
                      precio = :precio,
                      monto = :monto, 
                      fecha_pago = :fecha_pago, 
                      comprobante = :comprobante, 
                      estado = :estado
                  WHERE id_otro_gasto = :id_otro_gasto";
        
        $stmt = $this->conn->prepare($query);

        $id_otro_gasto = htmlspecialchars(strip_tags($data['id_otro_gasto']));
        $nombre_gasto = htmlspecialchars(strip_tags($data['nombre_gasto'] ?? $data['detalle'] ?? ''));
        $detalle = htmlspecialchars(strip_tags($data['detalle'] ?? ''));
        $unidad_medida = htmlspecialchars(strip_tags($data['unidad_medida'] ?? ''));
        $cantidad = !empty($data['cantidad']) ? floatval($data['cantidad']) : 1.00;
        $monto = floatval($data['monto']);
        $precio = !empty($data['precio']) ? floatval($data['precio']) : ($cantidad > 0 ? $monto / $cantidad : $monto);
        $fecha_pago = htmlspecialchars(strip_tags($data['fecha_pago']));
        $comprobante = htmlspecialchars(strip_tags($data['comprobante'] ?? ''));
        $estado = htmlspecialchars(strip_tags($data['estado'] ?? 'Pagado'));

        $stmt->bindParam(":id_otro_gasto", $id_otro_gasto);
        $stmt->bindParam(":nombre_gasto", $nombre_gasto);
        $stmt->bindParam(":detalle", $detalle);
        $stmt->bindParam(":unidad_medida", $unidad_medida);
        $stmt->bindParam(":cantidad", $cantidad);
        $stmt->bindParam(":precio", $precio);
        $stmt->bindParam(":monto", $monto);
        $stmt->bindParam(":fecha_pago", $fecha_pago);
        $stmt->bindParam(":comprobante", $comprobante);
        $stmt->bindParam(":estado", $estado);

        return $stmt->execute();
    }

    // Eliminar un registro
    public function delete($id_otro_gasto) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_otro_gasto = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_otro_gasto);
        return $stmt->execute();
    }
}
?>
