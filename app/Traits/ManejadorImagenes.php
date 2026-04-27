<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;

trait ManejadorImagenes
{
    /**
     * Guarda un archivo de imagen en public/img/{subdirectorio}/
     * y retorna la ruta relativa al public/.
     *
     * No depende de storage:link — funciona en cualquier hosting compartido.
     */
    protected function guardarImagen(UploadedFile $archivo, string $subdirectorio): string
    {
        $directorio = public_path("img/{$subdirectorio}");

        if (!is_dir($directorio)) {
            mkdir($directorio, 0775, true);
        }

        $nombreArchivo = time() . '_' . uniqid() . '.' . $archivo->getClientOriginalExtension();
        $archivo->move($directorio, $nombreArchivo);

        return "img/{$subdirectorio}/{$nombreArchivo}";
    }

    /**
     * Elimina un archivo de imagen dado su ruta relativa al public/.
     * Si la ruta es nula o el archivo no existe, no hace nada.
     */
    protected function eliminarImagen(?string $rutaRelativa): void
    {
        if (!$rutaRelativa) {
            return;
        }

        $rutaAbsoluta = public_path($rutaRelativa);

        if (file_exists($rutaAbsoluta)) {
            unlink($rutaAbsoluta);
        }
    }
}