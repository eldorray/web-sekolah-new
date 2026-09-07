<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Stores uploaded files and removes the one they replace.
 *
 * ponytail: no resizing or WebP conversion — files are kept as uploaded and
 * capped by validation. Add an image pipeline here when page weight matters.
 */
class FileStore
{
    public function putPublic(UploadedFile $file, string $directory, ?string $replacing = null): string
    {
        $path = $file->store($directory, 'public');

        if ($path === false) {
            throw new RuntimeException('Berkas gagal disimpan.');
        }

        $this->deletePublic($replacing);

        return $path;
    }

    public function putPrivate(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'local');

        if ($path === false) {
            throw new RuntimeException('Berkas gagal disimpan.');
        }

        return $path;
    }

    public function deletePublic(?string $path): void
    {
        if ($path !== null && $path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
