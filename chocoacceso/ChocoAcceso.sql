-- 1. Creación de la base de datos
CREATE DATABASE IF NOT EXISTS CHOCOACCESO;
USE CHOCOACCESO;

-- 2. Tabla de Usuarios (Actualizada con soporte para contraseñas hasheadas)
CREATE TABLE `usuarios` (
  `id_usuario` INT NOT NULL AUTO_INCREMENT,
  `cedula` VARCHAR(20) NOT NULL,
  `nombre_completo` VARCHAR(100) NOT NULL,
  `rol` ENUM('Personal', 'Visitante', 'Operador_Seguridad', 'Administrador', 'Gerencia') NOT NULL,
  `empresa_externa` VARCHAR(100) NULL,
  `password` VARCHAR(255) NULL, -- Nueva columna para el hash de seguridad
  `activo` TINYINT(1) DEFAULT 1,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE INDEX `cedula_UNIQUE` (`cedula` ASC)
) ENGINE=InnoDB;

-- 3. Tabla de Estado en Planta (Nueva: Jerarquía de Ubicación)
-- Esta tabla permite saber exactamente dónde está cada usuario sin duplicar registros.
CREATE TABLE `estado_planta` (
  `id_usuario` INT NOT NULL,
  `ubicacion_actual` VARCHAR(100) DEFAULT 'FUERA',
  `ultima_actualizacion` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `fk_estado_usuario` 
    FOREIGN KEY (`id_usuario`) 
    REFERENCES `usuarios` (`id_usuario`) 
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Tabla de Movimientos (Historial para Auditoría)
CREATE TABLE `movimientos` (
  `id_movimiento` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `id_operador` INT NOT NULL,
  `tipo_movimiento` ENUM('ENTRADA', 'SALIDA') NOT NULL,
  `timestamp_registro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sincronizado_jpy` TINYINT(1) DEFAULT 0,
  `observaciones` TEXT NULL, -- Aquí guardamos el departamento/área
  PRIMARY KEY (`id_movimiento`),
  INDEX `fk_mov_usuario_idx` (`id_usuario` ASC),
  INDEX `fk_mov_operador_idx` (`id_operador` ASC),
  CONSTRAINT `fk_mov_usuario`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `fk_mov_operador`
    FOREIGN KEY (`id_operador`)
    REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB;

-- 5. Tabla de Agenda (Citas para visitantes)
CREATE TABLE `agenda` (
  `id_cita` INT NOT NULL AUTO_INCREMENT,
  `id_visitante` INT NOT NULL,
  `fecha_cita` DATE NOT NULL,
  `hora_cita` TIME NOT NULL,
  `motivo` TEXT NULL,
  `estado_confirmacion` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id_cita`),
  INDEX `fk_agenda_usuarios_idx` (`id_visitante` ASC),
  CONSTRAINT `fk_agenda_usuarios`
    FOREIGN KEY (`id_visitante`)
    REFERENCES `usuarios` (`id_usuario`)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Tabla de Auditoría del Sistema (Logs de errores y acciones)
CREATE TABLE `auditoria_sistema` (
  `id_log` INT NOT NULL AUTO_INCREMENT,
  `accion_realizada` VARCHAR(255) NOT NULL,
  `detalle_error` TEXT NULL,
  `fecha_suceso` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`)
) ENGINE=InnoDB;

ALTER TABLE usuarios ADD COLUMN creado_por INT NULL;
-- Y al crear el usuario en el controlador:
-- $usuario->creado_por = $_SESSION['id_usuario'];

-- Vistas de verificación rápida
SELECT * FROM usuarios;
SELECT * FROM estado_planta;
SELECT * FROM movimientos;