/**
 * Scripts del área de administración del plugin.
 *
 * @package    Konecte_Modular
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Manejar las pestañas de navegación
        $('.nav-tab-wrapper a').on('click', function(e) {
            var $this = $(this);
            var tab = $this.attr('href').split('tab=')[1];
            
            if (tab) {
                $('.nav-tab-wrapper a').removeClass('nav-tab-active');
                $this.addClass('nav-tab-active');
            }
        });

        // Validar campos de formulario
        $('.konecte-modular-settings-form').on('submit', function(e) {
            var $sheet_id = $('#konecte_modular_google_sheets_id');
            var $api_key = $('#konecte_modular_google_sheets_api_key');
            
            if ($sheet_id.length && $sheet_id.val() === '') {
                alert('Por favor, introduce el ID de la hoja de Google.');
                $sheet_id.focus();
                e.preventDefault();
                return false;
            }
            
            if ($api_key.length && $api_key.val() === '') {
                alert('Por favor, introduce la API Key de Google.');
                $api_key.focus();
                e.preventDefault();
                return false;
            }
        });

        // Mostrar/ocultar ayuda contextual
        $('.konecte-modular-help-toggle').on('click', function(e) {
            e.preventDefault();
            var $help = $(this).closest('.form-field').find('.konecte-modular-help');
            $help.slideToggle();
        });

        // Copiar shortcode al portapapeles
        $('.konecte-modular-copy-shortcode').on('click', function(e) {
            e.preventDefault();
            var $code = $(this).prev('code');
            var text = $code.text();
            
            var $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(text).select();
            document.execCommand('copy');
            $temp.remove();
            
            var $message = $('<span class="konecte-modular-copy-message">Copiado!</span>');
            $(this).after($message);
            setTimeout(function() {
                $message.fadeOut(function() {
                    $(this).remove();
                });
            }, 2000);
        });
    });

})(jQuery); 