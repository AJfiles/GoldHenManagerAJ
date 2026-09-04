# Guía de uso y pruebas

## Antes de empezar

1. Haz una copia de seguridad de datos importantes de la PS4.
2. Conecta teléfono y PS4 a la misma Wi-Fi.
3. Activa GoldHEN y confirma que el servidor FTP responde en el puerto `2121`.
4. En Termux, ejecuta `bash goldhen.sh` desde `$HOME/GoldHenManagerAJ`.

## Conectar la consola

1. Abre `http://localhost:8080/index.php`.
2. Escribe la IP de la PS4 y el puerto FTP.
3. Pulsa el icono de enlace. El indicador debe cambiar a **Conectado**.
4. Si no conoces la IP, usa Radar; sólo debe utilizarse en tu red local.

## Biblioteca

1. Entra en Biblioteca y pulsa sincronizar.
2. El modal informa de conexión, lectura de títulos, descarga de metadatos y actualización de caché.
3. Espera al mensaje final antes de salir del módulo.
4. Usa filtros, búsqueda y vistas para localizar un juego.

## Explorador FTP

- Usa rutas conocidas y evita borrar directorios de sistema.
- Mantén pulsado un elemento para acciones adicionales cuando la interfaz lo permita.
- Para archivos grandes usa la subida por fragmentos y no cierres la PWA durante la operación.
- Confirma visualmente la ruta de destino antes de sobrescribir un archivo.

## Transferencias

1. Selecciona uno o más archivos.
2. Escoge la carpeta de destino mediante el explorador integrado.
3. Inicia la transferencia.
4. Revisa porcentaje, velocidad media, estimación y tiempo transcurrido.
5. Usa Cancelar sólo si es necesario; puede quedar un archivo parcial en la consola.

## Modding y Game Mods

- Inyecta portadas únicamente después de verificar el CUSA del juego.
- Para AFR, realiza primero el backup de plugins; después selecciona el ZIP con **Instalar Plugins AFR**.
- Para Minecraft, espera a que termine la subida y se guarde `index.json` antes de activar el mod.
- Reinicia el juego cuando la interfaz lo indique.

## Ajustes y accesibilidad

- Tamaño de texto: de 85% a 130%.
- Mantener pulsado botones o tarjetas produce una vibración breve en Android compatible.
- Los fondos y las intros se eligen desde ventanas emergentes para no quedar
  ocultos detrás del contenido.

## Administración privada de Store

1. Como mantenedor, abre una nueva sesión de Termux y escribe `store-admin`.
2. Se abre automáticamente el panel local `http://127.0.0.1:8081`; no uses
   esa dirección como enlace público ni la compartas.
3. Añade o edita los metadatos. El ID puede ser CUSA, SLUS/SLES, una aplicación
   o homebrew; no está limitado a CUSA. Usa una URL directa autorizada que
   termine en `.pkg`, y opcionalmente una carátula JPG/PNG/WebP.
4. Update y DLC son opcionales: déjalos vacíos o escribe `No aplica` cuando no
   correspondan.
5. Confirma que tienes autorización de distribución y guarda el elemento.
6. Pulsa **Descargar cambios ZIP**. Contiene el catálogo, las carátulas
   modificadas y `store-changes.json`, que indica las carátulas eliminadas.
7. Sube el contenido a las mismas rutas del repositorio. Los usuarios solo
   abren Store y pulsan **Actualizar** para consultar el nuevo catálogo.

La tienda no acepta enlaces de páginas intermedias ni extrae enlaces de
servicios de alojamiento: usa un enlace directo a un paquete autorizado.

## Prueba de aceptación en Termux

```bash
cd $HOME/GoldHenManagerAJ
bash goldhen.sh
```

Comprueba en este orden:

1. La página abre en localhost sin pantalla en blanco.
2. La conexión FTP responde con una PS4 disponible.
3. Biblioteca sincroniza al menos un título o devuelve un error controlado.
4. Explorador lista la raíz FTP.
5. Una transferencia pequeña muestra métricas y termina correctamente.
6. Ajustes conserva tema y tamaño tras recargar la página.
7. En Ajustes, usa **Verificar** para consultar actualizaciones y **Actualizar**
   sólo cuando no tengas cambios locales pendientes.
8. En Payloads, abre Linux o Especial, confirma el envío y comprueba que
   BinLoader esté disponible en el puerto 9090.
9. Ejecuta `store-admin`, guarda un elemento de prueba autorizado y verifica
   que el ZIP contiene `store/data/catalogo.json` y `store-changes.json`.

Si falla una operación, conserva el mensaje mostrado y verifica IP, puerto, red Wi-Fi, GoldHEN y permisos de almacenamiento de Termux.
