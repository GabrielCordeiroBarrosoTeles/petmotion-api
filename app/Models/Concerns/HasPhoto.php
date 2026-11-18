<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Foto guardada em `photo_path`, exposta como `photo_url`:
 * - link externo (http…) vai como está;
 * - `seed/…` são as fotos de demonstração versionadas em public/seed;
 * - o resto é upload no disco public (storage).
 */
trait HasPhoto
{
    public function getPhotoUrlAttribute(): ?string
    {
        $path = $this->photo_path;

        return match (true) {
            ! $path => null,
            str_starts_with($path, 'http') => $path,
            str_starts_with($path, 'seed/') => url($path),
            default => url(Storage::url($path)),
        };
    }

    /** Só uploads são apagados do disco (link externo e foto de demonstração não). */
    public function hasStoredPhoto(): bool
    {
        return $this->photo_path && ! str_starts_with($this->photo_path, 'http') && ! str_starts_with($this->photo_path, 'seed/');
    }
}
