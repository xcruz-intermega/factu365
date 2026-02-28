<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    /**
     * Process and store an uploaded image: resize, compress, keep original format.
     */
    public function store(UploadedFile $file, string $directory, int $maxWidth, int $quality = 85): string
    {
        $image = Image::read($file);

        // Only downscale, never upscale
        if ($image->width() > $maxWidth) {
            $image->scaleDown(width: $maxWidth);
        }

        $extension = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $path = $directory . '/' . $filename;

        $encoded = $image->encodeByExtension($extension, quality: $quality);

        Storage::disk('local')->put($path, (string) $encoded);

        return $path;
    }
}
