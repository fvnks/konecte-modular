<?php
/**
 * Proporciona una vista de administración para la configuración de Google Sheets
 *
 * @package    Konecte_Modular
 * @subpackage Konecte_Modular/admin/partials
 */
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php settings_errors(); ?>
    
    <div class="nav-tab-wrapper">
        <a href="?page=konecte-modular-google-sheets" class="nav-tab nav-tab-active"><?php _e('Configuración', 'konecte-modular'); ?></a>
        <a href="?page=konecte-modular-google-sheets&tab=ayuda" class="nav-tab"><?php _e('Ayuda', 'konecte-modular'); ?></a>
    </div>
    
    <?php
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'config';
    
    if ($active_tab == 'ayuda') {
        // Contenido de la pestaña de ayuda
        ?>
        <div class="card">
            <h2><?php _e('Cómo configurar una cuenta de servicio de Google', 'konecte-modular'); ?></h2>
            <p><?php _e('Para acceder a tus hojas de Google Sheets de forma segura, necesitas configurar una cuenta de servicio:', 'konecte-modular'); ?></p>
            <ol>
                <li><?php _e('Ve a la <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a>', 'konecte-modular'); ?></li>
                <li><?php _e('Crea un nuevo proyecto o selecciona uno existente', 'konecte-modular'); ?></li>
                <li><?php _e('Ve a "APIs y Servicios" > "Biblioteca"', 'konecte-modular'); ?></li>
                <li><?php _e('Busca "Google Sheets API" y actívala', 'konecte-modular'); ?></li>
                <li><?php _e('Ve a "APIs y Servicios" > "Credenciales"', 'konecte-modular'); ?></li>
                <li><?php _e('Haz clic en "Crear credenciales" y selecciona "Cuenta de servicio"', 'konecte-modular'); ?></li>
                <li><?php _e('Asigna un nombre, ID y descripción para la cuenta de servicio y haz clic en "Crear"', 'konecte-modular'); ?></li>
                <li><?php _e('Puedes omitir los pasos de otorgar acceso al proyecto, simplemente haz clic en "Continuar" y luego en "Listo"', 'konecte-modular'); ?></li>
                <li><?php _e('En la lista de cuentas de servicio, haz clic en la cuenta que acabas de crear', 'konecte-modular'); ?></li>
                <li><?php _e('Ve a la pestaña "Claves" y haz clic en "Agregar clave" > "Crear nueva clave"', 'konecte-modular'); ?></li>
                <li><?php _e('Selecciona "JSON" como tipo de clave y haz clic en "Crear"', 'konecte-modular'); ?></li>
                <li><?php _e('Se descargará un archivo JSON. Ábrelo con un editor de texto y copia el valor del campo "client_email" en el campo "Email de la cuenta de servicio" del plugin', 'konecte-modular'); ?></li>
                <li><?php _e('Copia el valor del campo "private_key" en el campo "Clave privada de la cuenta de servicio" del plugin', 'konecte-modular'); ?></li>
            </ol>
            <p><strong><?php _e('Importante:', 'konecte-modular'); ?></strong> <?php _e('Para permitir que la cuenta de servicio acceda a tu hoja de cálculo, debes compartir la hoja con la dirección de correo de la cuenta de servicio que copiaste en el paso 12, dándole al menos permisos de "Lector".', 'konecte-modular'); ?></p>
        </div>
        
        <div class="card">
            <h2><?php _e('Cómo encontrar el ID de tu hoja de Google', 'konecte-modular'); ?></h2>
            <p><?php _e('El ID de tu hoja de Google se encuentra en la URL de la hoja. Por ejemplo:', 'konecte-modular'); ?></p>
            <p><code>https://docs.google.com/spreadsheets/d/<strong>1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms</strong>/edit#gid=0</code></p>
            <p><?php _e('La parte resaltada es el ID de la hoja.', 'konecte-modular'); ?></p>
        </div>
        
        <div class="card">
            <h2><?php _e('Compartir tu hoja con la cuenta de servicio', 'konecte-modular'); ?></h2>
            <p><?php _e('Para que la cuenta de servicio pueda acceder a tu hoja, debes compartirla con ella:', 'konecte-modular'); ?></p>
            <ol>
                <li><?php _e('Abre tu hoja de Google', 'konecte-modular'); ?></li>
                <li><?php _e('Haz clic en "Compartir" en la esquina superior derecha', 'konecte-modular'); ?></li>
                <li><?php _e('Ingresa el email de la cuenta de servicio (que termina en @*.iam.gserviceaccount.com)', 'konecte-modular'); ?></li>
                <li><?php _e('Asegúrate de que el permiso sea al menos "Lector"', 'konecte-modular'); ?></li>
                <li><?php _e('Haz clic en "Listo"', 'konecte-modular'); ?></li>
            </ol>
        </div>
        <?php
    } else {
        // Contenido de la pestaña de configuración
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('konecte_modular_google_sheets_options');
            do_settings_sections('konecte-modular-google-sheets');
            submit_button();
            ?>
        </form>
        
        <div class="card">
            <h2><?php _e('Estado de la conexión', 'konecte-modular'); ?></h2>
            <p><?php _e('Verifica si tu hoja de Google Sheets está correctamente configurada y accesible.', 'konecte-modular'); ?></p>
            
            <div class="connection-status-container">
                <button type="button" id="check-connection-btn" class="button button-primary"><?php _e('Verificar conexión', 'konecte-modular'); ?></button>
                <span class="spinner" id="connection-spinner" style="float:none; visibility:hidden; margin-left:10px;"></span>
                <div id="connection-status-message" class="connection-status" style="margin-top:10px;"></div>
            </div>
        </div>
        
        <div class="card">
            <h2><?php _e('Ejemplo de Uso', 'konecte-modular'); ?></h2>
            <p><?php _e('Una vez configurada las credenciales y el ID de la hoja, puedes usar estos shortcodes:', 'konecte-modular'); ?></p>
            <p><code>[google_sheets]</code> - <?php _e('Muestra la hoja completa', 'konecte-modular'); ?></p>
            <p><code>[google_sheets_column column="B"]</code> - <?php _e('Muestra solo la columna B', 'konecte-modular'); ?></p>
        </div>
        
        <div class="card">
            <h2><?php _e('Previsualización', 'konecte-modular'); ?></h2>
            <p><?php _e('Prueba cómo se verán tus shortcodes antes de utilizarlos en tu sitio.', 'konecte-modular'); ?></p>
            
            <div class="konecte-modular-preview-container">
                <div class="konecte-modular-preview-controls">
                    <select id="preview-shortcode-type">
                        <option value="google_sheets"><?php _e('Tabla completa', 'konecte-modular'); ?></option>
                        <option value="google_sheets_column"><?php _e('Columna específica', 'konecte-modular'); ?></option>
                    </select>
                    
                    <div id="column-options" style="display:none; margin-top:10px;">
                        <label for="preview-column"><?php _e('Columna:', 'konecte-modular'); ?></label>
                        <input type="text" id="preview-column" value="A" maxlength="2" size="2" />
                        
                        <label for="preview-header" style="margin-left:15px;">
                            <input type="checkbox" id="preview-header" checked />
                            <?php _e('Mostrar encabezado', 'konecte-modular'); ?>
                        </label>
                        
                        <label for="preview-list" style="margin-left:15px;">
                            <input type="checkbox" id="preview-list" checked />
                            <?php _e('Mostrar como lista', 'konecte-modular'); ?>
                        </label>
                    </div>
                    
                    <button type="button" id="generate-preview-btn" class="button button-primary" style="margin-top:15px;"><?php _e('Generar previsualización', 'konecte-modular'); ?></button>
                    <span class="spinner" id="preview-spinner" style="float:none; visibility:hidden; margin-left:10px;"></span>
                </div>
                
                <div class="konecte-modular-preview-shortcode" style="margin-top:15px; padding:10px; background:#f9f9f9; border:1px solid #ddd; border-radius:4px;">
                    <code id="preview-shortcode-text">[google_sheets]</code>
                    <button type="button" id="copy-shortcode-btn" class="button button-secondary" style="margin-left:10px;"><?php _e('Copiar', 'konecte-modular'); ?></button>
                </div>
                
                <div class="konecte-modular-preview-result" style="margin-top:20px;">
                    <h3><?php _e('Resultado:', 'konecte-modular'); ?></h3>
                    <div id="preview-result" class="konecte-modular-preview-content">
                        <p><?php _e('Haz clic en "Generar previsualización" para ver el resultado.', 'konecte-modular'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div> 