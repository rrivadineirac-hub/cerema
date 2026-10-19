-- Migración: Valor de la Acción
ALTER TABLE `asociados`
  ADD COLUMN `valor_accion` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `cuota_inicial`;

CREATE TABLE IF NOT EXISTS `valor_accion` (
  `id_valor` INT AUTO_INCREMENT PRIMARY KEY,
  `id_socio` INT NOT NULL,
  `numero_accion` INT NOT NULL DEFAULT 1,
  `valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `fecha_valor` DATE NOT NULL,
  `observaciones` TEXT DEFAULT NULL,
  `fechaRegistro` DATETIME DEFAULT NULL,
  `fechaEdicion` DATETIME DEFAULT NULL,
  UNIQUE KEY `uk_socio_accion` (`id_socio`, `numero_accion`),
  CONSTRAINT `valor_accion_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `asociados` (`id_socio`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
