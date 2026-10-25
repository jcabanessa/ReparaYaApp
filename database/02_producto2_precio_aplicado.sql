-- PRODUCTO 2: ejecutar UNA SOLA VEZ, después del SQL oficial y de 01_producto2_ampliaciones.sql.
-- Corrección documentada: el ejemplo REP-2026-XXXX tiene 13 caracteres,
-- aunque el SQL inicial define localizador VARCHAR(12). Se amplía sin perder datos.
ALTER TABLE incidencias
    MODIFY COLUMN localizador VARCHAR(13) NOT NULL;

-- Guardar el precio que se aplicó al crear cada incidencia. Se utilizará en Producto 3
-- para conservar el importe histórico cuando cambie el precio base del servicio.
ALTER TABLE incidencias
    ADD COLUMN precio_aplicado DECIMAL(10,2) NOT NULL DEFAULT 0.00;
