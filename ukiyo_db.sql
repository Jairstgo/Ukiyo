
CREATE DATABASE IF NOT EXISTS ukiyo_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ukiyo_db;


CREATE TABLE IF NOT EXISTS usuarios (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(100)    NOT NULL COMMENT 'Nombre completo del empleado',
    usuario     VARCHAR(50)     NOT NULL UNIQUE COMMENT 'Nombre de usuario para iniciar sesión',
    contrasena  VARCHAR(255)    NOT NULL COMMENT 'Hash bcrypt de la contraseña (PHP password_hash)',
    rol         ENUM('admin','empleado') NOT NULL DEFAULT 'empleado' COMMENT 'admin: acceso total | empleado: solo pedidos',
    PRIMARY KEY (id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Usuarios del sistema con sus roles';

-- ============================================================
-- TABLA: categorias
-- Agrupa los platillos en secciones del menú, por ejemplo:
-- Entradas, Sopas y Caldos, Platos Fuertes, Sushi, etc.
-- ============================================================
CREATE TABLE IF NOT EXISTS categorias (
    id      INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre  VARCHAR(100)    NOT NULL UNIQUE COMMENT 'Nombre de la categoría del menú',
    PRIMARY KEY (id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Categorías principales del menú';

-- ============================================================
-- TABLA: subcategorias
-- Divisiones más específicas dentro de una categoría.
-- Por ejemplo, dentro de "Sushi" puede haber "Rollos clásicos"
-- y "Rollos empanizados".
-- ============================================================
CREATE TABLE IF NOT EXISTS subcategorias (
    id           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_categoria INT UNSIGNED    NOT NULL COMMENT 'Categoría padre a la que pertenece',
    nombre       VARCHAR(100)    NOT NULL COMMENT 'Nombre de la subcategoría',
    PRIMARY KEY (id),
    CONSTRAINT fk_subcat_categoria
        FOREIGN KEY (id_categoria) REFERENCES categorias (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Subdivisiones dentro de cada categoría del menú';

-- ============================================================
-- TABLA: platillos
-- Contiene cada platillo o bebida del menú con su precio.
-- La subcategoría es opcional (nullable) porque no todos los
-- platillos necesitan una subdivisión.
-- ============================================================
CREATE TABLE IF NOT EXISTS platillos (
    id               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_categoria     INT UNSIGNED    NOT NULL COMMENT 'Categoría del platillo',
    id_subcategoria  INT UNSIGNED    NULL     COMMENT 'Subcategoría opcional del platillo',
    nombre           VARCHAR(150)    NOT NULL COMMENT 'Nombre del platillo en el menú',
    precio           DECIMAL(8,2)    NOT NULL COMMENT 'Precio de venta en pesos',
    disponible       TINYINT(1)      NOT NULL DEFAULT 1 COMMENT '1=disponible | 0=no disponible',
    PRIMARY KEY (id),
    CONSTRAINT fk_platillo_categoria
        FOREIGN KEY (id_categoria) REFERENCES categorias (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_platillo_subcategoria
        FOREIGN KEY (id_subcategoria) REFERENCES subcategorias (id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Platillos y bebidas disponibles en el menú';

-- ============================================================
-- TABLA: pedidos
-- Registra cada orden que hace un cliente, ya sea para comer
-- en el local, para llevar o a domicilio.
-- ============================================================
CREATE TABLE IF NOT EXISTS pedidos (
    id             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_usuario     INT UNSIGNED    NOT NULL COMMENT 'Empleado que tomó el pedido',
    tipo           ENUM('local','llevar','domicilio') NOT NULL DEFAULT 'local' COMMENT 'Tipo de servicio',
    nombre_cliente VARCHAR(100)    NOT NULL COMMENT 'Nombre del cliente',
    direccion      VARCHAR(255)    NULL     COMMENT 'Solo se llena si el tipo es domicilio',
    telefono       VARCHAR(20)     NULL     COMMENT 'Teléfono del cliente, opcional',
    metodo_pago    ENUM('efectivo','transferencia') NOT NULL DEFAULT 'efectivo',
    estado         ENUM('pendiente','en_preparacion','listo') NOT NULL DEFAULT 'pendiente',
    fecha          DATETIME        NOT NULL DEFAULT (NOW()) COMMENT 'Fecha y hora en que se creó el pedido',
    PRIMARY KEY (id),
    CONSTRAINT fk_pedido_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Pedidos realizados por los clientes';

-- ============================================================
-- TABLA: detalle_pedido
-- Guarda los platillos que forman parte de cada pedido.
-- Se guarda el precio_unitario en el momento de la venta
-- para que no cambie si después se modifica el precio del platillo.
-- ============================================================
CREATE TABLE IF NOT EXISTS detalle_pedido (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_pedido       INT UNSIGNED    NOT NULL COMMENT 'Pedido al que pertenece esta línea',
    id_platillo     INT UNSIGNED    NOT NULL COMMENT 'Platillo pedido',
    cantidad        INT UNSIGNED    NOT NULL DEFAULT 1 COMMENT 'Número de porciones',
    precio_unitario DECIMAL(8,2)    NOT NULL COMMENT 'Precio del platillo al momento de la venta',
    PRIMARY KEY (id),
    CONSTRAINT fk_detalle_pedido
        FOREIGN KEY (id_pedido) REFERENCES pedidos (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_detalle_platillo
        FOREIGN KEY (id_platillo) REFERENCES platillos (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Líneas de cada pedido: qué se pidió y cuánto costó';

-- ============================================================
-- TABLA: inventario
-- Controla los ingredientes o insumos del restaurante.
-- Permite saber si queda suficiente stock antes de preparar
-- un platillo (lógica adicional se maneja desde PHP).
-- ============================================================
CREATE TABLE IF NOT EXISTS inventario (
    id                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre_ingrediente VARCHAR(100)   NOT NULL UNIQUE COMMENT 'Nombre del ingrediente o insumo',
    cantidad          DECIMAL(10,3)   NOT NULL DEFAULT 0 COMMENT 'Cantidad actual en existencia',
    unidad            VARCHAR(20)     NOT NULL COMMENT 'Unidad de medida: kg, litros, piezas, etc.',
    stock_minimo      DECIMAL(10,3)   NOT NULL DEFAULT 0 COMMENT 'Cantidad mínima antes de pedir más',
    PRIMARY KEY (id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Inventario de ingredientes e insumos del restaurante';


-- ============================================================
-- DATOS DE PRUEBA
-- ============================================================

-- ------------------------------------------------------------
-- Categorías del menú
-- ------------------------------------------------------------
INSERT INTO categorias (nombre) VALUES
    ('Entradas'),        -- id 1
    ('Sopas y Caldos'),  -- id 2
    ('Platos Fuertes'),  -- id 3
    ('Sushi'),           -- id 4
    ('Postres'),         -- id 5
    ('Bebidas');         -- id 6

-- ------------------------------------------------------------
-- Subcategorías (asociadas a su categoría correspondiente)
-- ------------------------------------------------------------
INSERT INTO subcategorias (id_categoria, nombre) VALUES
    -- Platos Fuertes (id_categoria = 3)
    (3, 'Arroces'),
    (3, 'Yakisoba'),
    (3, 'Teppanyaki'),
    (3, 'Sugerencias de la casa'),
    (3, 'Cortes de Carne'),
    -- Sushi (id_categoria = 4)
    (4, 'Rollos clásicos'),
    (4, 'Rollos empanizados'),
    -- Bebidas (id_categoria = 6)
    (6, 'Limonadas y Naranjas'),
    (6, 'Cerveza');

-- ------------------------------------------------------------
-- Usuario administrador de prueba
-- Contraseña: admin123
-- Hash generado con PHP: password_hash('admin123', PASSWORD_BCRYPT)
-- ¡Cambiar este hash en producción!
-- ------------------------------------------------------------
INSERT INTO usuarios (nombre, usuario, contrasena, rol) VALUES
    (
        'Administrador Ukiyo',
        'admin',
        '$2y$10$YHqjVl9wnuT6iqJqhE9IduXN7QkKmR3sP8hA5bDwLcTeVf2GxOyZu',
        'admin'
    );

-- ============================================================
-- FIN DEL SCRIPT
-- Para regenerar el hash usa en PHP:
--   echo password_hash('admin123', PASSWORD_BCRYPT);
-- ============================================================
