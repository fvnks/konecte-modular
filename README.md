# Konecte Modular - Google Sheets Connector

Plugin modular para WordPress que permite conectar con Google Sheets y mostrar datos mediante shortcodes.

## Características

- Conecta WordPress con hojas de cálculo de Google Sheets
- Muestra datos completos o columnas específicas mediante shortcodes
- Interfaz de administración con pestañas para fácil configuración
- Sistema de actualizaciones automáticas desde GitHub
- Diseño modular para facilitar la adición de nuevas funcionalidades

## Requisitos

- WordPress 5.0 o superior
- PHP 7.0 o superior
- API Key de Google para acceder a Google Sheets

## Instalación

1. Descarga el plugin desde GitHub
2. Sube la carpeta `konecte-modular` al directorio `/wp-content/plugins/`
3. Activa el plugin a través del menú 'Plugins' en WordPress
4. Ve a 'Konecte Modular > Google Sheets' para configurar tu API Key y ID de hoja

## Uso

### Shortcodes Disponibles

#### Mostrar una tabla completa de Google Sheets

```
[google_sheets id="ID_HOJA" range="A1:Z1000" sheet="0"]
```

- `id`: El ID de tu hoja de Google. Opcional si ya lo configuraste en los ajustes.
- `range`: El rango de celdas a mostrar. Por defecto A1:Z1000.
- `sheet`: El índice de la hoja (0 para la primera). Por defecto 0.

#### Mostrar una columna específica

```
[google_sheets_column id="ID_HOJA" range="A1:Z1000" sheet="0" column="A" header="yes" list="yes"]
```

- `id`: El ID de tu hoja de Google. Opcional si ya lo configuraste en los ajustes.
- `range`: El rango de celdas a considerar. Por defecto A1:Z1000.
- `sheet`: El índice de la hoja (0 para la primera). Por defecto 0.
- `column`: La letra de la columna a mostrar (A, B, C, etc.). Por defecto A.
- `header`: Si se debe mostrar la cabecera (yes/no). Por defecto yes.
- `list`: Si se debe mostrar como lista (yes/no). Por defecto yes.

## Configuración

### Google Sheets

1. Ve a la [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuevo proyecto o selecciona uno existente
3. Ve a "APIs y Servicios" > "Biblioteca"
4. Busca "Google Sheets API" y actívala
5. Ve a "APIs y Servicios" > "Credenciales"
6. Haz clic en "Crear credenciales" y selecciona "Clave de API"
7. Copia la API Key generada y pégala en la configuración del plugin

### Hoja de Google

1. Crea una hoja de cálculo en Google Sheets
2. Asegúrate de que la hoja sea pública o accesible mediante enlace:
   - Ve a "Compartir" > "Obtener enlace"
   - Cambia el permiso a "Cualquier persona con el enlace puede ver"
3. Obtén el ID de la hoja de la URL:
   - `https://docs.google.com/spreadsheets/d/ESTE-ES-EL-ID-DE-LA-HOJA/edit`
4. Introduce el ID en la configuración del plugin

## Actualizaciones

El plugin comprueba periódicamente si hay nuevas versiones disponibles en el repositorio de GitHub especificado. Cuando se detecta una nueva versión, WordPress mostrará una notificación de actualización como lo hace con cualquier otro plugin.

### Configuración de Actualizaciones

1. Ve a 'Konecte Modular > Configuración'
2. Configura el usuario y repositorio de GitHub
3. Opcionalmente, introduce un token de acceso para repositorios privados
4. Establece el intervalo de comprobación de actualizaciones

## Estructura del Plugin

El plugin está diseñado con un enfoque modular para facilitar la adición de nuevas características:

```
konecte-modular/
├── admin/                  # Archivos de administración
│   ├── css/                # Estilos de administración
│   ├── js/                 # Scripts de administración
│   └── partials/           # Plantillas de administración
├── includes/               # Clases principales
├── languages/              # Archivos de traducción
├── modules/                # Módulos del plugin
│   ├── google-sheets/      # Funcionalidad de Google Sheets
│   └── updater/            # Sistema de actualizaciones
├── public/                 # Archivos públicos
│   ├── css/                # Estilos públicos
│   └── js/                 # Scripts públicos
├── konecte-modular.php     # Archivo principal del plugin
└── README.md               # Este archivo
```

## Desarrollo

Para añadir nuevas funcionalidades, simplemente crea un nuevo directorio en la carpeta `modules/` siguiendo el patrón establecido por los módulos existentes.

## Licencia

Este plugin está licenciado bajo GPL v2 o posterior.

## Créditos

Desarrollado por [FvNks](https://github.com/fvnks) 