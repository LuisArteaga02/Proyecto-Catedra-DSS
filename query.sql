-- Insertar los tipos de DTE básicos exigidos por Hacienda
INSERT INTO
  cat_tipo_dte (codigo, nombre, descripcion)
VALUES
  (
    '01',
    'Factura',
    'Factura electrónica para consumidor final'
  ),
  (
    '03',
    'Comprobante de Crédito Fiscal',
    'CCF electrónico para contribuyentes'
  ),
  (
    '11',
    'Factura de Exportación',
    'Documento para operaciones de exportación'
  ),
  (
    '14',
    'Factura de Sujeto Excluido',
    'Documento para compras a sujetos excluidos'
  );