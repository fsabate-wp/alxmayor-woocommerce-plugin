<?php
/**
 * Plugin Name: AlxMayor
 * Plugin URI: https://fernandosabate.com/alxmayor
 * Description: Complemento para convertir la tienda WooCommerce en una tienda para mayoristas
 * Version: 1.0.5
 * Author: Fer Sabaté
 * Author URI: https://fernandosabate.com
 * Text Domain: alxmayor
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('ALXMAYOR_VERSION', '1.0.0');
define('ALXMAYOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ALXMAYOR_PLUGIN_URL', plugin_dir_url(__FILE__));

// Comprobar si WooCommerce está activo
function alxmayor_check_woocommerce() {
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        add_action('admin_notices', 'alxmayor_woocommerce_missing_notice');
        return false;
    }
    return true;
}

// Mostrar aviso si WooCommerce no está activo
function alxmayor_woocommerce_missing_notice() {
    ?>
    <div class="error">
        <p><?php _e('AlxMayor requiere que WooCommerce esté instalado y activado.', 'alxmayor'); ?></p>
    </div>
    <?php
}

// Función de activación del plugin
function alxmayor_activate() {
    if (!alxmayor_check_woocommerce()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('AlxMayor requiere que WooCommerce esté instalado y activado.', 'alxmayor'));
    }
}
register_activation_hook(__FILE__, 'alxmayor_activate');

// Cargar las clases principales del plugin
require_once ALXMAYOR_PLUGIN_DIR . 'includes/class-alxmayor-admin.php';
require_once ALXMAYOR_PLUGIN_DIR . 'includes/class-alxmayor.php';

// Inicializar el plugin
function alxmayor_init() {
    if (alxmayor_check_woocommerce()) {
        $plugin = new AlxMayor();
        $plugin->run();
    }
}
add_action('plugins_loaded', 'alxmayor_init');
