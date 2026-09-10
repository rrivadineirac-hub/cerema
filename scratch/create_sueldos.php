<?php
include 'Config/database.php';
$db = (new Database())->getConnection();
$sql = "CREATE TABLE IF NOT EXISTS `sueldos` (
  `id_sueldo` int(11) NOT NULL AUTO_INCREMENT,
  `id_gestion` int(11) DEFAULT NULL,
  `cargo` varchar(100) NOT NULL,
  `empleado` varchar(150) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `comprobante` varchar(100) DEFAULT NULL,
  `estado` enum('Pagado','Anulado') DEFAULT 'Pagado',
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_sueldo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
try {
    $db->exec($sql);
    echo "Tabla sueldos creada correctamente.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
