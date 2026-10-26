<?php 
require_once __DIR__ . '/../../controllers/IncidenciaController.php';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Solicitar <span>Nueva Incidencia</span></h1>
            <p class="hero-subtitle">Rellena los datos de la avería. Recuerda que las solicitudes estándar requieren 48h de antelación.</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <p class="alert-error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3>Datos de la Cita</h3>
            <form action="" method="POST">
                <input type="hidden" name="accion" value="crear_incidencia">

                <div class="form-group">
                    <label for="especialidad_id" class="form-label">Tipo de Servicio *</label>
                    <!-- CAMBIO CRÍTICO 1: Ahora es un select con name="especialidad_id" -->
                    <select name="especialidad_id" id="especialidad_id" class="form-control" required>
                        <!-- Los values deben coincidir con los ID de tu tabla especialidades en la BD -->
                        <option value="1">Fontanería</option>
                        <option value="2">Electricidad</option>
                        <option value="3">Climatización</option>
                        <option value="4">Cerrajería</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tipo_urgencia" class="form-label">Carácter del Servicio *</label>
                    <select name="tipo_urgencia" id="tipo_urgencia" class="form-control" required>
                        <!-- CAMBIO CRÍTICO 2: Los values ahora son idénticos al ENUM de la base de datos -->
                        <option value="Estándar">Estándar (Mínimo 48h antelación)</option>
                        <option value="Urgente">Urgente</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha_solicitud" class="form-label">Fecha Solicitada *</label>
                    <input type="date" name="fecha_solicitud" id="fecha_solicitud" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="franja_horaria" class="form-label">Franja Horaria *</label>
                    <input type="text" name="franja_horaria" id="franja_horaria" class="form-control" placeholder="Ej: Mañana (09:00 - 14:00)" required>
                </div>
        </div>

        <div class="dashboard-card">
            <h3>Ubicación y Detalle</h3>
                <div class="form-group">
                    <label for="direccion" class="form-label">Dirección de la Intervención *</label>
                    <input type="text" name="direccion" id="direccion" class="form-control" placeholder="Calle, número, piso..." required>
                </div>

                <div class="form-group">
                    <label for="contacto" class="form-label">Teléfono de Contacto *</label>
                    <input type="text" name="contacto" id="contacto" class="form-control" placeholder="Número de teléfono" required>
                </div>

                <div class="form-group">
                    <label for="descripcion" class="form-label">Descripción Detallada *</label>
                    <textarea name="descripcion" id="descripcion" rows="4" class="form-control" placeholder="Explica brevemente el problema..." required></textarea>
                </div>

                <button type="submit" class="btn-submit">Enviar Solicitud de Aviso</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>