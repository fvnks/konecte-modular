<?php
/**
 * Plugin Name: Konecte Modular - Google Sheets Connector
 * Plugin URI: https://github.com/fvnks/konecte-modular
 * Description: Plugin modular para conectar WordPress con Google Sheets y mostrar datos mediante shortcodes.
 * Version: 1.0.1
 * Author: FvNks
 * Author URI: https://github.com/fvnks
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: konecte-modular
 * Domain Path: /languages
 */

// Si este archivo es llamado directamente, abortar.
if (!defined('WPINC')) {
    die;
}

// Definir constantes
define('KONECTE_MODULAR_VERSION', '1.0.1');
define('KONECTE_MODULAR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KONECTE_MODULAR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KONECTE_MODULAR_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('KONECTE_MODULAR_UPDATE_URL', 'https://github.com/fvnks/konecte-modular');

// Cargar el cargador de módulos
require_once KONECTE_MODULAR_PLUGIN_DIR . 'includes/class-konecte-modular-loader.php';

// Activación y desactivación del plugin
register_activation_hook(__FILE__, 'konecte_modular_activate');
register_deactivation_hook(__FILE__, 'konecte_modular_deactivate');

/**
 * Código ejecutado durante la activación del plugin
 */
function konecte_modular_activate() {
    // Código de activación
    flush_rewrite_rules();
}

/**
 * Código ejecutado durante la desactivación del plugin
 */
function konecte_modular_deactivate() {
    // Código de desactivación
    flush_rewrite_rules();
}

/**
 * Inicia el plugin
 */
function konecte_modular_init() {
    // Cargar núcleo del plugin
    require_once KONECTE_MODULAR_PLUGIN_DIR . 'includes/class-konecte-modular.php';
    
    // Iniciar el plugin
    $plugin = new Konecte_Modular();
    $plugin->run();
}
konecte_modular_init(); 