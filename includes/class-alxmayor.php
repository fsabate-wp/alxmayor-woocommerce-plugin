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
        add_action('woocommerce_product_options_pricing', array($this, 'add_unidades_por_bulto_field'));
        add_action('woocommerce_process_product_meta', array($this, 'save_unidades_por_bulto_field'));
    }

    private function define_public_hooks() {
        add_filter('woocommerce_get_price_html', array($this, 'manejar_visibilidad_precio'), 10, 2);
        add_filter('woocommerce_get_price_html', array($this, 'mostrar_precio_por_unidad'), 20, 2);
        add_filter('woocommerce_product_get_permalink', array($this, 'remover_enlaces_producto'), 10, 2);
        add_filter('woocommerce_is_purchasable', array($this, 'ocultar_carrito_para_no_registrados'), 10, 2);
        add_action('woocommerce_single_product_summary', array($this, 'mostrar_unidades_por_bulto'), 11);
        add_action('woocommerce_after_shop_loop_item_title', array($this, 'mostrar_unidades_por_bulto'), 11);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_styles'));
        add_action('woocommerce_check_cart_items', array($this, 'verificar_monto_minimo'));
        add_action('woocommerce_before_cart', array($this, 'verificar_monto_minimo'));
    }

    public function ocultar_precio_para_no_registrados($price) {
        $options = get_option($this->plugin_name);
        if (isset($options['ocultar_precio']) && $options['ocultar_precio'] && !is_user_logged_in()) {
            // Retornamos una cadena vacía en lugar del mensaje
            return '';
        }
        return $price;
    }

    public function ocultar_carrito_para_no_registrados($purchasable, $product) {
        $options = get_option($this->plugin_name);
        if (isset($options['ocultar_precio']) && $options['ocultar_precio'] && !is_user_logged_in()) {
            return false;
        }
        return $purchasable;
    }

    public function verificar_monto_minimo() {
        $options = get_option($this->plugin_name);
        if (isset($options['habilitar_monto_minimo']) && $options['habilitar_monto_minimo'] && isset($options['monto_minimo'])) {
            $monto_minimo = floatval($options['monto_minimo']);
            $total_carrito = 0;
            
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $product_id = $cart_item['product_id'];
                $precio_original = get_post_meta($product_id, '_precio_original', true);
                if ($precio_original) {
                    $total_carrito += floatval($precio_original) * $cart_item['quantity'];
                } else {
                    $total_carrito += $cart_item['line_total'];
                }
            }
            
            if ($total_carrito < $monto_minimo) {
                wc_add_notice(
                    sprintf(__('El monto mínimo de compra es de %s. Tu carrito actual es de %s.', 'alxmayor'), 
                        wc_price($monto_minimo), 
                        wc_price($total_carrito)
                    ), 
                    'error'
                );
                remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
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

    public function add_unidades_por_bulto_field() {
        woocommerce_wp_text_input(
            array(
                'id' => '_unidades_por_bulto',
                'label' => __('Cantidad de Unidades por Bulto', 'alxmayor'),
                'desc_tip' => true,
                'description' => __('Ingrese la cantidad de unidades que contiene cada bulto.', 'alxmayor'),
                'type' => 'number',
                'custom_attributes' => array(
                    'step' => '1',
                    'min' => '1'
                )
            )
        );
    }

    public function save_unidades_por_bulto_field($post_id) {
        $unidades_por_bulto = isset($_POST['_unidades_por_bulto']) ? absint($_POST['_unidades_por_bulto']) : '';
        update_post_meta($post_id, '_unidades_por_bulto', $unidades_por_bulto);
    }

    public function manejar_visibilidad_precio($price, $product) {
        $options = get_option($this->plugin_name);
        if (isset($options['ocultar_precio']) && $options['ocultar_precio'] && !is_user_logged_in()) {
            $message = isset($options['mensaje_precio_oculto']) ? $options['mensaje_precio_oculto'] : 'Precio disponible solo para usuarios registrados.';
            $login_url = esc_url(get_permalink(get_option('woocommerce_myaccount_page_id')));
            return '<div class="alxmayor-hidden-price">' . ' <a href="' . $login_url . '">' . esc_html($message). '</a></div>';
        }
        return $price;
    }

    public function mostrar_precio_por_unidad($price_html, $product) {
        if (!is_user_logged_in()) {
            return $price_html; // No modificar el precio si el usuario no está registrado
        }

        $unidades_por_bulto = get_post_meta($product->get_id(), '_unidades_por_bulto', true);
        if (!empty($unidades_por_bulto) && $unidades_por_bulto > 0) {
            $price = $product->get_price();
            $price_per_unit = $price / $unidades_por_bulto;
            
            // Almacenar el precio original del producto
            update_post_meta($product->get_id(), '_precio_original', $price);
            
            $price_html = '<span class="precio-por-bulto">' . sprintf(__('Precio por bulto: %s', 'alxmayor'), wc_price($price)) . '</span>';
            $price_html .= '<br><small>' . sprintf(__('Precio por unidad: %s', 'alxmayor'), wc_price($price_per_unit)) . '</small>';
        }
        return $price_html;
    }

    public function mostrar_unidades_por_bulto() {
        $options = get_option($this->plugin_name);
        
        if (isset($options['ocultar_precio']) && $options['ocultar_precio'] && !is_user_logged_in()) {
            return; // No mostrar nada si el precio está oculto para usuarios no registrados
        }

        global $product;
        $unidades_por_bulto = get_post_meta($product->get_id(), '_unidades_por_bulto', true);
        if (!empty($unidades_por_bulto)) {
            echo '<div class="unidades-por-bulto">' . sprintf(__('Unidades por bulto: %s', 'alxmayor'), $unidades_por_bulto) . '</div>';
        }
    }

    public function enqueue_public_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(dirname(__FILE__)) . 'public/css/alxmayor-public.css', array(), $this->version, 'all');
    }

    public function remover_enlaces_producto($link, $product) {
        $options = get_option($this->plugin_name);
        if (isset($options['ocultar_precio']) && $options['ocultar_precio'] && !is_user_logged_in()) {
            return '';
        }
        return $link;
    }
}
