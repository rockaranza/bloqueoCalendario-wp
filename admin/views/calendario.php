<div class="wrap">
    <h1><?php _e('Calendario de Reservas', 'reservas'); ?></h1>
    
    <div class="reservas-calendario-container">
        <div class="reservas-calendario-header">
            <h2><?php _e('Agregar Bloqueo', 'reservas'); ?></h2>
            <form id="bloqueo_form" method="post">
                <input type="hidden" name="action" value="reservas_add_block">
                <?php wp_nonce_field('reservas_nonce', 'reservas_nonce'); ?>
                <input type="hidden" name="cabana_id" value="<?php echo esc_attr($cabana_id); ?>">
                
                <div class="form-group">
                    <label for="bloqueo_fecha_inicio"><?php _e('Fecha de Inicio:', 'reservas'); ?></label>
                    <input type="text" id="bloqueo_fecha_inicio" name="fecha_inicio" class="datepicker" required>
                </div>
                
                <div class="form-group">
                    <label for="bloqueo_fecha_fin"><?php _e('Fecha de Fin:', 'reservas'); ?></label>
                    <input type="text" id="bloqueo_fecha_fin" name="fecha_fin" class="datepicker" required>
                </div>
                
                <div class="form-group">
                    <label for="bloqueo_motivo"><?php _e('Motivo:', 'reservas'); ?></label>
                    <input type="text" id="bloqueo_motivo" name="motivo" required>
                </div>
                
                <button type="submit" class="button button-primary"><?php _e('Guardar Bloqueo', 'reservas'); ?></button>
            </form>
        </div>
        
        <div class="reservas-bloqueos-lista">
            <h2><?php _e('Bloqueos Actuales', 'reservas'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Fecha Inicio', 'reservas'); ?></th>
                        <th><?php _e('Fecha Fin', 'reservas'); ?></th>
                        <th><?php _e('Motivo', 'reservas'); ?></th>
                        <th><?php _e('Acciones', 'reservas'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bloqueos as $bloqueo): ?>
                    <tr>
                        <td><?php echo esc_html($bloqueo->fecha_inicio); ?></td>
                        <td><?php echo esc_html($bloqueo->fecha_fin); ?></td>
                        <td><?php echo esc_html($bloqueo->motivo); ?></td>
                        <td>
                            <button class="button delete-bloqueo" data-id="<?php echo esc_attr($bloqueo->id); ?>">
                                <?php _e('Eliminar', 'reservas'); ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.reservas-calendario-container {
    margin-top: 20px;
}

.reservas-calendario-header {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.form-group input {
    width: 100%;
    max-width: 300px;
}

.datepicker {
    width: 150px !important;
}

.reservas-bloqueos-lista {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.wp-list-table {
    margin-top: 10px;
}

.delete-bloqueo {
    color: #dc3545;
}

.delete-bloqueo:hover {
    color: #c82333;
}
</style> 