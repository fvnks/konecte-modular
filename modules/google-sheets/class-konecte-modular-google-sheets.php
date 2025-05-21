<?php
/**
 * La funcionalidad del módulo de Google Sheets.
 *
 * @package    Konecte_Modular
 * @subpackage Konecte_Modular/modules/google-sheets
 */
class Konecte_Modular_Google_Sheets {

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
        // Registrar shortcodes
        $this->loader->add_shortcode('google_sheets', $this, 'google_sheets_shortcode');
        $this->loader->add_shortcode('google_sheets_column', $this, 'google_sheets_column_shortcode');
        
        // Registrar acciones de administración
        $this->loader->add_action('admin_init', $this, 'register_settings');
        
        // Registrar acción AJAX para verificar la conexión
        $this->loader->add_action('wp_ajax_konecte_check_sheets_connection', $this, 'ajax_check_connection');
        
        // Registrar acción AJAX para la previsualización de shortcodes
        $this->loader->add_action('wp_ajax_konecte_preview_shortcode', $this, 'ajax_preview_shortcode');
    }

    /**
     * Registra las opciones de configuración para Google Sheets.
     */
    public function register_settings() {
        register_setting(
            'konecte_modular_google_sheets_options',
            'konecte_modular_google_sheets_settings',
            array($this, 'validate_settings')
        );

        add_settings_section(
            'konecte_modular_google_sheets_section',
            __('Configuración de Google Sheets', 'konecte-modular'),
            array($this, 'google_sheets_section_callback'),
            'konecte-modular-google-sheets'
        );

        add_settings_field(
            'konecte_modular_google_sheets_id',
            __('ID de la hoja de Google', 'konecte-modular'),
            array($this, 'google_sheets_id_callback'),
            'konecte-modular-google-sheets',
            'konecte_modular_google_sheets_section'
        );

        add_settings_field(
            'konecte_modular_google_sheets_api_key',
            __('API Key de Google', 'konecte-modular'),
            array($this, 'google_sheets_api_key_callback'),
            'konecte-modular-google-sheets',
            'konecte_modular_google_sheets_section'
        );
        
        add_settings_field(
            'konecte_modular_google_sheets_service_account_email',
            __('Email de la cuenta de servicio', 'konecte-modular'),
            array($this, 'google_sheets_service_account_email_callback'),
            'konecte-modular-google-sheets',
            'konecte_modular_google_sheets_section'
        );
        
        add_settings_field(
            'konecte_modular_google_sheets_private_key',
            __('Clave privada de la cuenta de servicio', 'konecte-modular'),
            array($this, 'google_sheets_private_key_callback'),
            'konecte-modular-google-sheets',
            'konecte_modular_google_sheets_section'
        );
    }

    /**
     * Callback para la sección de Google Sheets.
     */
    public function google_sheets_section_callback() {
        echo '<p>' . __('Ingrese la información de su hoja de Google y las credenciales para conectarse.', 'konecte-modular') . '</p>';
        echo '<p>' . __('Puedes usar API Key para acceso básico o una cuenta de servicio para acceso más seguro y mayores privilegios.', 'konecte-modular') . '</p>';
    }

    /**
     * Callback para el campo ID de la hoja de Google.
     */
    public function google_sheets_id_callback() {
        $options = get_option('konecte_modular_google_sheets_settings');
        $sheet_id = isset($options['sheet_id']) ? $options['sheet_id'] : '';
        echo '<input type="text" id="konecte_modular_google_sheets_id" name="konecte_modular_google_sheets_settings[sheet_id]" value="' . esc_attr($sheet_id) . '" class="regular-text" />';
        echo '<p class="description">' . __('Introduzca el ID de su hoja de Google. Puede encontrarlo en la URL de su hoja: https://docs.google.com/spreadsheets/d/[ID_DE_LA_HOJA]/edit', 'konecte-modular') . '</p>';
    }

    /**
     * Callback para el campo API Key de Google.
     */
    public function google_sheets_api_key_callback() {
        $options = get_option('konecte_modular_google_sheets_settings');
        $api_key = isset($options['api_key']) ? $options['api_key'] : '';
        echo '<input type="text" id="konecte_modular_google_sheets_api_key" name="konecte_modular_google_sheets_settings[api_key]" value="' . esc_attr($api_key) . '" class="regular-text" />';
        echo '<p class="description">' . __('Introduzca su API Key de Google para acceder a la API de Google Sheets.', 'konecte-modular') . '</p>';
    }

    /**
     * Callback para el campo email de la cuenta de servicio.
     */
    public function google_sheets_service_account_email_callback() {
        $options = get_option('konecte_modular_google_sheets_settings');
        $service_account_email = isset($options['service_account_email']) ? $options['service_account_email'] : '';
        echo '<input type="text" id="konecte_modular_google_sheets_service_account_email" name="konecte_modular_google_sheets_settings[service_account_email]" value="' . esc_attr($service_account_email) . '" class="regular-text" />';
        echo '<p class="description">' . __('Introduzca el email de la cuenta de servicio de Google (ejemplo: nombre@proyecto.iam.gserviceaccount.com).', 'konecte-modular') . '</p>';
    }

    /**
     * Callback para el campo clave privada de la cuenta de servicio.
     */
    public function google_sheets_private_key_callback() {
        $options = get_option('konecte_modular_google_sheets_settings');
        $private_key = isset($options['private_key']) ? $options['private_key'] : '';
        echo '<textarea id="konecte_modular_google_sheets_private_key" name="konecte_modular_google_sheets_settings[private_key]" rows="5" class="large-text code">' . esc_textarea($private_key) . '</textarea>';
        echo '<p class="description">' . __('Pegue la clave privada completa de la cuenta de servicio, incluyendo las líneas "-----BEGIN PRIVATE KEY-----" y "-----END PRIVATE KEY-----".', 'konecte-modular') . '</p>';
    }

    /**
     * Valida las opciones enviadas.
     *
     * @param array $input Las opciones introducidas por el usuario.
     * @return array Las opciones validadas y sanitizadas.
     */
    public function validate_settings($input) {
        $new_input = array();
        
        if (isset($input['sheet_id'])) {
            $new_input['sheet_id'] = sanitize_text_field($input['sheet_id']);
        }
        
        if (isset($input['api_key'])) {
            $new_input['api_key'] = sanitize_text_field($input['api_key']);
        }
        
        if (isset($input['service_account_email'])) {
            $new_input['service_account_email'] = sanitize_text_field($input['service_account_email']);
        }
        
        if (isset($input['private_key'])) {
            // No usamos sanitize_text_field aquí porque elimina los saltos de línea que son importantes
            $new_input['private_key'] = trim($input['private_key']);
        }
        
        return $new_input;
    }

    /**
     * Maneja la solicitud AJAX para verificar la conexión con Google Sheets.
     */
    public function ajax_check_connection() {
        // Verificar nonce para seguridad
        check_ajax_referer('konecte_sheets_connection_nonce', 'nonce');
        
        $options = get_option('konecte_modular_google_sheets_settings');
        $sheet_id = isset($options['sheet_id']) ? $options['sheet_id'] : '';
        $api_key = isset($options['api_key']) ? $options['api_key'] : '';
        $service_account_email = isset($options['service_account_email']) ? $options['service_account_email'] : '';
        $private_key = isset($options['private_key']) ? $options['private_key'] : '';
        
        $response = array(
            'success' => false,
            'message' => '',
            'status' => 'error'
        );
        
        // Verificar si se ha configurado el ID de la hoja
        if (empty($sheet_id)) {
            $response['message'] = __('Error: No se ha configurado el ID de la hoja de Google.', 'konecte-modular');
            wp_send_json($response);
            wp_die();
        }
        
        // Verificar si se han configurado al menos uno de los métodos de autenticación
        if (empty($api_key) && (empty($service_account_email) || empty($private_key))) {
            $response['message'] = __('Error: No se ha configurado ningún método de autenticación. Introduce una API Key o las credenciales de la cuenta de servicio.', 'konecte-modular');
            wp_send_json($response);
            wp_die();
        }
        
        // Intentar obtener datos de la hoja para verificar la conexión
        $result = $this->check_connection($sheet_id, $api_key, $service_account_email, $private_key);
        
        if (is_wp_error($result)) {
            $response['message'] = $result->get_error_message();
        } else {
            $response['success'] = true;
            $response['status'] = 'success';
            $response['message'] = sprintf(
                __('Conexión exitosa. Se detectaron %d columnas y %d filas en la hoja.', 'konecte-modular'),
                count($result['values'][0]),
                count($result['values'])
            );
        }
        
        // Asegurar que estamos enviando JSON válido
        wp_send_json($response);
        wp_die(); // Esta línea es redundante después de wp_send_json pero la mantenemos por claridad
    }
    
    /**
     * Verifica la conexión con Google Sheets.
     *
     * @param string $sheet_id ID de la hoja de Google.
     * @param string $api_key API Key de Google.
     * @param string $service_account_email Email de la cuenta de servicio.
     * @param string $private_key Clave privada de la cuenta de servicio.
     * @return array|WP_Error Los datos de la hoja o un objeto de error.
     */
    private function check_connection($sheet_id, $api_key, $service_account_email = '', $private_key = '') {
        // Determinar si se usa autenticación con cuenta de servicio o API Key
        $use_service_account = !empty($service_account_email) && !empty($private_key);
        
        // Construir la URL de la API para obtener solo los primeros datos
        if ($use_service_account) {
            $url = sprintf(
                'https://sheets.googleapis.com/v4/spreadsheets/%s/values/A1:C5',
                urlencode($sheet_id)
            );
        } else {
            $url = sprintf(
                'https://sheets.googleapis.com/v4/spreadsheets/%s/values/A1:C5?key=%s',
                urlencode($sheet_id),
                urlencode($api_key)
            );
        }
        
        // Configurar argumentos de la solicitud
        $args = array(
            'timeout'     => 15,
            'sslverify'   => true,
            'headers'     => array(
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ),
        );
        
        // Si se usa cuenta de servicio, añadir el token de autorización
        if ($use_service_account) {
            $token = $this->get_access_token($service_account_email, $private_key);
            
            if (is_wp_error($token)) {
                return $token;
            }
            
            $args['headers']['Authorization'] = 'Bearer ' . $token;
        }
        
        // Realizar la solicitud a la API
        $response = wp_remote_get($url, $args);
        
        // Verificar si hay errores en la respuesta
        if (is_wp_error($response)) {
            error_log('Konecte Modular - Error en conexión Google Sheets: ' . $response->get_error_message());
            return new WP_Error(
                'request_error',
                sprintf(__('Error al conectar con Google Sheets: %s', 'konecte-modular'), $response->get_error_message())
            );
        }
        
        // Obtener el código de respuesta y cuerpo
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($response_code !== 200) {
            $error_data = json_decode($body, true);
            $error_message = isset($error_data['error']['message']) ? $error_data['error']['message'] : __('Error desconocido', 'konecte-modular');
            $error_status = isset($error_data['error']['status']) ? $error_data['error']['status'] : 'ERROR';
            
            error_log('Konecte Modular - Error Google Sheets API: ' . $error_status . ' - ' . $error_message);
            
            return new WP_Error(
                'api_error_' . $response_code,
                sprintf(
                    __('Error en la API de Google Sheets (Código %s): %s', 'konecte-modular'), 
                    $response_code, 
                    $error_message
                )
            );
        }
        
        // Decodificar la respuesta JSON
        $data = json_decode($body, true);
        
        if (empty($data) || !isset($data['values'])) {
            return new WP_Error(
                'no_data', 
                __('No se pudieron obtener datos de la hoja de Google. La hoja podría estar vacía o no tener permisos correctos.', 'konecte-modular')
            );
        }
        
        return $data;
    }
    
    /**
     * Obtiene un token de acceso para la API de Google usando la cuenta de servicio.
     *
     * @param string $service_account_email Email de la cuenta de servicio.
     * @param string $private_key Clave privada de la cuenta de servicio.
     * @return string|WP_Error Token de acceso o un objeto de error.
     */
    private function get_access_token($service_account_email, $private_key) {
        // Verificar si ya hay un token válido en caché
        $token_data = get_transient('konecte_modular_google_token');
        if ($token_data) {
            return $token_data;
        }
        
        // Crear el JWT (JSON Web Token)
        $header = json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT'
        ]);
        
        // Tiempo actual y tiempo de expiración (1 hora)
        $now = time();
        $expiry = $now + 3600;
        
        $claim = json_encode([
            'iss' => $service_account_email,
            'scope' => 'https://www.googleapis.com/auth/spreadsheets.readonly',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $expiry,
            'iat' => $now
        ]);
        
        // Codificar header y claim en base64url
        $base64_header = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
        $base64_claim = rtrim(strtr(base64_encode($claim), '+/', '-_'), '=');
        
        // Crear la firma
        $private_key = str_replace(["-----BEGIN PRIVATE KEY-----\n", "\n-----END PRIVATE KEY-----"], "", $private_key);
        $private_key = str_replace(["\r", "\n"], '', $private_key);
        $private_key = "-----BEGIN PRIVATE KEY-----\n" . wordwrap($private_key, 64, "\n", true) . "\n-----END PRIVATE KEY-----";
        
        $to_sign = $base64_header . '.' . $base64_claim;
        $binary_signature = '';
        
        // Firmar el token
        if (!openssl_sign($to_sign, $binary_signature, $private_key, OPENSSL_ALGO_SHA256)) {
            return new WP_Error('jwt_signing_error', __('Error al firmar el token JWT con la clave privada proporcionada.', 'konecte-modular'));
        }
        
        $base64_signature = rtrim(strtr(base64_encode($binary_signature), '+/', '-_'), '=');
        
        // Construir el JWT completo
        $jwt = $base64_header . '.' . $base64_claim . '.' . $base64_signature;
        
        // Solicitar el token de acceso
        $response = wp_remote_post('https://oauth2.googleapis.com/token', [
            'body' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!isset($body['access_token'])) {
            $error_msg = isset($body['error_description']) ? $body['error_description'] : __('Error desconocido al obtener el token de acceso', 'konecte-modular');
            return new WP_Error('token_error', $error_msg);
        }
        
        // Guardar el token en caché (expires_in segundos menos 5 minutos para seguridad)
        set_transient('konecte_modular_google_token', $body['access_token'], $body['expires_in'] - 300);
        
        return $body['access_token'];
    }

    /**
     * Implementación del shortcode [google_sheets].
     *
     * @param array $atts Atributos del shortcode.
     * @return string El contenido HTML a mostrar.
     */
    public function google_sheets_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'id' => '',
                'range' => 'A1:Z1000',
                'sheet' => '0',
            ),
            $atts,
            'google_sheets'
        );

        // Si no se proporciona un ID, usar el ID guardado en la configuración
        if (empty($atts['id'])) {
            $options = get_option('konecte_modular_google_sheets_settings');
            $atts['id'] = isset($options['sheet_id']) ? $options['sheet_id'] : '';
        }

        // Si aún no hay ID, mostrar un mensaje de error
        if (empty($atts['id'])) {
            return '<p class="error">' . __('Error: No se ha especificado el ID de la hoja de Google.', 'konecte-modular') . '</p>';
        }

        // Obtener los datos de la hoja
        $data = $this->get_sheet_data($atts['id'], $atts['range'], $atts['sheet']);

        // Si hay un error, mostrar mensaje de error
        if (is_wp_error($data)) {
            return '<p class="error">' . $data->get_error_message() . '</p>';
        }

        // Construir la tabla HTML con los datos
        $html = '<div class="konecte-modular-google-sheets-table">';
        $html .= '<table>';
        
        // Encabezados
        if (!empty($data['values']) && count($data['values']) > 0) {
            $html .= '<thead><tr>';
            foreach ($data['values'][0] as $header) {
                $html .= '<th>' . esc_html($header) . '</th>';
            }
            $html .= '</tr></thead>';
            
            // Filas de datos
            $html .= '<tbody>';
            for ($i = 1; $i < count($data['values']); $i++) {
                $html .= '<tr>';
                foreach ($data['values'][$i] as $cell) {
                    $html .= '<td>' . esc_html($cell) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
        }
        
        $html .= '</table>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Implementación del shortcode [google_sheets_column].
     *
     * @param array $atts Atributos del shortcode.
     * @return string El contenido HTML a mostrar.
     */
    public function google_sheets_column_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'id' => '',
                'range' => 'A1:Z1000',
                'sheet' => '0',
                'column' => 'A',
                'header' => 'yes',
                'list' => 'yes',
            ),
            $atts,
            'google_sheets_column'
        );

        // Si no se proporciona un ID, usar el ID guardado en la configuración
        if (empty($atts['id'])) {
            $options = get_option('konecte_modular_google_sheets_settings');
            $atts['id'] = isset($options['sheet_id']) ? $options['sheet_id'] : '';
        }

        // Si aún no hay ID, mostrar un mensaje de error
        if (empty($atts['id'])) {
            return '<p class="error">' . __('Error: No se ha especificado el ID de la hoja de Google.', 'konecte-modular') . '</p>';
        }

        // Obtener los datos de la hoja
        $data = $this->get_sheet_data($atts['id'], $atts['range'], $atts['sheet']);

        // Si hay un error, mostrar mensaje de error
        if (is_wp_error($data)) {
            return '<p class="error">' . $data->get_error_message() . '</p>';
        }

        // Convertir la letra de la columna a índice numérico (A=0, B=1, etc.)
        $column_index = ord(strtoupper($atts['column'])) - 65;

        // Extraer los datos de la columna específica
        $column_data = array();
        if (!empty($data['values'])) {
            foreach ($data['values'] as $row) {
                if (isset($row[$column_index])) {
                    $column_data[] = $row[$column_index];
                }
            }
        }

        // Construir el HTML
        $html = '<div class="konecte-modular-google-sheets-column">';
        
        // Si se debe mostrar el encabezado y hay datos
        if ($atts['header'] === 'yes' && !empty($column_data)) {
            $html .= '<h3>' . esc_html($column_data[0]) . '</h3>';
            // Eliminar el encabezado para mostrar solo los datos
            array_shift($column_data);
        }
        
        // Mostrar los datos como lista o párrafos
        if ($atts['list'] === 'yes') {
            $html .= '<ul>';
            foreach ($column_data as $item) {
                $html .= '<li>' . esc_html($item) . '</li>';
            }
            $html .= '</ul>';
        } else {
            foreach ($column_data as $item) {
                $html .= '<p>' . esc_html($item) . '</p>';
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Obtiene los datos de una hoja de Google Sheets.
     *
     * @param string $sheet_id ID de la hoja de Google.
     * @param string $range Rango de celdas a obtener.
     * @param string $sheet_index Índice o nombre de la hoja específica.
     * @return array|WP_Error Los datos de la hoja o un objeto de error.
     */
    private function get_sheet_data($sheet_id, $range, $sheet_index) {
        $options = get_option('konecte_modular_google_sheets_settings');
        $api_key = isset($options['api_key']) ? $options['api_key'] : '';
        $service_account_email = isset($options['service_account_email']) ? $options['service_account_email'] : '';
        $private_key = isset($options['private_key']) ? $options['private_key'] : '';
        
        // Determinar método de autenticación a usar
        $use_service_account = !empty($service_account_email) && !empty($private_key);
        
        if (empty($api_key) && !$use_service_account) {
            return new WP_Error('no_auth_method', __('No se ha configurado ningún método de autenticación para Google Sheets.', 'konecte-modular'));
        }
        
        // Construir la URL de la API
        if ($use_service_account) {
            $url = sprintf(
                'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s!%s',
                urlencode($sheet_id),
                urlencode($sheet_index),
                urlencode($range)
            );
        } else {
            $url = sprintf(
                'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s!%s?key=%s',
                urlencode($sheet_id),
                urlencode($sheet_index),
                urlencode($range),
                urlencode($api_key)
            );
        }
        
        // Configurar argumentos de la solicitud
        $args = array(
            'timeout'     => 15,
            'sslverify'   => true,
            'headers'     => array(
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ),
        );
        
        // Si se usa cuenta de servicio, añadir el token de autorización
        if ($use_service_account) {
            $token = $this->get_access_token($service_account_email, $private_key);
            
            if (is_wp_error($token)) {
                return $token;
            }
            
            $args['headers']['Authorization'] = 'Bearer ' . $token;
        }
        
        // Realizar la solicitud a la API
        $response = wp_remote_get($url, $args);
        
        // Verificar si hay errores en la respuesta
        if (is_wp_error($response)) {
            error_log('Konecte Modular - Error en solicitud a Google Sheets: ' . $response->get_error_message());
            return $response;
        }
        
        // Obtener el código de respuesta y cuerpo
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($response_code !== 200) {
            $error_data = json_decode($body, true);
            $error_message = isset($error_data['error']['message']) ? $error_data['error']['message'] : __('Error desconocido', 'konecte-modular');
            
            return new WP_Error(
                'api_error_' . $response_code,
                sprintf(__('Error al obtener datos de Google Sheets (Código %s): %s', 'konecte-modular'), $response_code, $error_message)
            );
        }
        
        // Decodificar la respuesta JSON
        $data = json_decode($body, true);
        
        if (empty($data) || !isset($data['values'])) {
            return new WP_Error('no_data', __('No se pudieron obtener datos de la hoja de Google.', 'konecte-modular'));
        }
        
        return $data;
    }

    /**
     * Maneja la solicitud AJAX para la previsualización de shortcodes.
     */
    public function ajax_preview_shortcode() {
        // Verificar nonce para seguridad
        check_ajax_referer('konecte_sheets_preview_nonce', 'nonce');
        
        $response = array(
            'success' => false,
            'content' => '',
            'message' => ''
        );
        
        // Obtener parámetros
        $shortcode_type = isset($_POST['shortcode_type']) ? sanitize_text_field($_POST['shortcode_type']) : 'google_sheets';
        
        // Configurar el shortcode a ejecutar
        $shortcode = '';
        
        if ($shortcode_type === 'google_sheets') {
            $shortcode = '[google_sheets]';
        } elseif ($shortcode_type === 'google_sheets_column') {
            $column = isset($_POST['column']) ? sanitize_text_field($_POST['column']) : 'A';
            $header = isset($_POST['header']) && $_POST['header'] === 'true' ? 'yes' : 'no';
            $list = isset($_POST['list']) && $_POST['list'] === 'true' ? 'yes' : 'no';
            
            $shortcode = sprintf('[google_sheets_column column="%s" header="%s" list="%s"]', 
                $column, $header, $list);
        }
        
        // Ejecutar el shortcode
        $content = do_shortcode($shortcode);
        
        if (empty($content)) {
            $response['message'] = __('No se pudieron obtener datos para mostrar. Asegúrate de que las configuraciones de Google Sheets sean correctas.', 'konecte-modular');
        } else {
            $response['success'] = true;
            $response['content'] = $content;
        }
        
        wp_send_json($response);
        wp_die();
    }
} 