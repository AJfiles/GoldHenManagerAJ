# Análisis de la instantánea PS4 (`ps4/`)

Fecha de revisión: 8 de septiembre de 2026.  
Alcance: lectura de la copia temporal `ps4/data` y `ps4/user` incluida en este proyecto. No se modificó ningún archivo de esa copia.

## Resumen

La instantánea contiene dos zonas muy valiosas para GoldHen Manager AJ:

1. `data/GoldHEN`: configuración, plugins, parches, cheats y estadísticas que GoldHEN expone y que el Manager ya utiliza parcialmente.
2. `user/home/<USER_ID>`: perfiles reales de PS4, partidas por Title ID, metadatos de guardado, trofeos y datos de navegador.

Hay dos perfiles: `18a95ddf` y `18a95de0`. El primero tiene una colección amplia de guardados, incluidos Title IDs de PS4, homebrew y un título de otra plataforma (`SLUS21004`). Esto confirma que el proyecto no debe asumir que todos los identificadores son `CUSA`.

## Estructura revisada

| Ruta | Contenido observado | Posible uso en el Manager |
|---|---|---|
| `data/GoldHEN/cheats` | Cheats administrados por GoldHEN | Catálogo local, búsqueda por Title ID, copia de seguridad y activación guiada. |
| `data/GoldHEN/patches/xml` | Gran colección de XML por CUSA | Compatibilidad de parches por juego, contador y búsqueda desde Biblioteca. |
| `data/GoldHEN/plugins`, `plugins.ini` | PRX y asignaciones existentes | Ya integrado: lectura, ordenamiento y edición conservadora de asignaciones. |
| `data/GoldHEN/stats` | Un `.ini` por Title ID | Base para una futura ficha local de juego: presencia, uso y relación con plugin/parche. |
| `data/pkg` y `user/bgft` | Flujo de tareas/paquetes de consola | Solo diagnóstico y visor de estado; no borrar ni editar automáticamente. |
| `user/home/18a95ddf/savedata` | Guardados agrupados por Title ID | Módulo Saves: listar, exportar, importar y comparar respaldos. |
| `user/home/18a95ddf/savedata_meta` | Metadatos de guardado | Mostrar fecha/tamaño/estado cuando su formato sea verificado. |
| `user/home/*/trophy` | Datos de trofeos por perfil | Futuro visor/respaldo de progreso; debe ser estrictamente de solo lectura al inicio. |
| `user/home/*/webbrowser`, `webkit` | Navegación y caché privada | No incorporar: puede contener datos personales, sesiones o historial. |
| `user/home/*/np`, `license` | Cuenta/red/licencias | Excluir completamente de lectura y exportación de GoldHen Manager. |
| `data/app`, `data/FPKGi`, `data/PS4Xplorer`, `data/itemzflow` | Datos de otras aplicaciones | Posibles detectores de presencia/configuración, sin editar sus bases ni archivos internos. |

## Hallazgos concretos

- En `savedata/18a95ddf` hay decenas de carpetas de guardado identificadas por Title ID. Se observan `CUSA`, `APOL`, `GOLD`, `LAPY`, `SAAT` y `SLUS`, por lo que el catálogo, Store, Biblioteca y Saves deben aceptar IDs alfanuméricos flexibles.
- Cada título puede contener el archivo de datos, copias de seguridad `sce_bu_*` y acompañantes `sdimg_*`. Un respaldo correcto debe conservar la carpeta completa; exportar solo un `.bin` puede dejar un guardado incompleto.
- La colección `data/GoldHEN/patches/xml` es suficientemente extensa para habilitar una vista “Parches disponibles para este juego” sin descargar una base externa.
- `data/GoldHEN/stats` tiene correspondencia visible con muchos de los Title IDs de la Biblioteca. Antes de mostrar valores al usuario hay que documentar el formato de los `.ini` y no inferir métricas que no estén confirmadas.
- Los directorios de sistema, cuenta, licencias y navegador contienen material sensible. Su mera presencia no equivale a permiso para exportarlo, mostrarlo o sincronizarlo.

## Funciones recomendadas

### Prioridad alta: Gestor de guardados seguro

