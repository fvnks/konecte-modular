<?php
/**
 * Proporciona una vista de administración del área del plugin
 *
 * @package    Konecte_Modular
 * @subpackage Konecte_Modular/admin/partials
 */
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="welcome-panel">
        <div class="welcome-panel-content">
            <h2><?php _e('¡Bienvenido a Konecte Modular - Google Sheets Connector!', 'konecte-modular'); ?></h2>
            <p class="about-description"><?php _e('Este plugin te permite mostrar datos de Google Sheets en tu WordPress utilizando shortcodes.', 'konecte-modular'); ?></p>
            
            <div class="welcome-panel-column-container">
                <div class="welcome-panel-column">
                    <h3><span class="dashicons dashicons-admin-tools"></span> <?php _e('Iniciar', 'konecte-modular'); ?></h3>
                    <ul>
                        <li><?php _e('Configura la conexión a Google Sheets', 'konecte-modular'); ?></li>
                        <li><?php _e('Utiliza los shortcodes en tus páginas', 'konecte-modular'); ?></li>
                        <li><?php _e('Personaliza según tus necesidades', 'konecte-modular'); ?></li>
                    </ul>
                    <a href="<?php echo admin_url('admin.php?page=konecte-modular-google-sheets'); ?>" class="button button-primary"><?php _e('Configurar Google Sheets', 'konecte-modular'); ?></a>
                </div>
                
                <div class="welcome-panel-column">
                    <h3><span class="dashicons dashicons-shortcode"></span> <?php _e('Shortcodes Disponibles', 'konecte-modular'); ?></h3>
                    <p><?php _e('Copia estos shortcodes en tus páginas o posts:', 'konecte-modular'); ?></p>
                    <code>[google_sheets id="ID_HOJA" range="A1:Z1000" sheet="0"]</code>
                    <code>[google_sheets_column id="ID_HOJA" range="A1:Z1000" sheet="0" column="A"]</code>
                </div>
                
                <div class="welcome-panel-column welcome-panel-last">
                    <h3><span class="dashicons dashicons-update"></span> <?php _e('Actualizaciones', 'konecte-modular'); ?></h3>
                    <p><?php _e('Este plugin se actualiza automáticamente desde GitHub.', 'konecte-modular'); ?></p>
                    <p><?php _e('Versión actual:', 'konecte-modular'); ?> <strong><?php echo KONECTE_MODULAR_VERSION; ?></strong></p>
                    <a href="<?php echo admin_url('admin.php?page=konecte-modular-settings'); ?>" class="button button-primary"><?php _e('Configurar Actualizaciones', 'konecte-modular'); ?></a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h2><span class="dashicons dashicons-book"></span> <?php _e('Documentación Rápida', 'konecte-modular'); ?></h2>
        
        <h3><?php _e('Shortcode [google_sheets]', 'konecte-modular'); ?></h3>
        <p><?php _e('Este shortcode muestra una tabla completa con los datos de tu hoja de Google.', 'konecte-modular'); ?></p>
        <p><strong><?php _e('Atributos:', 'konecte-modular'); ?></strong></p>
        <ul>
            <li><code>id</code>: <?php _e('El ID de tu hoja de Google. Opcional si ya lo configuraste en los ajustes.', 'konecte-modular'); ?></li>
            <li><code>range</code>: <?php _e('El rango de celdas a mostrar. Por defecto A1:Z1000.', 'konecte-modular'); ?></li>
            <li><code>sheet</code>: <?php _e('El índice de la hoja (0 para la primera). Por defecto 0.', 'konecte-modular'); ?></li>
        </ul>
        
        <h3><?php _e('Shortcode [google_sheets_column]', 'konecte-modular'); ?></h3>
        <p><?php _e('Este shortcode muestra una columna específica de tu hoja de Google.', 'konecte-modular'); ?></p>
        <p><strong><?php _e('Atributos:', 'konecte-modular'); ?></strong></p>
        <ul>
            <li><code>id</code>: <?php _e('El ID de tu hoja de Google. Opcional si ya lo configuraste en los ajustes.', 'konecte-modular'); ?></li>
            <li><code>range</code>: <?php _e('El rango de celdas a considerar. Por defecto A1:Z1000.', 'konecte-modular'); ?></li>
            <li><code>sheet</code>: <?php _e('El índice de la hoja (0 para la primera). Por defecto 0.', 'konecte-modular'); ?></li>
            <li><code>column</code>: <?php _e('La letra de la columna a mostrar (A, B, C, etc.). Por defecto A.', 'konecte-modular'); ?></li>
            <li><code>header</code>: <?php _e('Si se debe mostrar la cabecera (yes/no). Por defecto yes.', 'konecte-modular'); ?></li>
            <li><code>list</code>: <?php _e('Si se debe mostrar como lista (yes/no). Por defecto yes.', 'konecte-modular'); ?></li>
        </ul>
    </div>
    
    <div class="card">
        <h2><span class="dashicons dashicons-editor-help"></span> <?php _e('¿Necesitas ayuda?', 'konecte-modular'); ?></h2>
        <p><?php _e('Si tienes preguntas o necesitas soporte, puedes:', 'konecte-modular'); ?></p>
        <ul>
            <li><?php _e('Visitar el', 'konecte-modular'); ?> <a href="https://github.com/fvnks/konecte-modular" target="_blank"><?php _e('repositorio en GitHub', 'konecte-modular'); ?></a></li>
            <li><?php _e('Revisar la documentación completa en línea', 'konecte-modular'); ?></li>
            <li><?php _e('Abrir un issue en GitHub para reportar problemas', 'konecte-modular'); ?></li>
        </ul>
    </div>
</div> 