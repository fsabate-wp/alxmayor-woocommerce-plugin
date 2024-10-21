<?php

class AlxMayor {

    protected $plugin_name;
    protected $version;
    protected $admin;

    public function __construct() {
        $this->plugin_name = 'alxmayor';
        $this->version = ALXMAYOR_VERSION;
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        $this->admin = new AlxMayor_Admin($this->plugin_name, $this->version);
    }

    private function set_locale() {
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
    }

    private function define_admin_hooks() {
        add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_scripts'));
        add_action('admin_menu', array($this->admin, 'add_plugin_admin_menu'));
        add_action('admin_init', array($this->admin, 'options_update'));
    }

    private function define_public_hooks() {
        add_filter('woocommerce_get_price_html', array($this, 'ocultar_precio_para_no_registrados'));
        add_filter('woocommerce_is_purchasable', array($this, 'ocultar_carrito_para_no_registrados'));
        add_action('woocommerce_check_cart_items', array($this, 'verificar_monto_minimo'));
    }

    public function ocultar_precio_para_no_registrados($price) {
        $options = get_option($this->plugin_name);
        if (isset($options['ocultar_precio']) && $options['ocultar_precio'] && !is_user_logged_in()) {
            $style = '
                <style>
                    .notice-woocommerce {
                        background-color: #f2f2f2;
                        border: 1px solid #ddd;
                        margin: 20px 0;
                        padding: 15px;
                    }
                    .notice-woocommerce a {
                        color: #0073aa;
                        text-decoration: underline;
                    }
                </style>
            ';
            $message = isset($options['mensaje_precio_oculto']) ? $options['mensaje_precio_oculto'] : 'Precio disponible solo para usuarios registrados.';
            //$message = '<div class="notice-woocommerce"><p>' . esc_html($message) . ' <a href="' . esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))) . '">' . __('Inicia sesión o Regístrate', 'alxmayor') . '</a></p></div>';
            return $style . $message;
        }
        return $price;
    }

    public function ocultar_carrito_para_no_registrados($purchasable) {
        $options = get_option($this->plugin_name);
        if (isset($options['ocultar_precio']) && $options['ocultar_precio'] && !is_user_logged_in()) {
            return false;
        }
        return $purchasable;
    }

    public function verificar_monto_minimo() {
        $options = get_option($this->plugin_name);
        if (isset($options['habilitar_monto_minimo']) && $options['habilitar_monto_minimo'] && isset($options['monto_minimo'])) {
            $monto_minimo = intval($options['monto_minimo']);
            $total_carrito = WC()->cart->total;
            
            if ($total_carrito < $monto_minimo) {
                wc_add_notice(
                    sprintf(__('El monto mínimo de compra es de %s. Tu carrito actual es de %s.', 'alxmayor'), 
                        wc_price($monto_minimo), 
                        wc_price($total_carrito)
                    ), 
                    'error'
                );
            }
        }
    }

    public function run() {
        // Los hooks ya están definidos en el constructor, así que no necesitamos hacer nada más aquí
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }

    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'alxmayor',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
