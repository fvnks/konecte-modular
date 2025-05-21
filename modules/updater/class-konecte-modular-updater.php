<?php
/**
 * La funcionalidad del módulo actualizador desde GitHub.
 *
 * @package    Konecte_Modular
 * @subpackage Konecte_Modular/modules/updater
 */
class Konecte_Modular_Updater {

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
     * El cargador que es responsable de mantener y registrar todos los hooks del plugin.
     *
     * @var      Konecte_Modular_Loader    $loader    Mantiene y registra todos los hooks para el plugin.
     */
    private $loader;

    /**
     * Inicializa la clase y establece sus propiedades.
     *
     * @param      string                  $plugin_name    El nombre del plugin.
     * @param      string                  $version        La versión del plugin.
     * @param      Konecte_Modular_Loader  $loader         El cargador del plugin.
     */
    public function __construct($plugin_name, $version, $loader) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->loader = $loader;
    }

    /**
     * Inicializa el módulo y registra los hooks.
     */
    public function init() {
        // Registrar filtros para el actualizador
        $this->loader->add_filter('pre_set_site_transient_update_plugins', $this, 'check_update');
        $this->loader->add_filter('plugins_api', $this, 'plugin_info', 10, 3);
        $this->loader->add_filter('upgrader_post_install', $this, 'after_install', 10, 3);
        
        // Registrar acciones de administración
        $this->loader->add_action('admin_init', $this, 'register_settings');
    }

    /**
     * Registra las opciones de configuración para el actualizador.
     */
    public function register_settings() {
        register_setting(
            'konecte_modular_updater_options',
            'konecte_modular_updater_settings',
            array($this, 'validate_settings')
        );

        add_settings_section(
            'konecte_modular_updater_section',
            __('Configuración del Actualizador', 'konecte-modular'),
            array($this, 'updater_section_callback'),
            'konecte-modular-settings'
        );

        add_settings_field(
            'konecte_modular_updater_username',
            __('Usuario de GitHub', 'konecte-modular'),
            array($this, 'updater_username_callback'),
            'konecte-modular-settings',
            'konecte_modular_updater_section'
        );

        add_settings_field(
            'konecte_modular_updater_repo',
            __('Repositorio', 'konecte-modular'),
            array($this, 'updater_repo_callback'),
            'konecte-modular-settings',
            'konecte_modular_updater_section'
        );

        add_settings_field(
            'konecte_modular_updater_access_token',
            __('Token de Acceso de GitHub (opcional)', 'konecte-modular'),
            array($this, 'updater_access_token_callback'),
            'konecte-modular-settings',
            'konecte_modular_updater_section'
        );

        add_settings_field(
            'konecte_modular_updater_check_interval',
            __('Intervalo de Comprobación (horas)', 'konecte-modular'),
            array($this, 'updater_check_interval_callback'),
            'konecte-modular-settings',
            'konecte_modular_updater_section'
        );
    }

    /**
     * Callback para la sección del actualizador.
     */
    public function updater_section_callback() {
        echo '<p>' . __('Configure el actualizador automático desde GitHub.', 'konecte-modular') . '</p>';
    }

    /**
     * Callback para el campo nombre de usuario de GitHub.
     */
    public function updater_username_callback() {
        $options = get_option('konecte_modular_updater_settings');
        $username = isset($options['username']) ? $options['username'] : 'fvnks';
        echo '<input type="text" id="konecte_modular_updater_username" name="konecte_modular_updater_settings[username]" value="' . esc_attr($username) . '" class="regular-text" />';
        echo '<p class="description">' . __('El nombre de usuario o organización de GitHub donde se encuentra el repositorio.', 'konecte-modular') . '</p>';
    }

    /**
     * Callback para el campo repositorio.
     */
    public function updater_repo_callback() {
        $options = get_option('konecte_modular_updater_settings');
        $repo = isset($options['repo']) ? $options['repo'] : 'konecte-modular';
        echo '<input type="text" id="konecte_modular_updater_repo" name="konecte_modular_updater_settings[repo]" value="' . esc_attr($repo) . '" class="regular-text" />';
        echo '<p class="description">' . __('El nombre del repositorio en GitHub.', 'konecte-modular') . '</p>';
    }

    /**
     * Callback para el campo token de acceso.
     */
    public function updater_access_token_callback() {
        $options = get_option('konecte_modular_updater_settings');
        $access_token = isset($options['access_token']) ? $options['access_token'] : '';
        echo '<input type="text" id="konecte_modular_updater_access_token" name="konecte_modular_updater_settings[access_token]" value="' . esc_attr($access_token) . '" class="regular-text" />';
        echo '<p class="description">' . __('Token de acceso personal de GitHub para repositorios privados (opcional).', 'konecte-modular') . '</p>';
    }

    /**
     * Callback para el campo intervalo de comprobación.
     */
    public function updater_check_interval_callback() {
        $options = get_option('konecte_modular_updater_settings');
        $check_interval = isset($options['check_interval']) ? $options['check_interval'] : 24;
        echo '<input type="number" id="konecte_modular_updater_check_interval" name="konecte_modular_updater_settings[check_interval]" value="' . esc_attr($check_interval) . '" class="small-text" min="1" max="168" />';
        echo '<p class="description">' . __('Cada cuántas horas se debe comprobar si hay actualizaciones (1-168).', 'konecte-modular') . '</p>';
    }

    /**
     * Valida las opciones enviadas.
     *
     * @param array $input Las opciones introducidas por el usuario.
     * @return array Las opciones validadas y sanitizadas.
     */
    public function validate_settings($input) {
        $new_input = array();
        
        if (isset($input['username'])) {
            $new_input['username'] = sanitize_text_field($input['username']);
        }
        
        if (isset($input['repo'])) {
            $new_input['repo'] = sanitize_text_field($input['repo']);
        }
        
        if (isset($input['access_token'])) {
            $new_input['access_token'] = sanitize_text_field($input['access_token']);
        }
        
        if (isset($input['check_interval'])) {
            $new_input['check_interval'] = absint($input['check_interval']);
            if ($new_input['check_interval'] < 1) {
                $new_input['check_interval'] = 1;
            } elseif ($new_input['check_interval'] > 168) {
                $new_input['check_interval'] = 168;
            }
        }
        
        return $new_input;
    }

    /**
     * Comprueba si hay actualizaciones disponibles.
     *
     * @param object $transient Objeto transient de actualizaciones.
     * @return object Objeto transient modificado.
     */
    public function check_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        // Comprobar si ya se ha verificado recientemente
        $last_check = get_option('konecte_modular_last_update_check');
        $options = get_option('konecte_modular_updater_settings');
        $check_interval = isset($options['check_interval']) ? (int) $options['check_interval'] : 24;
        $check_interval_seconds = $check_interval * HOUR_IN_SECONDS;
        
        if ($last_check && (time() - $last_check) < $check_interval_seconds) {
            return $transient;
        }
        
        // Actualizar la última comprobación
        update_option('konecte_modular_last_update_check', time());

        // Obtener información de la última versión desde GitHub
        $remote_version = $this->get_remote_version();
        if (!$remote_version) {
            return $transient;
        }

        // Verificar si la versión remota es más reciente
        if (version_compare($remote_version->tag_name, $this->version, '>')) {
            $plugin_file = KONECTE_MODULAR_PLUGIN_BASENAME;
            
            $item = new stdClass();
            $item->id = KONECTE_MODULAR_PLUGIN_BASENAME;
            $item->slug = $this->plugin_name;
            $item->new_version = $remote_version->tag_name;
            $item->url = KONECTE_MODULAR_UPDATE_URL;
            $item->package = $remote_version->zipball_url;
            $item->tested = isset($remote_version->tested) ? $remote_version->tested : '6.0';
            $item->requires_php = isset($remote_version->requires_php) ? $remote_version->requires_php : '7.0';
            
            $transient->response[$plugin_file] = $item;
        }

        return $transient;
    }

    /**
     * Proporciona información sobre el plugin para la pantalla de actualizaciones.
     *
     * @param false|object|array $result Resultado del hook plugins_api.
     * @param string $action La acción realizada.
     * @param object $args Argumentos proporcionados a plugins_api.
     * @return false|object
     */
    public function plugin_info($result, $action, $args) {
        // Verificar que se está solicitando información sobre este plugin
        if ($action !== 'plugin_information' || !isset($args->slug) || $args->slug !== $this->plugin_name) {
            return $result;
        }

        $remote_version = $this->get_remote_version();
        if (!$remote_version) {
            return $result;
        }

        $options = get_option('konecte_modular_updater_settings');
        $username = isset($options['username']) ? $options['username'] : 'fvnks';
        $repo = isset($options['repo']) ? $options['repo'] : 'konecte-modular';

        $plugin_info = new stdClass();
        $plugin_info->name = 'Konecte Modular - Google Sheets Connector';
        $plugin_info->slug = $this->plugin_name;
        $plugin_info->version = $remote_version->tag_name;
        $plugin_info->author = '<a href="https://github.com/' . esc_attr($username) . '">' . esc_html($username) . '</a>';
        $plugin_info->homepage = 'https://github.com/' . esc_attr($username) . '/' . esc_attr($repo);
        $plugin_info->requires = '5.0';
        $plugin_info->tested = isset($remote_version->tested) ? $remote_version->tested : '6.0';
        $plugin_info->requires_php = isset($remote_version->requires_php) ? $remote_version->requires_php : '7.0';
        $plugin_info->downloaded = 0;
        $plugin_info->last_updated = $remote_version->published_at;
        
        if (isset($remote_version->body)) {
            $plugin_info->sections = array(
                'description' => 'Plugin modular para conectar WordPress con Google Sheets y mostrar datos mediante shortcodes.',
                'changelog' => $this->format_markdown($remote_version->body),
            );
        } else {
            $plugin_info->sections = array(
                'description' => 'Plugin modular para conectar WordPress con Google Sheets y mostrar datos mediante shortcodes.',
            );
        }
        
        $plugin_info->download_link = $remote_version->zipball_url;

        return $plugin_info;
    }

    /**
     * Acciones a realizar después de instalar/actualizar el plugin.
     *
     * @param bool $response Respuesta del instalador.
     * @param array $hook_extra Información adicional sobre el contexto.
     * @param array $result Resultado de la instalación.
     * @return array Resultado modificado.
     */
    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;

        // Verificar que se está actualizando este plugin
        if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] != KONECTE_MODULAR_PLUGIN_BASENAME) {
            return $result;
        }

        // Asegurar que $wp_filesystem está disponible
        if (!$wp_filesystem) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }

        // Obtener la ruta al directorio del plugin
        $plugin_folder = WP_PLUGIN_DIR . '/' . dirname(KONECTE_MODULAR_PLUGIN_BASENAME);
        $wp_filesystem->move($result['destination'], $plugin_folder);

        // Actualizar la ubicación de destino para el upgrader
        $result['destination'] = $plugin_folder;

        return $result;
    }

    /**
     * Obtiene la información de la última versión desde GitHub.
     *
     * @return object|false Datos de la última versión o false en caso de error.
     */
    private function get_remote_version() {
        $options = get_option('konecte_modular_updater_settings');
        $username = isset($options['username']) ? $options['username'] : 'fvnks';
        $repo = isset($options['repo']) ? $options['repo'] : 'konecte-modular';
        $access_token = isset($options['access_token']) ? $options['access_token'] : '';

        // Construir la URL de la API
        $url = "https://api.github.com/repos/{$username}/{$repo}/releases/latest";
        
        // Configurar los argumentos de la solicitud
        $args = array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url'),
            ),
        );
        
        // Añadir token de acceso si está disponible
        if (!empty($access_token)) {
            $args['headers']['Authorization'] = 'token ' . $access_token;
        }
        
        // Realizar la solicitud a la API
        $response = wp_remote_get($url, $args);
        
        // Verificar si hay errores en la respuesta
        if (is_wp_error($response)) {
            error_log('Konecte Modular: Error al comprobar actualizaciones - ' . $response->get_error_message());
            return false;
        }
        
        // Obtener el código de respuesta
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            error_log('Konecte Modular: Error al comprobar actualizaciones - Código HTTP: ' . $response_code);
            return false;
        }
        
        // Decodificar la respuesta JSON
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);
        
        if (empty($data) || !isset($data->tag_name)) {
            error_log('Konecte Modular: Error al comprobar actualizaciones - Respuesta inválida');
            return false;
        }
        
        return $data;
    }

    /**
     * Formatea el markdown a HTML para mostrar en la información del plugin.
     *
     * @param string $markdown Texto en formato markdown.
     * @return string HTML formateado.
     */
    private function format_markdown($markdown) {
        // Convertir encabezados
        $markdown = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $markdown);
        $markdown = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $markdown);
        $markdown = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $markdown);
        
        // Convertir listas
        $markdown = preg_replace('/^\* (.*?)$/m', '<li>$1</li>', $markdown);
        $markdown = preg_replace('/^- (.*?)$/m', '<li>$1</li>', $markdown);
        $markdown = str_replace("<li>", "<ul><li>", $markdown);
        $markdown = str_replace("</li>\n", "</li></ul>\n", $markdown);
        
        // Convertir enlaces
        $markdown = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $markdown);
        
        // Convertir párrafos
        $markdown = '<p>' . str_replace("\n\n", '</p><p>', $markdown) . '</p>';
        
        return $markdown;
    }
} 