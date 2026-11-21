<?php
// Model/mingreso_venta_accion.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class IngresoVentaAccionModel {
    private $conn;
    private $table_name = "ingresos_venta_acciones";

    public function __construct($db) {
        $this->conn = $db;
        $this->ensureSchema();
    }

    /**
     * Asegurar que la tabla exista en base de datos tanto local como en hosting remoto
     */
    private function ensureSchema() {
        try {
            $this->conn->exec("CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
                id_venta_accion INT AUTO_INCREMENT PRIMARY KEY,
                id_gestion INT NOT NULL,
                id_socio INT DEFAULT NULL,
                comprador VARCHAR(150) NOT NULL,
                cantidad_acciones INT NOT NULL DEFAULT 1,
                numero_accion VARCHAR(100) DEFAULT NULL,
                precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                monto_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                fecha_venta DATE NOT NULL,
                numero_recibo VARCHAR(50) DEFAULT NULL,
                metodo_pago ENUM('Efectivo','Transferencia','QR','Depósito','Otro') NOT NULL DEFAULT 'Efectivo',
                observaciones TEXT DEFAULT NULL,
                estado ENUM('Cobrado','Anulado') NOT NULL DEFAULT 'Cobrado',
                fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
                fecha_edicion DATETIME DEFAULT NULL,
                KEY idx_gestion (id_gestion),
                KEY idx_socio (id_socio)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        } catch (Exception $e) { }
    }

    /**
     * Obtener todas las ventas de acciones de una gestión o globales
     */
    public function getAll($id_gestion = null) {
        $sql = "SELECT v.*, 
                       s.ci, s.complemento, s.nombre AS socio_nombre, s.ap_paterno AS socio_paterno, s.ap_materno AS socio_materno,
                       g.gestion AS gestion_nombre
                FROM " . $this->table_name . " v
                LEFT JOIN asociados s ON v.id_socio = s.id_socio
                LEFT JOIN gestiones g ON v.id_gestion = g.id_gestion";
        
        $params = [];
        if ($id_gestion !== null && $id_gestion !== '' && $id_gestion !== 'all') {
            $sql .= " WHERE v.id_gestion = ?";
            $params[] = $id_gestion;
        }
        $sql .= " ORDER BY v.fecha_venta DESC, v.id_venta_accion DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Obtener una venta de acción por su ID
     */
    public function getById($id_venta_accion) {
        $sql = "SELECT v.*, 
                       s.ci, s.complemento, s.nombre AS socio_nombre, s.ap_paterno AS socio_paterno, s.ap_materno AS socio_materno
                FROM " . $this->table_name . " v
                LEFT JOIN asociados s ON v.id_socio = s.id_socio
                WHERE v.id_venta_accion = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$id_venta_accion]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Registrar una nueva venta de acción
     */
    public function create($data) {
        $sql = "INSERT INTO " . $this->table_name . " 
                (id_gestion, id_socio, comprador, cantidad_acciones, numero_accion, precio_unitario, monto_total, fecha_venta, numero_recibo, metodo_pago, observaciones, estado, fecha_registro) 
                VALUES (:id_gestion, :id_socio, :comprador, :cantidad_acciones, :numero_accion, :precio_unitario, :monto_total, :fecha_venta, :numero_recibo, :metodo_pago, :observaciones, :estado, NOW())";
        
        $stmt = $this->conn->prepare($sql);

        $id_gestion = !empty($data['id_gestion']) ? (int)$data['id_gestion'] : 0;
        $id_socio = !empty($data['id_socio']) ? (int)$data['id_socio'] : null;
        $comprador = trim(strip_tags($data['comprador'] ?? ''));
        $cantidad_acciones = !empty($data['cantidad_acciones']) ? (int)$data['cantidad_acciones'] : 1;
        $numero_accion = trim(strip_tags($data['numero_accion'] ?? ''));
        $precio_unitario = (float)($data['precio_unitario'] ?? 0);
        $monto_total = (float)($data['monto_total'] ?? ($cantidad_acciones * $precio_unitario));
        $fecha_venta = !empty($data['fecha_venta']) ? $data['fecha_venta'] : date('Y-m-d');
        $numero_recibo = trim(strip_tags($data['numero_recibo'] ?? ''));
        $metodo_pago = in_array($data['metodo_pago'] ?? '', ['Efectivo', 'Transferencia', 'QR', 'Depósito', 'Otro']) ? $data['metodo_pago'] : 'Efectivo';
        $observaciones = trim(strip_tags($data['observaciones'] ?? ''));
        $estado = in_array($data['estado'] ?? '', ['Cobrado', 'Anulado']) ? $data['estado'] : 'Cobrado';

        $stmt->bindParam(":id_gestion", $id_gestion, PDO::PARAM_INT);
        if ($id_socio !== null) {
            $stmt->bindParam(":id_socio", $id_socio, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(":id_socio", null, PDO::PARAM_NULL);
        }
        $stmt->bindParam(":comprador", $comprador);
        $stmt->bindParam(":cantidad_acciones", $cantidad_acciones, PDO::PARAM_INT);
        $stmt->bindParam(":numero_accion", $numero_accion);
        $stmt->bindParam(":precio_unitario", $precio_unitario);
        $stmt->bindParam(":monto_total", $monto_total);
        $stmt->bindParam(":fecha_venta", $fecha_venta);
        $stmt->bindParam(":numero_recibo", $numero_recibo);
        $stmt->bindParam(":metodo_pago", $metodo_pago);
        $stmt->bindParam(":observaciones", $observaciones);
        $stmt->bindParam(":estado", $estado);

        return $stmt->execute();
    }

    /**
     * Actualizar una venta existente
     */
    public function update($data) {
        $sql = "UPDATE " . $this->table_name . " 
                SET id_socio = :id_socio,
                    comprador = :comprador,
                    cantidad_acciones = :cantidad_acciones,
                    numero_accion = :numero_accion,
                    precio_unitario = :precio_unitario,
                    monto_total = :monto_total,
                    fecha_venta = :fecha_venta,
                    numero_recibo = :numero_recibo,
                    metodo_pago = :metodo_pago,
                    observaciones = :observaciones,
                    estado = :estado,
                    fecha_edicion = NOW()
                WHERE id_venta_accion = :id_venta_accion";

        $stmt = $this->conn->prepare($sql);

        $id_venta_accion = (int)$data['id_venta_accion'];
        $id_socio = !empty($data['id_socio']) ? (int)$data['id_socio'] : null;
        $comprador = trim(strip_tags($data['comprador'] ?? ''));
        $cantidad_acciones = !empty($data['cantidad_acciones']) ? (int)$data['cantidad_acciones'] : 1;
        $numero_accion = trim(strip_tags($data['numero_accion'] ?? ''));
        $precio_unitario = (float)($data['precio_unitario'] ?? 0);
        $monto_total = (float)($data['monto_total'] ?? ($cantidad_acciones * $precio_unitario));
        $fecha_venta = !empty($data['fecha_venta']) ? $data['fecha_venta'] : date('Y-m-d');
        $numero_recibo = trim(strip_tags($data['numero_recibo'] ?? ''));
        $metodo_pago = in_array($data['metodo_pago'] ?? '', ['Efectivo', 'Transferencia', 'QR', 'Depósito', 'Otro']) ? $data['metodo_pago'] : 'Efectivo';
        $observaciones = trim(strip_tags($data['observaciones'] ?? ''));
        $estado = in_array($data['estado'] ?? '', ['Cobrado', 'Anulado']) ? $data['estado'] : 'Cobrado';

        $stmt->bindParam(":id_venta_accion", $id_venta_accion, PDO::PARAM_INT);
        if ($id_socio !== null) {
            $stmt->bindParam(":id_socio", $id_socio, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(":id_socio", null, PDO::PARAM_NULL);
        }
        $stmt->bindParam(":comprador", $comprador);
        $stmt->bindParam(":cantidad_acciones", $cantidad_acciones, PDO::PARAM_INT);
        $stmt->bindParam(":numero_accion", $numero_accion);
        $stmt->bindParam(":precio_unitario", $precio_unitario);
        $stmt->bindParam(":monto_total", $monto_total);
        $stmt->bindParam(":fecha_venta", $fecha_venta);
        $stmt->bindParam(":numero_recibo", $numero_recibo);
        $stmt->bindParam(":metodo_pago", $metodo_pago);
        $stmt->bindParam(":observaciones", $observaciones);
        $stmt->bindParam(":estado", $estado);

        return $stmt->execute();
    }

    /**
     * Eliminar registro de venta
     */
    public function delete($id_venta_accion) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id_venta_accion = ?");
        return $stmt->execute([(int)$id_venta_accion]);
    }

    /**
     * Obtener estadísticas y totales resumidos
     */
    public function getEstadisticas($id_gestion = null) {
        $sql = "SELECT 
                    COUNT(*) AS total_registros,
                    COALESCE(SUM(CASE WHEN estado = 'Cobrado' THEN cantidad_acciones ELSE 0 END), 0) AS total_acciones_cobradas,
                    COALESCE(SUM(CASE WHEN estado = 'Cobrado' THEN monto_total ELSE 0 END), 0) AS total_recaudado,
                    COALESCE(SUM(CASE WHEN estado = 'Anulado' THEN monto_total ELSE 0 END), 0) AS total_anulado
                FROM " . $this->table_name;
        
        $params = [];
        if ($id_gestion !== null && $id_gestion !== '' && $id_gestion !== 'all') {
            $sql .= " WHERE id_gestion = ?";
            $params[] = $id_gestion;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
