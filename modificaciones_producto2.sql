-- 1. Ampliación de la tabla especialidades para el precio base (útil para el Producto 3)
ALTER TABLE especialidades 
ADD COLUMN precio_base DECIMAL(10, 2) NOT NULL DEFAULT 0.00;

-- 2. Actualización de la columna estado en incidencias para incluir 'En curso'
-- (Usamos MODIFY si la columna ya existe, o cambiaríamos a ADD si no existiera)
ALTER TABLE incidencias 
MODIFY COLUMN estado ENUM('Pendiente', 'Asignada', 'En curso', 'Finalizada', 'Cancelada') NOT NULL DEFAULT 'Pendiente';

-- 3. Creación de la tabla de historial_estados respetando la compatibilidad
CREATE TABLE IF NOT EXISTS historial_estados (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    incidencia_id INT NOT NULL,
    estado_anterior VARCHAR(50) DEFAULT NULL,
    nuevo_estado VARCHAR(50) NOT NULL,
    usuario_id INT NOT NULL, 
    observaciones TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (incidencia_id) REFERENCES incidencias(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- 1. Eliminamos la restricción antigua que apuntaba a la tabla 'tecnicos'
ALTER TABLE incidencias DROP FOREIGN KEY incidencias_ibfk_2;

-- 2. Creamos la restricción correcta apuntando a la tabla 'usuarios'
ALTER TABLE incidencias 
ADD CONSTRAINT incidencias_ibfk_2 
FOREIGN KEY (tecnico_id) REFERENCES usuarios(id) 
ON DELETE SET NULL 
ON UPDATE CASCADE;