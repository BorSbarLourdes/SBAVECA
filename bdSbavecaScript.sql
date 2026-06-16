-- =============================================================
-- BASE DE DATOS: sbaveca
-- Script unificado completo — ejecutar sobre base vacía
-- Cubre: RF01, RF02, RF03, RF04, RF05, RF06, RF07, RF08,
--        RF09, RF10, RF11, RF12, RF13, RF15, RF16, RF17,
--        RF18, RF19, RF21
-- Orden: sin dependencias rotas, triggers al final
-- =============================================================

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS,     UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

USE `sbaveca`;

-- =============================================================
--  BLOQUE 1 — USUARIOS, PERSONAS Y ACCESO (RF01, RF02, RF03, RF21)
-- =============================================================

-- -------------------------------------------------------------
-- Table: usuario
-- Credenciales de acceso. Origen de todos los perfiles del sistema.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`usuario` (
  `idUsu`                INT          NOT NULL AUTO_INCREMENT,
  `nombreUsu`            VARCHAR(50)  NOT NULL,
  `apellidoUsu`          VARCHAR(45)  NOT NULL,
  `correoUsu`            VARCHAR(100) NOT NULL,
  `usuarioUsu`           VARCHAR(50)  NOT NULL,
  `contrasenaUsu`        VARCHAR(64)  NOT NULL,
  `CUILUsu`              VARCHAR(20)  NOT NULL,
  `telefonoUsu`          VARCHAR(15)  NOT NULL,
  `estadoUsu`            ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `rolUsu`               VARCHAR(50)  NOT NULL DEFAULT 'Cliente',
  `tokenRecuperacionUsu` VARCHAR(64)  NULL DEFAULT NULL,
  `expiracionTokenUsu`   DATETIME     NULL DEFAULT NULL,
  PRIMARY KEY (`idUsu`),
  UNIQUE INDEX `uk_usuario_correo` (`correoUsu` ASC),
  UNIQUE INDEX `uk_usuario_username` (`usuarioUsu` ASC),
  UNIQUE INDEX `uk_usuario_cuil`   (`CUILUsu`   ASC)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: persona
-- Perfil extendido vinculado al usuario (datos fiscales, etc.)
-- Depende de: usuario
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`persona` (
  `idPers`              BIGINT       NOT NULL AUTO_INCREMENT,
  `identificacionPers`  VARCHAR(30)  NULL DEFAULT NULL,
  `nombrePers`          VARCHAR(80)  NULL DEFAULT NULL,
  `apellidoPers`        VARCHAR(100) NULL DEFAULT NULL,
  `telefonoPers`        BIGINT       NULL DEFAULT NULL,
  `emailUsuarioPers`    VARCHAR(100) NULL DEFAULT NULL,
  `contraseñaPers`      VARCHAR(75)  NULL DEFAULT NULL,
  `nitPers`             VARCHAR(20)  NULL DEFAULT NULL  COMMENT 'Número de Identificación Tributaria',
  `nombreFiscalPers`    VARCHAR(80)  NULL DEFAULT NULL,
  `direccionFiscalPers` VARCHAR(100) NULL DEFAULT NULL,
  `tokenPers`           VARCHAR(100) NULL DEFAULT NULL,
  `rolIdPers`           BIGINT       NULL DEFAULT NULL,
  `fechaCreaPers`       DATETIME     NULL DEFAULT NULL,
  `estadoPers`          INT          NULL DEFAULT NULL,
  `idUsuarioPers`       INT          NULL DEFAULT NULL,
  PRIMARY KEY (`idPers`),
  INDEX `idx_persona_usuario` (`idUsuarioPers` ASC),
  CONSTRAINT `fk_persona_usuario`
    FOREIGN KEY (`idUsuarioPers`)
    REFERENCES `sbaveca`.`usuario` (`idUsu`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: codigos_verificacion
-- OTP para registro de empleados y administradores
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`codigos_verificacion` (
  `idCod`            INT          NOT NULL AUTO_INCREMENT,
  `emailCod`         VARCHAR(100) NOT NULL,
  `codigoCod`        VARCHAR(6)   NOT NULL,
  `rolSolicitadoCod` ENUM('Empleado','Admin') NOT NULL,
  `fechaCreaCod`     DATETIME     NULL DEFAULT CURRENT_TIMESTAMP,
  `fechaExpCod`      DATETIME     NOT NULL,
  `verificadoCod`    TINYINT(1)   NULL DEFAULT '0',
  PRIMARY KEY (`idCod`),
  INDEX `idx_email_codigo` (`emailCod` ASC, `codigoCod` ASC),
  INDEX `idx_expiracion`   (`fechaExpCod` ASC)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- =============================================================
--  BLOQUE 2 — PBAC: PERFILES, MÓDULOS Y PERMISOS (RF01.4-RF01.6, RF21)
-- =============================================================

-- -------------------------------------------------------------
-- Table: modulo
-- Cada sección del sistema que puede tener permisos asignados
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`modulo` (
  `idMod`          BIGINT      NOT NULL AUTO_INCREMENT,
  `tituloMod`      VARCHAR(50) NOT NULL,
  `descripcionMod` TEXT        NOT NULL,
  `estadoMod`      INT         NOT NULL,
  PRIMARY KEY (`idMod`)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: perfil
-- Roles de acceso (Admin, Empleado, Cliente Web)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`perfil` (
  `idPerfil`          BIGINT      NOT NULL AUTO_INCREMENT,
  `nombrePerfil`      VARCHAR(50) NOT NULL,
  `descripcionPerfil` TEXT        NOT NULL,
  `estadoPerfil`      INT         NOT NULL,
  PRIMARY KEY (`idPerfil`)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: permisos
-- Matriz PBAC: qué puede hacer cada perfil en cada módulo
-- Depende de: perfil, modulo
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`permisos` (
  `idPer`           BIGINT NOT NULL AUTO_INCREMENT,
  `perfil_idPerfil` BIGINT NOT NULL,
  `moduloIdPer`     BIGINT NOT NULL,
  `leerPer`         INT    NOT NULL DEFAULT '0',
  `escribirPer`     INT    NOT NULL DEFAULT '0',
  `actualizarPer`   INT    NOT NULL DEFAULT '0',
  `eliminarPer`     INT    NOT NULL DEFAULT '0',
  PRIMARY KEY (`idPer`),
  INDEX `idx_permisos_modulo`  (`moduloIdPer`     ASC),
  INDEX `idx_permisos_perfil`  (`perfil_idPerfil` ASC),
  CONSTRAINT `fk_permisos_perfil`
    FOREIGN KEY (`perfil_idPerfil`)
    REFERENCES `sbaveca`.`perfil` (`idPerfil`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- =============================================================
--  BLOQUE 3 — PROVEEDORES (RF04)
-- =============================================================

-- -------------------------------------------------------------
-- Table: proveedor
-- Entidades que suministran insumos al negocio
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`proveedor` (
  `idPro`        INT          NOT NULL AUTO_INCREMENT,
  `nombrePro`    VARCHAR(100) NOT NULL,
  `CUITPro`      VARCHAR(20)  NOT NULL,
  `telefonoPro`  VARCHAR(20)  NOT NULL,
  `emailPro`     VARCHAR(100) NOT NULL,
  `direccionPro` VARCHAR(150) NOT NULL,
  `ciudadPro`    VARCHAR(50)  NOT NULL,
  `provinciaPro` VARCHAR(50)  NOT NULL,
  PRIMARY KEY (`idPro`)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- =============================================================
--  BLOQUE 4 — INVENTARIO Y STOCK (RF05 completo)
-- =============================================================

-- -------------------------------------------------------------
-- Table: insumo  [RF05.1, RF05.2, RF05.5]
-- Cada ingrediente/insumo con nombre, categoría, unidad, precio.
-- Depende de: proveedor
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`insumo` (
  `idIns`           INT           NOT NULL AUTO_INCREMENT,
  `nombreIns`       VARCHAR(150)  NOT NULL                  COMMENT 'Nombre del insumo o ingrediente',
  `categoriaIns`    ENUM('Ingrediente','Utensilio','Producto Terminado') NOT NULL DEFAULT 'Ingrediente' COMMENT 'RF05.1',
  `unidadMedidaIns` VARCHAR(30)   NOT NULL                  COMMENT 'kg, gr, lt, unidad, etc.',
  `precioCompraIns` DECIMAL(10,2) NOT NULL DEFAULT 0.00     COMMENT 'Precio de compra unitario — RF05.5',
  `stockMinimoIns`  DECIMAL(10,3) NOT NULL DEFAULT 0.000    COMMENT 'Umbral mínimo para alertas — RF05.3',
  `imagenBlobIns`   MEDIUMBLOB    NULL DEFAULT NULL         COMMENT 'Foto del insumo — RF05.2',
  `imagenTipoIns`   VARCHAR(50)   NULL DEFAULT NULL,
  `imagenNombreIns` VARCHAR(255)  NULL DEFAULT NULL,
  `estadoIns`       ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `proveedorIdPro`  INT           NOT NULL                  COMMENT 'Proveedor principal — RF04.3',
  `fechaCreaIns`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idIns`),
  INDEX `idx_ins_proveedor` (`proveedorIdPro` ASC),
  INDEX `idx_ins_categoria` (`categoriaIns`   ASC),
  CONSTRAINT `fk_insumo_proveedor`
    FOREIGN KEY (`proveedorIdPro`)
    REFERENCES `sbaveca`.`proveedor` (`idPro`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: inventario  [RF05.1, RF05.2, RF05.4 — mejorada]
-- Stock físico de cada insumo. Vinculada 1:1 con insumo.
-- Depende de: insumo
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`inventario` (
  `idInv`                INT           NOT NULL AUTO_INCREMENT,
  `insumoIdInv`          INT           NULL DEFAULT NULL    COMMENT 'Insumo al que pertenece este registro de stock',
  `stockActualInv`       INT           NOT NULL DEFAULT 0,
  `stockActualDecInv`    DECIMAL(10,3) NOT NULL DEFAULT 0.000 COMMENT 'Stock decimal para insumos a granel',
  `stockMinimoInv`       INT           NOT NULL DEFAULT 0,
  `unidadMedidaInv`      VARCHAR(30)   NULL DEFAULT NULL    COMMENT 'Unidad de medida del stock',
  `fechaUltimoIngresoInv` DATE         NOT NULL DEFAULT (CURRENT_DATE),
  PRIMARY KEY (`idInv`),
  UNIQUE INDEX `uq_inv_insumo` (`insumoIdInv` ASC),
  CONSTRAINT `fk_inventario_insumo`
    FOREIGN KEY (`insumoIdInv`)
    REFERENCES `sbaveca`.`insumo` (`idIns`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: alerta_stock  [RF05.3 — NUEVA]
-- Alertas cuando el stock cae bajo el umbral mínimo.
-- Depende de: insumo, usuario
-- NOTA: Se crea ANTES del trigger que la referencia.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`alerta_stock` (
  `idAlerSt`          INT           NOT NULL AUTO_INCREMENT,
  `insumoIdAlerSt`    INT           NOT NULL,
  `stockAlAlerSt`     DECIMAL(10,3) NOT NULL COMMENT 'Stock al momento de la alerta',
  `stockMinimoAlerSt` DECIMAL(10,3) NOT NULL COMMENT 'Umbral mínimo configurado',
  `estadoAlerSt`      ENUM('Pendiente','Leida','Resuelta') NOT NULL DEFAULT 'Pendiente',
  `fechaAlerSt`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fechaResAlerSt`    DATETIME      NULL DEFAULT NULL COMMENT 'Cuándo fue resuelta',
  `usuarioResAlerSt`  INT           NULL DEFAULT NULL COMMENT 'Quien marcó como resuelta',
  PRIMARY KEY (`idAlerSt`),
  INDEX `idx_alertast_insumo` (`insumoIdAlerSt` ASC),
  INDEX `idx_alertast_estado` (`estadoAlerSt`   ASC),
  INDEX `idx_alertast_fecha`  (`fechaAlerSt`    ASC),
  CONSTRAINT `fk_alertast_insumo`
    FOREIGN KEY (`insumoIdAlerSt`)
    REFERENCES `sbaveca`.`insumo` (`idIns`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- =============================================================
--  BLOQUE 5 — CATÁLOGO DE PRODUCTOS (RF15)
-- =============================================================

-- -------------------------------------------------------------
-- Table: categoria
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`categoria` (
  `idCat`           BIGINT       NOT NULL,
  `nombreCat`       VARCHAR(100) NOT NULL,
  `descripcionCat`  TEXT         NOT NULL,
  `imagenBlobCat`   MEDIUMBLOB   NULL DEFAULT NULL,
  `imagenTipoCat`   VARCHAR(50)  NULL DEFAULT NULL,
  `imagenNombreCat` VARCHAR(255) NULL DEFAULT NULL,
  `fechaCreaCat`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estadoCat`       INT          NOT NULL DEFAULT '1',
  PRIMARY KEY (`idCat`)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: subcategoria
-- Depende de: categoria
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`subcategoria` (
  `idSubCat`          INT          NOT NULL,
  `idCat`             BIGINT       NOT NULL,
  `nombreSubCat`      VARCHAR(100) NOT NULL,
  `descripcionSubCat` VARCHAR(450) NOT NULL,
  `estadoSubCat`      ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `fechaCreaSubCat`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idSubCat`, `idCat`),
  UNIQUE INDEX `uq_subcategoria_id` (`idSubCat` ASC),
  INDEX `idx_subcat_cat`            (`idCat`    ASC),
  CONSTRAINT `fk_subcategoria_categoria`
    FOREIGN KEY (`idCat`)
    REFERENCES `sbaveca`.`categoria` (`idCat`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: producto
-- Depende de: subcategoria, inventario, proveedor
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`producto` (
  `idProducto`         INT           NOT NULL AUTO_INCREMENT,
  `IdSubCat`           INT           NOT NULL,
  `nombreProd`         VARCHAR(100)  NOT NULL,
  `descripcionProd`    VARCHAR(450)  NOT NULL,
  `SKUProd`            VARCHAR(50)   NOT NULL,
  `codigoBarrasProd`   VARCHAR(50)   NULL DEFAULT NULL,
  `MarcaProd`          VARCHAR(45)   NOT NULL,
  `precioCostoProd`    DECIMAL(10,2) NOT NULL,
  `precioVentaProd`    DECIMAL(10,2) NOT NULL,
  `precioOfertaProd`   DECIMAL(10,2) NULL DEFAULT NULL,
  `margenGananciaProd` DECIMAL(5,2)  NOT NULL,
  `stockActualProd`    INT           NOT NULL,
  `estadoProd`         ENUM('Activo','Inactivo','Descontinuado') NOT NULL DEFAULT 'Activo',
  `enOfertaProd`       TINYINT       NOT NULL DEFAULT '0',
  `esDestacadoProd`    TINYINT       NOT NULL DEFAULT '0',
  `inventarioIdInv`    INT           NOT NULL,
  `proveedorIdPro`     INT           NOT NULL,
  `imagenBlobProd`     MEDIUMBLOB    NULL DEFAULT NULL,
  `imagenTipoProd`     VARCHAR(50)   NULL DEFAULT NULL,
  `imagenNombreProd`   VARCHAR(255)  NULL DEFAULT NULL,
  PRIMARY KEY (`idProducto`),
  UNIQUE INDEX `SKU_UNIQUE`   (`SKUProd`          ASC),
  UNIQUE INDEX `codigo_barras`(`codigoBarrasProd`  ASC),
  INDEX `idx_prod_subcategoria`(`IdSubCat`         ASC),
  INDEX `idx_prod_inventario`  (`inventarioIdInv`  ASC),
  INDEX `idx_prod_proveedor`   (`proveedorIdPro`   ASC),
  CONSTRAINT `fk_producto_subcategoria`
    FOREIGN KEY (`IdSubCat`)
    REFERENCES `sbaveca`.`subcategoria` (`idSubCat`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_producto_inventario`
    FOREIGN KEY (`inventarioIdInv`)
    REFERENCES `sbaveca`.`inventario` (`idInv`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_producto_proveedor`
    FOREIGN KEY (`proveedorIdPro`)
    REFERENCES `sbaveca`.`proveedor` (`idPro`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- =============================================================
--  BLOQUE 6 — RECETAS Y COSTOS (RF06, RF07)
-- =============================================================

-- -------------------------------------------------------------
-- Table: recetas  [RF06.1, RF06.2, RF07.1, RF07.3 — rediseñada]
-- Recetario digital. Vinculada al producto terminado resultante.
-- Depende de: producto
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`recetas` (
  `idReceta`                  INT           NOT NULL AUTO_INCREMENT,
  `nombreReceta`              VARCHAR(150)  NOT NULL              COMMENT 'Nombre de la preparación',
  `descripcionReceta`         TEXT          NULL DEFAULT NULL,
  `instruccionesReceta`       LONGTEXT      NULL DEFAULT NULL     COMMENT 'Procedimiento técnico de elaboración — RF06.2',
  `porcionesReceta`           INT           NOT NULL DEFAULT 1    COMMENT 'Porciones que rinde',
  `tiempoPreparacionMinRec`   INT           NULL DEFAULT NULL     COMMENT 'Tiempo estimado en minutos',
  `costoProduccionReceta`     DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Calculado desde receta_ingrediente — RF07.1',
  `margenGananciaReceta`      DECIMAL(5,2)  NOT NULL DEFAULT 30.00 COMMENT 'Margen % configurable — RF07.3',
  `precioVentaSugeridoReceta` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Costo × (1 + margen/100) — RF07.3',
  `productoIdRec`             INT           NULL DEFAULT NULL     COMMENT 'Producto final vinculado — RF06.1',
  `estadoReceta`              ENUM('Activa','Inactiva') NOT NULL DEFAULT 'Activa',
  `fechaCreaReceta`           DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fechaModReceta`            DATETIME      NULL DEFAULT NULL     ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idReceta`),
  INDEX `idx_rec_producto` (`productoIdRec` ASC),
  CONSTRAINT `fk_receta_producto`
    FOREIGN KEY (`productoIdRec`)
    REFERENCES `sbaveca`.`producto` (`idProducto`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: receta_ingrediente  [RF06.1, RF06.2, RF07.2 — NUEVA]
-- Pivot: vincula recetas con insumos y cantidades exactas.
-- Depende de: recetas, insumo
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`receta_ingrediente` (
  `recetaIdRecIng`     INT           NOT NULL COMMENT 'Receta a la que pertenece',
  `insumoIdRecIng`     INT           NOT NULL COMMENT 'Insumo del inventario',
  `cantidadNecesaria`  DECIMAL(10,3) NOT NULL COMMENT 'Cantidad requerida para la receta completa',
  `unidadMedidaRecIng` VARCHAR(30)   NOT NULL COMMENT 'Unidad de medida para esta cantidad',
  `costoProporcional`  DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'precioCompraIns × cantidad — RF07.4',
  `observacionRecIng`  VARCHAR(255)  NULL DEFAULT NULL,
  PRIMARY KEY (`recetaIdRecIng`, `insumoIdRecIng`),
  INDEX `idx_recing_insumo` (`insumoIdRecIng` ASC),
  CONSTRAINT `fk_recing_receta`
    FOREIGN KEY (`recetaIdRecIng`)
    REFERENCES `sbaveca`.`recetas` (`idReceta`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_recing_insumo`
    FOREIGN KEY (`insumoIdRecIng`)
    REFERENCES `sbaveca`.`insumo` (`idIns`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- =============================================================
--  BLOQUE 7 — MENÚ SEMANAL (RF08)
-- =============================================================

-- -------------------------------------------------------------
-- Table: menu_semanal  [RF08.1 — NUEVA]
-- Encabezado del calendario semanal de menú.
-- Depende de: usuario
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`menu_semanal` (
  `idMenuSem`        INT      NOT NULL AUTO_INCREMENT,
  `fechaInicioSem`   DATE     NOT NULL COMMENT 'Lunes de la semana planificada',
  `fechaFinSem`      DATE     NOT NULL COMMENT 'Domingo de la semana planificada',
  `estadoSem`        ENUM('Borrador','Publicado','Archivado') NOT NULL DEFAULT 'Borrador',
  `observacionSem`   TEXT     NULL DEFAULT NULL,
  `usuarioCreaIdSem` INT      NOT NULL COMMENT 'Admin/empleado creador',
  `fechaCreaSem`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idMenuSem`),
  UNIQUE INDEX `uq_semana`        (`fechaInicioSem`   ASC),
  INDEX `idx_menusem_usuario`     (`usuarioCreaIdSem` ASC),
  CONSTRAINT `fk_menusem_usuario`
    FOREIGN KEY (`usuarioCreaIdSem`)
    REFERENCES `sbaveca`.`usuario` (`idUsu`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: menu_detalle  [RF08.2, RF08.3 — NUEVA]
-- Detalle diario: qué preparación se asigna a cada día.
-- Depende de: menu_semanal, recetas, producto
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`menu_detalle` (
  `idMenuDet`          INT          NOT NULL AUTO_INCREMENT,
  `menuSemIdMenuDet`   INT          NOT NULL COMMENT 'Semana a la que pertenece',
  `diaSemana`          ENUM('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo') NOT NULL,
  `recetaIdMenuDet`    INT          NULL DEFAULT NULL COMMENT 'Receta planificada para ese día',
  `productoIdMenuDet`  INT          NULL DEFAULT NULL COMMENT 'Producto alternativo sin receta',
  `cantidadPrevista`   INT          NOT NULL DEFAULT 1 COMMENT 'Porciones/unidades previstas — RF08.3',
  `observacionMenuDet` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`idMenuDet`),
  INDEX `idx_menudet_semana`   (`menuSemIdMenuDet`  ASC),
  INDEX `idx_menudet_receta`   (`recetaIdMenuDet`   ASC),
  INDEX `idx_menudet_producto` (`productoIdMenuDet` ASC),
  CONSTRAINT `fk_menudet_semana`
    FOREIGN KEY (`menuSemIdMenuDet`)
    REFERENCES `sbaveca`.`menu_semanal` (`idMenuSem`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_menudet_receta`
    FOREIGN KEY (`recetaIdMenuDet`)
    REFERENCES `sbaveca`.`recetas` (`idReceta`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_menudet_producto`
    FOREIGN KEY (`productoIdMenuDet`)
    REFERENCES `sbaveca`.`producto` (`idProducto`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- =============================================================
--  BLOQUE 8 — CLIENTES Y FIDELIZACIÓN (RF03, RF13)
-- =============================================================

-- -------------------------------------------------------------
-- Table: carrito
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`carrito` (
  `idCar`    INT  NOT NULL AUTO_INCREMENT,
  `estadoCar` ENUM('Activo','Pendiente','Pagado') NOT NULL,
  PRIMARY KEY (`idCar`)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: cliente  [RF03.3, RF03.4, RF13.1, RF13.2 — mejorada]
-- Agrega: puntos acumulados, flag habitual, descuento, baja lógica.
-- Depende de: usuario, carrito
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`cliente` (
  `idCli`         INT          NOT NULL AUTO_INCREMENT,
  `dniCli`        INT          NOT NULL,
  `usuarioIdUsu`  INT          NOT NULL,
  `carritoIdCar`  INT          NOT NULL,
  `puntosAcumCli` INT          NOT NULL DEFAULT 0    COMMENT 'Saldo de puntos de fidelización — RF13.2',
  `esHabitualCli` TINYINT(1)   NOT NULL DEFAULT 0    COMMENT 'Cliente habitual identificado — RF13.1',
  `descuentoCli`  DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Descuento personalizado % — RF03.4',
  `estadoCli`     ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo' COMMENT 'Baja lógica — RF03.3',
  PRIMARY KEY (`idCli`),
  UNIQUE INDEX `uk_cliente_dni` (`dniCli`      ASC),
  INDEX `idx_cliente_usuario`   (`usuarioIdUsu` ASC),
  INDEX `idx_cliente_carrito`   (`carritoIdCar` ASC),
  CONSTRAINT `fk_cliente_usuario`
    FOREIGN KEY (`usuarioIdUsu`)
    REFERENCES `sbaveca`.`usuario` (`idUsu`),
  CONSTRAINT `fk_cliente_carrito`
    FOREIGN KEY (`carritoIdCar`)
    REFERENCES `sbaveca`.`carrito` (`idCar`)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- =============================================================
--  BLOQUE 9 — PEDIDOS (RF09 completo)
-- =============================================================

-- -------------------------------------------------------------
-- Table: pedido  [RF09.1, RF09.2, RF09.4 — mejorada]
-- Agrega: canal de origen, estado de seguimiento, personalización,
-- fecha de entrega estimada.
-- Depende de: persona
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`pedido` (
  `idPed`                BIGINT        NOT NULL AUTO_INCREMENT,
  `persona_idPers`       BIGINT        NOT NULL,
  `referenciaCobroPed`   VARCHAR(255)  NULL DEFAULT NULL,
  `idTransaccionMpPed`   VARCHAR(255)  NULL DEFAULT NULL,
  `datosMpPed`           TEXT          NULL DEFAULT NULL,
  `montoPed`             DECIMAL(11,2) NULL DEFAULT NULL,
  `fechaPed`             DATETIME      NULL DEFAULT NULL,
  `costoEnvioPed`        DECIMAL(10,2) NULL DEFAULT NULL,
  `tipoPagoIdPed`        BIGINT        NULL DEFAULT NULL,
  `direccionEnvioPed`    TEXT          NULL DEFAULT NULL,
  `estadoPed`            VARCHAR(100)  NULL DEFAULT NULL,
  `canalOrigenPed`       ENUM('Presencial','Telefonico','Web') NULL DEFAULT NULL COMMENT 'Canal de origen del pedido — RF09.1',
  `personalizacionPed`   TEXT          NULL DEFAULT NULL     COMMENT 'Especificaciones del cliente — RF09.2',
  `fechaEntregaEstimPed` DATETIME      NULL DEFAULT NULL     COMMENT 'Fecha/hora estimada de entrega — RF09.2',
  `estadoSeguimientoPed` ENUM('Pendiente','En preparacion','Listo','Entregado','Cancelado') NULL DEFAULT 'Pendiente' COMMENT 'Estado operativo — RF09.4',
  PRIMARY KEY (`idPed`),
  INDEX `fk_pedido_persona_idx`  (`persona_idPers`       ASC),
  INDEX `idx_ped_canal`          (`canalOrigenPed`       ASC),
  INDEX `idx_ped_estado_seg`     (`estadoSeguimientoPed` ASC),
  INDEX `idx_ped_entrega`        (`fechaEntregaEstimPed` ASC),
  CONSTRAINT `fk_pedido_persona`
    FOREIGN KEY (`persona_idPers`)
    REFERENCES `sbaveca`.`persona` (`idPers`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: detalle_pedido
-- Depende de: pedido
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`detalle_pedido` (
  `idDetPed`             INT           NOT NULL AUTO_INCREMENT,
  `pedido_idPed`         BIGINT        NOT NULL,
  `productoIdDetPed`     INT           NOT NULL,
  `cantidadDetPed`       INT           NOT NULL,
  `precioUnitarioDetPed` DECIMAL(10,2) NOT NULL,
  `subtotalDetPed`       DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`idDetPed`),
  INDEX `idx_detped_producto`  (`productoIdDetPed` ASC),
  INDEX `idx_detped_pedido`    (`pedido_idPed`     ASC),
  CONSTRAINT `fk_detped_pedido`
    FOREIGN KEY (`pedido_idPed`)
    REFERENCES `sbaveca`.`pedido` (`idPed`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- =============================================================
--  BLOQUE 10 — VENTAS (RF10, RF11, RF12)
-- =============================================================

-- -------------------------------------------------------------
-- Table: tipopago
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`tipopago` (
  `idTipoPago`        INT         NOT NULL AUTO_INCREMENT,
  `modalidadTipoPago` VARCHAR(100) NOT NULL,
  `estadoTipoPago`    TINYINT     NULL DEFAULT '1',
  PRIMARY KEY (`idTipoPago`)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: venta
-- Depende de: cliente (implícito), usuario (empleado)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`venta` (
  `idVen`         INT           NOT NULL AUTO_INCREMENT,
  `numeroVen`     VARCHAR(20)   NOT NULL,
  `fechaVen`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estadoVen`     ENUM('Pendiente','Pagado','Cancelado') NOT NULL,
  `clienteIdVen`  INT           NOT NULL,
  `empleadoIdVen` INT           NOT NULL,
  `metodoPagoVen` VARCHAR(50)   NULL DEFAULT 'Efectivo',
  `TotalVen`      DECIMAL(10,2) NULL DEFAULT '0.00',
  PRIMARY KEY (`idVen`),
  INDEX `idx_venta_cliente`  (`clienteIdVen`  ASC),
  INDEX `idx_venta_empleado` (`empleadoIdVen` ASC)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: detalle_venta
-- Depende de: venta, producto
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`detalle_venta` (
  `idDetVen`             INT           NOT NULL AUTO_INCREMENT,
  `ventaIdDetVen`        INT           NOT NULL,
  `productoIdDetVen`     INT           NOT NULL,
  `cantidadDetVen`       INT           NOT NULL,
  `precioUnitarioDetVen` DECIMAL(10,2) NOT NULL,
  `subtotalDetVen`       DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`idDetVen`),
  INDEX `idx_detventa_venta`    (`ventaIdDetVen`    ASC),
  INDEX `idx_detventa_producto` (`productoIdDetVen` ASC),
  CONSTRAINT `fk_detventa_venta`
    FOREIGN KEY (`ventaIdDetVen`)
    REFERENCES `sbaveca`.`venta` (`idVen`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_detventa_producto`
    FOREIGN KEY (`productoIdDetVen`)
    REFERENCES `sbaveca`.`producto` (`idProducto`)
    ON DELETE RESTRICT
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: recibos  [RF12]
-- Depende de: venta
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`recibos` (
  `idReci`              INT          NOT NULL AUTO_INCREMENT,
  `ventaId`             INT          NOT NULL,
  `numeroReci`          VARCHAR(50)  NOT NULL,
  `htmlContent`         LONGTEXT     NOT NULL,
  `fechaGeneracionReci` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `subtotalReci`        DECIMAL(10,2) NOT NULL,
  `ivaReci`             DECIMAL(10,2) NOT NULL,
  `totalReci`           DECIMAL(10,2) NOT NULL,
  `metodoPagoReci`      VARCHAR(50)  NULL DEFAULT NULL,
  `empleadoNombreReci`  VARCHAR(255) NULL DEFAULT NULL,
  `datosClienteReci`    TEXT         NULL DEFAULT NULL,
  PRIMARY KEY (`idReci`),
  INDEX `idx_numero_recibo` (`numeroReci`          ASC),
  INDEX `idx_venta_id`      (`ventaId`             ASC),
  INDEX `idx_fecha_recibo`  (`fechaGeneracionReci` ASC),
  CONSTRAINT `fk_recibos_venta`
    FOREIGN KEY (`ventaId`)
    REFERENCES `sbaveca`.`venta` (`idVen`)
    ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- =============================================================
--  BLOQUE 11 — MOVIMIENTOS DE INVENTARIO (RF05.4)
-- =============================================================

-- -------------------------------------------------------------
-- Table: movimiento_inventario  [RF05.4 — NUEVA]
-- Auditoría de entradas y salidas de stock.
-- Se crea aquí porque depende de venta y pedido (ya definidas).
-- Depende de: insumo, venta, pedido, usuario
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`movimiento_inventario` (
  `idMovInv`              INT           NOT NULL AUTO_INCREMENT,
  `insumoIdMovInv`        INT           NOT NULL             COMMENT 'Insumo afectado',
  `tipoMovInv`            ENUM('Entrada','Salida','Ajuste') NOT NULL,
  `cantidadMovInv`        DECIMAL(10,3) NOT NULL             COMMENT 'Cantidad movida (siempre positivo)',
  `motivoMovInv`          VARCHAR(200)  NOT NULL             COMMENT 'Compra, Venta, Producción, Ajuste…',
  `ventaIdMovInv`         INT           NULL DEFAULT NULL    COMMENT 'Venta que generó la salida — RF05.4',
  `pedidoIdMovInv`        BIGINT        NULL DEFAULT NULL,
  `usuarioIdMovInv`       INT           NOT NULL             COMMENT 'Quien registró el movimiento',
  `stockResultanteMovInv` DECIMAL(10,3) NULL DEFAULT NULL    COMMENT 'Snapshot de stock post-movimiento',
  `fechaMovInv`           DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idMovInv`),
  INDEX `idx_movinv_insumo`  (`insumoIdMovInv`  ASC),
  INDEX `idx_movinv_venta`   (`ventaIdMovInv`   ASC),
  INDEX `idx_movinv_pedido`  (`pedidoIdMovInv`  ASC),
  INDEX `idx_movinv_usuario` (`usuarioIdMovInv` ASC),
  INDEX `idx_movinv_fecha`   (`fechaMovInv`     ASC),
  CONSTRAINT `fk_movinv_insumo`
    FOREIGN KEY (`insumoIdMovInv`)
    REFERENCES `sbaveca`.`insumo` (`idIns`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_movinv_venta`
    FOREIGN KEY (`ventaIdMovInv`)
    REFERENCES `sbaveca`.`venta` (`idVen`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_movinv_pedido`
    FOREIGN KEY (`pedidoIdMovInv`)
    REFERENCES `sbaveca`.`pedido` (`idPed`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_movinv_usuario`
    FOREIGN KEY (`usuarioIdMovInv`)
    REFERENCES `sbaveca`.`usuario` (`idUsu`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- =============================================================
--  BLOQUE 12 — PRESUPUESTOS (RF10.5, RF18)
-- =============================================================

-- -------------------------------------------------------------
-- Table: presupuesto  [RF10.5, RF18.1, RF18.2, RF18.3 — rediseñada]
-- Soporta ciclo de vida completo y descarga PDF.
-- Depende de: persona, pedido
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`presupuesto` (
  `idPresupuesto`     INT           NOT NULL AUTO_INCREMENT,
  `personaIdPres`     BIGINT        NOT NULL              COMMENT 'Cliente/persona solicitante',
  `fechaEmisionPres`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fechaValidezPres`  DATETIME      NULL DEFAULT NULL     COMMENT 'Vigencia del presupuesto — RF10.5',
  `montoSubtotalPres` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `descuentoPres`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `montoTotalPres`    DECIMAL(10,2) NOT NULL DEFAULT 0.00  COMMENT 'Monto total — RF10.5',
  `estadoPres`        ENUM('Borrador','Enviado','Aceptado','Rechazado','Vencido') NOT NULL DEFAULT 'Borrador' COMMENT 'RF10.5',
  `observacionesPres` TEXT          NULL DEFAULT NULL,
  `pdfContenidoPres`  LONGTEXT      NULL DEFAULT NULL     COMMENT 'HTML/JSON para generar PDF — RF18.2',
  `emailEnviadoPres`  TINYINT(1)    NOT NULL DEFAULT 0    COMMENT '1 si fue enviado por email — RF18.2',
  `pedidoIdPres`      BIGINT        NULL DEFAULT NULL     COMMENT 'Pedido generado si fue aceptado',
  PRIMARY KEY (`idPresupuesto`),
  INDEX `idx_pres_persona` (`personaIdPres` ASC),
  INDEX `idx_pres_estado`  (`estadoPres`   ASC),
  INDEX `idx_pres_pedido`  (`pedidoIdPres` ASC),
  CONSTRAINT `fk_pres_persona`
    FOREIGN KEY (`personaIdPres`)
    REFERENCES `sbaveca`.`persona` (`idPers`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_pres_pedido`
    FOREIGN KEY (`pedidoIdPres`)
    REFERENCES `sbaveca`.`pedido` (`idPed`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: presupuesto_detalle  [RF18.2 — NUEVA]
-- Ítems que componen cada presupuesto.
-- Depende de: presupuesto, producto
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`presupuesto_detalle` (
  `idPresDet`             INT           NOT NULL AUTO_INCREMENT,
  `presupuestoIdPresDet`  INT           NOT NULL,
  `productoIdPresDet`     INT           NULL DEFAULT NULL COMMENT 'Producto del catálogo (opcional)',
  `descripcionPresDet`    VARCHAR(255)  NOT NULL          COMMENT 'Descripción libre si no es producto del catálogo',
  `cantidadPresDet`       INT           NOT NULL DEFAULT 1,
  `precioUnitarioPresDet` DECIMAL(10,2) NOT NULL,
  `subtotalPresDet`       DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`idPresDet`),
  INDEX `idx_presdet_presupuesto` (`presupuestoIdPresDet` ASC),
  INDEX `idx_presdet_producto`    (`productoIdPresDet`    ASC),
  CONSTRAINT `fk_presdet_presupuesto`
    FOREIGN KEY (`presupuestoIdPresDet`)
    REFERENCES `sbaveca`.`presupuesto` (`idPresupuesto`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_presdet_producto`
    FOREIGN KEY (`productoIdPresDet`)
    REFERENCES `sbaveca`.`producto` (`idProducto`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- =============================================================
--  BLOQUE 13 — FIDELIZACIÓN: TRANSACCIONES DE PUNTOS (RF13)
-- =============================================================

-- -------------------------------------------------------------
-- Table: transaccion_puntos  [RF13.2, RF13.3 — NUEVA]
-- Historial de acumulación y canje de puntos por cliente.
-- Se crea aquí porque depende de venta y pedido (ya definidas).
-- Depende de: cliente, venta, pedido
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`transaccion_puntos` (
  `idTransPuntos`        INT          NOT NULL AUTO_INCREMENT,
  `clienteIdTransPuntos` INT          NOT NULL COMMENT 'Cliente titular de los puntos',
  `puntosTrans`          INT          NOT NULL COMMENT 'Positivo = acumulación, Negativo = canje',
  `motivoTrans`          ENUM('Compra','Canje','Ajuste Manual','Bonificacion') NOT NULL DEFAULT 'Compra',
  `ventaIdTransPuntos`   INT          NULL DEFAULT NULL COMMENT 'Venta que generó la acumulación',
  `pedidoIdTransPuntos`  BIGINT       NULL DEFAULT NULL,
  `observacionTrans`     VARCHAR(255) NULL DEFAULT NULL,
  `fechaTrans`           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idTransPuntos`),
  INDEX `idx_transpuntos_cliente` (`clienteIdTransPuntos` ASC),
  INDEX `idx_transpuntos_venta`   (`ventaIdTransPuntos`   ASC),
  INDEX `idx_transpuntos_pedido`  (`pedidoIdTransPuntos`  ASC),
  INDEX `idx_transpuntos_fecha`   (`fechaTrans`           ASC),
  CONSTRAINT `fk_transpuntos_cliente`
    FOREIGN KEY (`clienteIdTransPuntos`)
    REFERENCES `sbaveca`.`cliente` (`idCli`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_transpuntos_venta`
    FOREIGN KEY (`ventaIdTransPuntos`)
    REFERENCES `sbaveca`.`venta` (`idVen`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_transpuntos_pedido`
    FOREIGN KEY (`pedidoIdTransPuntos`)
    REFERENCES `sbaveca`.`pedido` (`idPed`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- =============================================================
--  BLOQUE 14 — TORTAS PERSONALIZADAS (RF17)
-- =============================================================

-- -------------------------------------------------------------
-- Table: torta_opcion  [RF17.1, RF17.3 — NUEVA]
-- Catálogo de opciones disponibles para configurar tortas.
-- Vinculada a insumo para verificar stock en tiempo real.
-- Depende de: insumo
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`torta_opcion` (
  `idTortaOpc`         INT           NOT NULL AUTO_INCREMENT,
  `tipoOpcion`         ENUM('Tamano','Relleno','Cobertura','Decoracion','Otro') NOT NULL,
  `nombreOpcion`       VARCHAR(100)  NOT NULL  COMMENT 'Ej: Grande, Dulce de leche, Ganache',
  `descripcionOpcion`  VARCHAR(255)  NULL DEFAULT NULL,
  `precioAdicional`    DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Incremento al precio base',
  `insumoIdTortaOpc`   INT           NULL DEFAULT NULL COMMENT 'Insumo vinculado para verificar stock — RF17.3',
  `estadoOpcion`       ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `ordenVisualizacion` INT           NOT NULL DEFAULT 0   COMMENT 'Orden en el configurador web',
  PRIMARY KEY (`idTortaOpc`),
  INDEX `idx_tortaopc_tipo`   (`tipoOpcion`       ASC),
  INDEX `idx_tortaopc_insumo` (`insumoIdTortaOpc` ASC),
  CONSTRAINT `fk_tortaopc_insumo`
    FOREIGN KEY (`insumoIdTortaOpc`)
    REFERENCES `sbaveca`.`insumo` (`idIns`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: torta_personalizada  [RF17.1, RF17.2, RF17.4 — rediseñada]
-- Configuración completa elegida por el cliente.
-- Depende de: cliente, pedido, presupuesto
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`torta_personalizada` (
  `idTortaPers`            INT           NOT NULL AUTO_INCREMENT,
  `clienteIdTortaPers`     INT           NOT NULL  COMMENT 'Cliente que configuró la torta',
  `nombreTortaPers`        VARCHAR(150)  NULL DEFAULT NULL COMMENT 'Etiqueta opcional',
  `precioBaseTortaPers`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `precioTotalTortaPers`   DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio calculado en tiempo real — RF17.2',
  `observacionTortaPers`   TEXT          NULL DEFAULT NULL COMMENT 'Instrucciones adicionales del cliente',
  `estadoTortaPers`        ENUM('En configuracion','Guardada','En carrito','Pedida') NOT NULL DEFAULT 'En configuracion',
  `pedidoIdTortaPers`      BIGINT        NULL DEFAULT NULL COMMENT 'Pedido generado al confirmar — RF17.4',
  `presupuestoIdTortaPers` INT           NULL DEFAULT NULL COMMENT 'Presupuesto si se pidió cotización — RF17.4',
  `fechaCreaTortaPers`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idTortaPers`),
  INDEX `idx_tortapers_cliente`      (`clienteIdTortaPers`    ASC),
  INDEX `idx_tortapers_pedido`       (`pedidoIdTortaPers`     ASC),
  INDEX `idx_tortapers_presupuesto`  (`presupuestoIdTortaPers` ASC),
  CONSTRAINT `fk_tortapers_cliente`
    FOREIGN KEY (`clienteIdTortaPers`)
    REFERENCES `sbaveca`.`cliente` (`idCli`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tortapers_pedido`
    FOREIGN KEY (`pedidoIdTortaPers`)
    REFERENCES `sbaveca`.`pedido` (`idPed`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tortapers_presupuesto`
    FOREIGN KEY (`presupuestoIdTortaPers`)
    REFERENCES `sbaveca`.`presupuesto` (`idPresupuesto`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: torta_personalizada_opcion  [RF17.1 — NUEVA]
-- Pivot N:N: opciones elegidas en cada torta configurada.
-- Depende de: torta_personalizada, torta_opcion
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`torta_personalizada_opcion` (
  `tortaPersId` INT NOT NULL COMMENT 'Torta configurada',
  `tortaOpcId`  INT NOT NULL COMMENT 'Opción elegida',
  PRIMARY KEY (`tortaPersId`, `tortaOpcId`),
  INDEX `idx_torta_opc` (`tortaOpcId` ASC),
  CONSTRAINT `fk_tortapersopc_torta`
    FOREIGN KEY (`tortaPersId`)
    REFERENCES `sbaveca`.`torta_personalizada` (`idTortaPers`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tortapersopc_opcion`
    FOREIGN KEY (`tortaOpcId`)
    REFERENCES `sbaveca`.`torta_opcion` (`idTortaOpc`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: pedido_personalizado  [RF09.2, RF17.4 — rediseñada]
-- Vincula un pedido estándar con su torta personalizada.
-- Depende de: pedido, torta_personalizada
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`pedido_personalizado` (
  `idPedPers`          INT    NOT NULL AUTO_INCREMENT,
  `pedidoIdPedPers`    BIGINT NOT NULL COMMENT 'Pedido base',
  `tortaPersIdPedPers` INT    NOT NULL COMMENT 'Torta configurada asociada',
  `cantidadPedPers`    INT    NOT NULL DEFAULT 1,
  `observacionPedPers` TEXT   NULL DEFAULT NULL COMMENT 'Instrucciones finales del cliente',
  PRIMARY KEY (`idPedPers`),
  INDEX `idx_pedpers_pedido` (`pedidoIdPedPers`    ASC),
  INDEX `idx_pedpers_torta`  (`tortaPersIdPedPers` ASC),
  CONSTRAINT `fk_pedpers_pedido`
    FOREIGN KEY (`pedidoIdPedPers`)
    REFERENCES `sbaveca`.`pedido` (`idPed`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_pedpers_torta`
    FOREIGN KEY (`tortaPersIdPedPers`)
    REFERENCES `sbaveca`.`torta_personalizada` (`idTortaPers`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- =============================================================
--  BLOQUE 15 — E-COMMERCE (RF15, RF16, RF19)
-- =============================================================

-- -------------------------------------------------------------
-- Table: producto_carrito
-- Depende de: producto, carrito
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`producto_carrito` (
  `idProducto`              INT           NOT NULL,
  `idCarrito`               INT           NOT NULL,
  `cantidadProductoProdCar` INT           NOT NULL DEFAULT '1',
  `precioUnitarioProdCar`   DECIMAL(10,2) NOT NULL,
  `subtotalProdCar`         DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`idProducto`, `idCarrito`),
  INDEX `idx_pc_carrito` (`idCarrito` ASC),
  CONSTRAINT `fk_pc_carrito`
    FOREIGN KEY (`idCarrito`)
    REFERENCES `sbaveca`.`carrito` (`idCar`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_pc_producto`
    FOREIGN KEY (`idProducto`)
    REFERENCES `sbaveca`.`producto` (`idProducto`)
    ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: favorito
-- Depende de: cliente, producto
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`favorito` (
  `idFav`         INT NOT NULL AUTO_INCREMENT,
  `clienteIdFav`  INT NOT NULL,
  `productoIdFav` INT NOT NULL,
  PRIMARY KEY (`idFav`),
  INDEX `idx_fav_cliente`  (`clienteIdFav`  ASC),
  INDEX `idx_fav_producto` (`productoIdFav` ASC),
  CONSTRAINT `fk_favorito_cliente`
    FOREIGN KEY (`clienteIdFav`)
    REFERENCES `sbaveca`.`cliente` (`idCli`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_favorito_producto`
    FOREIGN KEY (`productoIdFav`)
    REFERENCES `sbaveca`.`producto` (`idProducto`)
    ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: reembolso
-- Depende de: pedido
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`reembolso` (
  `idReem`            BIGINT       NOT NULL AUTO_INCREMENT,
  `pedidoIdReem`      BIGINT       NOT NULL,
  `idTransaccionReem` VARCHAR(255) NOT NULL,
  `datosReem`         TEXT         NOT NULL,
  `observacionReem`   TEXT         NOT NULL,
  `estadoReem`        VARCHAR(150) NOT NULL,
  PRIMARY KEY (`idReem`),
  INDEX `idx_pedidoid` (`pedidoIdReem` ASC),
  CONSTRAINT `fk_reembolso_pedido`
    FOREIGN KEY (`pedidoIdReem`)
    REFERENCES `sbaveca`.`pedido` (`idPed`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: resenas
-- Depende de: producto, usuario
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`resenas` (
  `idRes`           INT        NOT NULL AUTO_INCREMENT,
  `productoIdRes`   INT        NOT NULL,
  `pedidoIdRes`     INT        NULL DEFAULT NULL COMMENT 'Pedido donde se compró el producto',
  `usuarioIdRes`    INT        NULL DEFAULT NULL COMMENT 'Usuario registrado',
  `calificacionRes` TINYINT(1) NOT NULL          COMMENT 'Calificación 1 a 5 estrellas',
  `tituloRes`       VARCHAR(200) NULL DEFAULT NULL,
  `comentarioRes`   TEXT       NOT NULL,
  `fechaCreaRes`    DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estadoRes`       TINYINT(1) NOT NULL DEFAULT '1' COMMENT '1=Activo, 0=Inactivo',
  `verificadoRes`   TINYINT(1) NOT NULL DEFAULT '0' COMMENT '1=Compra verificada',
  `utilPositivoRes` INT        NOT NULL DEFAULT '0',
  `utilNegativoRes` INT        NOT NULL DEFAULT '0',
  PRIMARY KEY (`idRes`),
  INDEX `idx_resena_producto`         (`productoIdRes` ASC),
  INDEX `idx_resena_usuario`          (`usuarioIdRes`  ASC),
  INDEX `idx_resena_calificacion`     (`calificacionRes` ASC),
  INDEX `idx_resena_estado`           (`estadoRes`     ASC),
  INDEX `idx_resena_prod_estado_fecha`(`productoIdRes` ASC, `estadoRes` ASC, `fechaCreaRes` ASC),
  CONSTRAINT `fk_resena_producto`
    FOREIGN KEY (`productoIdRes`)
    REFERENCES `sbaveca`.`producto` (`idProducto`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_resena_usuario`
    FOREIGN KEY (`usuarioIdRes`)
    REFERENCES `sbaveca`.`usuario` (`idUsu`)
    ON DELETE SET NULL
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- -------------------------------------------------------------
-- Table: contacto
-- Formulario de contacto del sitio web
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sbaveca`.`contacto` (
  `idCont`            BIGINT       NOT NULL AUTO_INCREMENT,
  `nombreCont`        VARCHAR(200) NOT NULL,
  `emailCont`         VARCHAR(200) NOT NULL,
  `mensajeCont`       TEXT         NOT NULL,
  `ipCont`            VARCHAR(15)  NOT NULL,
  `dispositivoCont`   VARCHAR(25)  NOT NULL,
  `usuarioAgenteCont` TEXT         NOT NULL,
  `fechaCreaCont`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idCont`)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;


-- =============================================================
--  BLOQUE 16 — TRIGGERS
--  IMPORTANTE: van siempre al final, después de todas las tablas.
-- =============================================================

DELIMITER $$

-- -------------------------------------------------------------
-- Trigger: usuario INSERT → crear persona automáticamente
-- RF01/RF03: sincronización usuario → persona
-- -------------------------------------------------------------
DROP TRIGGER IF EXISTS `sbaveca`.`sync_usuario_to_persona`$$
CREATE
DEFINER=`root`@`localhost`
TRIGGER `sbaveca`.`sync_usuario_to_persona`
AFTER INSERT ON `sbaveca`.`usuario`
FOR EACH ROW
BEGIN
    IF NOT EXISTS (SELECT 1 FROM persona WHERE idUsuarioPers = NEW.idUsu) THEN
        INSERT INTO persona (
            idUsuarioPers, identificacionPers, nombrePers, apellidoPers,
            telefonoPers, emailUsuarioPers, contraseñaPers, estadoPers, fechaCreaPers
        )
        VALUES (
            NEW.idUsu,
            NEW.CUILUsu,
            NEW.nombreUsu,
            NEW.apellidoUsu,
            CASE WHEN NEW.telefonoUsu REGEXP '^[0-9]+$'
                 THEN CAST(NEW.telefonoUsu AS UNSIGNED)
                 ELSE NULL END,
            NEW.correoUsu,
            NEW.contrasenaUsu,
            CASE WHEN NEW.estadoUsu = 'Activo' THEN 1 ELSE 2 END,
            NOW()
        );
    END IF;
END$$


-- -------------------------------------------------------------
-- Trigger: usuario UPDATE → actualizar persona
-- RF01: cambios en usuario se propagan a persona
-- -------------------------------------------------------------
DROP TRIGGER IF EXISTS `sbaveca`.`sync_usuario_update_to_persona`$$
CREATE
DEFINER=`root`@`localhost`
TRIGGER `sbaveca`.`sync_usuario_update_to_persona`
AFTER UPDATE ON `sbaveca`.`usuario`
FOR EACH ROW
BEGIN
    UPDATE persona
    SET
        identificacionPers = NEW.CUILUsu,
        nombrePers         = NEW.nombreUsu,
        apellidoPers       = NEW.apellidoUsu,
        telefonoPers       = CASE WHEN NEW.telefonoUsu REGEXP '^[0-9]+$'
                                  THEN CAST(NEW.telefonoUsu AS UNSIGNED)
                                  ELSE telefonoPers END,
        emailUsuarioPers   = NEW.correoUsu,
        contraseñaPers     = NEW.contrasenaUsu,
        estadoPers         = CASE WHEN NEW.estadoUsu = 'Activo' THEN 1 ELSE 2 END
    WHERE idUsuarioPers = NEW.idUsu;
END$$


-- -------------------------------------------------------------
-- Trigger: usuario INSERT → crear carrito y cliente (si rolUsu = Cliente)
-- RF03: alta automática del cliente al registrarse
-- -------------------------------------------------------------
DROP TRIGGER IF EXISTS `sbaveca`.`crear_cliente_despues_registro`$$
CREATE
DEFINER=`root`@`localhost`
TRIGGER `sbaveca`.`crear_cliente_despues_registro`
AFTER INSERT ON `sbaveca`.`usuario`
FOR EACH ROW
BEGIN
    DECLARE nuevo_carrito_id INT;

    IF NEW.rolUsu = 'Cliente' THEN
        INSERT INTO carrito (estadoCar) VALUES ('Activo');
        SET nuevo_carrito_id = LAST_INSERT_ID();

        INSERT INTO cliente (dniCli, usuarioIdUsu, carritoIdCar)
        VALUES (
            CAST(REPLACE(REPLACE(NEW.CUILUsu, '-', ''), ' ', '') AS UNSIGNED) % 100000000,
            NEW.idUsu,
            nuevo_carrito_id
        );
    END IF;
END$$


-- -------------------------------------------------------------
-- Trigger: detalle_venta INSERT → descontar stock del producto
-- RF05.4: descuento automático al confirmar una venta
-- -------------------------------------------------------------
DROP TRIGGER IF EXISTS `sbaveca`.`trg_venta_descontar_stock`$$
CREATE
DEFINER=`root`@`localhost`
TRIGGER `sbaveca`.`trg_venta_descontar_stock`
AFTER INSERT ON `sbaveca`.`detalle_venta`
FOR EACH ROW
BEGIN
    UPDATE `sbaveca`.`producto`
    SET `stockActualProd` = `stockActualProd` - NEW.cantidadDetVen
    WHERE `idProducto` = NEW.productoIdDetVen;
END$$


-- -------------------------------------------------------------
-- Trigger: inventario UPDATE → generar alerta si stock < mínimo
-- RF05.3: alertas automáticas de stock bajo
-- La columna insumoIdInv ya existe porque se creó en este script.
-- -------------------------------------------------------------
DROP TRIGGER IF EXISTS `sbaveca`.`trg_alerta_stock_bajo`$$
CREATE
DEFINER=`root`@`localhost`
TRIGGER `sbaveca`.`trg_alerta_stock_bajo`
AFTER UPDATE ON `sbaveca`.`inventario`
FOR EACH ROW
BEGIN
    IF NEW.stockActualInv < NEW.stockMinimoInv
       AND NEW.insumoIdInv IS NOT NULL
       AND NOT EXISTS (
           SELECT 1 FROM `sbaveca`.`alerta_stock`
           WHERE `insumoIdAlerSt` = NEW.insumoIdInv
             AND `estadoAlerSt`   = 'Pendiente'
       )
    THEN
        INSERT INTO `sbaveca`.`alerta_stock`
          (`insumoIdAlerSt`, `stockAlAlerSt`, `stockMinimoAlerSt`, `estadoAlerSt`)
        VALUES
          (NEW.insumoIdInv, NEW.stockActualInv, NEW.stockMinimoInv, 'Pendiente');
    END IF;
END$$


-- -------------------------------------------------------------
-- Trigger: venta UPDATE → acumular puntos al cliente al pagar
-- RF13.3: 1 punto por cada $100 al marcar venta como 'Pagado'
-- (el divisor 100 puede ajustarse según la regla de negocio)
-- -------------------------------------------------------------
DROP TRIGGER IF EXISTS `sbaveca`.`trg_acumular_puntos_venta`$$
CREATE
DEFINER=`root`@`localhost`
TRIGGER `sbaveca`.`trg_acumular_puntos_venta`
AFTER UPDATE ON `sbaveca`.`venta`
FOR EACH ROW
BEGIN
    DECLARE puntos_ganados INT;

    IF NEW.estadoVen = 'Pagado' AND OLD.estadoVen != 'Pagado' THEN
        SET puntos_ganados = FLOOR(NEW.TotalVen / 100);

        IF puntos_ganados > 0 THEN
            UPDATE `sbaveca`.`cliente`
            SET `puntosAcumCli` = `puntosAcumCli` + puntos_ganados
            WHERE `idCli` = NEW.clienteIdVen;

            INSERT INTO `sbaveca`.`transaccion_puntos`
              (`clienteIdTransPuntos`, `puntosTrans`, `motivoTrans`,
               `ventaIdTransPuntos`, `observacionTrans`)
            VALUES
              (NEW.clienteIdVen, puntos_ganados, 'Compra', NEW.idVen,
               CONCAT('Puntos por venta #', NEW.numeroVen));
        END IF;
    END IF;
END$$

DELIMITER ;


-- =============================================================
-- RESTAURAR CONFIGURACIÓN
-- =============================================================
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
