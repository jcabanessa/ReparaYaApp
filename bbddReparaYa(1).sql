-- 1. TABLA DE USUARIOS (Para el Alumno de Gestión de Usuarios)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Se usará password_hash() de PHP
    rol ENUM('admin', 'tecnico', 'particular') NOT NULL DEFAULT 'particular',
    telefono VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. TABLA DE CATEGORÍAS/ESPECIALIDADES (Para el Alumno de Maestros)
CREATE TABLE especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_especialidad VARCHAR(50) NOT NULL, -- Fontanería, Electricidad, etc.
    precio_base DECIMAL(10, 2) NOT NULL DEFAULT 0.00 -- Precio base por hora o por servicio, según se defina
);

-- 3. TABLA DE TÉCNICOS (Para el Alumno de Gestión de Técnicos)
CREATE TABLE tecnicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNIQUE, -- Relación 1:1 con la tabla usuarios (si el técnico tiene login)
    nombre_completo VARCHAR(100) NOT NULL,
    especialidad_id INT,
    disponible BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id)
);

-- 4. TABLA DE INCIDENCIAS/RESERVAS (Para el Alumno de Gestión de Incidencias)
CREATE TABLE incidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    localizador VARCHAR(12) NOT NULL UNIQUE, -- Código tipo REP-2026-XXXX
    cliente_id INT NOT NULL,
    tecnico_id INT DEFAULT NULL, -- El admin lo asignará después
    especialidad_id INT NOT NULL, -- Tipo de servicio solicitado
    descripcion TEXT NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    fecha_servicio DATETIME NOT NULL, -- Campo clave para la regla de las 48h
    tipo_urgencia ENUM('Estándar', 'Urgente') DEFAULT 'Estándar',
    estado ENUM('Pendiente', 'Asignada', 'En curso', 'Finalizada', 'Cancelada') DEFAULT 'Pendiente', -- En curso, añadido
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id),
    FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id),
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id)
);

-- 5. TABLA DE HISTORIAL DE ESTADOS (Requerida para el seguimiento de cambios)
CREATE TABLE IF NOT EXISTS historial_estados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incidencia_id INT NOT NULL,
    estado_anterior VARCHAR(50) DEFAULT NULL,
    nuevo_estado VARCHAR(50) NOT NULL,
    usuario_id INT NOT NULL, -- Quién realizó el cambio
    observaciones TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (incidencia_id) REFERENCES incidencias(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);


