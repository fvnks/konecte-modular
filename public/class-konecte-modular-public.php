<?php
/**
 * La funcionalidad específica de la parte pública del plugin.
 *
 * @package    Konecte_Modular
 */
class Konecte_Modular_Public {

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
     * Registra los estilos para la parte pública del sitio.
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, KONECTE_MODULAR_PLUGIN_URL . 'public/css/konecte-modular-public.css', array(), $this->version, 'all');
    }

    /**
     * Registra los scripts para la parte pública del sitio.
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, KONECTE_MODULAR_PLUGIN_URL . 'public/js/konecte-modular-public.js', array('jquery'), $this->version, false);
    }
} 