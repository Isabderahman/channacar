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
use RuntimeException;

class CarImageController extends Controller
{
    public function store(Request $request, Car $car): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:8192'],
            'is_thumbnail' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
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
     */
    private function storeAsWebp(UploadedFile $file, string $directory, int $quality = 82): string
    {
        $source = @imagecreatefromstring(file_get_contents($file->getRealPath()));

        if ($source === false) {
            throw new RuntimeException('Unable to read the uploaded image for WebP conversion.');
        }

        // Preserve transparency (PNG/GIF) when re-encoding.
        imagepalettetotruecolor($source);
        imagealphablending($source, false);
        imagesavealpha($source, true);

        ob_start();
        imagewebp($source, null, $quality);
        $contents = ob_get_clean();
        imagedestroy($source);

        if ($contents === false || $contents === '') {
            throw new RuntimeException('Failed to encode the image as WebP.');
        }

        $path = $directory.'/'.Str::uuid()->toString().'.webp';
        Storage::disk('public')->put($path, $contents);

        return $path;
    }
}
