<?php
/**
 * La clase principal del plugin.
 *
 * Define la funcionalidad principal del plugin.
 *
 * @package    Konecte_Modular
 */
class Konecte_Modular {

    /**
     * El cargador que es responsable de mantener y registrar todos los hooks del plugin.
     *
     * @var      Konecte_Modular_Loader    $loader    Mantiene y registra todos los hooks para el plugin.
     */
    protected $loader;

    /**
     * El único identificador de este plugin.
     *
     * @var      string    $plugin_name    El nombre o identificador único de este plugin.
     */
    protected $plugin_name;

    /**
     * La versión actual del plugin.
     *
     * @var      string    $version    La versión actual del plugin.
     */
    protected $version;

    /**
     * Define la funcionalidad principal del plugin.
     *
     * Establece el nombre y la versión del plugin que puede ser utilizado en todo el plugin.
     * Carga las dependencias, define la configuración regional, y establece los hooks para
     * el área de administración y para la parte pública del sitio.
     */
    public function __construct() {
        $this->plugin_name = 'konecte-modular';
        $this->version = KONECTE_MODULAR_VERSION;

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->load_modules();
    }

    /**
     * Carga las dependencias necesarias para este plugin.
     *
     * Incluye los siguientes archivos que componen el plugin:
     *
     * - Konecte_Modular_Loader. Gestiona los hooks del plugin.
     * - Konecte_Modular_i18n. Define la funcionalidad de internacionalización.
     * - Konecte_Modular_Admin. Define todos los hooks del área de administración.
     * - Konecte_Modular_Public. Define todos los hooks de la parte pública del sitio.
     */
    private function load_dependencies() {
        // El archivo que gestiona la internacionalización del plugin.
        require_once KONECTE_MODULAR_PLUGIN_DIR . 'includes/class-konecte-modular-i18n.php';

        // El archivo que define la funcionalidad del área de administración.
        require_once KONECTE_MODULAR_PLUGIN_DIR . 'admin/class-konecte-modular-admin.php';

        // El archivo que define la funcionalidad de la parte pública del sitio.
        require_once KONECTE_MODULAR_PLUGIN_DIR . 'public/class-konecte-modular-public.php';

        $this->loader = new Konecte_Modular_Loader();
    }

    /**
     * Define la configuración regional del plugin para la internacionalización.
     */
    private function set_locale() {
        $plugin_i18n = new Konecte_Modular_i18n();
        $plugin_i18n->set_domain($this->plugin_name);

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Registra todos los hooks relacionados con la funcionalidad del área de administración.
     */
    private function define_admin_hooks() {
        $plugin_admin = new Konecte_Modular_Admin($this->get_plugin_name(), $this->get_version());

        // Agregar menú en el panel de administración
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        
        // Registrar estilos y scripts
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
    }

    /**
     * Registra todos los hooks relacionados con la funcionalidad de la parte pública del sitio.
     */
    private function define_public_hooks() {
        $plugin_public = new Konecte_Modular_Public($this->get_plugin_name(), $this->get_version());

        // Registrar estilos y scripts
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    /**
     * Carga los módulos del plugin.
     */
    private function load_modules() {
        // Cargar módulo de Google Sheets
        require_once KONECTE_MODULAR_PLUGIN_DIR . 'modules/google-sheets/class-konecte-modular-google-sheets.php';
        $google_sheets = new Konecte_Modular_Google_Sheets($this->get_plugin_name(), $this->get_version(), $this->loader);
        $google_sheets->init();

        // Cargar módulo de actualizaciones
        require_once KONECTE_MODULAR_PLUGIN_DIR . 'modules/updater/class-konecte-modular-updater.php';
        $updater = new Konecte_Modular_Updater($this->get_plugin_name(), $this->get_version(), $this->loader);
        $updater->init();
    }

    /**
     * Ejecuta el cargador para ejecutar todos los hooks con WordPress.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * El nombre del plugin utilizado para identificarlo dentro de WordPress.
     *
     * @return    string    El nombre del plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * El cargador que registra todas los hooks del plugin con WordPress.
     *
     * @return    Konecte_Modular_Loader    Mantiene y registra todos los hooks para el plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Devuelve el número de versión del plugin.
     *
     * @return    string    El número de versión del plugin.
     */
    public function get_version() {
        return $this->version;
    }
} 