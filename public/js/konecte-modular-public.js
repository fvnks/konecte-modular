/**
 * Scripts de la parte pública del plugin.
 *
 * @package    Konecte_Modular
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Hacer las tablas responsivas
        $('.konecte-modular-google-sheets-table').each(function() {
            var $table = $(this);
            
            // Si la tabla es muy ancha para su contenedor, agregar clase para scroll horizontal
            if ($table.find('table').width() > $table.width()) {
                $table.addClass('konecte-modular-table-scrollable');
            }
        });
        
        // Ordenar tablas al hacer clic en los encabezados
        $('.konecte-modular-google-sheets-table th').on('click', function() {
            var $th = $(this);
            var $table = $th.closest('table');
            var index = $th.index();
            
            // Determinar la dirección de ordenación
            var direction = $th.hasClass('konecte-modular-sort-asc') ? -1 : 1;
            
            // Eliminar clases de ordenación de todos los encabezados
            $table.find('th').removeClass('konecte-modular-sort-asc konecte-modular-sort-desc');
            
            // Agregar clase de ordenación al encabezado actual
            $th.addClass(direction === 1 ? 'konecte-modular-sort-asc' : 'konecte-modular-sort-desc');
            
            // Ordenar las filas
            var $rows = $table.find('tbody tr').detach().toArray();
            $rows.sort(function(a, b) {
                var aValue = $(a).find('td').eq(index).text();
                var bValue = $(b).find('td').eq(index).text();
                
                // Intentar ordenar como números si es posible
                if (!isNaN(aValue) && !isNaN(bValue)) {
                    return (parseFloat(aValue) - parseFloat(bValue)) * direction;
                }
                
                // De lo contrario, ordenar como texto
                return aValue.localeCompare(bValue) * direction;
            });
            
            // Volver a agregar las filas ordenadas
            $table.find('tbody').append($rows);
        });
        
        // Filtrar tablas
        $('.konecte-modular-filter-input').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            var $table = $(this).closest('.konecte-modular-google-sheets-container').find('table');
            
            $table.find('tbody tr').each(function() {
                var $row = $(this);
                var text = $row.text().toLowerCase();
                $row.toggle(text.indexOf(value) > -1);
            });
        });
    });

})(jQuery); 