1. Detectar perfiles disponibles en `/user/home/` y permitir elegir uno sin mostrar datos de cuenta.
2. Listar carpetas de `savedata` por Title ID, enlazándolas con Biblioteca cuando exista coincidencia.
3. Mostrar cantidad de archivos, peso total y fecha de modificación de cada carpeta.
4. Exportar **la carpeta completa** a ZIP con una estructura estable: `saves/<user-id>/<title-id>/...`.
5. Importar con validación: seleccionar perfil y Title ID, crear una copia local previa y pedir confirmación fuerte antes de reemplazar.
6. Marcar como experimental cualquier restauración hasta verificar el método para cada firmware y tipo de guardado.

Regla de seguridad: nunca restaurar archivos de save mientras el juego correspondiente esté abierto; nunca tocar `license`, `np`, `account.dat`, `token.dat` ni contenido de `webbrowser`.

### Prioridad alta: Centro GoldHEN por título

En la ficha de cada elemento de Biblioteca se pueden reunir:

- plugins asignados desde `plugins.ini`;
- PRX disponibles localmente;
- parches XML detectados en `patches/xml/<TITLE_ID>.xml`;
- cheats relacionados en `cheats`;
- existencia de save y su última fecha;
- información de Store, si el Title ID existe también en el catálogo autorizado.

La interfaz debe distinguir con claridad **detectado**, **disponible**, **activo** y **recomendado**. Detectar un archivo no significa que sea seguro aplicarlo.

### Prioridad media: Auditoría y respaldo de GoldHEN

Crear un respaldo independiente y reversible de:

- `data/GoldHEN/plugins.ini`;
- `data/GoldHEN/plugins/`;
- `data/GoldHEN/cheats/`;
- `data/GoldHEN/patches/`;
- configuraciones que GoldHen Manager haya creado.

Debe generar manifiesto con fecha, hashes y lista de archivos. La restauración debe mostrar un diff y permitir escoger categorías, no sobrescribir todo sin revisión.

### Prioridad media: Diagnóstico de almacenamiento y descargas

El Manager puede ofrecer una vista informativa de `data/pkg`, `user/bgft` y espacio libre reportado por FTP:

- tareas o paquetes en espera detectados;
- tamaño aproximado;
- advertencia de espacio insuficiente;
- limpieza manual con confirmación, solo sobre rutas documentadas y seleccionadas por el usuario.

No se recomienda automatizar eliminaciones de `bgft`, `pkg`, `app_tmp`, `temp` o directorios de sistema: una detección errónea puede romper una instalación o una descarga de PS4.

### Investigación posterior

Antes de implementar escritura real conviene capturar muestras pequeñas y anónimas de:

- un directorio completo de `savedata` y su `savedata_meta` equivalente;
- un archivo representativo de `data/GoldHEN/stats/*.ini`;
- un XML de `patches/xml`;
- estructura de `cheats`;
- respuesta FTP/privilegios reales para cada ruta.

Con esas muestras se puede diseñar un API con validación de ruta, perfiles y Title IDs, evitando que el navegador reciba acceso libre a cualquier ruta de la consola.

## Rutas no recomendadas para GoldHen Manager

Las siguientes rutas mencionadas no estaban presentes en la copia o no son necesarias para el alcance actual: `/adm`, `/app_tmp`, `/dev`, `/eap_user`, `/eap_vsh`, `/hdd`, `/host`, `/hostapp`, `/mnt`, `/preinst`, `/preinst2`, `/system`, `/system_data`, `/system_ex`, `/system_tmp`, `/update` y `/usb`.

Incluso si se aportan después, no se deben explorar ni modificar por defecto. Las rutas de sistema, dispositivos, actualizaciones, montajes y preinstalación pertenecen al sistema operativo de PS4; una función de usuario no debe operar sobre ellas salvo investigación específica, confirmación explícita y un modo solo lectura.

## Conclusión

La mayor oportunidad real es un módulo de **Saves por perfil**, unido a Biblioteca y al centro GoldHEN por Title ID. La segunda es un **respaldo verificable de configuración GoldHEN**. Ambas aportan valor a cualquier consola sin exponer datos de cuenta ni depender de rutas de sistema peligrosas.

La instantánea se mantuvo sin cambios y debe eliminarse del repositorio antes de publicar: contiene información de uso y potencialmente datos privados de una consola real.
