-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-06-2025 a las 19:30:24
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistemadeventas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_almacen`
--

CREATE TABLE `tb_almacen` (
  `id_producto` int(11) NOT NULL,
  `codigo` varchar(255) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `stock` int(11) NOT NULL,
  `stock_minimo` int(11) DEFAULT NULL,
  `stock_maximo` int(11) DEFAULT NULL,
  `precio_compra` decimal(10,2) DEFAULT NULL,
  `precio_venta` decimal(10,2) DEFAULT NULL,
  `iva_predeterminado` decimal(5,2) DEFAULT 0.00 COMMENT 'IVA predeterminado',
  `fecha_ingreso` date NOT NULL,
  `imagen` text DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `fyh_creacion` datetime NOT NULL,
  `fyh_actualizacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tb_almacen`
--

INSERT INTO `tb_almacen` (`id_producto`, `codigo`, `nombre`, `descripcion`, `stock`, `stock_minimo`, `stock_maximo`, `precio_compra`, `precio_venta`, `iva_predeterminado`, `fecha_ingreso`, `imagen`, `id_usuario`, `id_categoria`, `fyh_creacion`, `fyh_actualizacion`) VALUES
(1, 'P-00001', 'pepsi', '1 litro', 110, 11, 120, 0.50, 1.00, 0.00, '2025-04-19', '2025-04-22-02-46-39__pepsi.png', 1, 12, '2025-04-22 14:46:39', '2025-06-02 02:02:25'),
(18, 'P-00003', '22', '22', 63, 22, 222, 22.00, 22.00, 12.00, '2025-05-23', NULL, 1, 13, '2025-05-23 14:01:24', '2025-06-06 00:52:25'),
(19, 'P-00004', 'fasfasasfasfasfasfasfasfas', 'sfsfsfsfsf', 53, 33, 333, 33.00, 33.00, 33.00, '2025-05-23', NULL, 1, 13, '2025-05-23 14:04:06', '2025-06-02 03:00:20'),
(24, 'P-00005', '999', '999', 51, 9, 999, 9.00, 99.00, 9.00, '2025-05-23', 'default_product.png', 1, 13, '2025-05-23 15:22:33', '2025-06-02 03:00:20'),
(25, 'P-00006', '000', '000', 11, 9, 9, 9.00, 9.00, 9.00, '2025-05-23', 'default_product.png', 1, 13, '2025-05-23 15:28:11', '2025-06-06 00:52:25'),
(27, 'P-00001', 'sffs', 'sfsfsfsf', 1, 11, 11, 11.00, 111.00, 0.00, '2025-05-27', 'P-00001_1748372087.png', 19, 22, '2025-05-27 13:54:47', '2025-05-27 14:12:42'),
(28, 'P-00001', 'cables', 'cable hdmiv2', 105, 20, 200, 100.00, 120.00, 0.00, '2025-05-28', 'P-00001_1748419713.png', 21, 24, '2025-05-28 03:08:33', '2025-05-28 04:11:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_carrito`
--

