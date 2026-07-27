-- Schema for portalcliente
-- Based on Backend/BD/portalcliente.sql

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `cat_roles`
--
CREATE TABLE `cat_roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(50) NOT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre_rol` (`nombre_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `cat_servicios`
--
CREATE TABLE `cat_servicios` (
  `id_tipo_servicio` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_servicio` varchar(100) NOT NULL,
  PRIMARY KEY (`id_tipo_servicio`),
  UNIQUE KEY `nombre_servicio` (`nombre_servicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `cat_tipos_documentos`
--
CREATE TABLE `cat_tipos_documentos` (
  `id_tipo_doc` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_tipo` varchar(50) NOT NULL,
  PRIMARY KEY (`id_tipo_doc`),
  UNIQUE KEY `nombre_tipo` (`nombre_tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `usuarios`
--
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `id_rol` int(11) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `numero_cliente` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `require_password_change` tinyint(1) DEFAULT 0,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `correo` (`correo`),
  UNIQUE KEY `numero_cliente` (`numero_cliente`),
  KEY `id_rol` (`id_rol`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `cat_roles` (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `notificaciones`
--
CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario_destino` int(11) NOT NULL,
  `tipo_evento` varchar(50) NOT NULL,
  `mensaje_texto` text NOT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_notificacion`),
  KEY `id_usuario_destino` (`id_usuario_destino`),
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_usuario_destino`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `mensajes`
--
CREATE TABLE `mensajes` (
  `id_mensaje` int(11) NOT NULL AUTO_INCREMENT,
  `id_remitente` int(11) NOT NULL,
  `id_destinatario` int(11) NOT NULL,
  `tipo` enum('Chat interno','Ticket') DEFAULT 'Chat interno',
  `contenido_texto` text NOT NULL,
  `ruta_archivo_adjunto` varchar(255) DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_mensaje`),
  KEY `id_remitente` (`id_remitente`),
  KEY `id_destinatario` (`id_destinatario`),
  CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`id_remitente`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `mensajes_ibfk_2` FOREIGN KEY (`id_destinatario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `servicios_contables`
--
CREATE TABLE `servicios_contables` (
  `id_servicio` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `id_tipo_servicio` int(11) NOT NULL,
  `estado` enum('Pendiente','En proceso','Completado') DEFAULT 'Pendiente',
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_servicio`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_tipo_servicio` (`id_tipo_servicio`),
  CONSTRAINT `servicios_contables_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `servicios_contables_ibfk_2` FOREIGN KEY (`id_tipo_servicio`) REFERENCES `cat_servicios` (`id_tipo_servicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `citas`
--
CREATE TABLE `citas` (
  `id_cita` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `id_asesor` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `estado` enum('Programada','Reprogramada','Cancelada') DEFAULT 'Programada',
  `motivo_cancelacion` text DEFAULT NULL,
  PRIMARY KEY (`id_cita`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_asesor` (`id_asesor`),
  CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`id_asesor`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `documentos`
--
CREATE TABLE `documentos` (
  `id_documento` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario_propietario` int(11) NOT NULL,
  `id_tipo_doc` int(11) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `version` int(11) DEFAULT 1,
  `validacion_cfdi` tinyint(1) DEFAULT 0,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_documento`),
  KEY `id_usuario_propietario` (`id_usuario_propietario`),
  KEY `id_tipo_doc` (`id_tipo_doc`),
  CONSTRAINT `documentos_ibfk_1` FOREIGN KEY (`id_usuario_propietario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `documentos_ibfk_2` FOREIGN KEY (`id_tipo_doc`) REFERENCES `cat_tipos_documentos` (`id_tipo_doc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `pagos_facturacion`
--
CREATE TABLE `pagos_facturacion` (
  `id_pago` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `id_servicio` int(11) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `estado_pago` enum('Pendiente','Registrado','Aprobado') DEFAULT 'Pendiente',
  `estado_factura` enum('Pendiente','Emitida') DEFAULT 'Pendiente',
  `fecha_pago` datetime DEFAULT NULL,
  PRIMARY KEY (`id_pago`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_servicio` (`id_servicio`),
  CONSTRAINT `pagos_facturacion_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `pagos_facturacion_ibfk_2` FOREIGN KEY (`id_servicio`) REFERENCES `servicios_contables` (`id_servicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `perfiles`
--
CREATE TABLE `perfiles` (
  `id_perfil` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `nombre_completo` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  -- Campos específicos de Asesor
  `especialidad` varchar(100) DEFAULT NULL COMMENT 'Área de enfoque: fiscal, laboral, auditoría, etc.',
  `biografia` text DEFAULT NULL,
  `cedula_profesional` varchar(50) DEFAULT NULL,
  `correo_corporativo` varchar(150) DEFAULT NULL,
  `extension` varchar(20) DEFAULT NULL COMMENT 'Extensión telefónica',
  `disponibilidad` enum('activo','descanso','vacaciones') DEFAULT 'activo',
  -- Campos específicos de Cliente
  `rfc` varchar(13) DEFAULT NULL,
  `razon_social` varchar(255) DEFAULT NULL,
  `direccion_fiscal` text DEFAULT NULL,
  `regimen_fiscal` varchar(100) DEFAULT NULL,
  `representante_legal` varchar(255) DEFAULT NULL,
  `direccion_comercial` text DEFAULT NULL,
  `paquete_contratado` varchar(100) DEFAULT NULL COMMENT 'Paquete o iguala mensual',
  `estatus_suscripcion` enum('Activa','Suspendida','Cancelada') DEFAULT 'Activa',
  `constancia_fiscal` varchar(255) DEFAULT NULL COMMENT 'Ruta archivo constancia de situación fiscal',
  -- Campos específicos de Administrador (datos de la consultoría)
  `consultoria_nombre` varchar(255) DEFAULT NULL,
  `consultoria_direccion` text DEFAULT NULL,
  `consultoria_telefono` varchar(20) DEFAULT NULL,
  `consultoria_correo` varchar(150) DEFAULT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_perfil`),
  UNIQUE KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `perfiles_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `cliente_asesor`
--
CREATE TABLE `cliente_asesor` (
  `id_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `id_asesor` int(11) NOT NULL,
  `id_asignador` int(11) NOT NULL, -- Admin que realizó la asignación
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_fin` timestamp NULL DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `motivo_cambio` text DEFAULT NULL,
  PRIMARY KEY (`id_asignacion`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_asesor` (`id_asesor`),
  KEY `id_asignador` (`id_asignador`),
  CONSTRAINT `cliente_asesor_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `cliente_asesor_ibfk_2` FOREIGN KEY (`id_asesor`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `cliente_asesor_ibfk_3` FOREIGN KEY (`id_asignador`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `reportes`
--
CREATE TABLE `reportes` (
  `id_reporte` int(11) NOT NULL AUTO_INCREMENT,
  `id_asesor` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `contenido` text DEFAULT NULL,
  `tipo` enum('Mensual','Trimestral','Anual','Especial') DEFAULT 'Mensual',
  `estado` enum('Borrador','Publicado','Archivado') DEFAULT 'Borrador',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_reporte`),
  KEY `id_asesor` (`id_asesor`),
  CONSTRAINT `reportes_ibfk_1` FOREIGN KEY (`id_asesor`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `reset_logs`
-- Historial de reseteos de contraseña por Administradores
--
CREATE TABLE `reset_logs` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) NOT NULL COMMENT 'Admin que realizó el reseteo',
  `id_usuario_afectado` int(11) NOT NULL COMMENT 'Usuario al que se le resetéo la contraseña',
  `fecha_reseteo` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_log`),
  KEY `id_admin` (`id_admin`),
  KEY `id_usuario_afectado` (`id_usuario_afectado`),
  CONSTRAINT `reset_logs_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `reset_logs_ibfk_2` FOREIGN KEY (`id_usuario_afectado`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `anuncios`
--
CREATE TABLE `anuncios` (
  `id_anuncio` int(11) NOT NULL AUTO_INCREMENT,
  `id_autor` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `contenido` text NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_anuncio`),
  KEY `id_autor` (`id_autor`),
  CONSTRAINT `anuncios_ibfk_1` FOREIGN KEY (`id_autor`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `calificaciones`
-- Calificación anónima de clientes a asesores (1-5)
--
CREATE TABLE `calificaciones` (
  `id_calificacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `id_asesor` int(11) NOT NULL,
  `puntuacion` tinyint(1) NOT NULL CHECK (`puntuacion` between 1 and 5),
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_calificacion`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_asesor` (`id_asesor`),
  CONSTRAINT `calificaciones_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `calificaciones_ibfk_2` FOREIGN KEY (`id_asesor`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
