<?php
/**
 * Plugin Name: Ficha Cliente Woo
 * Plugin URI: https://tusitio.com/
 * Description: Plugin personalizado para WooCommerce que gestiona fichas clínicas de clientes y turnos.
 * Version: 1.0.0
 * Author: Tu Nombre
 * Author URI: https://tusitio.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ficha-cliente-woo
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

// Comentado temporalmente hasta que se instale Google API con Composer
// if (file_exists(__DIR__ . '/vendor/autoload.php')) {
//     require_once __DIR__ . '/vendor/autoload.php';
// }

// Activador / Desactivador
require_once plugin_dir_path(__FILE__) . 'includes/class-fcw-activator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-fcw-deactivator.php';

register_activation_hook(__FILE__, ['FCW_Activator', 'activate']);
register_deactivation_hook(__FILE__, ['FCW_Deactivator', 'deactivate']);

// Cargar componentes del plugin
require_once plugin_dir_path(__FILE__) . 'includes/class-fcw-loader.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-fcw-admin-ui.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-fcw-ficha.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-fcw-turnos.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-fcw-google-calendar.php';

add_action('plugins_loaded', function () {
    FCW_Loader::init();
    FCW_Admin_UI::init();
    FCW_Turnos::init();
    FCW_Google_Calendar::init();
});
