-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 16-06-2026 a las 10:51:39
-- Versión del servidor: 8.0.46
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sbaveca`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alerta_stock`
--

DROP TABLE IF EXISTS `alerta_stock`;
CREATE TABLE IF NOT EXISTS `alerta_stock` (
  `idAlerSt` int NOT NULL AUTO_INCREMENT,
  `insumoIdAlerSt` int NOT NULL,
  `stockAlAlerSt` decimal(10,3) NOT NULL COMMENT 'Stock al momento de la alerta',
  `stockMinimoAlerSt` decimal(10,3) NOT NULL COMMENT 'Umbral mínimo configurado',
  `estadoAlerSt` enum('Pendiente','Leida','Resuelta') NOT NULL DEFAULT 'Pendiente',
  `fechaAlerSt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fechaResAlerSt` datetime DEFAULT NULL COMMENT 'Cuándo fue resuelta',
  `usuarioResAlerSt` int DEFAULT NULL COMMENT 'Quien marcó como resuelta',
  PRIMARY KEY (`idAlerSt`),
  KEY `idx_alertast_insumo` (`insumoIdAlerSt`),
  KEY `idx_alertast_estado` (`estadoAlerSt`),
  KEY `idx_alertast_fecha` (`fechaAlerSt`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `alerta_stock`
--

INSERT INTO `alerta_stock` (`idAlerSt`, `insumoIdAlerSt`, `stockAlAlerSt`, `stockMinimoAlerSt`, `estadoAlerSt`, `fechaAlerSt`, `fechaResAlerSt`, `usuarioResAlerSt`) VALUES
(1, 4, 4.000, 5.000, 'Pendiente', '2026-06-15 20:06:00', NULL, NULL),
(2, 2, 4.000, 5.000, 'Pendiente', '2026-06-15 21:23:53', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

DROP TABLE IF EXISTS `carrito`;
CREATE TABLE IF NOT EXISTS `carrito` (
  `idCar` int NOT NULL AUTO_INCREMENT,
  `estadoCar` enum('Activo','Pendiente','Pagado') NOT NULL,
  PRIMARY KEY (`idCar`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`idCar`, `estadoCar`) VALUES
(1, 'Activo'),
(2, 'Activo'),
(3, 'Activo'),
(4, 'Activo'),
(5, 'Activo'),
(6, 'Activo'),
(7, 'Activo'),
(8, 'Activo'),
(9, 'Activo'),
(10, 'Activo'),
(11, 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

DROP TABLE IF EXISTS `categoria`;
CREATE TABLE IF NOT EXISTS `categoria` (
  `idCat` bigint NOT NULL,
  `nombreCat` varchar(100) NOT NULL,
  `descripcionCat` text NOT NULL,
  `imagenBlobCat` mediumblob,
  `imagenTipoCat` varchar(50) DEFAULT NULL,
  `imagenNombreCat` varchar(255) DEFAULT NULL,
  `fechaCreaCat` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estadoCat` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`idCat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`idCat`, `nombreCat`, `descripcionCat`, `imagenBlobCat`, `imagenTipoCat`, `imagenNombreCat`, `fechaCreaCat`, `estadoCat`) VALUES
(1, 'Tortas y Pasteles', 'Tortas artesanales, pasteles y preparaciones especiales', NULL, NULL, NULL, '2026-06-16 01:09:01', 1),
(2, 'Facturas y Medialunas', 'Masas hojaldradas y panes dulces de panadería', NULL, NULL, NULL, '2026-06-16 01:09:01', 1),
(3, 'Postres individuales', 'Mousse, budines, flanes y porciones individuales', NULL, NULL, NULL, '2026-06-16 01:09:01', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

DROP TABLE IF EXISTS `cliente`;
CREATE TABLE IF NOT EXISTS `cliente` (
  `idCli` int NOT NULL AUTO_INCREMENT,
  `dniCli` int NOT NULL,
  `usuarioIdUsu` int NOT NULL,
  `carritoIdCar` int NOT NULL,
  `puntosAcumCli` int NOT NULL DEFAULT '0' COMMENT 'Saldo de puntos de fidelización — RF13.2',
  `esHabitualCli` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Cliente habitual identificado — RF13.1',
  `descuentoCli` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Descuento personalizado % — RF03.4',
  `estadoCli` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo' COMMENT 'Baja lógica — RF03.3',
  PRIMARY KEY (`idCli`),
  UNIQUE KEY `uk_cliente_dni` (`dniCli`),
  KEY `idx_cliente_usuario` (`usuarioIdUsu`),
  KEY `idx_cliente_carrito` (`carritoIdCar`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`idCli`, `dniCli`, `usuarioIdUsu`, `carritoIdCar`, `puntosAcumCli`, `esHabitualCli`, `descuentoCli`, `estadoCli`) VALUES
(1, 93776029, 1, 1, 0, 0, 0.00, 'Activo'),
(2, 40094639, 2, 2, 0, 0, 0.00, 'Activo'),
(3, 6996829, 3, 3, 0, 0, 0.00, 'Activo'),
(4, 78632699, 4, 4, 0, 0, 0.00, 'Activo'),
(5, 60870159, 5, 5, 0, 0, 0.00, 'Activo'),
(6, 98045869, 6, 6, 0, 0, 0.00, 'Activo'),
(7, 99001001, 11, 7, 0, 0, 0.00, 'Activo'),
(8, 99001002, 12, 8, 0, 0, 0.00, 'Activo'),
(9, 99001003, 13, 9, 0, 0, 0.00, 'Activo'),
(10, 99001004, 14, 10, 0, 0, 0.00, 'Activo'),
(11, 99001005, 15, 11, 0, 0, 0.00, 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `codigos_verificacion`
--

DROP TABLE IF EXISTS `codigos_verificacion`;
CREATE TABLE IF NOT EXISTS `codigos_verificacion` (
  `idCod` int NOT NULL AUTO_INCREMENT,
  `emailCod` varchar(100) NOT NULL,
  `codigoCod` varchar(6) NOT NULL,
  `rolSolicitadoCod` enum('Empleado','Admin') NOT NULL,
  `fechaCreaCod` datetime DEFAULT CURRENT_TIMESTAMP,
  `fechaExpCod` datetime NOT NULL,
  `verificadoCod` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`idCod`),
  KEY `idx_email_codigo` (`emailCod`,`codigoCod`),
  KEY `idx_expiracion` (`fechaExpCod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contacto`
--

DROP TABLE IF EXISTS `contacto`;
CREATE TABLE IF NOT EXISTS `contacto` (
  `idCont` bigint NOT NULL AUTO_INCREMENT,
  `nombreCont` varchar(200) NOT NULL,
  `emailCont` varchar(200) NOT NULL,
  `mensajeCont` text NOT NULL,
  `ipCont` varchar(15) NOT NULL,
  `dispositivoCont` varchar(25) NOT NULL,
  `usuarioAgenteCont` text NOT NULL,
  `fechaCreaCont` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idCont`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dashboard_permisos`
--

DROP TABLE IF EXISTS `dashboard_permisos`;
CREATE TABLE IF NOT EXISTS `dashboard_permisos` (
  `idDashPer` int NOT NULL AUTO_INCREMENT,
  `nombreDashPer` varchar(100) NOT NULL,
  `fechaCreaDashPer` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idDashPer`),
  UNIQUE KEY `uk_dashboard_permisos_nombre` (`nombreDashPer`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `dashboard_permisos`
--

INSERT INTO `dashboard_permisos` (`idDashPer`, `nombreDashPer`, `fechaCreaDashPer`) VALUES
(1, 'Acceder al Dashboard', '2026-06-15 11:21:37'),
(2, 'Gestionar Stock', '2026-06-15 11:21:37'),
(3, 'Gestionar Recetas', '2026-06-15 11:21:37'),
(4, 'Gestionar Pedidos', '2026-06-15 11:21:37'),
(5, 'Gestionar Ventas', '2026-06-15 11:21:37'),
(6, 'Gestionar Clientes', '2026-06-15 11:21:37'),
(7, 'Gestionar Roles', '2026-06-15 11:21:37'),
(8, 'Ver Historial de Ventas', '2026-06-15 12:35:47'),
(9, 'Modificar Mi Perfil', '2026-06-15 22:14:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

DROP TABLE IF EXISTS `detalle_pedido`;
CREATE TABLE IF NOT EXISTS `detalle_pedido` (
  `idDetPed` int NOT NULL AUTO_INCREMENT,
  `pedido_idPed` bigint NOT NULL,
  `productoIdDetPed` int NOT NULL,
  `cantidadDetPed` int NOT NULL,
  `precioUnitarioDetPed` decimal(10,2) NOT NULL,
  `subtotalDetPed` decimal(10,2) NOT NULL,
  PRIMARY KEY (`idDetPed`),
  KEY `idx_detped_producto` (`productoIdDetPed`),
  KEY `idx_detped_pedido` (`pedido_idPed`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`idDetPed`, `pedido_idPed`, `productoIdDetPed`, `cantidadDetPed`, `precioUnitarioDetPed`, `subtotalDetPed`) VALUES
(1, 1, 1, 1, 4800.00, 4800.00),
(2, 2, 2, 3, 4200.00, 12600.00),
(3, 3, 3, 1, 900.00, 900.00),
(4, 4, 4, 3, 350.00, 1050.00),
(5, 5, 5, 1, 620.00, 620.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
--

DROP TABLE IF EXISTS `detalle_venta`;
CREATE TABLE IF NOT EXISTS `detalle_venta` (
  `idDetVen` int NOT NULL AUTO_INCREMENT,
  `ventaIdDetVen` int NOT NULL,
  `productoIdDetVen` int NOT NULL,
  `cantidadDetVen` int NOT NULL,
  `precioUnitarioDetVen` decimal(10,2) NOT NULL,
  `subtotalDetVen` decimal(10,2) NOT NULL,
  PRIMARY KEY (`idDetVen`),
  KEY `idx_detventa_venta` (`ventaIdDetVen`),
  KEY `idx_detventa_producto` (`productoIdDetVen`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `detalle_venta`
--

INSERT INTO `detalle_venta` (`idDetVen`, `ventaIdDetVen`, `productoIdDetVen`, `cantidadDetVen`, `precioUnitarioDetVen`, `subtotalDetVen`) VALUES
(1, 1, 1, 4, 4800.00, 19200.00),
(2, 1, 2, 3, 4200.00, 12600.00),
(3, 2, 3, 2, 900.00, 1800.00),
(4, 2, 4, 4, 350.00, 1400.00),
(5, 2, 5, 2, 620.00, 1240.00),
(6, 3, 5, 4, 620.00, 2480.00),
(7, 4, 7, 4, 6500.00, 26000.00),
(8, 4, 8, 3, 700.00, 2100.00),
(9, 5, 1, 4, 4800.00, 19200.00),
(10, 6, 3, 1, 900.00, 900.00),
(11, 6, 4, 3, 350.00, 1050.00),
(12, 7, 5, 1, 620.00, 620.00),
(13, 8, 7, 1, 6500.00, 6500.00),
(14, 8, 8, 3, 700.00, 2100.00),
(15, 9, 1, 1, 4800.00, 4800.00),
(16, 9, 2, 2, 4200.00, 8400.00),
(17, 10, 3, 4, 900.00, 3600.00),
(18, 10, 4, 4, 350.00, 1400.00),
(19, 10, 5, 2, 620.00, 1240.00);

--
-- Disparadores `detalle_venta`
--
DROP TRIGGER IF EXISTS `trg_venta_descontar_stock`;
DELIMITER $$
CREATE TRIGGER `trg_venta_descontar_stock` AFTER INSERT ON `detalle_venta` FOR EACH ROW BEGIN
    UPDATE `sbaveca`.`producto`
    SET `stockActualProd` = `stockActualProd` - NEW.cantidadDetVen
    WHERE `idProducto` = NEW.productoIdDetVen;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favorito`
--

DROP TABLE IF EXISTS `favorito`;
CREATE TABLE IF NOT EXISTS `favorito` (
  `idFav` int NOT NULL AUTO_INCREMENT,
  `clienteIdFav` int NOT NULL,
  `productoIdFav` int NOT NULL,
  PRIMARY KEY (`idFav`),
  KEY `idx_fav_cliente` (`clienteIdFav`),
  KEY `idx_fav_producto` (`productoIdFav`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `insumo`
--

DROP TABLE IF EXISTS `insumo`;
CREATE TABLE IF NOT EXISTS `insumo` (
  `idIns` int NOT NULL AUTO_INCREMENT,
  `nombreIns` varchar(150) NOT NULL COMMENT 'Nombre del insumo o ingrediente',
  `categoriaIns` enum('Ingrediente','Utensilio','Producto Terminado') NOT NULL DEFAULT 'Ingrediente' COMMENT 'RF05.1',
  `unidadMedidaIns` varchar(30) NOT NULL COMMENT 'kg, gr, lt, unidad, etc.',
  `pesoPorUnidadIns` decimal(10,3) DEFAULT NULL,
  `precioCompraIns` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Precio de compra unitario — RF05.5',
  `stockMinimoIns` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT 'Umbral mínimo para alertas — RF05.3',
  `imagenBlobIns` mediumblob COMMENT 'Foto del insumo — RF05.2',
  `imagenTipoIns` varchar(50) DEFAULT NULL,
  `imagenNombreIns` varchar(255) DEFAULT NULL,
  `estadoIns` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `proveedorIdPro` int DEFAULT NULL,
  `fechaCreaIns` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idIns`),
  KEY `idx_ins_proveedor` (`proveedorIdPro`),
  KEY `idx_ins_categoria` (`categoriaIns`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `insumo`
--

INSERT INTO `insumo` (`idIns`, `nombreIns`, `categoriaIns`, `unidadMedidaIns`, `pesoPorUnidadIns`, `precioCompraIns`, `stockMinimoIns`, `imagenBlobIns`, `imagenTipoIns`, `imagenNombreIns`, `estadoIns`, `proveedorIdPro`, `fechaCreaIns`) VALUES
(1, 'Harina de Trigo 000', 'Ingrediente', 'kg', 1000.000, 450.00, 20.000, '', NULL, NULL, 'Activo', 1, '2026-06-15 02:57:13'),
(2, 'Chocolate Taza Aguila Blanco', 'Ingrediente', '100 gr', NULL, 5.43, 5.000, 0x687474703a2f2f6c6f63616c686f73742f534241564543412f75706c6f6164732f366132663936643934353766652e6a706567, NULL, NULL, 'Activo', 1, '2026-06-15 03:08:25'),
(3, 'Huevos', 'Ingrediente', 'Unidad', NULL, 416.00, 12.000, 0x75706c6f6164732f366133303065353364376162332e6a706567, NULL, NULL, 'Activo', 1, '2026-06-15 11:38:11'),
(4, 'Leche Entera ', 'Ingrediente', '1 Ltr', NULL, 59.90, 5.000, 0x687474703a2f2f6c6f63616c686f73742f534241564543412f75706c6f6164732f366133303835353035353962302e6a706567, NULL, NULL, 'Activo', NULL, '2026-06-15 20:05:52'),
(5, 'Harina 000', 'Ingrediente', 'kg', NULL, 85.00, 20.000, NULL, NULL, NULL, 'Activo', 3, '2026-06-16 01:09:01'),
(6, 'Harina 0000', 'Ingrediente', 'kg', NULL, 95.00, 15.000, NULL, NULL, NULL, 'Activo', 3, '2026-06-16 01:09:01'),
(7, 'Azúcar', 'Ingrediente', 'kg', NULL, 70.00, 20.000, NULL, NULL, NULL, 'Activo', 3, '2026-06-16 01:09:01'),
(8, 'Manteca', 'Ingrediente', 'kg', NULL, 650.00, 10.000, NULL, NULL, NULL, 'Activo', 4, '2026-06-16 01:09:01'),
(9, 'Leche entera', 'Ingrediente', 'lt', NULL, 180.00, 20.000, NULL, NULL, NULL, 'Activo', 4, '2026-06-16 01:09:01'),
(10, 'Crema de leche', 'Ingrediente', 'lt', NULL, 420.00, 5.000, NULL, NULL, NULL, 'Activo', 4, '2026-06-16 01:09:01'),
(11, 'Cacao amargo', 'Ingrediente', 'kg', NULL, 900.00, 5.000, NULL, NULL, NULL, 'Activo', 5, '2026-06-16 01:09:01'),
(12, 'Dulce de leche', 'Ingrediente', 'kg', NULL, 450.00, 10.000, NULL, NULL, NULL, 'Activo', 5, '2026-06-16 01:09:01'),
(13, 'Vainilla esencia', 'Ingrediente', 'lt', NULL, 350.00, 2.000, NULL, NULL, NULL, 'Activo', 5, '2026-06-16 01:09:01'),
(14, 'Levadura seca', 'Ingrediente', 'kg', NULL, 800.00, 2.000, NULL, NULL, NULL, 'Activo', 3, '2026-06-16 01:09:01'),
(15, 'Sal fina', 'Ingrediente', 'kg', NULL, 45.00, 5.000, NULL, NULL, NULL, 'Activo', 3, '2026-06-16 01:09:01'),
(16, 'Polvo de hornear', 'Ingrediente', 'kg', NULL, 600.00, 2.000, NULL, NULL, NULL, 'Activo', 3, '2026-06-16 01:09:01'),
(17, 'Chocolate cobertura', 'Ingrediente', 'kg', NULL, 1800.00, 5.000, NULL, NULL, NULL, 'Activo', 5, '2026-06-16 01:09:01'),
(18, 'Frutillas frescas', 'Ingrediente', 'kg', NULL, 850.00, 3.000, NULL, NULL, NULL, 'Activo', 7, '2026-06-16 01:09:01'),
(19, 'Durazno en almíbar', 'Ingrediente', 'kg', NULL, 380.00, 4.000, NULL, NULL, NULL, 'Activo', 7, '2026-06-16 01:09:01'),
(20, 'Nueces', 'Ingrediente', 'kg', NULL, 2200.00, 2.000, NULL, NULL, NULL, 'Activo', 5, '2026-06-16 01:09:01'),
(21, 'Molde redondo 24cm', 'Utensilio', 'unidad', NULL, 2500.00, 5.000, NULL, NULL, NULL, 'Activo', 6, '2026-06-16 01:09:01'),
(22, 'Manga pastelera', 'Utensilio', 'unidad', NULL, 450.00, 5.000, NULL, NULL, NULL, 'Activo', 6, '2026-06-16 01:09:01'),
(23, 'Papel manteca rollo', 'Utensilio', 'rollo', NULL, 180.00, 3.000, NULL, NULL, NULL, 'Activo', 6, '2026-06-16 01:09:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

DROP TABLE IF EXISTS `inventario`;
CREATE TABLE IF NOT EXISTS `inventario` (
  `idInv` int NOT NULL AUTO_INCREMENT,
  `insumoIdInv` int DEFAULT NULL COMMENT 'Insumo al que pertenece este registro de stock',
  `stockActualInv` int NOT NULL DEFAULT '0',
  `stockActualDecInv` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT 'Stock decimal para insumos a granel',
  `stockMinimoInv` int NOT NULL DEFAULT '0',
  `unidadMedidaInv` varchar(30) DEFAULT NULL COMMENT 'Unidad de medida del stock',
  `fechaUltimoIngresoInv` date NOT NULL DEFAULT (curdate()),
  PRIMARY KEY (`idInv`),
  UNIQUE KEY `uq_inv_insumo` (`insumoIdInv`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`idInv`, `insumoIdInv`, `stockActualInv`, `stockActualDecInv`, `stockMinimoInv`, `unidadMedidaInv`, `fechaUltimoIngresoInv`) VALUES
(1, 1, 150, 0.000, 20, 'kg', '2026-06-15'),
(2, 2, 4, 0.000, 5, '100 gr', '2026-06-15'),
(3, 3, 12, 0.000, 12, 'Unidad', '2026-06-15'),
(4, 4, 10, 0.000, 5, '1 Ltr', '2026-06-15'),
(5, 5, 150, 0.000, 20, 'kg', '2026-06-16'),
(6, 6, 120, 0.000, 15, 'kg', '2026-06-16'),
(7, 7, 200, 0.000, 20, 'kg', '2026-06-16'),
(8, 8, 80, 0.000, 10, 'kg', '2026-06-16'),
(9, 9, 100, 0.000, 20, 'lt', '2026-06-16'),
(10, 10, 40, 0.000, 5, 'lt', '2026-06-16'),
(11, 11, 30, 0.000, 5, 'kg', '2026-06-16'),
(12, 12, 60, 0.000, 10, 'kg', '2026-06-16'),
(13, 13, 10, 0.000, 2, 'lt', '2026-06-16'),
(14, 14, 15, 0.000, 2, 'kg', '2026-06-16'),
(15, 15, 50, 0.000, 5, 'kg', '2026-06-16'),
(16, 16, 12, 0.000, 2, 'kg', '2026-06-16'),
(17, 17, 25, 0.000, 5, 'kg', '2026-06-16'),
(18, 18, 20, 0.000, 3, 'kg', '2026-06-16'),
(19, 19, 30, 0.000, 4, 'kg', '2026-06-16'),
(20, 20, 8, 0.000, 2, 'kg', '2026-06-16'),
(21, 21, 15, 0.000, 5, 'unidad', '2026-06-16'),
(22, 22, 20, 0.000, 5, 'unidad', '2026-06-16'),
(23, 23, 18, 0.000, 3, 'rollo', '2026-06-16');

--
-- Disparadores `inventario`
--
DROP TRIGGER IF EXISTS `trg_alerta_stock_bajo`;
DELIMITER $$
CREATE TRIGGER `trg_alerta_stock_bajo` AFTER UPDATE ON `inventario` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menu_detalle`
--

DROP TABLE IF EXISTS `menu_detalle`;
CREATE TABLE IF NOT EXISTS `menu_detalle` (
  `idMenuDet` int NOT NULL AUTO_INCREMENT,
  `menuSemIdMenuDet` int NOT NULL COMMENT 'Semana a la que pertenece',
  `diaSemana` enum('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo') NOT NULL,
  `recetaIdMenuDet` int DEFAULT NULL COMMENT 'Receta planificada para ese día',
  `productoIdMenuDet` int DEFAULT NULL COMMENT 'Producto alternativo sin receta',
  `cantidadPrevista` int NOT NULL DEFAULT '1' COMMENT 'Porciones/unidades previstas — RF08.3',
  `observacionMenuDet` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idMenuDet`),
  KEY `idx_menudet_semana` (`menuSemIdMenuDet`),
  KEY `idx_menudet_receta` (`recetaIdMenuDet`),
  KEY `idx_menudet_producto` (`productoIdMenuDet`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `menu_detalle`
--

INSERT INTO `menu_detalle` (`idMenuDet`, `menuSemIdMenuDet`, `diaSemana`, `recetaIdMenuDet`, `productoIdMenuDet`, `cantidadPrevista`, `observacionMenuDet`) VALUES
(1, 1, 'Lunes', 2, NULL, 22, NULL),
(2, 1, 'Martes', 3, NULL, 17, NULL),
(3, 1, 'Miercoles', 4, NULL, 21, NULL),
(4, 1, 'Jueves', 5, NULL, 31, NULL),
(5, 1, 'Viernes', 6, NULL, 23, NULL),
(6, 1, 'Sabado', 7, NULL, 21, NULL),
(7, 1, 'Domingo', 2, NULL, 39, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menu_semanal`
--

DROP TABLE IF EXISTS `menu_semanal`;
CREATE TABLE IF NOT EXISTS `menu_semanal` (
  `idMenuSem` int NOT NULL AUTO_INCREMENT,
  `fechaInicioSem` date NOT NULL COMMENT 'Lunes de la semana planificada',
  `fechaFinSem` date NOT NULL COMMENT 'Domingo de la semana planificada',
  `estadoSem` enum('Borrador','Publicado','Archivado') NOT NULL DEFAULT 'Borrador',
  `observacionSem` text,
  `usuarioCreaIdSem` int NOT NULL COMMENT 'Admin/empleado creador',
  `fechaCreaSem` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idMenuSem`),
  UNIQUE KEY `uq_semana` (`fechaInicioSem`),
  KEY `idx_menusem_usuario` (`usuarioCreaIdSem`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `menu_semanal`
--

INSERT INTO `menu_semanal` (`idMenuSem`, `fechaInicioSem`, `fechaFinSem`, `estadoSem`, `observacionSem`, `usuarioCreaIdSem`, `fechaCreaSem`) VALUES
(1, '2026-06-15', '2026-06-21', 'Publicado', 'Menú de la semana generado automáticamente', 7, '2026-06-16 01:09:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulo`
--

DROP TABLE IF EXISTS `modulo`;
CREATE TABLE IF NOT EXISTS `modulo` (
  `idMod` bigint NOT NULL AUTO_INCREMENT,
  `tituloMod` varchar(50) NOT NULL,
  `descripcionMod` text NOT NULL,
  `estadoMod` int NOT NULL,
  PRIMARY KEY (`idMod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_inventario`
--

DROP TABLE IF EXISTS `movimiento_inventario`;
CREATE TABLE IF NOT EXISTS `movimiento_inventario` (
  `idMovInv` int NOT NULL AUTO_INCREMENT,
  `insumoIdMovInv` int NOT NULL COMMENT 'Insumo afectado',
  `tipoMovInv` enum('Entrada','Salida','Ajuste') NOT NULL,
  `cantidadMovInv` decimal(10,3) NOT NULL COMMENT 'Cantidad movida (siempre positivo)',
  `motivoMovInv` varchar(200) NOT NULL COMMENT 'Compra, Venta, Producción, Ajuste…',
  `ventaIdMovInv` int DEFAULT NULL COMMENT 'Venta que generó la salida — RF05.4',
  `pedidoIdMovInv` bigint DEFAULT NULL,
  `usuarioIdMovInv` int NOT NULL COMMENT 'Quien registró el movimiento',
  `stockResultanteMovInv` decimal(10,3) DEFAULT NULL COMMENT 'Snapshot de stock post-movimiento',
  `fechaMovInv` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idMovInv`),
  KEY `idx_movinv_insumo` (`insumoIdMovInv`),
  KEY `idx_movinv_venta` (`ventaIdMovInv`),
  KEY `idx_movinv_pedido` (`pedidoIdMovInv`),
  KEY `idx_movinv_usuario` (`usuarioIdMovInv`),
  KEY `idx_movinv_fecha` (`fechaMovInv`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

DROP TABLE IF EXISTS `pedido`;
CREATE TABLE IF NOT EXISTS `pedido` (
  `idPed` bigint NOT NULL AUTO_INCREMENT,
  `persona_idPers` bigint NOT NULL,
  `referenciaCobroPed` varchar(255) DEFAULT NULL,
  `idTransaccionMpPed` varchar(255) DEFAULT NULL,
  `datosMpPed` text,
  `montoPed` decimal(11,2) DEFAULT NULL,
  `fechaPed` datetime DEFAULT NULL,
  `costoEnvioPed` decimal(10,2) DEFAULT NULL,
  `tipoPagoIdPed` bigint DEFAULT NULL,
  `direccionEnvioPed` text,
  `estadoPed` varchar(100) DEFAULT NULL,
  `canalOrigenPed` enum('Presencial','Telefonico','Web') DEFAULT NULL COMMENT 'Canal de origen del pedido — RF09.1',
  `personalizacionPed` text COMMENT 'Especificaciones del cliente — RF09.2',
  `fechaEntregaEstimPed` datetime DEFAULT NULL COMMENT 'Fecha/hora estimada de entrega — RF09.2',
  `estadoSeguimientoPed` enum('Pendiente','En preparacion','Listo','Entregado','Cancelado') DEFAULT 'Pendiente' COMMENT 'Estado operativo — RF09.4',
  PRIMARY KEY (`idPed`),
  KEY `fk_pedido_persona_idx` (`persona_idPers`),
  KEY `idx_ped_canal` (`canalOrigenPed`),
  KEY `idx_ped_estado_seg` (`estadoSeguimientoPed`),
  KEY `idx_ped_entrega` (`fechaEntregaEstimPed`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`idPed`, `persona_idPers`, `referenciaCobroPed`, `idTransaccionMpPed`, `datosMpPed`, `montoPed`, `fechaPed`, `costoEnvioPed`, `tipoPagoIdPed`, `direccionEnvioPed`, `estadoPed`, `canalOrigenPed`, `personalizacionPed`, `fechaEntregaEstimPed`, `estadoSeguimientoPed`) VALUES
(1, 1, NULL, NULL, NULL, 4800.00, '2026-06-06 04:09:02', NULL, NULL, NULL, 'Entregado', 'Presencial', NULL, '2026-06-07 04:09:02', 'Entregado'),
(2, 2, NULL, NULL, NULL, 12600.00, '2026-06-09 04:09:02', NULL, NULL, NULL, 'Entregado', 'Web', NULL, '2026-06-10 04:09:02', 'Entregado'),
(3, 3, NULL, NULL, NULL, 900.00, '2026-06-15 04:09:02', NULL, NULL, NULL, 'En preparacion', 'Telefonico', NULL, '2026-06-17 04:09:02', 'En preparacion'),
(4, 4, NULL, NULL, NULL, 1050.00, '2026-06-16 04:09:02', NULL, NULL, NULL, 'Pendiente', 'Web', NULL, '2026-06-18 04:09:02', 'Pendiente'),
(5, 5, NULL, NULL, NULL, 620.00, '2026-06-16 04:09:02', NULL, NULL, NULL, 'Listo', 'Presencial', NULL, '2026-06-16 04:09:02', 'Listo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_personalizado`
--

DROP TABLE IF EXISTS `pedido_personalizado`;
CREATE TABLE IF NOT EXISTS `pedido_personalizado` (
  `idPedPers` int NOT NULL AUTO_INCREMENT,
  `pedidoIdPedPers` bigint NOT NULL COMMENT 'Pedido base',
  `tortaPersIdPedPers` int NOT NULL COMMENT 'Torta configurada asociada',
  `cantidadPedPers` int NOT NULL DEFAULT '1',
  `observacionPedPers` text COMMENT 'Instrucciones finales del cliente',
  PRIMARY KEY (`idPedPers`),
  KEY `idx_pedpers_pedido` (`pedidoIdPedPers`),
  KEY `idx_pedpers_torta` (`tortaPersIdPedPers`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfil`
--

DROP TABLE IF EXISTS `perfil`;
CREATE TABLE IF NOT EXISTS `perfil` (
  `idPerfil` bigint NOT NULL AUTO_INCREMENT,
  `nombrePerfil` varchar(50) NOT NULL,
  `descripcionPerfil` text NOT NULL,
  `estadoPerfil` int NOT NULL,
  PRIMARY KEY (`idPerfil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

DROP TABLE IF EXISTS `permisos`;
CREATE TABLE IF NOT EXISTS `permisos` (
  `idPer` bigint NOT NULL AUTO_INCREMENT,
  `perfil_idPerfil` bigint NOT NULL,
  `moduloIdPer` bigint NOT NULL,
  `leerPer` int NOT NULL DEFAULT '0',
  `escribirPer` int NOT NULL DEFAULT '0',
  `actualizarPer` int NOT NULL DEFAULT '0',
  `eliminarPer` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`idPer`),
  KEY `idx_permisos_modulo` (`moduloIdPer`),
  KEY `idx_permisos_perfil` (`perfil_idPerfil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `persona`
--

DROP TABLE IF EXISTS `persona`;
CREATE TABLE IF NOT EXISTS `persona` (
  `idPers` bigint NOT NULL AUTO_INCREMENT,
  `identificacionPers` varchar(30) DEFAULT NULL,
  `nombrePers` varchar(80) DEFAULT NULL,
  `apellidoPers` varchar(100) DEFAULT NULL,
  `telefonoPers` bigint DEFAULT NULL,
  `emailUsuarioPers` varchar(100) DEFAULT NULL,
  `contraseñaPers` varchar(75) DEFAULT NULL,
  `nitPers` varchar(20) DEFAULT NULL COMMENT 'Número de Identificación Tributaria',
  `nombreFiscalPers` varchar(80) DEFAULT NULL,
  `direccionFiscalPers` varchar(100) DEFAULT NULL,
  `tokenPers` varchar(100) DEFAULT NULL,
  `rolIdPers` bigint DEFAULT NULL,
  `fechaCreaPers` datetime DEFAULT NULL,
  `estadoPers` int DEFAULT NULL,
  `idUsuarioPers` int DEFAULT NULL,
  PRIMARY KEY (`idPers`),
  KEY `idx_persona_usuario` (`idUsuarioPers`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `persona`
--

INSERT INTO `persona` (`idPers`, `identificacionPers`, `nombrePers`, `apellidoPers`, `telefonoPers`, `emailUsuarioPers`, `contraseñaPers`, `nitPers`, `nombreFiscalPers`, `direccionFiscalPers`, `tokenPers`, `rolIdPers`, `fechaCreaPers`, `estadoPers`, `idUsuarioPers`) VALUES
(1, '20193776029', 'Test', '-', 123, 'test@test.com', '$2y$10$dWSx6I7IomXyi1OHVWJKTuD8Lub5/LCrubTlpvlcSs57kXchqo8pK', NULL, NULL, '123 Main St', NULL, NULL, '2026-06-14 21:20:10', 1, 1),
(2, '20340094639', 'Lourdes Bordon', '-', 3704273333, 'luli.antonella19@gmail.com', '$2y$10$Flphg2VObXz4lHtNlf72Mu8Jq8rz2/uX9Xw6X2e6inE1f3VaVdv5m', NULL, NULL, NULL, NULL, NULL, '2026-06-14 21:22:39', 1, 2),
(3, '20206996829', 'Test', 'User', 1234567890, 'admin_test@test.com', '$2y$10$m1EUNGa8XpvkPOU2inoR/uEcRUKQTUhCv2yVLN.KKRFEvtqvtqUx.', NULL, NULL, 'Test Address 123', NULL, NULL, '2026-06-14 23:25:15', 2, 3),
(4, '20978632699', 'Monnie', '-', NULL, 'raymonnie7@gmail.com', '$2y$10$9d/k0WE4UhFebS.rbD0Ji.uscaR58Xxn5EAymYZSrcFfP9UQH7tNK', NULL, NULL, NULL, NULL, NULL, '2026-06-15 11:17:26', 2, 4),
(5, '20360870159', 'Facundo Caceres', '-', 3704273333, 'caceresfacundo28@gmail.com', '$2y$10$8YcxEnBsmrZ/1NKJRL1GGepwiVvmITM6JayIsEzE3oO5X1YBh/Ur6', NULL, NULL, 'Av. Pipi', NULL, NULL, '2026-06-15 20:00:01', 1, 5),
(6, '20698045869', 'Mandy Celestine', '-', NULL, 'lookout@gmail.com', '$2y$10$7BTmPLUUtSolOQKPUPQuFefj4lkxsGqjPup7CDhlsJ6HZdU5P7gj.', NULL, NULL, NULL, NULL, NULL, '2026-06-15 20:18:18', 1, 6),
(7, '20123456789', 'Lourdes Bordon', '-', 123456789, 'admin@sbaveca.com', '$2y$10$EYNvci5mOeboRl0BZ38TBe0NCmtR78FaSindLKozPeBZW4clix6j2', NULL, NULL, NULL, NULL, NULL, '2026-06-16 01:09:00', 1, 7),
(8, '27345678901', 'Susana Oria', '-', 3704273333, 'susanitaoria@sbaveca.com', '$2y$10$kvkAthe7sh2jSPhSofE25O14C7kbCgkghW7PWZl/Wgygw5OjjdJEu', NULL, NULL, NULL, NULL, NULL, '2026-06-16 01:09:00', 2, 8),
(9, '20456789012', 'Hijitus Super', '-', 3512000003, 'raymonnie27@gmail.com', '$2y$10$iZBvK.Gh6EhK7ZYkytm.JucLUvGavJqRqFy0pdz3mlmSCTkrp4noC', NULL, NULL, NULL, NULL, NULL, '2026-06-16 01:09:00', 1, 9),
(10, '27567890123', 'Remy Etienne LeBeau', '-', 3512000004, 'neverfolds@gmail.com', '$2y$10$S05HMStP/IMtKTnUEvsLxuj2VS0Ej3Qq8dYn45DW7nohPAqrIfP8i', NULL, NULL, NULL, NULL, NULL, '2026-06-16 01:09:01', 1, 10),
(11, '20199001001', 'María', 'González', 3512200001, 'maria.gonzalez@mail.com', '$2y$10$CihmUaE/k6SM2D5xojbHKeEVWPO2kxMvY5GDIWS61Ha4tWYfnd/.q', NULL, NULL, NULL, NULL, NULL, '2026-06-16 01:09:01', 1, 11),
(12, '20299001002', 'Carlos', 'Martínez', 3512200002, 'carlos.martinez@mail.com', '$2y$10$MV/Ml3DXLJA9faGbCnQCUuoCH/zJNEdGAqgqAzFKQ411ZIsgMlFYq', NULL, NULL, NULL, NULL, NULL, '2026-06-16 01:09:01', 1, 12),
(13, '27399001003', 'Ana', 'Rodríguez', 3512200003, 'ana.rodriguez@mail.com', '$2y$10$BtFxk52np9dMF9ZGa07ueOQqVdcJ3TBK2ZSEF3ARqLnXvTVbx9JcK', NULL, NULL, NULL, NULL, NULL, '2026-06-16 01:09:01', 1, 13),
(14, '20499001004', 'Diego', 'López', 3512200004, 'diego.lopez@mail.com', '$2y$10$NCXqF8QCxEumN0BINbGewe0s5wLZ9tg9fx/1QlTv0m4E4avSVKY6q', NULL, NULL, NULL, NULL, NULL, '2026-06-16 01:09:02', 1, 14),
(15, '27599001005', 'Sofía', 'Fernández', 3512200005, 'sofia.fernandez@mail.com', '$2y$10$jr9XySkTW98WOTPMY2WXF.caM7UfZLSnY78aSVyw7GuNsJSntBT56', NULL, NULL, NULL, NULL, NULL, '2026-06-16 01:09:02', 1, 15);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto`
--

DROP TABLE IF EXISTS `presupuesto`;
CREATE TABLE IF NOT EXISTS `presupuesto` (
  `idPresupuesto` int NOT NULL AUTO_INCREMENT,
  `personaIdPres` bigint NOT NULL COMMENT 'Cliente/persona solicitante',
  `fechaEmisionPres` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fechaValidezPres` datetime DEFAULT NULL COMMENT 'Vigencia del presupuesto — RF10.5',
  `montoSubtotalPres` decimal(10,2) NOT NULL DEFAULT '0.00',
  `descuentoPres` decimal(10,2) NOT NULL DEFAULT '0.00',
  `montoTotalPres` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Monto total — RF10.5',
  `estadoPres` enum('Borrador','Enviado','Aceptado','Rechazado','Vencido') NOT NULL DEFAULT 'Borrador' COMMENT 'RF10.5',
  `observacionesPres` text,
  `pdfContenidoPres` longtext COMMENT 'HTML/JSON para generar PDF — RF18.2',
  `emailEnviadoPres` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 si fue enviado por email — RF18.2',
  `pedidoIdPres` bigint DEFAULT NULL COMMENT 'Pedido generado si fue aceptado',
  PRIMARY KEY (`idPresupuesto`),
  KEY `idx_pres_persona` (`personaIdPres`),
  KEY `idx_pres_estado` (`estadoPres`),
  KEY `idx_pres_pedido` (`pedidoIdPres`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_detalle`
--

DROP TABLE IF EXISTS `presupuesto_detalle`;
CREATE TABLE IF NOT EXISTS `presupuesto_detalle` (
  `idPresDet` int NOT NULL AUTO_INCREMENT,
  `presupuestoIdPresDet` int NOT NULL,
  `productoIdPresDet` int DEFAULT NULL COMMENT 'Producto del catálogo (opcional)',
  `descripcionPresDet` varchar(255) NOT NULL COMMENT 'Descripción libre si no es producto del catálogo',
  `cantidadPresDet` int NOT NULL DEFAULT '1',
  `precioUnitarioPresDet` decimal(10,2) NOT NULL,
  `subtotalPresDet` decimal(10,2) NOT NULL,
  PRIMARY KEY (`idPresDet`),
  KEY `idx_presdet_presupuesto` (`presupuestoIdPresDet`),
  KEY `idx_presdet_producto` (`productoIdPresDet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

DROP TABLE IF EXISTS `producto`;
CREATE TABLE IF NOT EXISTS `producto` (
  `idProducto` int NOT NULL AUTO_INCREMENT,
  `IdSubCat` int NOT NULL,
  `nombreProd` varchar(100) NOT NULL,
  `descripcionProd` varchar(450) NOT NULL,
  `SKUProd` varchar(50) NOT NULL,
  `codigoBarrasProd` varchar(50) DEFAULT NULL,
  `MarcaProd` varchar(45) NOT NULL,
  `precioCostoProd` decimal(10,2) NOT NULL,
  `precioVentaProd` decimal(10,2) NOT NULL,
  `precioOfertaProd` decimal(10,2) DEFAULT NULL,
  `margenGananciaProd` decimal(5,2) NOT NULL,
  `stockActualProd` int NOT NULL,
  `estadoProd` enum('Activo','Inactivo','Descontinuado') NOT NULL DEFAULT 'Activo',
  `enOfertaProd` tinyint NOT NULL DEFAULT '0',
  `esDestacadoProd` tinyint NOT NULL DEFAULT '0',
  `inventarioIdInv` int NOT NULL,
  `proveedorIdPro` int NOT NULL,
  `imagenBlobProd` mediumblob,
  `imagenTipoProd` varchar(50) DEFAULT NULL,
  `imagenNombreProd` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idProducto`),
  UNIQUE KEY `SKU_UNIQUE` (`SKUProd`),
  UNIQUE KEY `codigo_barras` (`codigoBarrasProd`),
  KEY `idx_prod_subcategoria` (`IdSubCat`),
  KEY `idx_prod_inventario` (`inventarioIdInv`),
  KEY `idx_prod_proveedor` (`proveedorIdPro`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`idProducto`, `IdSubCat`, `nombreProd`, `descripcionProd`, `SKUProd`, `codigoBarrasProd`, `MarcaProd`, `precioCostoProd`, `precioVentaProd`, `precioOfertaProd`, `margenGananciaProd`, `stockActualProd`, `estadoProd`, `enOfertaProd`, `esDestacadoProd`, `inventarioIdInv`, `proveedorIdPro`, `imagenBlobProd`, `imagenTipoProd`, `imagenNombreProd`) VALUES
(1, 1, 'Torta Chocolate Grande', 'Torta de chocolate para 12 personas con ganache', 'TRT-CHOC-001', NULL, 'SBAVECA', 2800.00, 4800.00, NULL, 71.40, -1, 'Activo', 1, 0, 1, 1, NULL, NULL, NULL),
(2, 1, 'Torta de Frutillas', 'Tarta de frutillas con crema pastelera para 10 personas', 'TRT-FRUT-001', NULL, 'SBAVECA', 2200.00, 4200.00, NULL, 90.90, 1, 'Activo', 1, 1, 1, 1, NULL, NULL, NULL),
(3, 0, 'Alfajores x12', 'Caja de 12 alfajores de maicena rellenos con dulce de leche', 'ALF-001', NULL, 'SBAVECA', 480.00, 900.00, NULL, 87.50, 18, 'Activo', 4, 0, 1, 1, NULL, NULL, NULL),
(4, 1, 'Medialunas x6', 'Bolsa de 6 medialunas de manteca artesanales', 'MED-001', NULL, 'SBAVECA', 180.00, 350.00, NULL, 94.40, 19, 'Activo', 3, 0, 1, 1, NULL, NULL, NULL),
(5, 0, 'Mousse de Chocolate', 'Mousse individual de chocolate amargo 200gr', 'MOU-CHOC-001', NULL, 'SBAVECA', 320.00, 620.00, NULL, 93.80, 11, 'Activo', 5, 1, 1, 1, NULL, NULL, NULL),
(6, 0, 'Budín de Nueces', 'Budín artesanal de nueces 500gr', 'BUD-NUC-001', NULL, 'SBAVECA', 420.00, 780.00, NULL, 85.70, 15, 'Activo', 6, 0, 1, 1, NULL, NULL, NULL),
(7, 1, 'Torta de Cumpleaños', 'Torta personalizable para cumpleaños — consultar sabores', 'TRT-CMP-001', NULL, 'SBAVECA', 3500.00, 6500.00, NULL, 85.70, -1, 'Activo', 2, 0, 1, 1, NULL, NULL, NULL),
(8, 0, 'Facturas Surtidas x12', 'Caja de 12 facturas variadas del día', 'FAC-SUR-001', NULL, 'SBAVECA', 360.00, 700.00, NULL, 94.40, 14, 'Activo', 4, 0, 1, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_carrito`
--

DROP TABLE IF EXISTS `producto_carrito`;
CREATE TABLE IF NOT EXISTS `producto_carrito` (
  `idProducto` int NOT NULL,
  `idCarrito` int NOT NULL,
  `cantidadProductoProdCar` int NOT NULL DEFAULT '1',
  `precioUnitarioProdCar` decimal(10,2) NOT NULL,
  `subtotalProdCar` decimal(10,2) NOT NULL,
  PRIMARY KEY (`idProducto`,`idCarrito`),
  KEY `idx_pc_carrito` (`idCarrito`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

DROP TABLE IF EXISTS `proveedor`;
CREATE TABLE IF NOT EXISTS `proveedor` (
  `idPro` int NOT NULL AUTO_INCREMENT,
  `nombrePro` varchar(100) NOT NULL,
  `CUITPro` varchar(20) NOT NULL,
  `telefonoPro` varchar(20) NOT NULL,
  `emailPro` varchar(100) NOT NULL,
  `direccionPro` varchar(150) NOT NULL,
  `ciudadPro` varchar(50) NOT NULL,
  `provinciaPro` varchar(50) NOT NULL,
  PRIMARY KEY (`idPro`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`idPro`, `nombrePro`, `CUITPro`, `telefonoPro`, `emailPro`, `direccionPro`, `ciudadPro`, `provinciaPro`) VALUES
(1, 'Distribuidora Harinera S.A.', '00-00000000-0', '11456789', 'harinas@dist.com', 'Catalog: Harina, Manteca', 'Desconocida', 'Desconocida'),
(2, 'Arcor', '00-00000000-0', '3704333333', 'arcor@gmail.com', 'Catalog: Chocolate, Leche', 'Desconocida', 'Desconocida'),
(3, 'Distribuidora El Molino', '30-11111111-1', '3512111001', 'contacto@elmolino.com', 'Av. Vélez Sársfield 1200', 'Córdoba', 'Córdoba'),
(4, 'Lácteos del Centro', '30-22222222-2', '3512111002', 'ventas@lacteosdel.com', 'Calle Las Heras 450', 'Córdoba', 'Córdoba'),
(5, 'Dulces y Esencias SA', '30-33333333-3', '3512111003', 'info@dulcesesencias.com', 'Bv. San Juan 880', 'Córdoba', 'Córdoba'),
(6, 'Empaque y Más', '30-44444444-4', '3512111004', 'pedidos@empaquemas.com', 'Ruta 9 Km 12', 'Córdoba', 'Córdoba'),
(7, 'Frutas del Valle', '30-55555555-5', '3512111005', 'frutas@delvalle.com', 'Mercado Norte Local 42', 'Córdoba', 'Córdoba');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recetas`
--

DROP TABLE IF EXISTS `recetas`;
CREATE TABLE IF NOT EXISTS `recetas` (
  `idReceta` int NOT NULL AUTO_INCREMENT,
  `nombreReceta` varchar(150) NOT NULL COMMENT 'Nombre de la preparación',
  `descripcionReceta` text,
  `instruccionesReceta` longtext COMMENT 'Procedimiento técnico de elaboración — RF06.2',
  `porcionesReceta` int NOT NULL DEFAULT '1' COMMENT 'Porciones que rinde',
  `tiempoPreparacionMinRec` int DEFAULT NULL COMMENT 'Tiempo estimado en minutos',
  `costoProduccionReceta` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Calculado desde receta_ingrediente — RF07.1',
  `margenGananciaReceta` decimal(5,2) NOT NULL DEFAULT '30.00' COMMENT 'Margen % configurable — RF07.3',
  `precioVentaSugeridoReceta` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Costo × (1 + margen/100) — RF07.3',
  `productoIdRec` int DEFAULT NULL COMMENT 'Producto final vinculado — RF06.1',
  `estadoReceta` enum('Activa','Inactiva') NOT NULL DEFAULT 'Activa',
  `fechaCreaReceta` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fechaModReceta` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idReceta`),
  KEY `idx_rec_producto` (`productoIdRec`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `recetas`
--

INSERT INTO `recetas` (`idReceta`, `nombreReceta`, `descripcionReceta`, `instruccionesReceta`, `porcionesReceta`, `tiempoPreparacionMinRec`, `costoProduccionReceta`, `margenGananciaReceta`, `precioVentaSugeridoReceta`, `productoIdRec`, `estadoReceta`, `fechaCreaReceta`, `fechaModReceta`) VALUES
(1, 'Bizcochuelo Casero', 'http://localhost/SBAVECA/uploads/6a3085bcad93c.jpeg', 'Mezclar harina, huevos, azucar. Batir y hornear 40 minutos.', 1, NULL, 0.00, 25.00, 0.00, NULL, 'Activa', '2026-06-15 02:59:10', '2026-06-15 20:08:22'),
(2, 'Torta de Chocolate Clásica', 'uploads/6a31274f42fd3.jpeg', 'Precalentar el horno a 180°C. Tamizar la harina con el cacao y el polvo de hornear. Batir los huevos con el azúcar hasta blanquear. Incorporar la manteca derretida y la leche. Agregar los secos en forma envolvente. Volcar en molde enmantecado y hornear 40 minutos.', 1, NULL, 248.41, 65.00, 409.88, NULL, 'Activa', '2026-06-16 01:09:01', '2026-06-16 07:37:03'),
(3, 'Alfajores de Maicena', '', 'Mezclar harina con almidón, polvo de hornear y sal. Integrar la manteca pomada con el azúcar, agregar los huevos y la esencia de vainilla. Incorporar los secos. Refrigerar 30 minutos, estirar y cortar. Hornear a 170°C por 12 minutos. Rellenar con dulce de leche y unir de a pares.', 1, NULL, 243.18, 70.00, 413.41, NULL, 'Activa', '2026-06-16 01:09:01', '2026-06-16 07:37:23'),
(4, 'Tarta de Frutillas con Crema', '', 'Preparar masa sablée con harina, manteca y azúcar. Refrigerar 20 minutos, estirar y forrar molde. Hornear en blanco a 175°C por 20 minutos. Preparar crema pastelera con leche, huevos y azúcar. Dejar enfriar, rellenar la tarta y decorar con frutillas frescas.', 1, NULL, 664.75, 60.00, 1063.60, NULL, 'Activa', '2026-06-16 01:09:01', '2026-06-16 07:37:30'),
(5, 'Budín de Nueces', NULL, 'Batir huevos con azúcar. Incorporar manteca derretida, harina tamizada con polvo de hornear y sal. Agregar nueces picadas groseramente. Volcar en molde budinera enmantecado. Hornear a 180°C por 45 minutos.', 1, NULL, 434.68, 75.00, 760.70, NULL, 'Activa', '2026-06-16 01:09:01', NULL),
(6, 'Mousse de Chocolate', NULL, 'Derretir el chocolate a baño María. Separar yemas de claras. Batir las claras a punto nieve con el azúcar. Mezclar el chocolate con las yemas y la crema batida. Incorporar las claras en forma envolvente. Distribuir en copas y refrigerar mínimo 4 horas.', 1, NULL, 673.26, 80.00, 1211.88, NULL, 'Activa', '2026-06-16 01:09:01', NULL),
(7, 'Medialuna de Manteca', NULL, 'Disolver levadura en leche tibia con azúcar. Mezclar con harina y sal. Integrar la manteca en cuadros fríos. Refrigerar 1 hora. Estirar, plegar y cortar en triángulos. Enrollar y dar forma de medialuna. Dejar leudar 40 minutos. Pintar con huevo y hornear a 200°C por 15 minutos.', 1, NULL, 253.28, 55.00, 392.58, NULL, 'Activa', '2026-06-16 01:09:01', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `receta_ingrediente`
--

DROP TABLE IF EXISTS `receta_ingrediente`;
CREATE TABLE IF NOT EXISTS `receta_ingrediente` (
  `recetaIdRecIng` int NOT NULL COMMENT 'Receta a la que pertenece',
  `insumoIdRecIng` int NOT NULL COMMENT 'Insumo del inventario',
  `cantidadNecesaria` decimal(10,3) NOT NULL COMMENT 'Cantidad requerida para la receta completa',
  `unidadMedidaRecIng` varchar(30) NOT NULL COMMENT 'Unidad de medida para esta cantidad',
  `pesoPorUnidadRecIng` decimal(10,3) DEFAULT NULL,
  `costoProporcional` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'precioCompraIns × cantidad — RF07.4',
  `observacionRecIng` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`recetaIdRecIng`,`insumoIdRecIng`),
  KEY `idx_recing_insumo` (`insumoIdRecIng`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `receta_ingrediente`
--

INSERT INTO `receta_ingrediente` (`recetaIdRecIng`, `insumoIdRecIng`, `cantidadNecesaria`, `unidadMedidaRecIng`, `pesoPorUnidadRecIng`, `costoProporcional`, `observacionRecIng`) VALUES
(1, 1, 2.000, 'kg', NULL, 900.00, NULL),
(1, 3, 3.000, 'Unidad', NULL, 1248.00, NULL),
(1, 4, 1.000, 'Ltr', NULL, 59.90, NULL),
(2, 3, 4.000, 'Unidad', NULL, 1664.00, NULL),
(2, 5, 250.000, 'gr', NULL, 21.25, NULL),
(2, 7, 200.000, 'gr', NULL, 14.00, NULL),
(2, 8, 150.000, 'gr', NULL, 97.50, NULL),
(2, 9, 200.000, 'ml', NULL, 36000.00, NULL),
(2, 11, 80.000, 'gr', NULL, 72.00, NULL),
(2, 16, 10.000, 'gr', NULL, 6.00, NULL),
(3, 3, 2.000, 'unidad', NULL, 832.00, NULL),
(3, 6, 200.000, 'gr', NULL, 19.00, NULL),
(3, 7, 80.000, 'gr', NULL, 5.60, NULL),
(3, 8, 120.000, 'gr', NULL, 78.00, NULL),
(3, 12, 300.000, 'gr', NULL, 135.00, NULL),
(3, 13, 5.000, 'ml', NULL, 1750.00, NULL),
(3, 16, 5.000, 'gr', NULL, 3.00, NULL),
(4, 3, 3.000, 'unidad', NULL, 1248.00, NULL),
(4, 5, 300.000, 'gr', NULL, 25.50, NULL),
(4, 7, 100.000, 'gr', NULL, 7.00, NULL),
(4, 8, 180.000, 'gr', NULL, 117.00, NULL),
(4, 9, 500.000, 'ml', NULL, 90000.00, NULL),
(4, 10, 200.000, 'ml', NULL, 84000.00, NULL),
(4, 18, 400.000, 'gr', NULL, 340.00, NULL),
(5, 3, 3.000, 'unidad', NULL, 1.25, NULL),
(5, 6, 220.000, 'gr', NULL, 20.90, NULL),
(5, 7, 180.000, 'gr', NULL, 12.60, NULL),
(5, 8, 100.000, 'gr', NULL, 65.00, NULL),
(5, 15, 3.000, 'gr', NULL, 0.14, NULL),
(5, 16, 8.000, 'gr', NULL, 4.80, NULL),
(5, 20, 150.000, 'gr', NULL, 330.00, NULL),
(6, 3, 4.000, 'unidad', NULL, 1.66, NULL),
(6, 7, 80.000, 'gr', NULL, 5.60, NULL),
(6, 10, 300.000, 'ml', NULL, 126.00, NULL),
(6, 17, 300.000, 'gr', NULL, 540.00, NULL),
(7, 3, 1.000, 'unidad', NULL, 0.42, NULL),
(7, 5, 500.000, 'gr', NULL, 42.50, NULL),
(7, 7, 50.000, 'gr', NULL, 3.50, NULL),
(7, 8, 250.000, 'gr', NULL, 162.50, NULL),
(7, 9, 200.000, 'ml', NULL, 36.00, NULL),
(7, 14, 10.000, 'gr', NULL, 8.00, NULL),
(7, 15, 8.000, 'gr', NULL, 0.36, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recibos`
--

DROP TABLE IF EXISTS `recibos`;
CREATE TABLE IF NOT EXISTS `recibos` (
  `idReci` int NOT NULL AUTO_INCREMENT,
  `ventaId` int NOT NULL,
  `numeroReci` varchar(50) NOT NULL,
  `htmlContent` longtext NOT NULL,
  `fechaGeneracionReci` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `subtotalReci` decimal(10,2) NOT NULL,
  `ivaReci` decimal(10,2) NOT NULL,
  `totalReci` decimal(10,2) NOT NULL,
  `metodoPagoReci` varchar(50) DEFAULT NULL,
  `empleadoNombreReci` varchar(255) DEFAULT NULL,
  `datosClienteReci` text,
  PRIMARY KEY (`idReci`),
  KEY `idx_numero_recibo` (`numeroReci`),
  KEY `idx_venta_id` (`ventaId`),
  KEY `idx_fecha_recibo` (`fechaGeneracionReci`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `recibos`
--

INSERT INTO `recibos` (`idReci`, `ventaId`, `numeroReci`, `htmlContent`, `fechaGeneracionReci`, `subtotalReci`, `ivaReci`, `totalReci`, `metodoPagoReci`, `empleadoNombreReci`, `datosClienteReci`) VALUES
(1, 1, 'R-V-2026-001', '<p>Recibo V-2026-001</p>', '2026-06-16 04:09:02', 25122.00, 6678.00, 31800.00, 'Transferencia', NULL, NULL),
(2, 2, 'R-V-2026-002', '<p>Recibo V-2026-002</p>', '2026-06-16 04:09:02', 3507.60, 932.40, 4440.00, 'Tarjeta de Crédito', NULL, NULL),
(3, 3, 'R-V-2026-003', '<p>Recibo V-2026-003</p>', '2026-06-16 04:09:02', 1959.20, 520.80, 2480.00, 'Efectivo', NULL, NULL),
(4, 4, 'R-V-2026-004', '<p>Recibo V-2026-004</p>', '2026-06-16 04:09:02', 22199.00, 5901.00, 28100.00, 'Tarjeta de Crédito', NULL, NULL),
(5, 5, 'R-V-2026-005', '<p>Recibo V-2026-005</p>', '2026-06-16 04:09:02', 15168.00, 4032.00, 19200.00, 'Tarjeta de Débito', NULL, NULL),
(6, 6, 'R-V-2026-006', '<p>Recibo V-2026-006</p>', '2026-06-16 04:09:02', 1540.50, 409.50, 1950.00, 'Transferencia', NULL, NULL),
(7, 8, 'R-V-2026-008', '<p>Recibo V-2026-008</p>', '2026-06-16 04:09:02', 6794.00, 1806.00, 8600.00, 'Efectivo', NULL, NULL),
(8, 9, 'R-V-2026-009', '<p>Recibo V-2026-009</p>', '2026-06-16 04:09:02', 10428.00, 2772.00, 13200.00, 'Tarjeta de Débito', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reembolso`
--

DROP TABLE IF EXISTS `reembolso`;
CREATE TABLE IF NOT EXISTS `reembolso` (
  `idReem` bigint NOT NULL AUTO_INCREMENT,
  `pedidoIdReem` bigint NOT NULL,
  `idTransaccionReem` varchar(255) NOT NULL,
  `datosReem` text NOT NULL,
  `observacionReem` text NOT NULL,
  `estadoReem` varchar(150) NOT NULL,
  PRIMARY KEY (`idReem`),
  KEY `idx_pedidoid` (`pedidoIdReem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resenas`
--

DROP TABLE IF EXISTS `resenas`;
CREATE TABLE IF NOT EXISTS `resenas` (
  `idRes` int NOT NULL AUTO_INCREMENT,
  `productoIdRes` int NOT NULL,
  `pedidoIdRes` int DEFAULT NULL COMMENT 'Pedido donde se compró el producto',
  `usuarioIdRes` int DEFAULT NULL COMMENT 'Usuario registrado',
  `calificacionRes` tinyint(1) NOT NULL COMMENT 'Calificación 1 a 5 estrellas',
  `tituloRes` varchar(200) DEFAULT NULL,
  `comentarioRes` text NOT NULL,
  `fechaCreaRes` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estadoRes` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=Activo, 0=Inactivo',
  `verificadoRes` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1=Compra verificada',
  `utilPositivoRes` int NOT NULL DEFAULT '0',
  `utilNegativoRes` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`idRes`),
  KEY `idx_resena_producto` (`productoIdRes`),
  KEY `idx_resena_usuario` (`usuarioIdRes`),
  KEY `idx_resena_calificacion` (`calificacionRes`),
  KEY `idx_resena_estado` (`estadoRes`),
  KEY `idx_resena_prod_estado_fecha` (`productoIdRes`,`estadoRes`,`fechaCreaRes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `idRol` int NOT NULL AUTO_INCREMENT,
  `nombreRol` varchar(100) NOT NULL,
  `fechaCreaRol` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idRol`),
  UNIQUE KEY `uk_roles_nombre` (`nombreRol`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`idRol`, `nombreRol`, `fechaCreaRol`) VALUES
(1, 'Administrador', '2026-06-15 11:21:37'),
(2, 'Empleado', '2026-06-15 11:21:37'),
(3, 'Cliente', '2026-06-15 11:21:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_permiso`
--

DROP TABLE IF EXISTS `rol_permiso`;
CREATE TABLE IF NOT EXISTS `rol_permiso` (
  `idRol` int NOT NULL,
  `idDashPer` int NOT NULL,
  `puede_ver` tinyint(1) DEFAULT '1',
  `puede_crear` tinyint(1) DEFAULT '1',
  `puede_modificar` tinyint(1) DEFAULT '1',
  `puede_eliminar` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`idRol`,`idDashPer`),
  KEY `idDashPer` (`idDashPer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `rol_permiso`
--

INSERT INTO `rol_permiso` (`idRol`, `idDashPer`, `puede_ver`, `puede_crear`, `puede_modificar`, `puede_eliminar`) VALUES
(1, 1, 1, 1, 1, 1),
(1, 2, 1, 1, 1, 1),
(1, 3, 1, 1, 1, 1),
(1, 4, 1, 1, 1, 1),
(1, 5, 1, 1, 1, 1),
(1, 6, 1, 1, 1, 1),
(1, 7, 1, 1, 1, 1),
(1, 8, 1, 1, 1, 1),
(1, 9, 1, 1, 1, 1),
(2, 1, 1, 1, 1, 1),
(2, 2, 1, 1, 1, 1),
(2, 3, 1, 0, 0, 0),
(2, 4, 1, 1, 1, 1),
(2, 5, 1, 1, 1, 1),
(2, 6, 1, 1, 0, 0),
(2, 8, 1, 0, 0, 0),
(2, 9, 1, 0, 0, 0),
(3, 1, 1, 1, 1, 1),
(3, 4, 1, 1, 1, 1),
(3, 9, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcategoria`
--

DROP TABLE IF EXISTS `subcategoria`;
CREATE TABLE IF NOT EXISTS `subcategoria` (
  `idSubCat` int NOT NULL,
  `idCat` bigint NOT NULL,
  `nombreSubCat` varchar(100) NOT NULL,
  `descripcionSubCat` varchar(450) NOT NULL,
  `estadoSubCat` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `fechaCreaSubCat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idSubCat`,`idCat`),
  UNIQUE KEY `uq_subcategoria_id` (`idSubCat`),
  KEY `idx_subcat_cat` (`idCat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `subcategoria`
--

INSERT INTO `subcategoria` (`idSubCat`, `idCat`, `nombreSubCat`, `descripcionSubCat`, `estadoSubCat`, `fechaCreaSubCat`) VALUES
(1, 1, 'Tortas Clásicas', 'Tortas de sabores tradicionales', 'ACTIVO', '2026-06-16 04:09:01'),
(2, 1, 'Tortas Especiales', 'Tortas temáticas y personalizadas', 'ACTIVO', '2026-06-16 04:09:01'),
(3, 2, 'Medialunas', 'Medialunas de manteca y grasa', 'ACTIVO', '2026-06-16 04:09:01'),
(4, 2, 'Facturas surtidas', 'Vigilantes, cuernitos, bolas de fraile', 'ACTIVO', '2026-06-16 04:09:01'),
(5, 3, 'Mousses', 'Mousses y cremas frías', 'ACTIVO', '2026-06-16 04:09:01'),
(6, 3, 'Budines', 'Budines y bizcochuelos', 'ACTIVO', '2026-06-16 04:09:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipopago`
--

DROP TABLE IF EXISTS `tipopago`;
CREATE TABLE IF NOT EXISTS `tipopago` (
  `idTipoPago` int NOT NULL AUTO_INCREMENT,
  `modalidadTipoPago` varchar(100) NOT NULL,
  `estadoTipoPago` tinyint DEFAULT '1',
  PRIMARY KEY (`idTipoPago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `torta_opcion`
--

DROP TABLE IF EXISTS `torta_opcion`;
CREATE TABLE IF NOT EXISTS `torta_opcion` (
  `idTortaOpc` int NOT NULL AUTO_INCREMENT,
  `tipoOpcion` enum('Tamano','Relleno','Cobertura','Decoracion','Otro') NOT NULL,
  `nombreOpcion` varchar(100) NOT NULL COMMENT 'Ej: Grande, Dulce de leche, Ganache',
  `descripcionOpcion` varchar(255) DEFAULT NULL,
  `precioAdicional` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Incremento al precio base',
  `insumoIdTortaOpc` int DEFAULT NULL COMMENT 'Insumo vinculado para verificar stock — RF17.3',
  `estadoOpcion` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `ordenVisualizacion` int NOT NULL DEFAULT '0' COMMENT 'Orden en el configurador web',
  PRIMARY KEY (`idTortaOpc`),
  KEY `idx_tortaopc_tipo` (`tipoOpcion`),
  KEY `idx_tortaopc_insumo` (`insumoIdTortaOpc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `torta_personalizada`
--

DROP TABLE IF EXISTS `torta_personalizada`;
CREATE TABLE IF NOT EXISTS `torta_personalizada` (
  `idTortaPers` int NOT NULL AUTO_INCREMENT,
  `clienteIdTortaPers` int NOT NULL COMMENT 'Cliente que configuró la torta',
  `nombreTortaPers` varchar(150) DEFAULT NULL COMMENT 'Etiqueta opcional',
  `precioBaseTortaPers` decimal(10,2) NOT NULL DEFAULT '0.00',
  `precioTotalTortaPers` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Precio calculado en tiempo real — RF17.2',
  `observacionTortaPers` text COMMENT 'Instrucciones adicionales del cliente',
  `estadoTortaPers` enum('En configuracion','Guardada','En carrito','Pedida') NOT NULL DEFAULT 'En configuracion',
  `pedidoIdTortaPers` bigint DEFAULT NULL COMMENT 'Pedido generado al confirmar — RF17.4',
  `presupuestoIdTortaPers` int DEFAULT NULL COMMENT 'Presupuesto si se pidió cotización — RF17.4',
  `fechaCreaTortaPers` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idTortaPers`),
  KEY `idx_tortapers_cliente` (`clienteIdTortaPers`),
  KEY `idx_tortapers_pedido` (`pedidoIdTortaPers`),
  KEY `idx_tortapers_presupuesto` (`presupuestoIdTortaPers`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `torta_personalizada_opcion`
--

DROP TABLE IF EXISTS `torta_personalizada_opcion`;
CREATE TABLE IF NOT EXISTS `torta_personalizada_opcion` (
  `tortaPersId` int NOT NULL COMMENT 'Torta configurada',
  `tortaOpcId` int NOT NULL COMMENT 'Opción elegida',
  PRIMARY KEY (`tortaPersId`,`tortaOpcId`),
  KEY `idx_torta_opc` (`tortaOpcId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transaccion_puntos`
--

DROP TABLE IF EXISTS `transaccion_puntos`;
CREATE TABLE IF NOT EXISTS `transaccion_puntos` (
  `idTransPuntos` int NOT NULL AUTO_INCREMENT,
  `clienteIdTransPuntos` int NOT NULL COMMENT 'Cliente titular de los puntos',
  `puntosTrans` int NOT NULL COMMENT 'Positivo = acumulación, Negativo = canje',
  `motivoTrans` enum('Compra','Canje','Ajuste Manual','Bonificacion') NOT NULL DEFAULT 'Compra',
  `ventaIdTransPuntos` int DEFAULT NULL COMMENT 'Venta que generó la acumulación',
  `pedidoIdTransPuntos` bigint DEFAULT NULL,
  `observacionTrans` varchar(255) DEFAULT NULL,
  `fechaTrans` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idTransPuntos`),
  KEY `idx_transpuntos_cliente` (`clienteIdTransPuntos`),
  KEY `idx_transpuntos_venta` (`ventaIdTransPuntos`),
  KEY `idx_transpuntos_pedido` (`pedidoIdTransPuntos`),
  KEY `idx_transpuntos_fecha` (`fechaTrans`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE IF NOT EXISTS `usuario` (
  `idUsu` int NOT NULL AUTO_INCREMENT,
  `nombreUsu` varchar(50) NOT NULL,
  `apellidoUsu` varchar(45) NOT NULL,
  `correoUsu` varchar(100) NOT NULL,
  `usuarioUsu` varchar(50) NOT NULL,
  `contrasenaUsu` varchar(64) NOT NULL,
  `CUILUsu` varchar(20) DEFAULT NULL,
  `telefonoUsu` varchar(15) NOT NULL,
  `estadoUsu` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `rolUsu` enum('Administrador','Empleado','Cliente') NOT NULL DEFAULT 'Cliente',
  `tokenRecuperacionUsu` varchar(64) DEFAULT NULL,
  `expiracionTokenUsu` datetime DEFAULT NULL,
  `direccionUsu` json DEFAULT NULL,
  `fechaNacUsu` date DEFAULT NULL,
  PRIMARY KEY (`idUsu`),
  UNIQUE KEY `uk_usuario_correo` (`correoUsu`),
  UNIQUE KEY `uk_usuario_username` (`usuarioUsu`),
  UNIQUE KEY `uk_usuario_cuil` (`CUILUsu`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idUsu`, `nombreUsu`, `apellidoUsu`, `correoUsu`, `usuarioUsu`, `contrasenaUsu`, `CUILUsu`, `telefonoUsu`, `estadoUsu`, `rolUsu`, `tokenRecuperacionUsu`, `expiracionTokenUsu`, `direccionUsu`, `fechaNacUsu`) VALUES
(1, 'Test', '-', 'test@test.com', 'testuser', '$2y$10$dWSx6I7IomXyi1OHVWJKTuD8Lub5/LCrubTlpvlcSs57kXchqo8pK', '20193776029', '123', 'Activo', 'Cliente', NULL, NULL, NULL, NULL),
(2, 'Lourdes Bordon', '-', 'luli.antonella19@gmail.com', 'lourdes antonellab', '$2y$10$Flphg2VObXz4lHtNlf72Mu8Jq8rz2/uX9Xw6X2e6inE1f3VaVdv5m', '20340094639', '3704273333', 'Activo', 'Administrador', NULL, NULL, NULL, NULL),
(3, 'Test', 'User', 'admin_test@test.com', 'admin_test', '$2y$10$m1EUNGa8XpvkPOU2inoR/uEcRUKQTUhCv2yVLN.KKRFEvtqvtqUx.', '20206996829', '1234567890', 'Inactivo', 'Cliente', NULL, NULL, NULL, NULL),
(4, 'Monnie', '-', 'raymonnie7@gmail.com', 'monnieu', '$2y$10$9d/k0WE4UhFebS.rbD0Ji.uscaR58Xxn5EAymYZSrcFfP9UQH7tNK', '20978632699', '', 'Inactivo', 'Empleado', NULL, NULL, '[]', NULL),
(5, 'Facundo Caceres', '-', 'caceresfacundo28@gmail.com', 'Pipi', '$2y$10$8YcxEnBsmrZ/1NKJRL1GGepwiVvmITM6JayIsEzE3oO5X1YBh/Ur6', '20360870159', '03704273333', 'Activo', 'Administrador', NULL, NULL, '[]', NULL),
(6, 'Mandy Celestine', '-', 'lookout@gmail.com', 'mantis', '$2y$10$7BTmPLUUtSolOQKPUPQuFefj4lkxsGqjPup7CDhlsJ6HZdU5P7gj.', '20698045869', '', 'Activo', 'Cliente', NULL, NULL, '[]', NULL),
(7, 'Lourdes Bordon', '-', 'admin@sbaveca.com', 'admin', '$2y$10$EYNvci5mOeboRl0BZ38TBe0NCmtR78FaSindLKozPeBZW4clix6j2', '20123456789', '123456789', 'Activo', 'Administrador', NULL, NULL, NULL, NULL),
(8, 'Susana Oria', '-', 'susanitaoria@sbaveca.com', 'lbordon', '$2y$10$kvkAthe7sh2jSPhSofE25O14C7kbCgkghW7PWZl/Wgygw5OjjdJEu', '27345678901', '03704273333', 'Inactivo', 'Empleado', NULL, NULL, '{\"pais\": \"Argentina\", \"ciudad\": \"Formosa\", \"provincia\": \"Formosa\", \"codigoPostal\": \"3600\"}', '1971-01-01'),
(9, 'Hijitus Super', '-', 'raymonnie27@gmail.com', 'fufuychucuchucuchucu', '$2y$10$iZBvK.Gh6EhK7ZYkytm.JucLUvGavJqRqFy0pdz3mlmSCTkrp4noC', '20456789012', '3512000003', 'Activo', 'Empleado', NULL, NULL, '[]', '2001-03-09'),
(10, 'Remy Etienne LeBeau', '-', 'neverfolds@gmail.com', 'gambit', '$2y$10$S05HMStP/IMtKTnUEvsLxuj2VS0Ej3Qq8dYn45DW7nohPAqrIfP8i', '27567890123', '3512000004', 'Activo', 'Empleado', NULL, NULL, '[]', '1990-07-06'),
(11, 'María', 'González', 'maria.gonzalez@mail.com', 'mgonzalez', '$2y$10$CihmUaE/k6SM2D5xojbHKeEVWPO2kxMvY5GDIWS61Ha4tWYfnd/.q', '20199001001', '3512200001', 'Activo', 'Cliente', NULL, NULL, NULL, NULL),
(12, 'Carlos', 'Martínez', 'carlos.martinez@mail.com', 'cmartinez', '$2y$10$MV/Ml3DXLJA9faGbCnQCUuoCH/zJNEdGAqgqAzFKQ411ZIsgMlFYq', '20299001002', '3512200002', 'Activo', 'Cliente', NULL, NULL, NULL, NULL),
(13, 'Ana', 'Rodríguez', 'ana.rodriguez@mail.com', 'arodriguez', '$2y$10$BtFxk52np9dMF9ZGa07ueOQqVdcJ3TBK2ZSEF3ARqLnXvTVbx9JcK', '27399001003', '3512200003', 'Activo', 'Cliente', NULL, NULL, NULL, NULL),
(14, 'Diego', 'López', 'diego.lopez@mail.com', 'dlopez', '$2y$10$NCXqF8QCxEumN0BINbGewe0s5wLZ9tg9fx/1QlTv0m4E4avSVKY6q', '20499001004', '3512200004', 'Activo', 'Cliente', NULL, NULL, NULL, NULL),
(15, 'Sofía', 'Fernández', 'sofia.fernandez@mail.com', 'sfernandez', '$2y$10$jr9XySkTW98WOTPMY2WXF.caM7UfZLSnY78aSVyw7GuNsJSntBT56', '27599001005', '3512200005', 'Activo', 'Cliente', NULL, NULL, NULL, NULL);

--
-- Disparadores `usuario`
--
DROP TRIGGER IF EXISTS `crear_cliente_despues_registro`;
DELIMITER $$
CREATE TRIGGER `crear_cliente_despues_registro` AFTER INSERT ON `usuario` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `sync_usuario_to_persona`;
DELIMITER $$
CREATE TRIGGER `sync_usuario_to_persona` AFTER INSERT ON `usuario` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `sync_usuario_update_to_persona`;
DELIMITER $$
CREATE TRIGGER `sync_usuario_update_to_persona` AFTER UPDATE ON `usuario` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_rol`
--

DROP TABLE IF EXISTS `usuario_rol`;
CREATE TABLE IF NOT EXISTS `usuario_rol` (
  `idUsu` int NOT NULL,
  `idRol` int NOT NULL,
  PRIMARY KEY (`idUsu`,`idRol`),
  KEY `idRol` (`idRol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuario_rol`
--

INSERT INTO `usuario_rol` (`idUsu`, `idRol`) VALUES
(2, 1),
(5, 1),
(7, 1),
(4, 2),
(8, 2),
(9, 2),
(10, 2),
(1, 3),
(3, 3),
(6, 3),
(11, 3),
(12, 3),
(13, 3),
(14, 3),
(15, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta`
--

DROP TABLE IF EXISTS `venta`;
CREATE TABLE IF NOT EXISTS `venta` (
  `idVen` int NOT NULL AUTO_INCREMENT,
  `numeroVen` varchar(20) NOT NULL,
  `fechaVen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estadoVen` enum('Pendiente','Pagado','Cancelado') NOT NULL,
  `clienteIdVen` int NOT NULL,
  `empleadoIdVen` int NOT NULL,
  `metodoPagoVen` varchar(50) DEFAULT 'Efectivo',
  `TotalVen` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`idVen`),
  KEY `idx_venta_cliente` (`clienteIdVen`),
  KEY `idx_venta_empleado` (`empleadoIdVen`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `venta`
--

INSERT INTO `venta` (`idVen`, `numeroVen`, `fechaVen`, `estadoVen`, `clienteIdVen`, `empleadoIdVen`, `metodoPagoVen`, `TotalVen`) VALUES
(1, 'V-2026-001', '2026-06-01 07:09:02', 'Pagado', 3, 4, 'Transferencia', 31800.00),
(2, 'V-2026-002', '2026-06-04 07:09:02', 'Pagado', 2, 4, 'Tarjeta de Crédito', 4440.00),
(3, 'V-2026-003', '2026-06-06 07:09:02', 'Pagado', 5, 4, 'Efectivo', 2480.00),
(4, 'V-2026-004', '2026-06-08 07:09:02', 'Pagado', 4, 4, 'Tarjeta de Crédito', 28100.00),
(5, 'V-2026-005', '2026-06-10 07:09:02', 'Pagado', 1, 4, 'Tarjeta de Débito', 19200.00),
(6, 'V-2026-006', '2026-06-11 07:09:02', 'Pagado', 3, 4, 'Transferencia', 1950.00),
(7, 'V-2026-007', '2026-06-12 07:09:02', 'Cancelado', 2, 4, 'Efectivo', 620.00),
(8, 'V-2026-008', '2026-06-13 07:09:02', 'Pagado', 5, 4, 'Efectivo', 8600.00),
(9, 'V-2026-009', '2026-06-14 07:09:02', 'Pagado', 4, 4, 'Tarjeta de Débito', 13200.00),
(10, 'V-2026-010', '2026-06-16 07:09:02', 'Pendiente', 1, 4, 'Tarjeta de Crédito', 6240.00);

--
-- Disparadores `venta`
--
DROP TRIGGER IF EXISTS `trg_acumular_puntos_venta`;
DELIMITER $$
CREATE TRIGGER `trg_acumular_puntos_venta` AFTER UPDATE ON `venta` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alerta_stock`
--
ALTER TABLE `alerta_stock`
  ADD CONSTRAINT `fk_alertast_insumo` FOREIGN KEY (`insumoIdAlerSt`) REFERENCES `insumo` (`idIns`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `fk_cliente_carrito` FOREIGN KEY (`carritoIdCar`) REFERENCES `carrito` (`idCar`),
  ADD CONSTRAINT `fk_cliente_usuario` FOREIGN KEY (`usuarioIdUsu`) REFERENCES `usuario` (`idUsu`);

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `fk_detped_pedido` FOREIGN KEY (`pedido_idPed`) REFERENCES `pedido` (`idPed`);

--
-- Filtros para la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `fk_detventa_producto` FOREIGN KEY (`productoIdDetVen`) REFERENCES `producto` (`idProducto`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_detventa_venta` FOREIGN KEY (`ventaIdDetVen`) REFERENCES `venta` (`idVen`) ON DELETE CASCADE;

--
-- Filtros para la tabla `favorito`
--
ALTER TABLE `favorito`
  ADD CONSTRAINT `fk_favorito_cliente` FOREIGN KEY (`clienteIdFav`) REFERENCES `cliente` (`idCli`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_favorito_producto` FOREIGN KEY (`productoIdFav`) REFERENCES `producto` (`idProducto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `insumo`
--
ALTER TABLE `insumo`
  ADD CONSTRAINT `fk_insumo_proveedor` FOREIGN KEY (`proveedorIdPro`) REFERENCES `proveedor` (`idPro`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `fk_inventario_insumo` FOREIGN KEY (`insumoIdInv`) REFERENCES `insumo` (`idIns`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `menu_detalle`
--
ALTER TABLE `menu_detalle`
  ADD CONSTRAINT `fk_menudet_producto` FOREIGN KEY (`productoIdMenuDet`) REFERENCES `producto` (`idProducto`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_menudet_receta` FOREIGN KEY (`recetaIdMenuDet`) REFERENCES `recetas` (`idReceta`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_menudet_semana` FOREIGN KEY (`menuSemIdMenuDet`) REFERENCES `menu_semanal` (`idMenuSem`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `menu_semanal`
--
ALTER TABLE `menu_semanal`
  ADD CONSTRAINT `fk_menusem_usuario` FOREIGN KEY (`usuarioCreaIdSem`) REFERENCES `usuario` (`idUsu`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  ADD CONSTRAINT `fk_movinv_insumo` FOREIGN KEY (`insumoIdMovInv`) REFERENCES `insumo` (`idIns`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_movinv_pedido` FOREIGN KEY (`pedidoIdMovInv`) REFERENCES `pedido` (`idPed`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_movinv_usuario` FOREIGN KEY (`usuarioIdMovInv`) REFERENCES `usuario` (`idUsu`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_movinv_venta` FOREIGN KEY (`ventaIdMovInv`) REFERENCES `venta` (`idVen`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `fk_pedido_persona` FOREIGN KEY (`persona_idPers`) REFERENCES `persona` (`idPers`);

--
-- Filtros para la tabla `pedido_personalizado`
--
ALTER TABLE `pedido_personalizado`
  ADD CONSTRAINT `fk_pedpers_pedido` FOREIGN KEY (`pedidoIdPedPers`) REFERENCES `pedido` (`idPed`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pedpers_torta` FOREIGN KEY (`tortaPersIdPedPers`) REFERENCES `torta_personalizada` (`idTortaPers`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD CONSTRAINT `fk_permisos_perfil` FOREIGN KEY (`perfil_idPerfil`) REFERENCES `perfil` (`idPerfil`);

--
-- Filtros para la tabla `persona`
--
ALTER TABLE `persona`
  ADD CONSTRAINT `fk_persona_usuario` FOREIGN KEY (`idUsuarioPers`) REFERENCES `usuario` (`idUsu`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `presupuesto`
--
ALTER TABLE `presupuesto`
  ADD CONSTRAINT `fk_pres_pedido` FOREIGN KEY (`pedidoIdPres`) REFERENCES `pedido` (`idPed`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pres_persona` FOREIGN KEY (`personaIdPres`) REFERENCES `persona` (`idPers`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `presupuesto_detalle`
--
ALTER TABLE `presupuesto_detalle`
  ADD CONSTRAINT `fk_presdet_presupuesto` FOREIGN KEY (`presupuestoIdPresDet`) REFERENCES `presupuesto` (`idPresupuesto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_presdet_producto` FOREIGN KEY (`productoIdPresDet`) REFERENCES `producto` (`idProducto`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `fk_producto_inventario` FOREIGN KEY (`inventarioIdInv`) REFERENCES `inventario` (`idInv`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_producto_proveedor` FOREIGN KEY (`proveedorIdPro`) REFERENCES `proveedor` (`idPro`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_producto_subcategoria` FOREIGN KEY (`IdSubCat`) REFERENCES `subcategoria` (`idSubCat`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto_carrito`
--
ALTER TABLE `producto_carrito`
  ADD CONSTRAINT `fk_pc_carrito` FOREIGN KEY (`idCarrito`) REFERENCES `carrito` (`idCar`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pc_producto` FOREIGN KEY (`idProducto`) REFERENCES `producto` (`idProducto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `recetas`
--
ALTER TABLE `recetas`
  ADD CONSTRAINT `fk_receta_producto` FOREIGN KEY (`productoIdRec`) REFERENCES `producto` (`idProducto`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `receta_ingrediente`
--
ALTER TABLE `receta_ingrediente`
  ADD CONSTRAINT `fk_recing_insumo` FOREIGN KEY (`insumoIdRecIng`) REFERENCES `insumo` (`idIns`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_recing_receta` FOREIGN KEY (`recetaIdRecIng`) REFERENCES `recetas` (`idReceta`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `recibos`
--
ALTER TABLE `recibos`
  ADD CONSTRAINT `fk_recibos_venta` FOREIGN KEY (`ventaId`) REFERENCES `venta` (`idVen`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reembolso`
--
ALTER TABLE `reembolso`
  ADD CONSTRAINT `fk_reembolso_pedido` FOREIGN KEY (`pedidoIdReem`) REFERENCES `pedido` (`idPed`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `resenas`
--
ALTER TABLE `resenas`
  ADD CONSTRAINT `fk_resena_producto` FOREIGN KEY (`productoIdRes`) REFERENCES `producto` (`idProducto`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_resena_usuario` FOREIGN KEY (`usuarioIdRes`) REFERENCES `usuario` (`idUsu`) ON DELETE SET NULL;

--
-- Filtros para la tabla `rol_permiso`
--
ALTER TABLE `rol_permiso`
  ADD CONSTRAINT `rol_permiso_ibfk_1` FOREIGN KEY (`idRol`) REFERENCES `roles` (`idRol`) ON DELETE CASCADE,
  ADD CONSTRAINT `rol_permiso_ibfk_2` FOREIGN KEY (`idDashPer`) REFERENCES `dashboard_permisos` (`idDashPer`) ON DELETE CASCADE;

--
-- Filtros para la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD CONSTRAINT `fk_subcategoria_categoria` FOREIGN KEY (`idCat`) REFERENCES `categoria` (`idCat`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `torta_opcion`
--
ALTER TABLE `torta_opcion`
  ADD CONSTRAINT `fk_tortaopc_insumo` FOREIGN KEY (`insumoIdTortaOpc`) REFERENCES `insumo` (`idIns`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `torta_personalizada`
--
ALTER TABLE `torta_personalizada`
  ADD CONSTRAINT `fk_tortapers_cliente` FOREIGN KEY (`clienteIdTortaPers`) REFERENCES `cliente` (`idCli`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tortapers_pedido` FOREIGN KEY (`pedidoIdTortaPers`) REFERENCES `pedido` (`idPed`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tortapers_presupuesto` FOREIGN KEY (`presupuestoIdTortaPers`) REFERENCES `presupuesto` (`idPresupuesto`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `torta_personalizada_opcion`
--
ALTER TABLE `torta_personalizada_opcion`
  ADD CONSTRAINT `fk_tortapersopc_opcion` FOREIGN KEY (`tortaOpcId`) REFERENCES `torta_opcion` (`idTortaOpc`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tortapersopc_torta` FOREIGN KEY (`tortaPersId`) REFERENCES `torta_personalizada` (`idTortaPers`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `transaccion_puntos`
--
ALTER TABLE `transaccion_puntos`
  ADD CONSTRAINT `fk_transpuntos_cliente` FOREIGN KEY (`clienteIdTransPuntos`) REFERENCES `cliente` (`idCli`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transpuntos_pedido` FOREIGN KEY (`pedidoIdTransPuntos`) REFERENCES `pedido` (`idPed`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transpuntos_venta` FOREIGN KEY (`ventaIdTransPuntos`) REFERENCES `venta` (`idVen`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario_rol`
--
ALTER TABLE `usuario_rol`
  ADD CONSTRAINT `usuario_rol_ibfk_1` FOREIGN KEY (`idUsu`) REFERENCES `usuario` (`idUsu`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_rol_ibfk_2` FOREIGN KEY (`idRol`) REFERENCES `roles` (`idRol`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
