# GoldHen Manager AJ v4.0

Aplicación web local para Termux orientada a administrar una PS4 con GoldHEN mediante FTP. Se ejecuta en el teléfono y abre una interfaz PWA para consultar juegos, explorar archivos, transferir contenido y gestionar mods.

GoldHen Manager AJ parte del proyecto GoldHenManager-v3 como base reconocida y lo
extiende con correcciones, módulos y flujos propios. La comparación técnica y el
alcance de esas diferencias están documentados en
[COMPARATIVA_BASE_V3.md](COMPARATIVA_BASE_V3.md).

## Funciones

- Biblioteca de juegos: escaneo de títulos, iconos, categorías, DLC, actualizaciones y capturas.
- Explorador FTP: navegación, subida por fragmentos, creación, renombrado, copia, movimiento, eliminación y accesos rápidos.
- Búsqueda global: localiza módulos, juegos sincronizados, archivos de la carpeta FTP actual, plugins y payloads cargados.
- Respaldo de configuración: exporta/importa ajustes, catálogo Store y recursos personales permitidos mediante ZIP.
- Transferencias: cargas FTP de archivos grandes y envío de PKG mediante Remote Package Installer.
- Modding: respaldo e inyección de portadas, procesado de imágenes y galerías locales.
- Game Mods: bóveda de mods de Minecraft e integración AFR para juegos compatibles.
- Ajustes: notificaciones, perfiles SFX, fuentes, fondos, intros, tamaño de texto, vista vertical y diseño automático/móvil/PC.
- Plugins: sube `.prx`, consulta los instalados y asigna plugins a `[default]` o a CUSA mediante `plugins.ini`.
- Payload Loader: lista payloads locales/remotos y los envía al BinLoader de GoldHEN en el puerto `9090`.
- Ventilador y Actualizaciones FW: selección controlada de payloads compatibles por firmware.
- Store: catálogo de paquetes autorizados y envío directo al instalador compatible de la PS4.

## Requisitos

- Android con Termux.
- Windows: paquete portable de PC en `GoldHenPC-Release-v1.0`; extrae el ZIP y ejecuta `Iniciar-GoldHenManager.bat`.
- Una PS4 con GoldHEN y servidor FTP activo, normalmente en el puerto `2121`.
- Ambos dispositivos conectados a la misma red local.
- Permiso de almacenamiento para Termux.

## Instalación

En Termux, ejecuta un único comando:

```bash
curl -sL https://raw.githubusercontent.com/AJfiles/GoldHenManagerAJ/main/goldhen.sh | bash
```

El instalador solicita acceso al almacenamiento, instala Git, PHP, PHP GD, ZIP/UNZIP y Termux API, descarga el proyecto en `$HOME/GoldHenManagerAJ`, crea `/sdcard/GoldHenManager/user` y configura los comandos locales. La salida usa un spinner silencioso. Si un mirror de Termux está desincronizado, limpia los índices y prueba automáticamente el mirror oficial y un segundo mirror estable antes de detenerse.

Al finalizar, el instalador abre GoldHen Manager automáticamente. Para próximas sesiones basta con escribir `goldhen`; la terminal mostrará la URL exacta cuando el servidor esté listo. El servidor queda en segundo plano y los registros se guardan en `~/.goldhen-server.log`, por lo que la consola sigue disponible. Al abrir Termux de nuevo se ofrece un inicio automático con 15 segundos de espera: Enter abre de inmediato y otra tecla cancela. El servidor principal escucha en la red local para que la PS4 descargue PKG por RPI; solo el endpoint que valida cada archivo `.pkg` los entrega. Usa esta función solo en una Wi-Fi de confianza.

