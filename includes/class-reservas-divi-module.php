<?php

class Reservas_Divi_Module extends ET_Builder_Module {
    public $slug = 'reservas_module';
    public $vb_support = 'on';

    protected $module_credits = array(
        'module_uri' => 'https://tusitio.com/reservas',
        'author'     => 'Tu Nombre',
        'author_uri' => 'https://tusitio.com',
    );

    public function init() {
        $this->name = esc_html__('Formulario de Reservas', 'reservas');
        $this->icon_path = RESERVAS_PLUGIN_DIR . 'assets/images/icon.svg';
    }

    public function get_fields() {
        return array(
            'title' => array(
                'label'           => esc_html__('Título', 'reservas'),
                'type'            => 'text',
                'option_category' => 'basic_option',
                'description'     => esc_html__('Ingrese el título del formulario', 'reservas'),
                'toggle_slug'     => 'main_content',
            ),
            'description' => array(
                'label'           => esc_html__('Descripción', 'reservas'),
                'type'            => 'textarea',
                'option_category' => 'basic_option',
                'description'     => esc_html__('Ingrese la descripción del formulario', 'reservas'),
                'toggle_slug'     => 'main_content',
            ),
        );
    }

    public function render($attrs, $content = null, $render_slug) {
        $title = $this->props['title'];
        $description = $this->props['description'];

        $output = sprintf(
            '<div class="reservas-divi-module">
                <h2 class="reservas-title">%1$s</h2>
                <div class="reservas-description">%2$s</div>
                %3$s
            </div>',
            esc_html($title),
            wpautop(esc_html($description)),
            $this->render_form()
        );

        return $output;
    }

    private function render_form() {
        ob_start();
        ?>
        <form class="reservas-form" method="post">
            <div class="reservas-form-group">
                <label for="nombre"><?php esc_html_e('Nombre', 'reservas'); ?></label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="reservas-form-group">
                <label for="email"><?php esc_html_e('Email', 'reservas'); ?></label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="reservas-form-group">
                <label for="fecha"><?php esc_html_e('Fecha', 'reservas'); ?></label>
                <input type="date" id="fecha" name="fecha" required>
            </div>
            <div class="reservas-form-group">
                <label for="hora"><?php esc_html_e('Hora', 'reservas'); ?></label>
                <input type="time" id="hora" name="hora" required>
            </div>
            <div class="reservas-form-group">
                <button type="submit" class="reservas-submit"><?php esc_html_e('Reservar', 'reservas'); ?></button>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }
}

new Reservas_Divi_Module(); 