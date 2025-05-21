<?php
/**
 * El cargador de módulos del plugin.
 *
 * Se encarga de registrar todas las acciones y filtros para el plugin.
 *
 * @package    Konecte_Modular
 */

/**
 * El cargador de módulos del plugin.
 *
 * Mantiene una lista de todas las acciones y filtros que están registrados en WordPress.
 * Llama a la API de WordPress para ejecutar los filtros y las acciones.
 */
class Konecte_Modular_Loader {

    /**
     * Array de acciones registradas con WordPress.
     *
     * @var      array    $actions    Las acciones registradas con WordPress para ejecutar cuando se cargue el plugin.
     */
    protected $actions;

    /**
     * Array de filtros registrados con WordPress.
     *
     * @var      array    $filters    Los filtros registrados con WordPress para ejecutar cuando se cargue el plugin.
     */
    protected $filters;

    /**
     * Array de shortcodes registrados con WordPress.
     *
     * @var      array    $shortcodes    Los shortcodes registrados con WordPress.
     */
    protected $shortcodes;

    /**
     * Inicializa las colecciones utilizadas para mantener las acciones y filtros.
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();
        $this->shortcodes = array();
    }

    /**
     * Agrega una acción a la colección para registrar con WordPress.
     *
     * @param    string               $hook             El nombre de la acción de WordPress que está siendo registrada.
     * @param    object               $component        Una referencia a la instancia del objeto en el que existe el método de callback.
     * @param    string               $callback         El nombre del método de callback.
     * @param    int                  $priority         La prioridad en la que debe ejecutarse el callback.
     * @param    int                  $accepted_args    El número de argumentos que deben pasarse al callback.
     */
    public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * Agrega un filtro a la colección para registrar con WordPress.
     *
     * @param    string               $hook             El nombre del filtro de WordPress que está siendo registrado.
     * @param    object               $component        Una referencia a la instancia del objeto en el que existe el método de callback.
     * @param    string               $callback         El nombre del método de callback.
     * @param    int                  $priority         La prioridad en la que debe ejecutarse el callback.
     * @param    int                  $accepted_args    El número de argumentos que deben pasarse al callback.
     */
    public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * Agrega un shortcode a la colección para registrar con WordPress.
     *
     * @param    string               $tag              El nombre del shortcode.
     * @param    object               $component        Una referencia a la instancia del objeto en el que existe el método de callback.
     * @param    string               $callback         El nombre del método de callback.
     */
    public function add_shortcode( $tag, $component, $callback ) {
        $this->shortcodes = $this->add_shortcode_item( $this->shortcodes, $tag, $component, $callback );
    }

    /**
     * Utilidad que se utiliza para registrar las acciones y ganchos en una sola pasada.
     *
     * @param    array                $hooks            La colección de hooks que se está registrando.
     * @param    string               $hook             El nombre del filtro de WordPress que está siendo registrado.
     * @param    object               $component        Una referencia a la instancia del objeto en el que existe el método de callback.
     * @param    string               $callback         El nombre del método de callback.
     * @param    int                  $priority         La prioridad en la que debe ejecutarse el callback.
     * @param    int                  $accepted_args    El número de argumentos que deben pasarse al callback.
     * @return   array                                  La colección de acciones y filtros registrados con WordPress.
     */
    private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;
    }

    /**
     * Utilidad que se utiliza para registrar shortcodes.
     *
     * @param    array                $shortcodes       La colección de shortcodes que se está registrando.
     * @param    string               $tag              El nombre del shortcode.
     * @param    object               $component        Una referencia a la instancia del objeto en el que existe el método de callback.
     * @param    string               $callback         El nombre del método de callback.
     * @return   array                                  La colección de shortcodes registrados con WordPress.
     */
    private function add_shortcode_item( $shortcodes, $tag, $component, $callback ) {
        $shortcodes[] = array(
            'tag'           => $tag,
            'component'     => $component,
            'callback'      => $callback,
        );

        return $shortcodes;
    }

    /**
     * Registra los hooks con WordPress.
     */
    public function run() {
        foreach ( $this->filters as $hook ) {
            add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
        }

        foreach ( $this->actions as $hook ) {
            add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
        }

        foreach ( $this->shortcodes as $shortcode ) {
            add_shortcode( $shortcode['tag'], array( $shortcode['component'], $shortcode['callback'] ) );
        }
    }
} 