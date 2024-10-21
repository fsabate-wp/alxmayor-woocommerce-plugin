<?php

class AlxMayor_Admin {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . '../admin/css/alxmayor-admin.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . '../admin/js/alxmayor-admin.js', array('jquery'), $this->version, false);
    }

    public function add_plugin_admin_menu() {
        add_menu_page(
            'AlxMayor Opciones', 
            'AlxMayor', 
            'manage_options', 
            $this->plugin_name, 
            array($this, 'display_plugin_setup_page'),
            'dashicons-cart',
            56
        );
    }

    public function display_plugin_setup_page() {
        include_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/alxmayor-admin-display.php';
    }

    public function options_update() {
        register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
    }

    public function validate($input) {
        $valid = array();
        $valid['ocultar_precio'] = (isset($input['ocultar_precio']) && !empty($input['ocultar_precio'])) ? 1 : 0;
        $valid['mensaje_precio_oculto'] = (isset($input['mensaje_precio_oculto']) && !empty($input['mensaje_precio_oculto'])) ? sanitize_text_field($input['mensaje_precio_oculto']) : 'Precio disponible solo para usuarios registrados.';
        $valid['habilitar_monto_minimo'] = (isset($input['habilitar_monto_minimo']) && !empty($input['habilitar_monto_minimo'])) ? 1 : 0;
        $valid['monto_minimo'] = (isset($input['monto_minimo']) && !empty($input['monto_minimo'])) ? intval($input['monto_minimo']) : 0;
        return $valid;
    }
}
