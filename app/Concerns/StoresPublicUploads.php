<?php

namespace App\Concerns;

use Illuminate\Http\UploadedFile;
use RuntimeException;

/**
 * Stores files uploaded through the admin area on the public disk.
 */
trait StoresPublicUploads
{
    /**
     * Store an uploaded file on the public disk, failing loudly if it could
     * not be written rather than saving a broken path.
     */
    protected function storeUpload(UploadedFile $upload, string $directory): string
    {
        $path = $upload->store($directory, 'public');

        if ($path === false) {
            throw new RuntimeException("The upload could not be saved to {$directory}.");
        }

        return $path;
    }
}
