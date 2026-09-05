# Análisis de perfil PS4 `18a95ddf`

Auditoría realizada en modo lectura: no se modificó ningún archivo de esta carpeta.

## Hallazgos verificables

- 695 archivos en total. Las extensiones predominantes son `.bin` (153), `.png`
  (147), `.jpg` (57), `.img` (55), `.sfo` (45) y bases de datos `.db` (7).
- La estructura corresponde a un perfil local de PS4: `savedata`,
  `savedata_meta`, `trophy`, `np`, `license`, `webbrowser`, `webkit`, `ime`,
  `mms`, `topmenu`, `sticker` y `username.dat`.
- `savedata` contiene 45 directorios de título, incluidos CUSA, SLUS21004 y
  aplicaciones/homebrew como APOL00004, GOLD00777 y LAPY20006.
- `savedata_meta/user/<TITLE_ID>` aporta iconos PNG y metadatos de los slots;
  aparecen ficheros `.sfo` asociados a copias/slots y nombres legibles como
  `SAVEDATAPGTA5`, `DaysGoneAutoSave`, `SaveGame`, `ProfileData` o
  `BedrockWorld`.

## Implicaciones para GoldHen Manager

1. **Gestor de saves viable.** La app puede descubrir perfiles mediante
   `/user/home/*/savedata/`, listar títulos/slots y mostrar iconos desde
   `/user/home/*/savedata_meta/user/<TITLE_ID>/`.
2. **Respaldo seguro recomendado.** Antes de restaurar, debe descargar y
   conservar juntos los datos del título y sus metadatos; una restauración debe
   crear un respaldo previo, pedir doble confirmación y nunca asumir que el
   perfil sea siempre `18a95ddf`.
3. **No editar binarios a ciegas.** `.bin`, `.img`, `.aes`, `.db` y `.sfo`
   pueden ser datos protegidos, índices o metadatos del sistema. No deben
   exponerse al editor de texto ni alterarse desde una función genérica.
4. **Información potencialmente sensible.** Licencias, perfil, navegación,
   notificaciones y trofeos deben excluirse de exportaciones por defecto.

## Próxima fase segura

Implementar primero una pantalla de solo lectura: detector de perfiles,
lista de títulos, icono, tamaño, fecha FTP y botón **Respaldar**. La restauración
solo debe habilitarse después de comprobar en una PS4 real qué permisos FTP da
GoldHEN para cada ruta y qué conjunto mínimo de archivos necesita cada juego.

## Ideas de funciones a investigar

| Función | Datos observados | Viabilidad | Precaución necesaria |
| --- | --- | --- | --- |
| Detector de perfiles | Directorio de perfil y `username.dat` | Alta | Enumerar `/user/home/*` en vez de fijar un ID. |
| Biblioteca de saves | `savedata/<TITLE_ID>` y 45 títulos detectados | Alta | Solo lectura en la primera versión. |
| Tarjetas de slots | PNG y `.sfo` de `savedata_meta/user/<TITLE_ID>` | Alta | Mostrar metadatos sin modificar `.sfo`. |
| Backup por título | Datos y metadatos del mismo título | Media | ZIP con manifiesto y verificación de espacio. |
| Comparar backups | Tamaño, lista de archivos y fecha FTP | Media | No intentar comparar binarios cifrados. |
| Restauración guiada | Datos de `savedata` | Media/baja | Backup previo obligatorio, doble confirmación y prueba por juego. |
| Lanzador Apollo | Presencia de APOL00004 | Media | Solo accesos/ayuda; no reemplaza funciones de Apollo. |
| Resumen de trofeos | Directorio `trophy` | Baja | Requiere conocer sus bases de datos y preservar privacidad. |
| Limpieza de navegador/medios | `webbrowser`, `webkit`, `mms`, `sticker` | Baja | No habilitar borrado automático por posible pérdida de datos. |
| Inventario de licencias | `license` y `np` | Solo informativa | Nunca editar, exportar ni publicar estos datos. |

## Límites confirmados

La presencia de los archivos demuestra que el perfil dispone de estas áreas,
pero no prueba que el FTP de GoldHEN permita escritura segura sobre todas ellas.
La futura implementación debe comprobar permisos en una consola real, comenzar
por operaciones de lectura y excluir por defecto `license`, `np`, bases de datos,
trofeos, navegador y cualquier archivo cifrado o binario del sistema.
