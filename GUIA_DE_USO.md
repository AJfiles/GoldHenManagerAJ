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

- Tema: oscuro, claro o automático.
- Tamaño de texto: de 85% a 130%.
- Mantener pulsado botones o tarjetas produce una vibración breve en Android compatible.

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

Si falla una operación, conserva el mensaje mostrado y verifica IP, puerto, red Wi-Fi, GoldHEN y permisos de almacenamiento de Termux.
