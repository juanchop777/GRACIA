-- Base de datos corregida con ON DELETE CASCADE para permitir eliminar productos
-- sin errores por claves foráneas


-- Tabla: usuarios
CREATE TABLE usuarios (
  id CHAR(36) NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(100) NOT NULL,
  contrasena VARCHAR(255) NOT NULL,
  rol ENUM('admin','usuario') DEFAULT 'usuario',
  PRIMARY KEY (id),
  UNIQUE (correo)
);

-- Tabla: categorias
CREATE TABLE categorias (
  id CHAR(36) NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  imagen VARCHAR(255),
  PRIMARY KEY (id)
);

-- Tabla: productos
CREATE TABLE productos (
  id CHAR(36) NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT,
  precio DECIMAL(10,2) NOT NULL,
  stock INT DEFAULT 0,
  categoria_id CHAR(36),
  imagen VARCHAR(255),
  PRIMARY KEY (id),
  FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);

-- Tabla: movimientos_inventario
CREATE TABLE movimientos_inventario (
  id CHAR(36) NOT NULL,
  producto_id CHAR(36) NOT NULL,
  tipo ENUM('entrada','salida','ajuste') NOT NULL,
  cantidad INT NOT NULL,
  descripcion TEXT,
  realizado_por CHAR(36),
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
  FOREIGN KEY (realizado_por) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla: pedidos
CREATE TABLE pedidos (
  id CHAR(36) NOT NULL,
  usuario_id CHAR(36) NOT NULL,
  fecha DATETIME NOT NULL,
  estado ENUM('pendiente','procesado','cancelado','enviado','entregado') NOT NULL,
  fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla: pedido_items
CREATE TABLE pedido_items (
  id CHAR(36) NOT NULL,
  pedido_id CHAR(36) NOT NULL,
  producto_id CHAR(36) NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Tabla: facturas
CREATE TABLE facturas (
  id CHAR(36) NOT NULL,
  pedido_id CHAR(36) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
);

-- Tabla: cajas
CREATE TABLE cajas (
  id CHAR(36) NOT NULL,
  usuario_id CHAR(36) NOT NULL,
  apertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  cierre TIMESTAMP DEFAULT NULL,
  monto_inicial DECIMAL(10,2) NOT NULL,
  monto_final DECIMAL(10,2),
  observaciones TEXT,
  PRIMARY KEY (id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla: tipos_gasto
CREATE TABLE tipos_gasto (
  id CHAR(36) NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT,
  PRIMARY KEY (id)
);

-- Tabla: transacciones_contables
CREATE TABLE transacciones_contables (
  id CHAR(36) NOT NULL,
  tipo ENUM('ingreso','egreso','gasto') NOT NULL,
  descripcion TEXT,
  monto DECIMAL(10,2) NOT NULL,
  relacionado_con ENUM('pedido','compra','otro') DEFAULT 'otro',
  referencia_id CHAR(36),
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  realizado_por CHAR(36),
  tipo_gasto_id CHAR(36),
  PRIMARY KEY (id),
  FOREIGN KEY (realizado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
  FOREIGN KEY (tipo_gasto_id) REFERENCES tipos_gasto(id) ON DELETE SET NULL
);

-- Tabla: configuracion
CREATE TABLE configuracion (
  id INT AUTO_INCREMENT PRIMARY KEY,
  clave VARCHAR(100) NOT NULL UNIQUE,
  valor TEXT,
  descripcion TEXT,
  fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
