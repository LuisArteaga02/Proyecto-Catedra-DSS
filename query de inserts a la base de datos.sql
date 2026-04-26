INSERT INTO `cat_departamento` (`codigo`, `nombre`) VALUES
	("1", "San Salvador");
	
INSERT INTO `cat_municipio` (`codigo`, `codigo_depto`, `nombre`) VALUES
	("1", "1", "Soyapango");
	
INSERT INTO `cat_actividad_economica` (`codigo`, `descripcion`) VALUES
	("1", "Consumo final");
	
INSERT INTO `cat_tipo_documento` (`codigo`, `nombre`) VALUES
	("13", "DUI");
-- 1. Desactivamos las llaves foráneas temporalmente para poder actualizar en cascada
SET FOREIGN_KEY_CHECKS=0;

-- 2. Actualizamos el Departamento a 2 dígitos (Hacienda usa '06' para San Salvador, lo pondremos oficial)
UPDATE cat_departamento SET codigo = '06' WHERE codigo = '1';

-- 3. Actualizamos el Municipio a 2 dígitos (Depto '06', Municipio '14' es San Salvador, pero pondremos '01' para tu Soyapango)
UPDATE cat_municipio SET codigo = '01', codigo_depto = '06' WHERE codigo = '1' AND codigo_depto = '1';

-- 4. Actualizamos la Actividad Económica a 5 dígitos (Consumidor Final suele ser 10005)
UPDATE cat_actividad_economica SET codigo = '10005' WHERE codigo = '1';

-- 5. Sincronizamos a tu Emisor (Pizzería) para que haga match perfecto con los catálogos
UPDATE emisor 
SET departamento = '06', 
    municipio = '01', 
    codigo_actividad_economica = '10005' 
WHERE id_emisor = 1;

-- 6. Volvemos a activar la protección de la base de datos
SET FOREIGN_KEY_CHECKS=1;


