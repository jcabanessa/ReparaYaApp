-- PRODUCTO 2 - ReparaYaApp
-- Ampliaciones de la base de datos original

-- 1. Precio base de cada especialidad
ALTER TABLE especialidades
ADD COLUMN precio_base DECIMAL(10,2)
NOT NULL DEFAULT 0.00;

-- 2. Nuevos campos y estados de incidencias
ALTER TABLE incidencias
MODIFY COLUMN estado ENUM(
    'Pendiente',
    'Asignada',
    'En curso',
    'Finalizada',
    'Cancelada'
) DEFAULT 'Pendiente',
ADD COLUMN franja_horaria VARCHAR(50) NULL,
ADD COLUMN telefono_contacto VARCHAR(20) NULL;

-- 3. Historial de cambios de estado
CREATE TABLE historial_estados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incidencia_id INT NOT NULL,
    estado_anterior VARCHAR(50) DEFAULT NULL,
    nuevo_estado VARCHAR(50) NOT NULL,
    usuario_id INT NOT NULL,
    observaciones TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (incidencia_id)
        REFERENCES incidencias(id)
        ON DELETE CASCADE,

    FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
);