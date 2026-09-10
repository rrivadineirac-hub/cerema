<?php
require_once __DIR__ . '/../Config/database.php';
$db = (new Database())->getConnection();

$sql = "CREATE TABLE IF NOT EXISTS otros_ingresos (
    id_otro_ingreso INT AUTO_INCREMENT PRIMARY KEY,
    id_gestion INT NOT NULL,
    detalle VARCHAR(255) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    fecha_pago DATE NOT NULL,
    comprobante VARCHAR(100) DEFAULT NULL,
    estado ENUM('Cobrado','Anulado') DEFAULT 'Cobrado',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

$db->exec($sql);
echo "Table otros_ingresos created successfully!\n";