CREATE TABLE `tb_carrito` (
  `id_carrito` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nro_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fyh_creacion` datetime NOT NULL,
  `fyh_actualizacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tb_carrito`
--

INSERT INTO `tb_carrito` (`id_carrito`, `id_usuario`, `nro_venta`, `id_producto`, `cantidad`, `fyh_creacion`, `fyh_actualizacion`) VALUES
(126, 1, 1747480194, 1, 1, '2025-05-17 06:09:18', '2025-05-17 06:09:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_categorias`
--

CREATE TABLE `tb_categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre_categoria` varchar(255) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fyh_creacion` datetime NOT NULL,
  `fyh_actualizacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tb_categorias`
--

INSERT INTO `tb_categorias` (`id_categoria`, `nombre_categoria`, `id_usuario`, `fyh_creacion`, `fyh_actualizacion`) VALUES
(12, 'LIQUIDO', 1, '2025-04-22 14:09:40', '0000-00-00 00:00:00'),
(13, 'ELECTRODOMESTICOS', 1, '2025-04-22 14:09:54', '0000-00-00 00:00:00'),
(14, '1', 1, '2025-05-10 10:18:31', '0000-00-00 00:00:00'),
(15, '2', 1, '2025-05-10 10:21:16', '0000-00-00 00:00:00'),
(18, '1', 1, '2025-05-11 08:45:05', '0000-00-00 00:00:00'),
(19, 'sfsf', 1, '2025-05-24 15:21:20', '2025-05-24 15:21:20'),
(21, 'sfsfsf', 16, '2025-05-24 15:29:47', '2025-05-24 15:29:47'),
(22, 'sfsf', 19, '2025-05-27 13:54:30', '2025-05-27 13:54:30'),
(24, 'electrodomesticos', 21, '2025-05-28 03:06:18', '2025-05-28 03:06:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_clientes`
--

CREATE TABLE `tb_clientes` (
  `id_cliente` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL COMMENT 'FK to tb_usuarios.id_usuario, owner of this client record',
  `nombre_cliente` varchar(255) NOT NULL,
  `tipo_documento` enum('cedula','ruc','pasaporte','consumidor_final','otro','extranjero') DEFAULT 'consumidor_final',
  `nit_ci_cliente` varchar(25) DEFAULT NULL COMMENT 'Cédula (10), RUC (13), Pasaporte (variable), o nulo/genérico para Consumidor Final',
  `celular_cliente` varchar(20) DEFAULT NULL,
  `telefono_fijo` varchar(20) DEFAULT NULL,
  `email_cliente` varchar(255) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fyh_creacion` datetime NOT NULL,
  `fyh_actualizacion` datetime NOT NULL,
  `direccion_cliente` varchar(255) DEFAULT NULL,
  `provincia_cliente` varchar(100) DEFAULT NULL,
  `ciudad_cliente` varchar(100) DEFAULT NULL,
  `referencia_cliente` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tb_clientes`
--

INSERT INTO `tb_clientes` (`id_cliente`, `id_usuario`, `nombre_cliente`, `tipo_documento`, `nit_ci_cliente`, `celular_cliente`, `telefono_fijo`, `email_cliente`, `direccion`, `ciudad`, `provincia`, `fecha_nacimiento`, `observaciones`, `estado`, `fyh_creacion`, `fyh_actualizacion`, `direccion_cliente`, `provincia_cliente`, `ciudad_cliente`, `referencia_cliente`) VALUES
(1, 1, 'CONSUMIDOR FINAL', 'consumidor_final', '9999999999', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cliente genérico para ventas rápidas.', 'activo', '2025-05-24 09:50:39', '2025-05-24 10:06:44', NULL, NULL, NULL, NULL),
(5, 1, 'juan perezf', 'cedula', '1757949936', '42424224', '121134224', 'clientese@gmail.com', 'fsafsafas', 'lago agrio', 'Cotopaxi', '2025-05-22', 'fasfsaasf', 'activo', '2025-05-24 13:47:02', '2025-05-24 13:47:02', NULL, NULL, NULL, NULL),
(7, 1, 'juan perez', 'cedula', '1757949934', '42424224', NULL, 'clientese@gmail.com', NULL, NULL, NULL, NULL, NULL, 'activo', '2025-05-24 13:48:25', '2025-05-24 13:48:25', NULL, NULL, NULL, NULL),
(9, 1, 'juan perez', 'cedula', '2112424242', '42424224', NULL, 'clientee@gmail.com', NULL, NULL, NULL, NULL, NULL, 'activo', '2025-05-24 14:17:35', '2025-05-24 14:17:35', NULL, NULL, NULL, NULL),
(11, 1, 'juan perez', 'cedula', '0962525225', 'fsafasfsfa', NULL, 'asfasf525@gmai.com', NULL, NULL, NULL, NULL, NULL, 'activo', '2025-05-24 14:18:51', '2025-05-24 14:18:51', NULL, NULL, NULL, NULL),
(13, 19, '1111', 'consumidor_final', '9999999999', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo', '2025-05-27 13:55:58', '2025-05-27 13:55:58', NULL, NULL, NULL, NULL),
(14, 21, 'juan perez', 'consumidor_final', '9999999999', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo', '2025-05-28 03:13:21', '2025-05-28 03:13:21', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_compras`
--

CREATE TABLE `tb_compras` (
  `id_compra` int(11) NOT NULL,
  `nro_compra` int(11) NOT NULL,
  `codigo_compra_referencia` varchar(50) DEFAULT NULL,
  `fecha_compra` date NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `comprobante` varchar(255) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `subtotal_general` decimal(10,2) DEFAULT 0.00,
  `monto_iva_general` decimal(10,2) DEFAULT 0.00,
  `total_general` decimal(10,2) DEFAULT 0.00,
  `fyh_creacion` datetime NOT NULL,
  `fyh_actualizacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tb_compras`
--

INSERT INTO `tb_compras` (`id_compra`, `nro_compra`, `codigo_compra_referencia`, `fecha_compra`, `id_proveedor`, `comprobante`, `id_usuario`, `subtotal_general`, `monto_iva_general`, `total_general`, `fyh_creacion`, `fyh_actualizacion`) VALUES
(3, 3, 'C-00003', '2025-05-22', 14, NULL, 1, 1.00, 0.12, 1.12, '2025-05-22 22:10:54', '2025-05-22 22:10:54'),
(7, 4, 'C-00004', '2025-05-23', 16, NULL, 1, 44.00, 5.28, 49.28, '2025-05-23 14:01:37', '2025-05-23 14:01:37'),
(8, 5, 'C-00005', '2025-05-23', 16, NULL, 1, 33.00, 10.89, 43.89, '2025-05-23 14:04:23', '2025-05-23 14:04:23'),
(9, 6, 'C-00006', '2025-05-23', 14, NULL, 1, 9.00, 0.81, 9.81, '2025-05-23 15:22:33', '2025-05-23 15:22:33'),
(10, 7, 'C-00007', '2025-05-23', 19, NULL, 1, 31.00, 3.45, 34.45, '2025-05-23 15:28:11', '2025-05-23 17:32:41'),
(12, 8, 'C-00008', '2025-05-23', 12, NULL, 1, 33.00, 10.89, 43.89, '2025-05-23 17:51:29', '2025-05-23 17:51:29'),
(13, 1, 'C-00001', '2025-05-27', 20, NULL, 19, 11.00, 1.32, 12.32, '2025-05-27 13:55:34', '2025-05-27 13:55:34'),
(14, 1, 'C-00001', '2025-05-28', 22, NULL, 21, 500.00, 60.00, 560.00, '2025-05-28 03:10:30', '2025-05-28 03:10:30'),
(15, 9, 'C-00009', '2025-06-06', 12, '222', 1, 155.00, 18.60, 173.60, '2025-06-06 00:52:25', '2025-06-06 00:52:25'),
(16, 10, 'C-00010', '2025-06-06', 12, '222', 1, 155.00, 18.60, 173.60, '2025-06-06 00:52:25', '2025-06-06 00:52:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_detalle_compras`
--

CREATE TABLE `tb_detalle_compras` (
  `id_detalle_compra` int(11) NOT NULL,
  `id_compra` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_compra_unitario` decimal(10,2) NOT NULL,
  `porcentaje_iva_item` decimal(5,2) NOT NULL DEFAULT 0.00,
  `subtotal_item` decimal(10,2) NOT NULL,
  `monto_iva_item` decimal(10,2) NOT NULL,
  `total_item` decimal(10,2) NOT NULL,
  `fyh_creacion` datetime NOT NULL,
  `fyh_actualizacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tb_detalle_compras`
--

INSERT INTO `tb_detalle_compras` (`id_detalle_compra`, `id_compra`, `id_producto`, `cantidad`, `precio_compra_unitario`, `porcentaje_iva_item`, `subtotal_item`, `monto_iva_item`, `total_item`, `fyh_creacion`, `fyh_actualizacion`) VALUES
(3, 3, 1, 2.00, 0.50, 12.00, 1.00, 0.12, 1.12, '2025-05-22 22:10:54', '2025-05-22 22:10:54'),
(7, 7, 18, 2.00, 22.00, 12.00, 44.00, 5.28, 49.28, '2025-05-23 14:01:37', '2025-05-23 14:01:37'),
(8, 8, 19, 1.00, 33.00, 33.00, 33.00, 10.89, 43.89, '2025-05-23 14:04:23', '2025-05-23 14:04:23'),
(9, 9, 24, 1.00, 9.00, 9.00, 9.00, 0.81, 9.81, '2025-05-23 15:22:33', '2025-05-23 15:22:33'),
(10, 10, 25, 1.00, 9.00, 9.00, 9.00, 0.81, 9.81, '2025-05-23 15:28:11', '2025-05-23 17:32:41'),
(12, 10, 18, 1.00, 22.00, 12.00, 22.00, 2.64, 24.64, '2025-05-23 17:17:47', '2025-05-23 17:32:41'),
(13, 12, 19, 1.00, 33.00, 33.00, 33.00, 10.89, 43.89, '2025-05-23 17:51:29', '2025-05-23 17:51:29'),
(14, 13, 27, 1.00, 11.00, 12.00, 11.00, 1.32, 12.32, '2025-05-27 13:55:34', '2025-05-27 13:55:34'),
(15, 14, 28, 5.00, 100.00, 12.00, 500.00, 60.00, 560.00, '2025-05-28 03:10:30', '2025-05-28 03:10:30'),
(16, 15, 18, 5.00, 22.00, 12.00, 110.00, 13.20, 123.20, '2025-06-06 00:52:25', '2025-06-06 00:52:25'),
(17, 15, 25, 5.00, 9.00, 12.00, 45.00, 5.40, 50.40, '2025-06-06 00:52:25', '2025-06-06 00:52:25'),
(18, 16, 18, 5.00, 22.00, 12.00, 110.00, 13.20, 123.20, '2025-06-06 00:52:25', '2025-06-06 00:52:25'),
(19, 16, 25, 5.00, 9.00, 12.00, 45.00, 5.40, 50.40, '2025-06-06 00:52:25', '2025-06-06 00:52:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_detalle_ventas`
--

CREATE TABLE `tb_detalle_ventas` (
  `id_detalle_venta` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_venta_unitario` decimal(10,2) NOT NULL,
  `porcentaje_iva_item` decimal(5,2) NOT NULL DEFAULT 0.00,
  `monto_iva_item` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento_item` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal_item` decimal(10,2) NOT NULL COMMENT 'cantidad * precio_venta_unitario - descuento_item',
  `total_item` decimal(10,2) NOT NULL COMMENT 'subtotal_item + monto_iva_item',
  `fyh_creacion` datetime NOT NULL,
  `fyh_actualizacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tb_detalle_ventas`
--

INSERT INTO `tb_detalle_ventas` (`id_detalle_venta`, `id_venta`, `id_producto`, `cantidad`, `precio_venta_unitario`, `porcentaje_iva_item`, `monto_iva_item`, `descuento_item`, `subtotal_item`, `total_item`, `fyh_creacion`, `fyh_actualizacion`) VALUES
(2, 1, 27, 1.00, 111.00, 12.00, 13.32, 0.00, 111.00, 124.32, '2025-05-27 14:12:42', '2025-05-27 14:12:42'),
(3, 2, 28, 70.00, 12.00, 12.00, 100.80, 0.00, 840.00, 940.80, '2025-05-28 03:16:30', '2025-05-28 03:16:30'),
(4, 3, 1, 5.00, 5.00, 12.00, 3.00, 0.00, 25.00, 28.00, '2025-05-28 04:26:51', '2025-05-28 04:26:51'),
(5, 4, 1, 1.00, 1.00, 15.00, 0.15, 0.00, 1.00, 1.15, '2025-06-02 02:02:25', '2025-06-02 02:02:25'),
(6, 4, 18, 2.00, 22.00, 12.00, 5.28, 0.00, 44.00, 49.28, '2025-06-02 02:02:25', '2025-06-02 02:02:25'),
(7, 4, 24, 1.00, 99.00, 9.00, 8.91, 0.00, 99.00, 107.91, '2025-06-02 02:02:25', '2025-06-02 02:02:25'),
(8, 5, 18, 2.00, 22.00, 12.00, 5.28, 0.00, 44.00, 49.28, '2025-06-02 03:00:20', '2025-06-02 03:00:20'),
(9, 5, 19, 2.00, 33.00, 33.00, 21.78, 0.00, 66.00, 87.78, '2025-06-02 03:00:20', '2025-06-02 03:00:20'),
(10, 5, 24, 4.00, 99.00, 9.00, 35.64, 0.00, 396.00, 431.64, '2025-06-02 03:00:20', '2025-06-02 03:00:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_historial_ventas`
--

CREATE TABLE `tb_historial_ventas` (
  `id_historial` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `detalles` text DEFAULT NULL,
  `fyh_registro` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tb_historial_ventas`
--

INSERT INTO `tb_historial_ventas` (`id_historial`, `id_venta`, `id_usuario`, `accion`, `detalles`, `fyh_registro`) VALUES
(1, 2, 21, 'ANULACION', 'fsfsffs', '2025-05-28 04:11:13'),
(2, 3, 1, 'ANULACION', 'fssfsf', '2025-05-28 04:27:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_proveedores`
--

CREATE TABLE `tb_proveedores` (
  `id_proveedor` int(11) NOT NULL,
  `nombre_proveedor` varchar(255) NOT NULL,
  `celular` varchar(50) NOT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `empresa` varchar(255) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `direccion` varchar(255) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fyh_creacion` datetime NOT NULL,
  `fyh_actualizacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tb_proveedores`
--

INSERT INTO `tb_proveedores` (`id_proveedor`, `nombre_proveedor`, `celular`, `telefono`, `empresa`, `email`, `direccion`, `id_usuario`, `fyh_creacion`, `fyh_actualizacion`) VALUES
(12, 'Jonathan Ordoñez', '0954924922', '1', 'EMPRESA Xx', 'ordoñez@gmail.com', 'Via quito Kilometro 3/2', 1, '2025-04-22 14:12:18', '2025-05-11 12:58:28'),
(14, 'JUAN1', '12345678910', '1212', 'xxx111', '1@gmail.com', 'calle xd', 1, '2025-05-11 12:52:03', '0000-00-00 00:00:00'),
(16, 'fasf', '1212', '211212', '1221', 'fasf', 'asffasasf', 1, '2025-05-11 12:59:15', '0000-00-00 00:00:00'),
(17, 'fasasf', '1212', '112', 'asfasf', '1@gmail.com', 'fasfsafsaf', 1, '2025-05-11 13:11:18', '0000-00-00 00:00:00'),
(18, 'fffffffffffffffff', '222222222', '2222222222', '2222222222222', '222222222', '222222222222', 13, '2025-05-11 13:11:43', '0000-00-00 00:00:00'),
(19, '111', '11', '111', '111', '11@111.com', '111', 1, '2025-05-23 16:21:15', '2025-05-23 16:21:15'),
(20, '11111', '111', '1111', '111', 'marcelo@gmail.com', 'sffssfssf', 19, '2025-05-27 13:55:15', '2025-05-27 13:55:15'),
(21, 'asfasf', '122112122', NULL, 'sfsfsfsf', 'marcelo@gmail.com', 'sffssfsffs', 1, '2025-05-27 14:58:24', '2025-05-27 14:58:24'),
(22, 'jonathan', '0967407066', NULL, 'xxxxx', NULL, 'Via quito 3/2', 21, '2025-05-28 03:09:55', '2025-05-28 03:09:55'),
(23, 'pedro', '0967407066', NULL, 'asasas', 'marcelo@gmail.com', 'sfsafasfasafasf', 21, '2025-05-28 03:11:32', '2025-05-28 03:11:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_provincias_ecuador`
--

CREATE TABLE `tb_provincias_ecuador` (
  `id_provincia` int(11) NOT NULL,
  `codigo_provincia` varchar(2) NOT NULL,
  `nombre_provincia` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tb_provincias_ecuador`
--

INSERT INTO `tb_provincias_ecuador` (`id_provincia`, `codigo_provincia`, `nombre_provincia`) VALUES
(1, '01', 'Azuay'),
(2, '02', 'Bolívar'),
(3, '03', 'Cañar'),
(4, '04', 'Carchi'),
(5, '05', 'Cotopaxi'),
(6, '06', 'Chimborazo'),
(7, '07', 'El Oro'),
(8, '08', 'Esmeraldas'),
(9, '09', 'Guayas'),
(10, '10', 'Imbabura'),
(11, '11', 'Loja'),
(12, '12', 'Los Ríos'),
(13, '13', 'Manabí'),
(14, '14', 'Morona Santiago'),
(15, '15', 'Napo'),
(16, '16', 'Pastaza'),
(17, '17', 'Pichincha'),
(18, '18', 'Tungurahua'),
(19, '19', 'Zamora Chinchipe'),
(20, '20', 'Galápagos'),
(21, '21', 'Sucumbíos'),
(22, '22', 'Orellana'),
(23, '23', 'Santo Domingo de los Tsáchilas'),
(24, '24', 'Santa Elena');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_roles`
--

CREATE TABLE `tb_roles` (
  `id_rol` int(11) NOT NULL,
  `rol` varchar(255) NOT NULL,
  `fyh_creacion` datetime NOT NULL,
  `fyh_actualizacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tb_roles`
--

INSERT INTO `tb_roles` (`id_rol`, `rol`, `fyh_creacion`, `fyh_actualizacion`) VALUES
(1, 'administrador', '2025-04-10 20:05:00', '2025-04-22 21:05:00'),
(7, 'vendedor', '2025-05-04 06:18:50', '0000-00-00 00:00:00'),
(9, 'xdd', '2025-05-04 09:38:01', '2025-05-04 09:39:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_usuarios`
--

CREATE TABLE `tb_usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombres` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `imagen_perfil` varchar(255) DEFAULT 'user_default.png',
  `estado` enum('activo','eliminado') DEFAULT 'activo',
  `password_user` text NOT NULL,
  `token` varchar(100) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `fyh_creacion` datetime NOT NULL,
  `fyh_actualizacion` datetime NOT NULL,
  `fecha_eliminacion` datetime DEFAULT NULL,
  `eliminado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tb_usuarios`
--

INSERT INTO `tb_usuarios` (`id_usuario`, `nombres`, `email`, `usuario`, `imagen_perfil`, `estado`, `password_user`, `token`, `id_rol`, `fyh_creacion`, `fyh_actualizacion`, `fecha_eliminacion`, `eliminado_por`) VALUES
(1, 'marcelo mamanis', 'marcelo@gmail.com', 'marcelo', '2025-05-11-14-59-30_682101a228f7c.PNG', 'activo', '$2y$10$75JF2CgxfIl0D2FvA2n7Ce0lJogqlHHmV9I38Z2SHytN7iElNtSxm', '', 1, '2025-04-14 21:07:42', '2025-06-11 12:28:22', NULL, NULL),
(12, 'venderw', 'vender@gmail.com', 'vender', 'user_default.png', 'activo', '$2y$10$EBfd4aY2yFbbWpkPSAC1XO4PKFeWTK9r9WKa/WB5iRTD5fRGlRNJi', '', 7, '2025-05-04 06:19:20', '2025-05-11 14:41:07', NULL, NULL),
(13, 'xd 22', '1@gmail.com', '1', 'user_default.png', 'activo', '$2y$10$uzWGvuEd0xTk3.jnYza22.FMIzgIxgsPYuGzl7oHngZDxtE6mrr4S', '', 1, '2025-05-08 21:57:46', '2025-05-11 15:04:05', NULL, NULL),
(14, '2', '2@gmail.com', '2', 'user_default.png', 'activo', '$2y$10$8NahENpQCkCI565YSxAffOcfuyB5gfzrtt5UkHyhvx0YfBLrWkXt6', '', 7, '2025-05-10 17:32:16', '2025-05-10 17:32:22', NULL, NULL),
(16, 'xxxx', 'xxxx@xxx.com', 'xxxx', 'user_default.png', 'activo', '$2y$10$wBQtOi/0pGcyvOX4icMlZ.5VklvZOS0/5gwFvnAcPSMJHfoZiGGGi', '', 1, '2025-05-24 15:22:28', '2025-05-24 15:22:50', NULL, NULL),
(17, 'eee', 'eeee@gmail.com', 'eeee', 'user_default.png', 'activo', '$2y$10$8cpQ748aUYawDVj7LQisieFsNAgsMmpZZF9T2lnsm87Pv.CuJlKwG', '', 7, '2025-05-25 08:40:53', '2025-05-25 08:40:53', NULL, NULL),
(19, '222222', 'donpancho2312@gmail.com', 'marcelo1', 'user_19_1748372670.png', 'activo', '$2y$10$/iGORLMJ4BG8iss3hMpehOQE5E2vlCgnm4wY35AOOE7p/vunzoISG', '', 7, '2025-05-27 13:54:04', '2025-05-27 14:04:30', NULL, NULL),
(20, 'sfsffsfsfs', 'sa@ff.com', 'nnnn', 'user_default.png', 'activo', '$2y$10$LGL5zQdiXsgCZxcFzOIyfu1SvWlaVKciqJzQzau1oN5g59Laa2IRG', '', 7, '2025-05-27 18:00:42', '2025-05-27 18:01:16', NULL, NULL),
(21, 'Marceloo Mamanii', '1ffff@gmail.com', 'ffff', 'user_default.png', 'activo', '$2y$10$ej7Nhb5PJFZ3ltASi17..ervXrQ8DWS28Z5zAWDNRnpxp5WtsEXii', '', 7, '2025-05-28 03:04:50', '2025-05-28 03:04:56', NULL, NULL),
(22, 'sssss', 'mamanis@gmail.com', 'ssss', 'user_default.png', 'activo', '$2y$10$XNJqUBC2i3l.rqTOiXz60epa.emGrinzl4SEnMlw6k1MWgBUOOaGS', '', 7, '2025-05-28 09:04:14', '2025-05-28 09:04:20', NULL, NULL),
(23, 'Marceloo Mamaniiss', 'marcelo2@gmail.com', 'marcelo2', 'user_default.png', 'activo', '$2y$10$RwmVSBN.qeCixXJS/swh6elTJArsPmmLJ8TqsEGTmyrJydBpX9Bdi', '', 7, '2025-06-01 22:25:42', '2025-06-01 22:25:47', NULL, NULL),
(24, 'admin', 'admin@gmail.com', 'admin', 'user_default.png', 'activo', '$2y$10$VHzNgyCdoDrP0T19AjV48.aDvRQ2HYHJiTItu2uWtl7xG1qW/re.O', '', 1, '2025-06-03 01:46:12', '2025-06-04 03:15:36', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_ventas`
--

CREATE TABLE `tb_ventas` (
  `id_venta` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `nro_venta_secuencial` int(11) NOT NULL,
  `codigo_venta_referencia` varchar(50) NOT NULL,
  `fecha_venta` date NOT NULL,
  `tipo_comprobante` varchar(100) DEFAULT NULL,
  `nro_comprobante_fisico` varchar(100) DEFAULT NULL,
  `subtotal_general` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_iva_general` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento_general` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_general` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado_venta` enum('PENDIENTE','PAGADA','ANULADA','ENTREGADA') DEFAULT 'PENDIENTE',
  `observaciones` text DEFAULT NULL,
  `fyh_creacion` datetime NOT NULL,
  `fyh_actualizacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tb_ventas`
--

INSERT INTO `tb_ventas` (`id_venta`, `id_usuario`, `id_cliente`, `nro_venta_secuencial`, `codigo_venta_referencia`, `fecha_venta`, `tipo_comprobante`, `nro_comprobante_fisico`, `subtotal_general`, `monto_iva_general`, `descuento_general`, `total_general`, `estado_venta`, `observaciones`, `fyh_creacion`, `fyh_actualizacion`) VALUES
(1, 19, 13, 1, 'V-00001', '2025-05-27', 'NOTA DE VENTA', '', 111.00, 13.32, 0.00, 124.32, 'PAGADA', '', '2025-05-27 13:56:15', '2025-05-27 14:12:43'),
(2, 21, 14, 1, 'V-00001', '2025-05-28', 'NOTA DE VENTA', NULL, 840.00, 100.80, 0.00, 940.80, 'ANULADA', NULL, '2025-05-28 03:16:30', '2025-05-28 04:11:13'),
(3, 1, 1, 1, 'V-00001', '2025-05-28', 'NOTA DE VENTA', NULL, 25.00, 3.00, 0.00, 28.00, 'ANULADA', NULL, '2025-05-28 04:26:51', '2025-05-28 04:27:18'),
(4, 1, 9, 2, 'V-00002', '2025-06-02', 'NOTA DE VENTA', NULL, 144.00, 14.34, 0.00, 158.34, 'PAGADA', NULL, '2025-06-02 02:02:25', '2025-06-02 02:02:25'),
(5, 1, 5, 3, 'V-00003', '2025-06-02', 'NOTA DE VENTA', NULL, 506.00, 62.70, 0.00, 568.70, 'PAGADA', NULL, '2025-06-02 03:00:20', '2025-06-02 03:00:20');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `tb_almacen`
--
ALTER TABLE `tb_almacen`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `tb_carrito`
--
ALTER TABLE `tb_carrito`
  ADD PRIMARY KEY (`id_carrito`),
  ADD KEY `id_venta` (`nro_venta`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `tb_categorias`
--
ALTER TABLE `tb_categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD KEY `fk_categoria_usuario` (`id_usuario`);

--
-- Indices de la tabla `tb_clientes`
--
ALTER TABLE `tb_clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `uq_usuario_documento` (`id_usuario`,`tipo_documento`,`nit_ci_cliente`),
  ADD KEY `idx_id_usuario` (`id_usuario`),
  ADD KEY `idx_tipo_documento` (`tipo_documento`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `tb_compras`
--
ALTER TABLE `tb_compras`
  ADD PRIMARY KEY (`id_compra`),
  ADD KEY `idx_proveedor_compra` (`id_proveedor`),
  ADD KEY `idx_usuario_compra` (`id_usuario`);

--
-- Indices de la tabla `tb_detalle_compras`
--
ALTER TABLE `tb_detalle_compras`
  ADD PRIMARY KEY (`id_detalle_compra`),
  ADD KEY `idx_compra_detalle` (`id_compra`),
  ADD KEY `idx_producto_detalle` (`id_producto`);

--
-- Indices de la tabla `tb_detalle_ventas`
--
ALTER TABLE `tb_detalle_ventas`
  ADD PRIMARY KEY (`id_detalle_venta`),
  ADD KEY `fk_detalle_venta_venta` (`id_venta`),
  ADD KEY `fk_detalle_venta_producto` (`id_producto`);

--
-- Indices de la tabla `tb_historial_ventas`
--
ALTER TABLE `tb_historial_ventas`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `id_venta` (`id_venta`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `tb_proveedores`
--
ALTER TABLE `tb_proveedores`
  ADD PRIMARY KEY (`id_proveedor`);

--
-- Indices de la tabla `tb_provincias_ecuador`
--
ALTER TABLE `tb_provincias_ecuador`
  ADD PRIMARY KEY (`id_provincia`);

--
-- Indices de la tabla `tb_roles`
--
ALTER TABLE `tb_roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `tb_usuarios`
--
ALTER TABLE `tb_usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `idx_usuario_unique` (`usuario`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `fk_usuario_eliminado_por` (`eliminado_por`);

--
-- Indices de la tabla `tb_ventas`
--
ALTER TABLE `tb_ventas`
  ADD PRIMARY KEY (`id_venta`),
  ADD UNIQUE KEY `uq_usuario_codigo_venta` (`id_usuario`,`codigo_venta_referencia`),
  ADD KEY `fk_venta_usuario` (`id_usuario`),
  ADD KEY `fk_venta_cliente` (`id_cliente`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tb_almacen`
--
ALTER TABLE `tb_almacen`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `tb_carrito`
--
ALTER TABLE `tb_carrito`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT de la tabla `tb_categorias`
--
ALTER TABLE `tb_categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `tb_clientes`
--
ALTER TABLE `tb_clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `tb_compras`
--
ALTER TABLE `tb_compras`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `tb_detalle_compras`
--
ALTER TABLE `tb_detalle_compras`
  MODIFY `id_detalle_compra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `tb_detalle_ventas`
--
ALTER TABLE `tb_detalle_ventas`
  MODIFY `id_detalle_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `tb_historial_ventas`
--
ALTER TABLE `tb_historial_ventas`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tb_proveedores`
--
ALTER TABLE `tb_proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `tb_provincias_ecuador`
--
ALTER TABLE `tb_provincias_ecuador`
  MODIFY `id_provincia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `tb_roles`
--
ALTER TABLE `tb_roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `tb_usuarios`
--
ALTER TABLE `tb_usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `tb_ventas`
--
ALTER TABLE `tb_ventas`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `tb_almacen`
--
ALTER TABLE `tb_almacen`
  ADD CONSTRAINT `tb_almacen_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `tb_categorias` (`id_categoria`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tb_almacen_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `tb_usuarios` (`id_usuario`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `tb_carrito`
--
ALTER TABLE `tb_carrito`
  ADD CONSTRAINT `tb_carrito_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `tb_almacen` (`id_producto`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `tb_categorias`
--
ALTER TABLE `tb_categorias`
  ADD CONSTRAINT `fk_categoria_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tb_usuarios` (`id_usuario`);

--
-- Filtros para la tabla `tb_clientes`
--
ALTER TABLE `tb_clientes`
  ADD CONSTRAINT `fk_cliente_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tb_usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tb_compras`
--
ALTER TABLE `tb_compras`
  ADD CONSTRAINT `fk_compras_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `tb_proveedores` (`id_proveedor`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compras_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tb_usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tb_detalle_compras`
--
ALTER TABLE `tb_detalle_compras`
  ADD CONSTRAINT `fk_detallecompras_compra` FOREIGN KEY (`id_compra`) REFERENCES `tb_compras` (`id_compra`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detallecompras_producto` FOREIGN KEY (`id_producto`) REFERENCES `tb_almacen` (`id_producto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tb_detalle_ventas`
--
ALTER TABLE `tb_detalle_ventas`
  ADD CONSTRAINT `fk_detalle_venta_producto` FOREIGN KEY (`id_producto`) REFERENCES `tb_almacen` (`id_producto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_venta_venta` FOREIGN KEY (`id_venta`) REFERENCES `tb_ventas` (`id_venta`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tb_historial_ventas`
--
ALTER TABLE `tb_historial_ventas`
  ADD CONSTRAINT `tb_historial_ventas_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `tb_ventas` (`id_venta`),
  ADD CONSTRAINT `tb_historial_ventas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `tb_usuarios` (`id_usuario`);

--
-- Filtros para la tabla `tb_usuarios`
--
ALTER TABLE `tb_usuarios`
  ADD CONSTRAINT `fk_usuario_eliminado_por` FOREIGN KEY (`eliminado_por`) REFERENCES `tb_usuarios` (`id_usuario`),
  ADD CONSTRAINT `tb_usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `tb_roles` (`id_rol`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tb_ventas`
--
ALTER TABLE `tb_ventas`
  ADD CONSTRAINT `fk_venta_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `tb_clientes` (`id_cliente`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_venta_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tb_usuarios` (`id_usuario`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
