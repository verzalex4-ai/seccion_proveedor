-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-10-2025 a las 01:34:22
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_compras`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_orden`
--

CREATE TABLE `detalle_orden` (
  `id` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `producto` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `unidad_medida` varchar(50) DEFAULT 'Unidad',
  `precio_unitario` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) GENERATED ALWAYS AS (`cantidad` * `precio_unitario`) STORED,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `detalle_orden`
--

INSERT INTO `detalle_orden` (`id`, `id_orden`, `producto`, `descripcion`, `cantidad`, `unidad_medida`, `precio_unitario`, `fecha_registro`) VALUES
(1, 1, 'Producto A', 'Descripción del producto A', 10, 'Unidad', 500.00, '2025-10-16 20:49:06'),
(2, 1, 'Producto B', 'Descripción del producto B', 20, 'Unidad', 250.00, '2025-10-16 20:49:06'),
(3, 2, 'Galletas', NULL, 12, 'Unidad', 5000.00, '2025-10-17 01:55:51'),
(4, 3, '1212', NULL, 121, 'Unidad', 1212.00, '2025-10-21 20:44:19'),
(5, 4, 'Galletas', NULL, 1, 'Unidad', 12.00, '2025-10-21 22:23:14'),
(6, 5, 'café', NULL, 1, 'Unidad', 12.00, '2025-10-22 03:11:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes_compra`
--

CREATE TABLE `ordenes_compra` (
  `id` int(11) NOT NULL,
  `numero_orden` varchar(50) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_entrega_estimada` date DEFAULT NULL,
  `estado` enum('Pendiente','Enviada','Recibida','Cancelada') DEFAULT 'Pendiente',
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `impuestos` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00,
  `observaciones` text DEFAULT NULL,
  `fecha_recepcion` date DEFAULT NULL,
  `usuario_registro` varchar(100) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `ordenes_compra`
--

INSERT INTO `ordenes_compra` (`id`, `numero_orden`, `id_proveedor`, `fecha_emision`, `fecha_entrega_estimada`, `estado`, `subtotal`, `impuestos`, `total`, `observaciones`, `fecha_recepcion`, `usuario_registro`, `fecha_registro`, `fecha_modificacion`) VALUES
(1, 'OC-2025-001', 1, '2025-10-01', '2025-10-15', 'Pendiente', 10000.00, 2100.00, 12100.00, NULL, NULL, NULL, '2025-10-16 20:49:06', '2025-10-16 20:49:06'),
(2, 'OC-2025-002', 4, '2025-10-16', '2025-10-31', 'Recibida', 60000.00, 12600.00, 72600.00, 'Vender todo', '2025-10-22', NULL, '2025-10-17 01:55:51', '2025-10-22 03:10:18'),
(3, 'OC-2025-003', 4, '2025-10-21', '0001-02-21', 'Recibida', 146652.00, 30796.92, 177448.92, '2121', '2025-10-21', NULL, '2025-10-21 20:44:19', '2025-10-21 22:23:43'),
(4, 'OC-2025-004', 4, '2025-10-21', '2222-12-12', 'Recibida', 12.00, 2.52, 14.52, 'galletas', '2025-10-22', NULL, '2025-10-21 22:23:14', '2025-10-22 03:05:38'),
(5, 'OC-2025-005', 4, '2025-10-22', '2025-10-30', 'Recibida', 12.00, 2.52, 14.52, 'pago café', '2025-10-22', NULL, '2025-10-22 03:11:29', '2025-10-22 03:12:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `fecha_pago` date NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `metodo_pago` enum('Efectivo','Transferencia','Cheque','Tarjeta','Otro') DEFAULT 'Transferencia',
  `numero_comprobante` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `usuario_registro` varchar(100) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `id_orden`, `fecha_pago`, `monto`, `metodo_pago`, `numero_comprobante`, `observaciones`, `usuario_registro`, `fecha_registro`) VALUES
(2, 3, '2025-10-21', 123113.00, 'Efectivo', '12421412', 'pagado', NULL, '2025-10-22 02:58:09'),
(3, 3, '2025-10-22', 12412512.00, 'Cheque', '12312321', 'pagado', NULL, '2025-10-22 03:16:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `razon_social` varchar(200) DEFAULT NULL,
  `cuit` varchar(13) DEFAULT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `condiciones_pago` varchar(100) DEFAULT 'Contado',
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre`, `razon_social`, `cuit`, `contacto`, `email`, `telefono`, `direccion`, `condiciones_pago`, `estado`, `fecha_registro`, `fecha_modificacion`) VALUES
(1, 'Proveedor ABC S.A.', 'ABC Sociedad Anónima', '20-12345678-9', 'Juan Pérez', 'contacto@abc.com', '0387-4123456', NULL, '30 días', 'Activo', '2025-10-16 20:49:04', '2025-10-16 20:49:04'),
(4, 'Mayorista 42323', 'Mayorista 2131', '21-2345235-1', 'Jose saravia', 'info@jose12.com', '0387-4345678', 'Calle 12', '7 días', 'Activo', '2025-10-17 01:54:51', '2025-10-17 01:54:51');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_orden` (`id_orden`);

--
-- Indices de la tabla `ordenes_compra`
--
ALTER TABLE `ordenes_compra`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_orden` (`numero_orden`),
  ADD KEY `idx_orden_fecha` (`fecha_emision`),
  ADD KEY `idx_orden_estado` (`estado`),
  ADD KEY `idx_orden_proveedor` (`id_proveedor`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_orden` (`id_orden`),
  ADD KEY `idx_pago_fecha` (`fecha_pago`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cuit` (`cuit`),
  ADD KEY `idx_proveedor_nombre` (`nombre`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `ordenes_compra`
--
ALTER TABLE `ordenes_compra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD CONSTRAINT `detalle_orden_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `ordenes_compra` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ordenes_compra`
--
ALTER TABLE `ordenes_compra`
  ADD CONSTRAINT `ordenes_compra_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `ordenes_compra` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
