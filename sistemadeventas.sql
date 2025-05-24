-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-05-2025 a las 01:17:14
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
(1, 'P-00001', 'pepsi', '1 litro', 111, 11, 120, 0.50, 1.00, 0.00, '2025-04-19', '2025-04-22-02-46-39__pepsi.png', 1, 12, '2025-04-22 14:46:39', '2025-05-23 13:53:19'),
(18, 'P-00003', '22', '22', 3, 22, 222, 22.00, 22.00, 12.00, '2025-05-23', NULL, 1, 13, '2025-05-23 14:01:24', '2025-05-23 17:17:47'),
(19, 'P-00004', 'fasfasasfasfasfasfasfasfas', 'sfsfsfsfsf', 2, 33, 333, 33.00, 33.00, 33.00, '2025-05-23', NULL, 1, 13, '2025-05-23 14:04:06', '2025-05-23 17:51:29'),
(24, 'P-00005', '999', '999', 1, 9, 999, 9.00, 99.00, 9.00, '2025-05-23', 'default_product.png', 1, 13, '2025-05-23 15:22:33', '2025-05-23 15:22:33'),
(25, 'P-00006', '000', '000', 1, 9, 9, 9.00, 9.00, 9.00, '2025-05-23', 'default_product.png', 1, 13, '2025-05-23 15:28:11', '2025-05-23 15:28:11');

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
(18, '1', 1, '2025-05-11 08:45:05', '0000-00-00 00:00:00');

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
(12, 15, 'CONSUMIDOR FINAL', 'consumidor_final', '9999999999', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Cliente genérico para ventas rápidas.', 'activo', '2025-05-24 17:55:21', '2025-05-24 17:55:21', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_compras`
--

CREATE TABLE `tb_compras` (
  `id_compra` int(11) NOT NULL,
  `nro_compra` int(11) NOT NULL COMMENT 'Número secuencial interno de la compra para el usuario',
  `codigo_compra_referencia` varchar(50) DEFAULT NULL COMMENT 'Código de referencia interno formateado, ej: C-00001',
  `fecha_compra` date NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `comprobante` varchar(255) DEFAULT NULL COMMENT 'Nro de Factura/Boleta del proveedor',
  `id_usuario` int(11) NOT NULL,
  `subtotal_general` decimal(10,2) DEFAULT 0.00 COMMENT 'Subtotal general de la compra (suma de subtotales de todos los items)',
  `monto_iva_general` decimal(10,2) DEFAULT 0.00 COMMENT 'Monto total del IVA de la compra (suma de IVA de todos los items)',
  `total_general` decimal(10,2) DEFAULT 0.00 COMMENT 'Total final de la compra (subtotal_general + monto_iva_general)',
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
(12, 8, 'C-00008', '2025-05-23', 12, NULL, 1, 33.00, 10.89, 43.89, '2025-05-23 17:51:29', '2025-05-23 17:51:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_detalle_compras`
--

CREATE TABLE `tb_detalle_compras` (
  `id_detalle_compra` int(11) NOT NULL,
  `id_compra` int(11) NOT NULL COMMENT 'FK a tb_compras.id_compra',
  `id_producto` int(11) NOT NULL COMMENT 'FK a tb_almacen.id_producto',
  `cantidad` decimal(10,2) NOT NULL COMMENT 'Cantidad comprada, puede ser decimal',
  `precio_compra_unitario` decimal(10,2) NOT NULL,
  `porcentaje_iva_item` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Porcentaje de IVA aplicado a este item específico',
  `subtotal_item` decimal(10,2) NOT NULL COMMENT 'cantidad * precio_compra_unitario',
  `monto_iva_item` decimal(10,2) NOT NULL COMMENT 'subtotal_item * (porcentaje_iva_item / 100)',
  `total_item` decimal(10,2) NOT NULL COMMENT 'subtotal_item + monto_iva_item',
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
(13, 12, 19, 1.00, 33.00, 33.00, 33.00, 10.89, 43.89, '2025-05-23 17:51:29', '2025-05-23 17:51:29');

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
(19, '111', '11', '111', '111', '11@111.com', '111', 1, '2025-05-23 16:21:15', '2025-05-23 16:21:15');

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
  `imagen_perfil` varchar(255) DEFAULT 'user_default.png',
  `password_user` text NOT NULL,
  `token` varchar(100) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `fyh_creacion` datetime NOT NULL,
  `fyh_actualizacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tb_usuarios`
--

INSERT INTO `tb_usuarios` (`id_usuario`, `nombres`, `email`, `imagen_perfil`, `password_user`, `token`, `id_rol`, `fyh_creacion`, `fyh_actualizacion`) VALUES
(1, 'marcelo mamani', 'marcelo@gmail.com', '2025-05-11-14-59-30_682101a228f7c.PNG', '$2y$10$75JF2CgxfIl0D2FvA2n7Ce0lJogqlHHmV9I38Z2SHytN7iElNtSxm', '', 1, '2025-04-14 21:07:42', '2025-05-24 08:12:56'),
(10, 'administrador', 'admin@gmail.com', 'user_default.png', '$2y$10$LOP8dOv1tmWBnuZOrxmnw.TK6358ZDbFSgo6FwjuOtm.JVYxd8YGG', '', 1, '2025-05-03 06:55:54', '0000-00-00 00:00:00'),
(12, 'venderw', 'vender@gmail.com', 'user_default.png', '$2y$10$EBfd4aY2yFbbWpkPSAC1XO4PKFeWTK9r9WKa/WB5iRTD5fRGlRNJi', '', 7, '2025-05-04 06:19:20', '2025-05-11 14:41:07'),
(13, 'xd 22', '1@gmail.com', 'user_default.png', '$2y$10$uzWGvuEd0xTk3.jnYza22.FMIzgIxgsPYuGzl7oHngZDxtE6mrr4S', '', 1, '2025-05-08 21:57:46', '2025-05-11 15:04:05'),
(14, '2', '2@gmail.com', 'user_default.png', '$2y$10$8NahENpQCkCI565YSxAffOcfuyB5gfzrtt5UkHyhvx0YfBLrWkXt6', '', 7, '2025-05-10 17:32:16', '2025-05-10 17:32:22'),
(15, 'xxx', 'xxx@gmail.com', 'user_default.png', '$2y$10$cDaz6wsPAuXoLIENVFEWmOZYMUSeb9zNdR8ZGRryS/YVmGg9dnRdC', '', 7, '2025-05-24 17:54:55', '2025-05-24 17:55:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_ventas`
--

CREATE TABLE `tb_ventas` (
  `id_venta` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL COMMENT 'Usuario que registra la venta (vendedor)',
  `id_cliente` int(11) NOT NULL COMMENT 'Cliente al que se realiza la venta',
  `nro_venta_secuencial` int(11) NOT NULL COMMENT 'Número secuencial único por usuario/sistema',
  `codigo_venta_referencia` varchar(50) NOT NULL COMMENT 'Código visible para el usuario (ej. V-00001)',
  `fecha_venta` date NOT NULL,
  `tipo_comprobante` varchar(100) DEFAULT NULL COMMENT 'Ej: Factura, Nota de Venta, Ticket',
  `nro_comprobante_fisico` varchar(100) DEFAULT NULL COMMENT 'Número del documento físico si aplica',
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
  ADD KEY `id_rol` (`id_rol`);

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
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `tb_carrito`
--
ALTER TABLE `tb_carrito`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT de la tabla `tb_categorias`
--
ALTER TABLE `tb_categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `tb_clientes`
--
ALTER TABLE `tb_clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `tb_compras`
--
ALTER TABLE `tb_compras`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `tb_detalle_compras`
--
ALTER TABLE `tb_detalle_compras`
  MODIFY `id_detalle_compra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `tb_detalle_ventas`
--
ALTER TABLE `tb_detalle_ventas`
  MODIFY `id_detalle_venta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tb_proveedores`
--
ALTER TABLE `tb_proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

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
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `tb_ventas`
--
ALTER TABLE `tb_ventas`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT;

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
-- Filtros para la tabla `tb_usuarios`
--
ALTER TABLE `tb_usuarios`
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
