ALTER TABLE `factura` 
ADD COLUMN `id_receptor` INT NOT NULL AFTER `id_factura`;

ALTER TABLE `factura`
ADD CONSTRAINT `fk_factura_receptor` FOREIGN KEY (`id_receptor`) REFERENCES `receptor`(`id_receptor`);