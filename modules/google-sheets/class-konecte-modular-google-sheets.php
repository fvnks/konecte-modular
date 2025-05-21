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
    }

    /**
     * Callback para la sección de Google Sheets.
     */
    public function google_sheets_section_callback() {
        echo '<p>' . __('Ingrese la información de su hoja de Google y la API Key para conectarse.', 'konecte-modular') . '</p>';
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
        
        return $new_input;
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
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('No se ha configurado la API Key de Google.', 'konecte-modular'));
        }
        
        // Construir la URL de la API
        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s!%s?key=%s',
            $sheet_id,
            urlencode($sheet_index),
            urlencode($range),
            $api_key
        );
        
        // Realizar la solicitud a la API
        $response = wp_remote_get($url);
        
        // Verificar si hay errores en la respuesta
        if (is_wp_error($response)) {
            return $response;
        }
        
        // Obtener el código de respuesta
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return new WP_Error(
                'api_error',
                sprintf(__('Error al obtener datos de Google Sheets. Código: %s', 'konecte-modular'), $response_code)
            );
        }
        
        // Decodificar la respuesta JSON
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (empty($data) || !isset($data['values'])) {
            return new WP_Error('no_data', __('No se pudieron obtener datos de la hoja de Google.', 'konecte-modular'));
        }
        
        return $data;
    }
} 