-- Initial data for portalcliente

-- Roles
INSERT INTO `cat_roles` (`id_rol`, `nombre_rol`) VALUES
(1, 'Administrador'),
(2, 'Asesor Contable'),
(3, 'Cliente');

-- Tipos de Servicios
INSERT INTO `cat_servicios` (`id_tipo_servicio`, `nombre_servicio`) VALUES
(1, 'Contabilidad Mensual'),
(2, 'Declaración Anual'),
(3, 'Nóminas'),
(4, 'Asesoría Fiscal'),
(5, 'Auditoría');

-- Tipos de Documentos
INSERT INTO `cat_tipos_documentos` (`id_tipo_doc`, `nombre_tipo`) VALUES
(1, 'Identificación Oficial'),
(2, 'Constancia de Situación Fiscal'),
(3, 'Comprobante de Domicilio'),
(4, 'Factura (PDF/XML)'),
(5, 'Declaración Presentada');

-- Usuarios Iniciales
-- password123 -> $2y$10$C7Y2fN1pxZg6n.1iNybJUOi7hQq.99vEdBvZTxRz4uKhBsPxe6vcK
INSERT INTO `usuarios` (`id_rol`, `correo`, `password_hash`, `estado`) VALUES
(1, 'admin@consultoria.com', '$2y$10$C7Y2fN1pxZg6n.1iNybJUOi7hQq.99vEdBvZTxRz4uKhBsPxe6vcK', 'activo');

INSERT INTO `usuarios` (`id_rol`, `correo`, `password_hash`, `estado`) VALUES
(2, 'asesor@consultoria.com', '$2y$10$C7Y2fN1pxZg6n.1iNybJUOi7hQq.99vEdBvZTxRz4uKhBsPxe6vcK', 'activo');

INSERT INTO `usuarios` (`id_rol`, `correo`, `password_hash`, `numero_cliente`, `estado`) VALUES
(3, 'cliente@ejemplo.com', '$2y$10$C7Y2fN1pxZg6n.1iNybJUOi7hQq.99vEdBvZTxRz4uKhBsPxe6vcK', 'CLI-001', 'activo');
