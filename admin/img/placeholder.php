<?php
/**
 * Script para generar imágenes de marcador de posición para el plugin Konecte Modular
 * Este archivo debe ser ejecutado una vez para generar las imágenes necesarias.
 */

// Comprobar si estamos en WordPress
if (!defined('ABSPATH')) {
    die('Acceso directo no permitido');
}

// Función para generar una imagen simple con texto
function generate_image_with_text($width, $height, $text, $filename) {
    // Crear la imagen
    $image = imagecreatetruecolor($width, $height);
    
    // Definir colores
    $bg_color = imagecolorallocate($image, 46, 139, 87); // Verde mar medio
    $text_color = imagecolorallocate($image, 255, 255, 255); // Blanco
    
    // Rellenar fondo
    imagefill($image, 0, 0, $bg_color);
    
    // Texto centrado
    $font_size = min($width, $height) / 10;
    $box = imagettfbbox($font_size, 0, 'arial', $text);
    $text_width = abs($box[4] - $box[0]);
    $text_height = abs($box[5] - $box[1]);
    $x = ($width - $text_width) / 2;
    $y = ($height - $text_height) / 2 + $text_height;
    
    // Añadir texto
    imagettftext($image, $font_size, 0, $x, $y, $text_color, 'arial', $text);
    
    // Guardar la imagen
    imagepng($image, $filename);
    
    // Liberar memoria
    imagedestroy($image);
    
    return true;
}

// Directorio actual
$dir = dirname(__FILE__);

// Generar iconos del plugin
generate_image_with_text(128, 128, 'Konecte Modular', $dir . '/konecte-modular-128x128.png');
generate_image_with_text(256, 256, 'Konecte Modular', $dir . '/konecte-modular-256x256.png');

// Generar banners del plugin
generate_image_with_text(772, 250, 'Konecte Modular', $dir . '/konecte-modular-banner-772x250.jpg');
generate_image_with_text(1544, 500, 'Konecte Modular', $dir . '/konecte-modular-banner-1544x500.jpg');

echo "Imágenes generadas con éxito."; 