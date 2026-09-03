# GoldHen Manager AJ

Aplicación web local para Termux orientada a administrar una PS4 con GoldHEN mediante FTP. Se ejecuta en el teléfono y abre una interfaz PWA para consultar juegos, explorar archivos, transferir contenido y gestionar mods.

## Funciones

- Biblioteca de juegos: escaneo de títulos, iconos, categorías, DLC, actualizaciones y capturas.
- Explorador FTP: navegación, subida por fragmentos, creación, renombrado, copia, movimiento, eliminación y accesos rápidos.
- Transferencias: cargas FTP de archivos grandes y envío de PKG mediante Remote Package Installer.
- Modding: respaldo e inyección de portadas, procesado de imágenes y galerías locales.
- Game Mods: bóveda de mods de Minecraft e integración AFR para juegos compatibles.
- Ajustes: notificaciones, audio, fondos, intros, tema y tamaño de texto.
- Plugins: sube `.prx`, consulta los instalados y asigna plugins a `[default]` o a CUSA mediante `plugins.ini`.
- Payload Loader: lista payloads locales/remotos y los envía al BinLoader de GoldHEN en el puerto `9090`.

## Requisitos

- Android con Termux.
- Una PS4 con GoldHEN y servidor FTP activo, normalmente en el puerto `2121`.
- Ambos dispositivos conectados a la misma red local.
- Permiso de almacenamiento para Termux.

## Instalación

En Termux, ejecuta un único comando:

```bash
curl -sL https://raw.githubusercontent.com/AJfiles/GoldHenManagerAJ/main/goldhen.sh | bash
```

El instalador solicita acceso al almacenamiento, instala Git, PHP y Termux API, descarga el proyecto en `$HOME/GoldHenManagerAJ`, crea `/sdcard/GoldHenManager/user` y configura el arranque local. La salida usa un spinner: si falla un paso, se muestran las últimas líneas del registro para diagnosticarlo.

Después de finalizar, cierra Termux por completo y vuelve a abrirlo. La aplicación estará disponible en `http://localhost:8080/index.php`.

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

Consulta [GUIA_DE_USO.md](GUIA_DE_USO.md) para un recorrido operativo y [AUDITORIA_TECNICA.txt](AUDITORIA_TECNICA.txt) para el estado de revisión de archivos.

## Limitaciones conocidas

- El FTP de GoldHEN no proporciona telemetría de CPU, GPU o temperatura.
- Una PWA no puede garantizar cargas FTP largas en segundo plano cuando Android la suspende.
- La compatibilidad de rutas y permisos depende de la versión de GoldHEN y del juego.
