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
            <h2><?php _e('Cómo obtener una API Key de Google', 'konecte-modular'); ?></h2>
            <ol>
                <li><?php _e('Ve a la <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a>', 'konecte-modular'); ?></li>
                <li><?php _e('Crea un nuevo proyecto o selecciona uno existente', 'konecte-modular'); ?></li>
                <li><?php _e('Ve a "APIs y Servicios" > "Biblioteca"', 'konecte-modular'); ?></li>
                <li><?php _e('Busca "Google Sheets API" y actívala', 'konecte-modular'); ?></li>
                <li><?php _e('Ve a "APIs y Servicios" > "Credenciales"', 'konecte-modular'); ?></li>
                <li><?php _e('Haz clic en "Crear credenciales" y selecciona "Clave de API"', 'konecte-modular'); ?></li>
                <li><?php _e('Copia la API Key generada y pégala en la configuración de este plugin', 'konecte-modular'); ?></li>
            </ol>
        </div>
        
        <div class="card">
            <h2><?php _e('Cómo encontrar el ID de tu hoja de Google', 'konecte-modular'); ?></h2>
            <p><?php _e('El ID de tu hoja de Google se encuentra en la URL de la hoja. Por ejemplo:', 'konecte-modular'); ?></p>
            <p><code>https://docs.google.com/spreadsheets/d/<strong>1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms</strong>/edit#gid=0</code></p>
            <p><?php _e('La parte resaltada es el ID de la hoja.', 'konecte-modular'); ?></p>
        </div>
        
        <div class="card">
            <h2><?php _e('Cómo hacer tu hoja pública', 'konecte-modular'); ?></h2>
            <p><?php _e('Para que este plugin pueda acceder a tu hoja de Google, debes hacer que sea accesible públicamente:', 'konecte-modular'); ?></p>
            <ol>
                <li><?php _e('Abre tu hoja de Google', 'konecte-modular'); ?></li>
                <li><?php _e('Haz clic en "Archivo" > "Compartir" > "Publicar en la web"', 'konecte-modular'); ?></li>
                <li><?php _e('Selecciona la hoja o el rango de celdas que deseas publicar', 'konecte-modular'); ?></li>
                <li><?php _e('Haz clic en "Publicar"', 'konecte-modular'); ?></li>
            </ol>
            <p><?php _e('Alternativamente, puedes compartir la hoja para que cualquier persona con el enlace pueda verla:', 'konecte-modular'); ?></p>
            <ol>
                <li><?php _e('Abre tu hoja de Google', 'konecte-modular'); ?></li>
                <li><?php _e('Haz clic en "Compartir" en la esquina superior derecha', 'konecte-modular'); ?></li>
                <li><?php _e('Cambia la configuración a "Cualquier persona con el enlace"', 'konecte-modular'); ?></li>
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
            <p><?php _e('Una vez configurada la API Key y el ID de la hoja, puedes usar estos shortcodes:', 'konecte-modular'); ?></p>
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