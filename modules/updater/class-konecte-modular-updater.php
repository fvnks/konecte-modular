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
     * Inicializa el módulo de actualizaciones.
     */
    public function init() {
        // Establecer los filtros para la comprobación de actualizaciones
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));
        add_filter('plugins_api', array($this, 'plugin_info'), 10, 3);
        
        // Registrar la página de ajustes
        add_action('admin_init', array($this, 'register_settings'));
        
        // Enlace de ajustes en la página de plugins
        add_filter('plugin_action_links_' . KONECTE_MODULAR_PLUGIN_BASENAME, array($this, 'add_settings_link'));
        
        // Manejador para verificación forzada
        add_action('admin_init', array($this, 'handle_force_check'));

        // Programar la comprobación automática de actualizaciones
        if (!wp_next_scheduled('konecte_check_updates')) {
            wp_schedule_event(time(), 'daily', 'konecte_check_updates');
        }
        add_action('konecte_check_updates', array($this, 'scheduled_update_check'));
        
        // Endpoint AJAX para verificación de actualizaciones
        add_action('wp_ajax_konecte_force_update_check', array($this, 'ajax_force_update_check'));
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
            __('Intervalo de Comprobación (minutos)', 'konecte-modular'),
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
        $check_interval = isset($options['check_interval']) ? $options['check_interval'] : 60;
        echo '<input type="number" id="konecte_modular_updater_check_interval" name="konecte_modular_updater_settings[check_interval]" value="' . esc_attr($check_interval) . '" class="small-text" min="5" max="1440" step="5" />';
        echo '<p class="description">' . __('Cada cuántos minutos se debe comprobar si hay actualizaciones (5-1440). Valores recomendados: 60 (1 hora), 720 (12 horas), 1440 (24 horas).', 'konecte-modular') . '</p>';
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
            if ($new_input['check_interval'] < 5) {
                $new_input['check_interval'] = 5;
            } elseif ($new_input['check_interval'] > 1440) {
                $new_input['check_interval'] = 1440;
            }
        }
        
        return $new_input;
    }

    /**
     * Comprueba si hay actualizaciones disponibles.
     *
     * @param object $transient Transient 'update_plugins'.
     * @return object Transient modificado con la información de actualización.
     */
    public function check_update($transient) {
        if (empty($transient) || !is_object($transient)) {
            error_log('Konecte Modular: Transient vacío o no es un objeto');
            return $transient;
        }
        
        // Verificar si estamos en un proceso de instalación o actualización
        if (isset($_GET['action']) && in_array($_GET['action'], array('do-plugin-upgrade', 'upgrade-plugin', 'update-selected'), true)) {
            error_log('Konecte Modular: Acción de actualización detectada, devolviendo transient sin modificar');
            return $transient;
        }
        
        // Ignorar llamadas cuando transient->checked está vacío (podría ser una solicitud para obtener la lista de plugins)
        if (!isset($transient->checked)) {
            error_log('Konecte Modular: transient->checked no está definido');
            return $transient;
        }
        
        $plugin_path = KONECTE_MODULAR_PLUGIN_BASENAME;
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_path);
        $current_version = $plugin_data['Version'];
        
        error_log('Konecte Modular: Verificando actualizaciones para la versión ' . $current_version);
        
        // Determinar si debemos comprobar actualizaciones
        $last_check = get_option('konecte_modular_last_update_check');
        $check_interval = 12 * HOUR_IN_SECONDS; // 12 horas
        $force_check = isset($_GET['konecte-force-check']) || isset($_GET['force-check']);
        
        if (!$force_check && $last_check && (time() - $last_check < $check_interval)) {
            error_log('Konecte Modular: Omitiendo comprobación, última verificación reciente');
            return $transient;
        }
        
        // Actualizar tiempo de última comprobación
        update_option('konecte_modular_last_update_check', time());
        error_log('Konecte Modular: Actualizando última comprobación a ' . date('Y-m-d H:i:s', time()));
        
        // Obtener la información de la versión más reciente
        $remote_version = $this->get_remote_version();
        
        if (!$remote_version) {
            error_log('Konecte Modular: No se pudo obtener la versión remota');
            return $transient;
        }
        
        error_log('Konecte Modular: Versión remota: ' . $remote_version->tag_name . ', Versión actual: ' . $current_version);
        
        // Comprobar si hay una nueva versión disponible
        if (version_compare($remote_version->tag_name, $current_version, '>')) {
            error_log('Konecte Modular: Nueva versión disponible: ' . $remote_version->tag_name);
            
            $options = get_option('konecte_modular_updater_settings');
            $username = isset($options['username']) ? $options['username'] : 'fvnks';
            $repo = isset($options['repo']) ? $options['repo'] : 'konecte-modular';
            
            // Construir la URL de descarga
            $download_url = isset($remote_version->zipball_url) 
                ? $remote_version->zipball_url 
                : "https://github.com/{$username}/{$repo}/archive/refs/tags/{$remote_version->tag_name}.zip";
            
            // Añadir el token de acceso a la URL si está configurado
            $access_token = isset($options['access_token']) ? $options['access_token'] : '';
            if (!empty($access_token)) {
                $download_url = add_query_arg('access_token', $access_token, $download_url);
            }
            
            // Añadir la información de actualización al transient
            $obj = new stdClass();
            $obj->slug = dirname($plugin_path);
            $obj->plugin = $plugin_path;
            $obj->new_version = $remote_version->tag_name;
            $obj->url = KONECTE_MODULAR_UPDATE_URL;
            $obj->package = $download_url;
            $obj->icons = array(
                '1x' => plugin_dir_url(__FILE__) . '../../admin/img/konecte-modular-128x128.png',
                '2x' => plugin_dir_url(__FILE__) . '../../admin/img/konecte-modular-256x256.png'
            );
            $obj->tested = get_bloginfo('version');
            $obj->requires_php = '7.0';
            
            $transient->response[$plugin_path] = $obj;
            
            error_log('Konecte Modular: Actualización añadida al transient');
        } else {
            error_log('Konecte Modular: No hay nueva versión disponible');
            
            // Asegurarse de que el plugin está listado en no_update para evitar problemas
            if (!isset($transient->no_update)) {
                $transient->no_update = array();
            }
            
            if (!isset($transient->no_update[$plugin_path])) {
                $obj = new stdClass();
                $obj->slug = dirname($plugin_path);
                $obj->plugin = $plugin_path;
                $obj->new_version = $current_version;
                $obj->url = KONECTE_MODULAR_UPDATE_URL;
                $obj->package = '';
                $obj->icons = array(
                    '1x' => plugin_dir_url(__FILE__) . '../../admin/img/konecte-modular-128x128.png',
                    '2x' => plugin_dir_url(__FILE__) . '../../admin/img/konecte-modular-256x256.png'
                );
                $obj->tested = get_bloginfo('version');
                $obj->requires_php = '7.0';
                
                $transient->no_update[$plugin_path] = $obj;
                
                error_log('Konecte Modular: Plugin añadido a no_update');
            }
        }
        
        return $transient;
    }

    /**
     * Proporciona información adicional del plugin para la pantalla de detalles.
     *
     * @param object $result Objeto con detalles del plugin.
     * @param string $action Acción solicitada.
     * @param object $args Argumentos adicionales.
     * @return object Objeto con detalles del plugin.
     */
    public function plugin_info($result, $action, $args) {
        // Solo procesamos peticiones para este plugin
        if ($action !== 'plugin_information' || !isset($args->slug) || $args->slug !== dirname(KONECTE_MODULAR_PLUGIN_BASENAME)) {
            return $result;
        }
        
        error_log('Konecte Modular: Proporcionando información del plugin para ' . $args->slug);
        
        // Obtener información de la versión más reciente desde GitHub
        $remote_version = $this->get_remote_version();
        
        if (!$remote_version) {
            error_log('Konecte Modular: No se pudo obtener información de la versión remota');
            return $result;
        }
        
        $options = get_option('konecte_modular_updater_settings');
        $username = isset($options['username']) ? $options['username'] : 'fvnks';
        $repo = isset($options['repo']) ? $options['repo'] : 'konecte-modular';
        
        // Construir la URL del archivo zip
        $download_url = isset($remote_version->zipball_url) 
            ? $remote_version->zipball_url 
            : "https://github.com/{$username}/{$repo}/archive/refs/tags/{$remote_version->tag_name}.zip";
        
        // Añadir el token de acceso a la URL si está configurado
        $access_token = isset($options['access_token']) ? $options['access_token'] : '';
        if (!empty($access_token)) {
            $download_url = add_query_arg('access_token', $access_token, $download_url);
        }
        
        // Obtener información del plugin actual
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . KONECTE_MODULAR_PLUGIN_BASENAME);
        
        // Crear el objeto de información
        $plugin_info = new stdClass();
        $plugin_info->name = $plugin_data['Name'];
        $plugin_info->slug = dirname(KONECTE_MODULAR_PLUGIN_BASENAME);
        $plugin_info->version = $remote_version->tag_name;
        $plugin_info->author = $plugin_data['Author'];
        $plugin_info->author_profile = $plugin_data['AuthorURI'];
        $plugin_info->requires = isset($remote_version->requires) ? $remote_version->requires : '5.0';
        $plugin_info->tested = isset($remote_version->tested) ? $remote_version->tested : get_bloginfo('version');
        $plugin_info->requires_php = isset($remote_version->requires_php) ? $remote_version->requires_php : '7.0';
        $plugin_info->last_updated = isset($remote_version->published_at) ? date('Y-m-d', strtotime($remote_version->published_at)) : date('Y-m-d');
        $plugin_info->homepage = KONECTE_MODULAR_UPDATE_URL;
        
        // Obtener la descripción del repositorio
        $repo_info_url = "https://api.github.com/repos/{$username}/{$repo}";
        $args = array(
            'timeout' => 15,
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url'),
            ),
        );
        
        if (!empty($access_token)) {
            $args['headers']['Authorization'] = 'token ' . $access_token;
        }
        
        $repo_response = wp_remote_get($repo_info_url, $args);
        
        if (!is_wp_error($repo_response) && wp_remote_retrieve_response_code($repo_response) === 200) {
            $repo_data = json_decode(wp_remote_retrieve_body($repo_response));
            
            if (isset($repo_data->description)) {
                $plugin_info->sections['description'] = wpautop($repo_data->description);
            }
            
            if (isset($repo_data->readme)) {
                // Obtener el contenido del README.md
                $readme_url = "https://api.github.com/repos/{$username}/{$repo}/readme";
                $readme_response = wp_remote_get($readme_url, $args);
                
                if (!is_wp_error($readme_response) && wp_remote_retrieve_response_code($readme_response) === 200) {
                    $readme_data = json_decode(wp_remote_retrieve_body($readme_response));
                    
                    if (isset($readme_data->content)) {
                        $readme_content = base64_decode($readme_data->content);
                        
                        // Convertir Markdown a HTML
                        if (function_exists('Markdown')) {
                            $readme_html = Markdown($readme_content);
                        } else {
                            // Formato básico si no está disponible el parser de Markdown
                            $readme_html = nl2br(esc_html($readme_content));
                        }
                        
                        $plugin_info->sections['description'] = $readme_html;
                    }
                }
            }
        } else {
            // Usar la descripción del plugin si no podemos obtener la información del repositorio
            $plugin_info->sections['description'] = wpautop($plugin_data['Description']);
        }
        
        // Obtener información del changelog
        $plugin_info->sections['changelog'] = '<h2>Historial de Cambios</h2>';
        
        // Intentar obtener releases para el changelog
        $releases_url = "https://api.github.com/repos/{$username}/{$repo}/releases";
        $releases_response = wp_remote_get($releases_url, $args);
        
        if (!is_wp_error($releases_response) && wp_remote_retrieve_response_code($releases_response) === 200) {
            $releases_data = json_decode(wp_remote_retrieve_body($releases_response));
            
            if (!empty($releases_data) && is_array($releases_data)) {
                foreach ($releases_data as $release) {
                    $plugin_info->sections['changelog'] .= '<h3>' . esc_html($release->name) . ' - ' . date('Y-m-d', strtotime($release->published_at)) . '</h3>';
                    
                    if (!empty($release->body)) {
                        // Convertir Markdown a HTML
                        if (function_exists('Markdown')) {
                            $plugin_info->sections['changelog'] .= Markdown($release->body);
                        } else {
                            // Formato básico si no está disponible el parser de Markdown
                            $plugin_info->sections['changelog'] .= nl2br(esc_html($release->body));
                        }
                    } else {
                        $plugin_info->sections['changelog'] .= '<p>No hay notas de lanzamiento disponibles para esta versión.</p>';
                    }
                }
            } else {
                $plugin_info->sections['changelog'] .= '<p>No hay información de releases disponible.</p>';
            }
        } else {
            $plugin_info->sections['changelog'] .= '<p>No se pudo obtener el historial de cambios desde GitHub.</p>';
        }
        
        // Establecer las capturas de pantalla
        $plugin_info->banners = array(
            'low' => plugin_dir_url(__FILE__) . '../../admin/img/konecte-modular-banner-772x250.jpg',
            'high' => plugin_dir_url(__FILE__) . '../../admin/img/konecte-modular-banner-1544x500.jpg'
        );
        
        $plugin_info->icons = array(
            '1x' => plugin_dir_url(__FILE__) . '../../admin/img/konecte-modular-128x128.png',
            '2x' => plugin_dir_url(__FILE__) . '../../admin/img/konecte-modular-256x256.png'
        );
        
        // URL de descarga
        $plugin_info->download_link = $download_url;
        
        error_log('Konecte Modular: Información del plugin proporcionada correctamente');
        
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
            'timeout' => 15,
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
        
        // Si el código es 404, podría ser que no haya releases, intentamos obtener tags
        if ($response_code === 404) {
            error_log('Konecte Modular: No se encontraron releases, intentando obtener tags');
            return $this->get_remote_version_from_tags($username, $repo, $access_token);
        }
        
        if ($response_code !== 200) {
            error_log('Konecte Modular: Error al comprobar actualizaciones - Código HTTP: ' . $response_code);
            
            // Obtener el mensaje de error detallado
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body);
            if (isset($data->message)) {
                error_log('Konecte Modular: Mensaje de error: ' . $data->message);
            }
            
            return false;
        }
        
        // Decodificar la respuesta JSON
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);
        
        if (empty($data) || !isset($data->tag_name)) {
            error_log('Konecte Modular: Error al comprobar actualizaciones - Respuesta inválida');
            return false;
        }
        
        // Eliminar 'v' del inicio de la versión si existe
        if (strpos($data->tag_name, 'v') === 0) {
            $data->tag_name = substr($data->tag_name, 1);
        }
        
        return $data;
    }
    
    /**
     * Obtiene la información de la última versión desde los tags de GitHub.
     *
     * @param string $username Nombre de usuario de GitHub.
     * @param string $repo Nombre del repositorio.
     * @param string $access_token Token de acceso de GitHub (opcional).
     * @return object|false Datos de la última versión o false en caso de error.
     */
    private function get_remote_version_from_tags($username, $repo, $access_token = '') {
        // Construir la URL de la API
        $url = "https://api.github.com/repos/{$username}/{$repo}/tags";
        
        // Configurar los argumentos de la solicitud
        $args = array(
            'timeout' => 15,
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
            error_log('Konecte Modular: Error al comprobar tags - ' . $response->get_error_message());
            return false;
        }
        
        // Obtener el código de respuesta
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            error_log('Konecte Modular: Error al comprobar tags - Código HTTP: ' . $response_code);
            return false;
        }
        
        // Decodificar la respuesta JSON
        $body = wp_remote_retrieve_body($response);
        $tags = json_decode($body);
        
        if (empty($tags) || !is_array($tags)) {
            error_log('Konecte Modular: Error al comprobar tags - Respuesta inválida');
            return false;
        }
        
        // Ordenar tags por creación (más reciente primero)
        usort($tags, function($a, $b) {
            // Eliminar 'v' del inicio de la versión si existe
            $version_a = (strpos($a->name, 'v') === 0) ? substr($a->name, 1) : $a->name;
            $version_b = (strpos($b->name, 'v') === 0) ? substr($b->name, 1) : $b->name;
            
            return version_compare($version_b, $version_a);
        });
        
        if (empty($tags)) {
            error_log('Konecte Modular: No se encontraron tags');
            return false;
        }
        
        // Convertir el primer tag al formato esperado
        $release = new stdClass();
        $release->tag_name = (strpos($tags[0]->name, 'v') === 0) ? substr($tags[0]->name, 1) : $tags[0]->name;
        $release->zipball_url = $tags[0]->zipball_url;
        $release->tarball_url = $tags[0]->tarball_url;
        $release->name = $tags[0]->name;
        $release->published_at = date('Y-m-d H:i:s');
        
        return $release;
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

    /**
     * Maneja la verificación forzada de actualizaciones.
     */
    public function handle_force_check() {
        if (isset($_GET['konecte-force-check']) && current_user_can('manage_options')) {
            // Verificar nonce
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'konecte-force-check')) {
                wp_die(__('Error de seguridad. Por favor, intenta de nuevo.', 'konecte-modular'));
            }
            
            // Eliminar la última comprobación para forzar una nueva
            delete_option('konecte_modular_last_update_check');
            
            // Limpiar transient de actualizaciones
            delete_site_transient('update_plugins');
            
            // Forzar una nueva comprobación
            $current = get_site_transient('update_plugins');
            $current = $this->check_update($current);
            set_site_transient('update_plugins', $current);
            
            // Añadir mensaje de éxito
            add_settings_error(
                'konecte_modular_updater',
                'konecte-force-check',
                __('Comprobación de actualizaciones completada. Si hay actualizaciones disponibles, aparecerán en la página de actualizaciones.', 'konecte-modular'),
                'success'
            );
            
            // Redirigir para evitar reenvío del formulario
            wp_redirect(remove_query_arg(array('konecte-force-check', '_wpnonce')));
            exit;
        }
    }

    /**
     * Realiza una comprobación programada de actualizaciones.
     */
    public function scheduled_update_check() {
        error_log('Konecte Modular: Ejecutando comprobación programada de actualizaciones');
        
        // Borrar la opción de última verificación para forzar la comprobación
        delete_option('konecte_modular_last_update_check');
        
        // Obtener el transient actual
        $current = get_site_transient('update_plugins');
        
        // Forzar una comprobación
        $current = $this->check_update($current);
        
        // Guardar el transient actualizado
        set_site_transient('update_plugins', $current);
        
        error_log('Konecte Modular: Comprobación programada completada');
    }

    /**
     * Maneja la solicitud AJAX para verificar actualizaciones.
     */
    public function ajax_force_update_check() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'konecte_admin_nonce')) {
            wp_send_json_error(array(
                'message' => __('Error de seguridad. Por favor, recarga la página e intenta de nuevo.', 'konecte-modular')
            ));
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('No tienes permisos para realizar esta acción.', 'konecte-modular')
            ));
        }
        
        // Eliminar la última comprobación para forzar una nueva
        delete_option('konecte_modular_last_update_check');
        
        // Limpiar transient de actualizaciones
        delete_site_transient('update_plugins');
        
        // Forzar una nueva comprobación
        $current = get_site_transient('update_plugins');
        $current = $this->check_update($current);
        set_site_transient('update_plugins', $current);
        
        // Obtener información sobre la versión actual y la remota
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . KONECTE_MODULAR_PLUGIN_BASENAME);
        $current_version = $plugin_data['Version'];
        $remote_version = $this->get_remote_version();
        
        if (!$remote_version) {
            wp_send_json_error(array(
                'message' => __('No se pudo obtener información del repositorio de GitHub. Verifica tu configuración.', 'konecte-modular')
            ));
        }
        
        // Comparar versiones
        if (version_compare($remote_version->tag_name, $current_version, '>')) {
            wp_send_json_success(array(
                'message' => sprintf(
                    __('¡Nueva versión disponible! Versión actual: %s, Versión remota: %s. Ve a la página de actualizaciones de WordPress para actualizar.', 'konecte-modular'),
                    $current_version,
                    $remote_version->tag_name
                ),
                'current_version' => $current_version,
                'remote_version' => $remote_version->tag_name,
                'has_update' => true
            ));
        } else {
            wp_send_json_success(array(
                'message' => sprintf(
                    __('Estás utilizando la última versión disponible (%s).', 'konecte-modular'),
                    $current_version
                ),
                'current_version' => $current_version,
                'remote_version' => $remote_version->tag_name,
                'has_update' => false
            ));
        }
    }
} 