<?php
// Model/mcuota_inicial.php

class CuotaInicialModel {
    private $conn;
    private $table_name = "cuota_inicial_pagos";

    public function __construct($db) {
        $this->conn = $db;
        // Crear tabla si no existe
        try {
            $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
                id_cuota INT AUTO_INCREMENT PRIMARY KEY,
                id_socio INT NOT NULL,
                numero_accion INT DEFAULT 1,
                numero_recibo VARCHAR(50) DEFAULT NULL,
                concepto VARCHAR(255) DEFAULT 'Pago Cuota Inicial',
                monto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                fecha_pago DATETIME NOT NULL,
                observaciones TEXT DEFAULT NULL,
                estado VARCHAR(20) DEFAULT 'Pagado',
                FOREIGN KEY (id_socio) REFERENCES asociados(id_socio) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $this->conn->exec($query);
        } catch (Exception $e) {
            // Silencioso si ya existe o hay error de permisos
        }
    }

    // Obtener todos los pagos de cuota inicial de un socio
    public function getBySocioId($id_socio) {
        $query = "SELECT c.*, s.nombre, s.ap_paterno, s.ap_materno, s.ci 
                  FROM " . $this->table_name . " c
                  JOIN asociados s ON c.id_socio = s.id_socio
                  WHERE c.id_socio = ?
                  ORDER BY c.fecha_pago DESC, c.id_cuota DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_socio);
        $stmt->execute();
        return $stmt;
    }

    // Obtener un registro por ID
    public function getById($id_cuota) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_cuota = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_cuota);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un pago de cuota inicial
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_socio, numero_accion, numero_recibo, concepto, monto, fecha_pago, observaciones, estado) 
                  VALUES (:id_socio, :numero_accion, :numero_recibo, :concepto, :monto, :fecha_pago, :observaciones, :estado)";
        
        $stmt = $this->conn->prepare($query);

        $id_socio = htmlspecialchars(strip_tags($data['id_socio']));
        $numero_accion = htmlspecialchars(strip_tags($data['numero_accion'] ?? 1));
        $numero_recibo = !empty($data['numero_recibo']) ? htmlspecialchars(strip_tags($data['numero_recibo'])) : null;
        $concepto = !empty($data['concepto']) ? htmlspecialchars(strip_tags($data['concepto'])) : 'Pago Cuota Inicial';
        $monto = (float)($data['monto'] ?? 0);
        $fecha_pago = !empty($data['fecha_pago']) ? htmlspecialchars(strip_tags($data['fecha_pago'])) : date('Y-m-d H:i:s');
        $observaciones = !empty($data['observaciones']) ? htmlspecialchars(strip_tags($data['observaciones'])) : null;
        $estado = htmlspecialchars(strip_tags($data['estado'] ?? 'Pagado'));

        $stmt->bindParam(":id_socio", $id_socio);
        $stmt->bindParam(":numero_accion", $numero_accion);
        $stmt->bindParam(":numero_recibo", $numero_recibo);
        $stmt->bindParam(":concepto", $concepto);
        $stmt->bindParam(":monto", $monto);
        $stmt->bindParam(":fecha_pago", $fecha_pago);
        $stmt->bindParam(":observaciones", $observaciones);
        $stmt->bindParam(":estado", $estado);

        return $stmt->execute();
    }

    // Actualizar un pago
    public function update($data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET numero_accion = :numero_accion,
                      numero_recibo = :numero_recibo,
                      concepto = :concepto,
                      monto = :monto,
                      fecha_pago = :fecha_pago,
                      observaciones = :observaciones,
                      estado = :estado
                  WHERE id_cuota = :id_cuota";

        $stmt = $this->conn->prepare($query);

        $id_cuota = htmlspecialchars(strip_tags($data['id_cuota']));
        $numero_accion = htmlspecialchars(strip_tags($data['numero_accion'] ?? 1));
        $numero_recibo = !empty($data['numero_recibo']) ? htmlspecialchars(strip_tags($data['numero_recibo'])) : null;
        $concepto = !empty($data['concepto']) ? htmlspecialchars(strip_tags($data['concepto'])) : 'Pago Cuota Inicial';
        $monto = (float)($data['monto'] ?? 0);
        $fecha_pago = !empty($data['fecha_pago']) ? htmlspecialchars(strip_tags($data['fecha_pago'])) : date('Y-m-d H:i:s');
        $observaciones = !empty($data['observaciones']) ? htmlspecialchars(strip_tags($data['observaciones'])) : null;
        $estado = htmlspecialchars(strip_tags($data['estado'] ?? 'Pagado'));

        $stmt->bindParam(":id_cuota", $id_cuota);
        $stmt->bindParam(":numero_accion", $numero_accion);
        $stmt->bindParam(":numero_recibo", $numero_recibo);
        $stmt->bindParam(":concepto", $concepto);
        $stmt->bindParam(":monto", $monto);
        $stmt->bindParam(":fecha_pago", $fecha_pago);
        $stmt->bindParam(":observaciones", $observaciones);
        $stmt->bindParam(":estado", $estado);

        return $stmt->execute();
    }

    // Eliminar un pago
    public function delete($id_cuota) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_cuota = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_cuota);
        return $stmt->execute();
    }

    // Calcular resumen de pago de cuota inicial para un socio
    public function getResumenSocio($id_socio) {
        // Obtener datos del socio (acciones y cuota_inicial por acción)
        $queryS = "SELECT acciones, COALESCE(cuota_inicial, 0) as cuota_inicial FROM asociados WHERE id_socio = ?";
        $stmtS = $this->conn->prepare($queryS);
        $stmtS->bindParam(1, $id_socio);
        $stmtS->execute();
        $socioData = $stmtS->fetch(PDO::FETCH_ASSOC);

        $acciones = (int)($socioData['acciones'] ?? 1);
        $cuotaPorAccion = (float)($socioData['cuota_inicial'] ?? 0);
        $metaTotal = $acciones * $cuotaPorAccion;

        // Sumar total pagado
        $queryP = "SELECT COALESCE(SUM(monto), 0) as total_pagado FROM " . $this->table_name . " WHERE id_socio = ? AND (estado = 'Pagado' OR estado IS NULL OR estado = '')";
        $stmtP = $this->conn->prepare($queryP);
        $stmtP->bindParam(1, $id_socio);
        $stmtP->execute();
        $pagadoData = $stmtP->fetch(PDO::FETCH_ASSOC);
        $totalPagado = (float)($pagadoData['total_pagado'] ?? 0);

        $saldoPendiente = max(0, $metaTotal - $totalPagado);

        return [
            'acciones' => $acciones,
            'cuota_por_accion' => $cuotaPorAccion,
            'meta_total' => $metaTotal,
            'total_pagado' => $totalPagado,
            'saldo_pendiente' => $saldoPendiente
        ];
    }
}
?>
