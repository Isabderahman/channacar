<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CarImageController extends Controller
{
    /**
     * Largest accepted upload, in kilobytes. Must stay below the PHP
     * upload_max_filesize / post_max_size set in Backend/docker/php.ini —
     * PHP drops oversized files before Laravel ever sees them, which surfaces
     * as a confusing "The image failed to upload." validation error.
     */
    private const MAX_UPLOAD_KB = 51200; // 50 MB

    /** Reject absurd resolutions before GD allocates ~4 bytes per pixel. */
    private const MAX_PIXELS = 50_000_000; // ~50 MP

    /** Gallery images are never displayed larger than this; downscale to keep files small. */
    private const MAX_DIMENSION = 2560;

    public function store(Request $request, Car $car): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:'.self::MAX_UPLOAD_KB],
            'is_thumbnail' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], [
            'image.required' => 'No image reached the server. It is usually too large for the upload limit — try a file under '.(int) (self::MAX_UPLOAD_KB / 1024).' MB.',
            'image.image' => 'Unsupported image format. Use JPEG, PNG or WebP (iPhone HEIC photos must be exported to JPEG first).',
            'image.max' => 'The image is larger than '.(int) (self::MAX_UPLOAD_KB / 1024).' MB.',
        ]);

        $isThumbnail = (bool) ($validated['is_thumbnail'] ?? false);

        if ($isThumbnail) {
            $car->images()->update(['is_thumbnail' => false]);
        }

        $image = $car->images()->create([
            'path' => $this->storeAsWebp($request->file('image'), 'cars'),
            'is_thumbnail' => $isThumbnail,
            'sort_order' => $validated['sort_order'] ?? (($car->images()->max('sort_order') ?? -1) + 1),
        ]);

        return response()->json([
            'data' => $image,
        ], 201);
    }

    public function destroy(CarImage $image): JsonResponse
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();

        return response()->json([
            'message' => 'Image deleted successfully.',
        ]);
    }

    /**
     * Convert an uploaded image to WebP and store it on the public disk.
     * Returns the stored relative path (e.g. "cars/abc123.webp").
     *
     * Anything the admin can trigger by picking the wrong file is reported as a
     * 422 with a fixable message rather than an opaque 500.
     */
    private function storeAsWebp(UploadedFile $file, string $directory, int $quality = 82): string
    {
        $dimensions = @getimagesize($file->getRealPath());

        if ($dimensions === false) {
            $this->fail('This file could not be read as an image. Use JPEG, PNG or WebP (iPhone HEIC photos must be exported to JPEG first).');
        }

        if ($dimensions[0] * $dimensions[1] > self::MAX_PIXELS) {
            $this->fail('This image resolution is too large to process. Resize it below '.(int) (self::MAX_PIXELS / 1_000_000).' megapixels and try again.');
        }

        $source = @imagecreatefromstring(file_get_contents($file->getRealPath()));

        if ($source === false) {
            $this->fail('This image could not be decoded. Re-save it as JPEG, PNG or WebP and try again.');
        }

        // Preserve transparency (PNG/GIF) when re-encoding.
        imagepalettetotruecolor($source);
        imagealphablending($source, false);
        imagesavealpha($source, true);

        $source = $this->applyExifOrientation($source, $file);
        $source = $this->downscale($source, self::MAX_DIMENSION);

        ob_start();
        $encoded = imagewebp($source, null, $quality);
        $contents = ob_get_clean();
        imagedestroy($source);

        if ($encoded === false || $contents === false || $contents === '') {
            $this->fail('The image could not be converted to WebP. Try a different file.');
        }

        $path = $directory.'/'.Str::uuid()->toString().'.webp';
        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    /**
     * Phone cameras store rotation in EXIF instead of rotating the pixels, so a
     * portrait photo lands sideways in the gallery once GD strips the metadata.
     */
    private function applyExifOrientation(\GdImage $image, UploadedFile $file): \GdImage
    {
        if (! function_exists('exif_read_data') || ! in_array(strtolower((string) $file->getClientOriginalExtension()), ['jpg', 'jpeg'], true)) {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());
        $orientation = (int) ($exif['Orientation'] ?? 0);

        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        if ($rotated === false) {
            return $image;
        }

        imagealphablending($rotated, false);
        imagesavealpha($rotated, true);
        imagedestroy($image);

        return $rotated;
    }

    /** Shrink oversized uploads so stored WebP files stay small; smaller images pass through untouched. */
    private function downscale(\GdImage $image, int $maxDimension): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $longest = max($width, $height);

        if ($longest <= $maxDimension) {
            return $image;
        }

        $ratio = $maxDimension / $longest;
        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));

        if (! imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height)) {
            imagedestroy($resized);

            return $image;
        }

        imagedestroy($image);

        return $resized;
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['image' => $message]);
    }
}
