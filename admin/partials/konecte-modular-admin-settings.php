<?php
/**
 * Proporciona una vista de administración para la configuración del actualizador
 *
 * @package    Konecte_Modular
 * @subpackage Konecte_Modular/admin/partials
 */
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php settings_errors(); ?>
    
    <div class="nav-tab-wrapper">
        <a href="?page=konecte-modular-settings" class="nav-tab nav-tab-active"><?php _e('Configuración', 'konecte-modular'); ?></a>
        <a href="?page=konecte-modular-settings&tab=ayuda" class="nav-tab"><?php _e('Ayuda', 'konecte-modular'); ?></a>
    </div>
    
    <?php
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'config';
    
    if ($active_tab == 'ayuda') {
        // Contenido de la pestaña de ayuda
        ?>
        <div class="card">
            <h2><?php _e('Cómo funcionan las actualizaciones desde GitHub', 'konecte-modular'); ?></h2>
            <p><?php _e('Este plugin está configurado para comprobar periódicamente si hay nuevas versiones disponibles en el repositorio de GitHub especificado.', 'konecte-modular'); ?></p>
            <p><?php _e('Cuando se detecta una nueva versión (basada en tags de GitHub), WordPress mostrará una notificación de actualización como lo hace con cualquier otro plugin.', 'konecte-modular'); ?></p>
        </div>
        
        <div class="card">
            <h2><?php _e('Cómo obtener un token de acceso de GitHub', 'konecte-modular'); ?></h2>
            <p><?php _e('Si estás utilizando un repositorio privado, necesitarás un token de acceso personal:', 'konecte-modular'); ?></p>
            <ol>
                <li><?php _e('Inicia sesión en tu cuenta de GitHub', 'konecte-modular'); ?></li>
                <li><?php _e('Ve a "Configuración" > "Configuración de desarrollador" > "Tokens de acceso personal"', 'konecte-modular'); ?></li>
                <li><?php _e('Haz clic en "Generar nuevo token"', 'konecte-modular'); ?></li>
                <li><?php _e('Dale un nombre descriptivo', 'konecte-modular'); ?></li>
                <li><?php _e('Selecciona al menos el permiso "repo" para repositorios privados', 'konecte-modular'); ?></li>
                <li><?php _e('Haz clic en "Generar token"', 'konecte-modular'); ?></li>
                <li><?php _e('Copia el token generado y pégalo en la configuración de este plugin', 'konecte-modular'); ?></li>
            </ol>
            <p><strong><?php _e('Nota importante:', 'konecte-modular'); ?></strong> <?php _e('Guarda este token en un lugar seguro. No podrás verlo de nuevo una vez que salgas de la página.', 'konecte-modular'); ?></p>
        </div>

        <div class="card">
            <h2><?php _e('Configuración del intervalo de actualización', 'konecte-modular'); ?></h2>
            <p><?php _e('Puedes configurar cada cuántos minutos el plugin verificará si hay actualizaciones disponibles en GitHub.', 'konecte-modular'); ?></p>
            <p><?php _e('Valores recomendados:', 'konecte-modular'); ?></p>
            <ul>
                <li><?php _e('60 minutos (1 hora): Para entornos de desarrollo donde necesitas actualizaciones frecuentes.', 'konecte-modular'); ?></li>
                <li><?php _e('720 minutos (12 horas): Para sitios de prueba o staging.', 'konecte-modular'); ?></li>
                <li><?php _e('1440 minutos (24 horas): Para sitios en producción.', 'konecte-modular'); ?></li>
            </ul>
            <p><?php _e('No se recomienda usar intervalos muy cortos (menos de 15 minutos) en sitios de producción, ya que podría afectar el rendimiento.', 'konecte-modular'); ?></p>
        </div>
        <?php
    } else {
        // Contenido de la pestaña de configuración
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('konecte_modular_updater_options');
            do_settings_sections('konecte-modular-settings');
            submit_button();
            ?>
        </form>
        
        <div class="card">
            <h2><?php _e('Información de Versión', 'konecte-modular'); ?></h2>
            <p><?php printf(__('Versión actual del plugin: %s', 'konecte-modular'), KONECTE_MODULAR_VERSION); ?></p>
            <?php
            // Comprobar si hay una actualización disponible
            $last_check = get_option('konecte_modular_last_update_check');
            $options = get_option('konecte_modular_updater_settings');
            $check_interval = isset($options['check_interval']) ? (int) $options['check_interval'] : 60;
            
            if ($last_check) {
                $date_format = get_option('date_format');
                $time_format = get_option('time_format');
                $formatted_date = date_i18n($date_format . ' ' . $time_format, $last_check);
                
                echo '<p>' . sprintf(__('Última comprobación de actualizaciones: %s', 'konecte-modular'), $formatted_date) . '</p>';
                
                // Calcular próxima comprobación
                $next_check = $last_check + ($check_interval * MINUTE_IN_SECONDS);
                $formatted_next_date = date_i18n($date_format . ' ' . $time_format, $next_check);
                
                echo '<p>' . sprintf(__('Próxima comprobación programada: %s', 'konecte-modular'), $formatted_next_date) . '</p>';
                echo '<p>' . sprintf(__('Intervalo de comprobación actual: %d minutos', 'konecte-modular'), $check_interval) . '</p>';
            }
            ?>
            <p><a href="<?php echo admin_url('update-core.php'); ?>" class="button"><?php _e('Buscar Actualizaciones Ahora', 'konecte-modular'); ?></a></p>
        </div>
        <?php
    }
    ?>
</div> 