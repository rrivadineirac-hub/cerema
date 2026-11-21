-- Migración: Tabla para Ingreso por Venta de Acciones
CREATE TABLE IF NOT EXISTS `ingresos_venta_acciones` (
  `id_venta_accion` INT AUTO_INCREMENT PRIMARY KEY,
  `id_gestion` INT NOT NULL,
  `id_socio` INT DEFAULT NULL,
  `comprador` VARCHAR(150) NOT NULL,
  `cantidad_acciones` INT NOT NULL DEFAULT 1,
  `numero_accion` VARCHAR(100) DEFAULT NULL,
  `precio_unitario` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `monto_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `fecha_venta` DATE NOT NULL,
  `numero_recibo` VARCHAR(50) DEFAULT NULL,
  `metodo_pago` ENUM('Efectivo','Transferencia','QR','Depósito','Otro') NOT NULL DEFAULT 'Efectivo',
  `observaciones` TEXT DEFAULT NULL,
  `estado` ENUM('Cobrado','Anulado') NOT NULL DEFAULT 'Cobrado',
  `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `fecha_edicion` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_gestion` (`id_gestion`),
  KEY `idx_socio` (`id_socio`),
  CONSTRAINT `fk_venta_accion_socio` FOREIGN KEY (`id_socio`) REFERENCES `asociados` (`id_socio`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
