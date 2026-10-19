<?php
// Model/mvalor_accion.php

class ValorAccionModel {
    private $conn;
    private $table_name = "valor_accion";

    public function __construct($db) {
        $this->conn = $db;
        $this->ensureSchema();
    }

    // Crea la tabla y el campo valor_accion en asociados si no existen
    private function ensureSchema() {
        try {
            $this->conn->exec("CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
                id_valor INT AUTO_INCREMENT PRIMARY KEY,
                id_socio INT NOT NULL,
                numero_accion INT NOT NULL DEFAULT 1,
                valor DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                fecha_valor DATE NOT NULL,
                observaciones TEXT DEFAULT NULL,
                fechaRegistro DATETIME DEFAULT NULL,
                fechaEdicion DATETIME DEFAULT NULL,
                UNIQUE KEY uk_socio_accion (id_socio, numero_accion),
                FOREIGN KEY (id_socio) REFERENCES asociados(id_socio) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        } catch (Exception $e) { }

        try {
            $chk = $this->conn->query("SHOW COLUMNS FROM asociados LIKE 'valor_accion'");
            if ($chk && $chk->rowCount() === 0) {
                $this->conn->exec("ALTER TABLE asociados ADD COLUMN valor_accion DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER cuota_inicial");
            }
        } catch (Exception $e) { }
    }

    public function getBySocioId($id_socio) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " WHERE id_socio = ? ORDER BY numero_accion ASC");
        $stmt->execute([$id_socio]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id_valor) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " WHERE id_valor = ? LIMIT 1");
        $stmt->execute([$id_valor]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function existsAccion($id_socio, $numero_accion, $exclude_id = 0) {
        $stmt = $this->conn->prepare("SELECT id_valor FROM " . $this->table_name . " WHERE id_socio = ? AND numero_accion = ? AND id_valor != ? LIMIT 1");
        $stmt->execute([$id_socio, $numero_accion, (int)$exclude_id]);
        return $stmt->rowCount() > 0;
    }

    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO " . $this->table_name . "
            (id_socio, numero_accion, valor, fecha_valor, observaciones, fechaRegistro)
            VALUES (?, ?, ?, ?, ?, NOW())");
        $ok = $stmt->execute([
            (int)$data['id_socio'],
            (int)$data['numero_accion'],
            (float)$data['valor'],
            $data['fecha_valor'],
            $data['observaciones'] !== '' ? htmlspecialchars(strip_tags($data['observaciones'])) : null
        ]);
        if ($ok) $this->syncSocio($data['id_socio']);
        return $ok;
    }

    public function update($data) {
        $stmt = $this->conn->prepare("UPDATE " . $this->table_name . "
            SET numero_accion = ?, valor = ?, fecha_valor = ?, observaciones = ?, fechaEdicion = NOW()
            WHERE id_valor = ?");
        $ok = $stmt->execute([
            (int)$data['numero_accion'],
            (float)$data['valor'],
            $data['fecha_valor'],
            $data['observaciones'] !== '' ? htmlspecialchars(strip_tags($data['observaciones'])) : null,
            (int)$data['id_valor']
        ]);
        if ($ok) $this->syncSocio($data['id_socio']);
        return $ok;
    }

    public function delete($id_valor) {
        $row = $this->getById($id_valor);
        if (!$row) return false;
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id_valor = ?");
        $ok = $stmt->execute([$id_valor]);
        if ($ok) $this->syncSocio($row['id_socio']);
        return $ok;
    }

    // Actualiza asociados.valor_accion con el valor total de las acciones del socio
    public function syncSocio($id_socio) {
        $stmt = $this->conn->prepare("UPDATE asociados SET valor_accion =
            (SELECT COALESCE(SUM(valor), 0) FROM " . $this->table_name . " WHERE id_socio = ?)
            WHERE id_socio = ?");
        return $stmt->execute([$id_socio, $id_socio]);
    }

    public function getResumenSocio($id_socio) {
        $stmt = $this->conn->prepare("SELECT acciones, COALESCE(valor_accion, 0) AS valor_total FROM asociados WHERE id_socio = ?");
        $stmt->execute([$id_socio]);
        $s = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['acciones' => 1, 'valor_total' => 0];

        $stmt2 = $this->conn->prepare("SELECT COUNT(*) AS registradas FROM " . $this->table_name . " WHERE id_socio = ?");
        $stmt2->execute([$id_socio]);
        $registradas = (int)$stmt2->fetchColumn();

        $acciones = max(1, (int)$s['acciones']);
        $total = (float)$s['valor_total'];
        return [
            'acciones'    => $acciones,
            'registradas' => $registradas,
            'valor_total' => $total,
            'promedio'    => $registradas > 0 ? $total / $registradas : 0
        ];
    }
}
?>
