
INSERT INTO empresas (razon_social, ruc, logo) VALUES
('CARGO EXPRESS HUNOS E.I.R.L.', '20545613426', 'cargo.png'),
('TURISMO EXPRES LOS HUNOS E.I.R.L.', '20519487005', 'turismo.png'),
('TRANSPORTE LOS HUNOS E.I.R.L.', '20604686238', 'transporte.png'),
('LOGISTICA LOS HUNOS S.A.C.', '20603487274', 'logistica.png');

INSERT INTO oficinas (nombre, direccion, telefono1, telefono2, serie) VALUES
('LIMA - YERBATEROS', 'Av. PARINACOCHAS', '987539412', NULL, '001'),
('LIMA - PARINACOCHAS', 'Av. Nicolás Ayllón N° 1369', '991321561', NULL, '001A'),
('OROYA', 'AV.MIGUEL GRAU N°511', '(084)391915', '987540527', '002'),
('PASCO', 'Psj.celso CURI s/n (Ref. espalda terminal)', '987540383', NULL, '003'),
('PUCARA', 'AV. NICOLAS AYLLON S/N-PUCARA', '987539050', NULL, '005'),
('HUANCAYO', 'Pasaje Bentanyacu s/n lote "F"', '96481722', NULL, '002'),
('CASAPALCA', 'C.Central Km.122 (Hotel cruz de mayo)', '987539412', NULL, '002'),
('COLQUIJIRCA', 'AV. EULOGIO FERNANDINI CDR 2 Y 3', '987539396', NULL, '003'),
('HUAYLLAY', 'JR. JOSE OLAYA N°175', '987539953', '943070986', '004');

-- catálogos
INSERT INTO catalogos (tipo, valor) VALUES
('destino','HUAYLLAY'),('destino','RANSA'),('destino','CERRO LINDO'),('destino','SEVA'),('destino','TICLIO');

INSERT INTO catalogos (tipo, valor) VALUES
('tipo_pago','PAGO DESTINO'),('tipo_pago','CANCELADO'),('tipo_pago','CREDITO');

INSERT INTO catalogos (tipo, valor) VALUES
('vendedor','DANITZA'),('vendedor','MARIELA'),('vendedor','ANTHONY'),
('vendedor','ELIZABETH'),('vendedor','KEVIN'),('vendedor','GEIDY'),
('vendedor','MELIZA'),('vendedor','STEPHANIE'),('vendedor','JOHANNA');

INSERT INTO catalogos (tipo, valor) VALUES
('tipo_servicio','DIRECTO'),('tipo_servicio','F. COMPLETO'),('tipo_servicio','COURRIER'),('tipo_servicio','EXPRESS');

-- Numeraciones iniciales por oficina (independientes)
INSERT INTO numeraciones (oficina_id, serie, ultimo_numero) 
SELECT id, serie, 2050 FROM oficinas;
