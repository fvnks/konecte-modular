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
        
        // Verificar conexión con Google Sheets
        $('#check-connection-btn').on('click', function() {
            var $button = $(this);
            var $spinner = $('#connection-spinner');
            var $message = $('#connection-status-message');
            
            // Deshabilitar el botón y mostrar spinner
            $button.prop('disabled', true);
            $spinner.css('visibility', 'visible');
            $message.html('').removeClass('connection-status-success connection-status-error');
            $message.html(konecte_modular_admin.checking_connection);
            
            // Realizar la solicitud AJAX
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'konecte_check_sheets_connection',
                    nonce: konecte_modular_admin.nonce
                },
                success: function(response) {
                    // Limpiar mensaje anterior
                    $message.html('');
                    
                    // Mostrar mensaje de resultado
                    if (response && typeof response === 'object') {
                        $message.html(response.message);
                        
                        if (response.success === true || response.status === 'success') {
                            $message.addClass('connection-status-success');
                        } else {
                            $message.addClass('connection-status-error');
                        }
                    } else {
                        // Fallback para respuestas no válidas
                        $message.html('Error: Respuesta no válida del servidor').addClass('connection-status-error');
                    }
                },
                error: function(xhr, status, error) {
                    $message.html('Error de comunicación con el servidor: ' + error).addClass('connection-status-error');
                    console.error('Error AJAX:', xhr, status, error);
                },
                complete: function() {
                    // Habilitar el botón y ocultar spinner
                    $button.prop('disabled', false);
                    $spinner.css('visibility', 'hidden');
                }
            });
        });
        
        // Previsualización de shortcodes
        if ($('#preview-shortcode-type').length) {
            // Manejar cambio de tipo de shortcode
            $('#preview-shortcode-type').on('change', function() {
                var type = $(this).val();
                
                if (type === 'google_sheets_column') {
                    $('#column-options').slideDown();
                    updatePreviewShortcode();
                } else {
                    $('#column-options').slideUp();
                    $('#preview-shortcode-text').text('[google_sheets]');
                }
            });
            
            // Actualizar el texto del shortcode cuando cambien las opciones
            $('#preview-column, #preview-header, #preview-list').on('change', function() {
                updatePreviewShortcode();
            });
            
            // Función para actualizar el texto del shortcode
            function updatePreviewShortcode() {
                var type = $('#preview-shortcode-type').val();
                
                if (type === 'google_sheets_column') {
                    var column = $('#preview-column').val();
                    var header = $('#preview-header').prop('checked') ? 'yes' : 'no';
                    var list = $('#preview-list').prop('checked') ? 'yes' : 'no';
                    
                    var shortcode = '[google_sheets_column column="' + column + '"';
                    
                    if (header !== 'yes') {
                        shortcode += ' header="' + header + '"';
                    }
                    
                    if (list !== 'yes') {
                        shortcode += ' list="' + list + '"';
                    }
                    
                    shortcode += ']';
                    $('#preview-shortcode-text').text(shortcode);
                } else {
                    $('#preview-shortcode-text').text('[google_sheets]');
                }
            }
            
            // Copiar shortcode al portapapeles
            $('#copy-shortcode-btn').on('click', function() {
                var shortcodeText = $('#preview-shortcode-text').text();
                
                var $temp = $('<textarea>');
                $('body').append($temp);
                $temp.val(shortcodeText).select();
                document.execCommand('copy');
                $temp.remove();
                
                var $button = $(this);
                var originalText = $button.text();
                
                $button.text('¡Copiado!');
                setTimeout(function() {
                    $button.text(originalText);
                }, 2000);
            });
            
            // Generar previsualización
            $('#generate-preview-btn').on('click', function() {
                var $button = $(this);
                var $spinner = $('#preview-spinner');
                var $result = $('#preview-result');
                
                // Deshabilitar el botón y mostrar spinner
                $button.prop('disabled', true);
                $spinner.css('visibility', 'visible');
                $result.html('<p>' + konecte_modular_admin.generating_preview + '</p>');
                
                // Obtener los parámetros del shortcode
                var type = $('#preview-shortcode-type').val();
                var data = {
                    action: 'konecte_preview_shortcode',
                    nonce: konecte_modular_admin.preview_nonce,
                    shortcode_type: type
                };
                
                if (type === 'google_sheets_column') {
                    data.column = $('#preview-column').val();
                    data.header = $('#preview-header').prop('checked').toString();
                    data.list = $('#preview-list').prop('checked').toString();
                }
                
                // Realizar la solicitud AJAX
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            $result.html(response.content);
                        } else {
                            $result.html('<div class="konecte-modular-error">' + response.message + '</div>');
                        }
                    },
                    error: function() {
                        $result.html('<div class="konecte-modular-error">Error de comunicación con el servidor.</div>');
                    },
                    complete: function() {
                        // Habilitar el botón y ocultar spinner
                        $button.prop('disabled', false);
                        $spinner.css('visibility', 'hidden');
                    }
                });
            });
        }
    });

})(jQuery); 