<?php
/** Utilidades compartidas para cachés locales de GoldHen Manager. */
function limpiar_cache_antigua($directorio, $dias = 30) {
    if (!is_dir($directorio)) return;

    $marca = rtrim($directorio, '/\\') . '/.last_cleanup';
    $ahora = time();
    if (is_file($marca) && ($ahora - filemtime($marca)) < 86400) return;

    $limite = $ahora - ($dias * 86400);
    try {
        $iterador = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directorio, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterador as $archivo) {
            if ($archivo->isFile() && $archivo->getFilename() !== '.nomedia' && $archivo->getMTime() < $limite) {
                @unlink($archivo->getPathname());
            }
        }
    } catch (UnexpectedValueException $e) {
        return;
    }
    @touch($marca);
}
?>