Para evitar incompatibilidades de paquetes, se recomienda Termux desde [F-Droid](https://f-droid.org/packages/com.termux/) o GitHub oficial, no la versión obsoleta de Play Store. En instalaciones posteriores el script detecta componentes ya instalados y omite la actualización de paquetes innecesaria.

Compatibilidad comprobada: PS4 Pro con firmware 9.00 y GoldHEN v2.4b18.10. Otras combinaciones de consola, firmware y GoldHEN pueden requerir rutas o permisos diferentes.

## Uso rápido

1. Conecta la PS4 a la red y habilita GoldHEN/FTP.
2. Abre la aplicación desde Termux o el navegador.
3. Introduce la IP de la PS4 y pulsa conectar o usa Radar.
4. Abre Biblioteca y ejecuta una sincronización.
5. Usa Explorador FTP o Transferencias sólo sobre rutas que conozcas.
6. Antes de cambiar mods o sobrescribir archivos, crea una copia de seguridad.

## Datos locales

Los datos generados por la aplicación se guardan fuera del repositorio en:

```text
/sdcard/GoldHenManager/user/
```

Incluye cachés, portadas, capturas, respaldos y archivos para RPI. La caché se limpia automáticamente cuando un archivo lleva más de 30 días sin uso.

Los plugins incluidos en `plugins/` se pueden subir desde el módulo Plugins. Los payloads que añadas se guardan en `user/payloads/`; los incluidos en `payloads/` aparecen directamente en el módulo. Linux, NanoDNS e Inicio de sesión falso se organizan por categoría.

## Seguridad

La herramienta está pensada para una red local de confianza. No expongas el servidor PHP a Internet ni abras puertos del teléfono hacia redes públicas. Las operaciones de eliminar, mover, inyectar y activar mods afectan datos reales de la consola.

El módulo Plugins realiza una copia local de `plugins.ini` antes de editarlo. Payload Loader ejecuta binarios en la consola: utiliza únicamente payloads que conozcas y entiendas.

## Desarrollo

- Backend: PHP y cURL/FTP.
- Frontend: JavaScript sin compilación, Tailwind CDN y Font Awesome CDN.
- Entrada: `index.php`.
- APIs: `api/`.
- Interfaz modular: `modulos/`.
- Controladores frontend: `js/`.

### Edición portable para Windows

Descarga `GoldHenPC-Release-v1.0.zip` desde Releases, descomprímelo y ejecuta
`Iniciar-GoldHenManager.bat`. El paquete incluye el runtime PHP portable y abre
el navegador al iniciar el servidor. No requiere configurar PHP ni agregarlo al
PATH. PHP puede necesitar Microsoft Visual C++ Redistributable 2015–2022 x86 si
ese componente no está instalado; el paquete indica el enlace oficial. Windows
puede solicitar permiso para permitir RPI en la red privada.

### Aplicación Android

El botón de instalación actual ofrece una PWA; esta sigue necesitando que el
servidor PHP esté ejecutándose en Termux o en un PC. Para generar un APK
autónomo hay que empaquetar también el backend PHP y sus extensiones, o migrar
las API que usan FTP, sockets y archivos a un backend Android nativo. Un simple
envoltorio WebView no sustituye esas API.

Consulta [GUIA_DE_USO.md](GUIA_DE_USO.md) para un recorrido operativo.

## Limitaciones conocidas

- El FTP de GoldHEN no proporciona telemetría de CPU, GPU o temperatura.
- Una PWA no puede garantizar cargas FTP largas en segundo plano cuando Android la suspende.
- La compatibilidad de rutas y permisos depende de la versión de GoldHEN y del juego.

## ⚠️ ADVERTENCIA LEGAL Y DE SEGURIDAD

Este software se proporciona "tal cual", sin garantías y es de código abierto.

El mantenedor "AJ" no se responsabiliza por daños a tu PS4, pérdida de datos o
violación de términos de servicio.

Usar payloads de firmware o Ventilador puede brickear tu consola si no se usan
como debe ser. Úsalo bajo tu propio riesgo.

Se recomienda tener una copia de seguridad del firmware original antes de
aplicar cualquier parche.
