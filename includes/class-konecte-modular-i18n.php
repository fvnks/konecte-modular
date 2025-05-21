<?php
/**
 * Define la funcionalidad de internacionalización.
 *
 * Carga y define los archivos de internacionalización para este plugin.
 *
 * @package    Konecte_Modular
 */
class Konecte_Modular_i18n {

    /**
     * El dominio del texto del plugin.
     *
     * @var      string    $domain    El dominio del texto del plugin.
     */
    private $domain;

    /**
     * Carga el dominio del texto para el plugin.
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            $this->domain,
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

    /**
     * Establece el dominio del texto del plugin.
     *
     * @param    string    $domain    El dominio del texto del plugin.
     */
    public function set_domain($domain) {
        $this->domain = $domain;
    }
} 