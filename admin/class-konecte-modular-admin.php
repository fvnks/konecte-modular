<?php
/**
 * La funcionalidad específica del área de administración del plugin.
 *
 * @package    Konecte_Modular
 */
class Konecte_Modular_Admin {

    /**
     * El ID de este plugin.
     *
     * @var      string    $plugin_name    El ID de este plugin.
     */
    private $plugin_name;

    /**
     * La versión del plugin.
     *
     * @var      string    $version    La versión actual del plugin.
     */
    private $version;

    /**
     * Inicializa la clase y establece sus propiedades.
     *
     * @param      string    $plugin_name       El nombre del plugin.
     * @param      string    $version           La versión del plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Registra los estilos para el área de administración.
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, KONECTE_MODULAR_PLUGIN_URL . 'admin/css/konecte-modular-admin.css', array(), $this->version, 'all');
    }

    /**
     * Registra los scripts para el área de administración.
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, KONECTE_MODULAR_PLUGIN_URL . 'admin/js/konecte-modular-admin.js', array('jquery'), $this->version, false);
        
        // Pasar variables a JavaScript
        wp_localize_script($this->plugin_name, 'konecte_modular_admin', array(
            'nonce' => wp_create_nonce('konecte_admin_nonce'),
            'sheets_nonce' => wp_create_nonce('konecte_sheets_connection_nonce'),
            'preview_nonce' => wp_create_nonce('konecte_sheets_preview_nonce'),
            'checking_connection' => __('Verificando conexión...', 'konecte-modular'),
            'connection_success' => __('Conexión exitosa', 'konecte-modular'),
            'connection_error' => __('Error de conexión', 'konecte-modular'),
            'generating_preview' => __('Generando previsualización...', 'konecte-modular'),
            'checking_update_message' => __('Verificando actualizaciones...', 'konecte-modular'),
            'update_success_message' => __('¡Verificación completada!', 'konecte-modular'),
            'update_error_message' => __('Error al verificar actualizaciones. Inténtalo de nuevo.', 'konecte-modular')
        ));
    }

    /**
     * Agrega un elemento de menú al panel de administración de WordPress.
     */
    public function add_plugin_admin_menu() {
        // Agregar menú principal
        add_menu_page(
            __('Konecte Modular', 'konecte-modular'),
            __('Konecte Modular', 'konecte-modular'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_admin_page'),
            'dashicons-grid-view',
            30
        );

        // Submenu para Google Sheets
        add_submenu_page(
            $this->plugin_name,
            __('Google Sheets', 'konecte-modular'),
            __('Google Sheets', 'konecte-modular'),
            'manage_options',
            $this->plugin_name . '-google-sheets',
            array($this, 'display_plugin_google_sheets_page')
        );

        // Submenu para Configuración
        add_submenu_page(
            $this->plugin_name,
            __('Configuración', 'konecte-modular'),
            __('Configuración', 'konecte-modular'),
            'manage_options',
            $this->plugin_name . '-settings',
            array($this, 'display_plugin_settings_page')
        );
    }

    /**
     * Renderiza la página principal del plugin en el área de administración.
     */
    public function display_plugin_admin_page() {
        include_once KONECTE_MODULAR_PLUGIN_DIR . 'admin/partials/konecte-modular-admin-display.php';
    }

    /**
     * Renderiza la página de Google Sheets del plugin en el área de administración.
     */
    public function display_plugin_google_sheets_page() {
        include_once KONECTE_MODULAR_PLUGIN_DIR . 'admin/partials/konecte-modular-admin-google-sheets.php';
    }

    /**
     * Renderiza la página de configuración del plugin en el área de administración.
     */
    public function display_plugin_settings_page() {
        include_once KONECTE_MODULAR_PLUGIN_DIR . 'admin/partials/konecte-modular-admin-settings.php';
    }
} 