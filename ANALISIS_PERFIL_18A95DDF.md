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
