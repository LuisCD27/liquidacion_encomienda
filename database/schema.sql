
-- database/schema.sql
CREATE TABLE IF NOT EXISTS empresas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  razon_social VARCHAR(150) NOT NULL,
  ruc VARCHAR(20) NOT NULL,
  logo VARCHAR(100) DEFAULT NULL,
  activo TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS oficinas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  direccion VARCHAR(200) NOT NULL,
  telefono1 VARCHAR(30),
  telefono2 VARCHAR(30),
  serie VARCHAR(10) NOT NULL
);

CREATE TABLE IF NOT EXISTS catalogos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo ENUM('destino','tipo_pago','vendedor','tipo_servicio') NOT NULL,
  valor VARCHAR(120) NOT NULL
);

CREATE TABLE IF NOT EXISTS numeraciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  oficina_id INT NOT NULL,
  serie VARCHAR(10) NOT NULL,
  ultimo_numero INT NOT NULL DEFAULT 0,
  FOREIGN KEY (oficina_id) REFERENCES oficinas(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS encomiendas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  empresa_id INT NOT NULL,
  oficina_id INT NOT NULL,
  le_serie VARCHAR(10) NOT NULL,
  le_numero INT NOT NULL,
  fecha DATE NOT NULL,
  hora TIME NOT NULL,
  remitente VARCHAR(120),
  consignado VARCHAR(120),
  ruc_dni VARCHAR(20),
  direccion VARCHAR(200),
  cel VARCHAR(30),
  origen VARCHAR(120),
  destino VARCHAR(120),
  tipo_pago VARCHAR(50),
  vendedor VARCHAR(120),
  tipo_servicio VARCHAR(80),
  contenido TEXT,
  cantidad INT DEFAULT 1,
  peso_kg DECIMAL(10,2) DEFAULT 0,
  precio_unit DECIMAL(10,2) DEFAULT 0,
  precio_total DECIMAL(10,2) DEFAULT 0,
  total_s DECIMAL(10,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (empresa_id) REFERENCES empresas(id),
  FOREIGN KEY (oficina_id) REFERENCES oficinas(id)
);
