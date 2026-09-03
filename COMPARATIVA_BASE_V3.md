# Comparativa técnica: GoldHen Manager AJ 3.2 frente a GoldHenManager-v3

Fecha de análisis: septiembre de 2026. proyecto `GoldHenManager-v3-main`

## Alcance y método

Se compararon 34 archivos que existen en ambos proyectos mediante su contenido
SHA-256: 29 han cambiado y 5 permanecen idénticos. También se revisaron las
rutas exclusivas de cada árbol. Este documento describe procedencia y cambios;
no acusa copia de futuras funciones por coincidencias de ideas generales. Para
atribuir una implementación futura habría que contrastar su código, fecha e
historial de commits.

## Elementos exclusivos de AJ

- Instalación y documentación: `goldhen.sh`, `README.md`, `GUIA_DE_USO.md`,
  `MEJORAS_IMPLEMENTADAS.txt` y `AUDITORIA_TECNICA.txt`.
- APIs: `cache_helpers.php`, `payload_api.php`, `plugins_api.php` y
  `update_api.php`.
- Módulos y controladores: `payloads.php`/`payloads.js` y
  `plugins.php`/`plugins.js`.
- Recursos propios: payloads Linux, NanoDNS y Fake Signin; catálogo local de
  plugins `.prx` y sus sumas de verificación.
- `store.php` está presente como prototipo independiente, no integrado al
  launcher y pendiente de rediseño seguro como catálogo de contenido autorizado.

## Elementos que AJ retiró de la base

- `instalar.sh` y `actualizar.sh`: sustituidos por el instalador único
  `goldhen.sh`.
- `api/tech_info_biblioteca.php`: el cálculo recursivo del peso de juegos se
  retiró porque era lento e impreciso por FTP.
- `modulos/Jsjdjfbd`: archivo residual eliminado.

## Cambios funcionales sobre archivos compartidos

| Área | Cambios de AJ frente a la base |
| --- | --- |
| Núcleo y PWA | `index.php`, `manifest.json` y `sw.js` actualizan identidad AJ 3.2, módulos nuevos y Service Worker de red directa. |
| Biblioteca | Caché con rutas absolutas, progreso detallado de sincronización, ficha cerrable, carátulas/galería y retiro del cálculo de tamaño. |
| Explorador y transferencias | Mejoras de interfaz, operaciones FTP, cachés y métricas de transferencia. |
| Modding y mods | Corrección de llamada inexistente, mejoras de portadas, subida por chunks e índice de mods. |
| APIs PHP | Normalización de rutas `__DIR__`, manejo de caché, validaciones de rutas y ajustes FTP. |
| Ajustes e interfaz | Tamaño de texto, PWA discreta, actualización segura, modales de fondos/intros y créditos AJ. |
| Intros | Versionado visual 3.2 y textos dinámicos coherentes. |

## Funciones AJ que no existían en la base

### Gestor de plugins

Lista PRX locales y remotos, interpreta `plugins.ini`, asigna plugins a
`[default]` o a una sección CUSA, realiza copia local previa y valida rutas
`/data/GoldHEN/plugins/*.prx`. La edición evita duplicados y mantiene las
líneas ajenas de `plugins.ini`.

### Payload Sender

Organiza payloads locales/remotos en General, Linux y Especial; admite `.bin`,
`.payload` y `.elf` y los envía sin transformación al BinLoader TCP 9090. La
compatibilidad del payload sigue dependiendo de GoldHEN, firmware y memoria.

### Instalador Termux

`goldhen.sh` instala dependencias disponibles, usa clonación superficial,
spinner nativo y la ruta `$HOME/GoldHenManagerAJ`. No instala `php-zip`, ya que
no es un paquete existente separado en los repositorios Termux actuales.

## Limitaciones y decisiones

- No se implementan transferencias largas FTP en segundo plano mediante
  Service Worker: Android puede suspenderlas y dañar archivos.
- El FTP de GoldHEN no proporciona telemetría fiable de CPU/GPU/temperatura.
- Una tienda debe contener únicamente enlaces a paquetes y contenido para cuya
  distribución el administrador tenga autorización. El prototipo `store.php`
  actual incluye proxy remoto y enlaces de terceros; no debe publicarse ni
  ampliarse así.
