<?php
require_once 'Config/database.php';
$db = (new Database())->getConnection();

$sql = "CREATE TABLE IF NOT EXISTS aportes_especiales (
    id_aporte_esp INT AUTO_INCREMENT PRIMARY KEY,
    id_socio INT NOT NULL,
    motivo VARCHAR(255) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    fecha_aporte DATE NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
)";

try {
    $db->exec($sql);
    echo "Table aportes_especiales created successfully.";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
