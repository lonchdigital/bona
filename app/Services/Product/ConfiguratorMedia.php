<?php

namespace App\Services\Product;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/** Immutable, content-addressed files survive releases and revision restores. */
class ConfiguratorMedia
{
    public function import(string $file): string
    {
        $root = realpath(public_path('assets/door-configurator/v1'));
        $path = realpath(public_path('assets/door-configurator/v1/'.$file));
        if (! $root || ! $path || ! str_starts_with($path, $root.DIRECTORY_SEPARATOR) || ! is_file($path)) {
            throw new RuntimeException('Немає підготовленого зображення: '.$file);
        }
        $info = @getimagesize($path);
        $ext = match ($info['mime'] ?? '') {
            'image/webp' => 'webp', 'image/png' => 'png', 'image/jpeg' => 'jpg', default => null,
        };
        if (! $ext) {
            throw new RuntimeException('Непідтримуване зображення: '.$file);
        }

        return $this->put(file_get_contents($path), $ext);
    }

    public function upload(UploadedFile $file): string
    {
        validator(['image' => $file], ['image' => 'required|image|mimes:jpg,jpeg,png,webp|max:8192|dimensions:min_width=40,min_height=40,max_width=4096,max_height=4096'])->validate();
        $source = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (! $source) {
            throw ValidationException::withMessages(['image' => 'Не вдалося прочитати зображення. Спробуйте інший PNG, WebP або JPEG.']);
        }
        // Re-encode; uploaded metadata and non-image bytes are not published.
        imagepalettetotruecolor($source);
        imagealphablending($source, false);
        imagesavealpha($source, true);
        ob_start();
        imagewebp($source, null, 92);
        $bytes = ob_get_clean();
        imagedestroy($source);
        if (! $bytes) {
            throw new RuntimeException('Не вдалося підготувати зображення.');
        }

        return $this->put($bytes, 'webp');
    }

    public function path(?string $url): ?string
    {
        if (! $url || ! preg_match('~^/storage/(door-configurator/[a-f0-9]{64}\.(webp|png|jpg))$~D', $url, $match)) {
            return null;
        }
        $path = Storage::disk('public')->path($match[1]);

        return is_file($path) ? $path : null;
    }

    private function put(string $bytes, string $extension): string
    {
        $key = 'door-configurator/'.hash('sha256', $bytes).'.'.$extension;
        $disk = Storage::disk('public');
        if (! $disk->exists($key) && ! $disk->put($key, $bytes)) {
            throw new RuntimeException('Не вдалося зберегти файл у shared storage. Перевірте права доступу.');
        }
        if (hash_file('sha256', $disk->path($key)) !== hash('sha256', $bytes)) {
            throw new RuntimeException('Перевірка збереженого зображення не пройшла.');
        }

        return '/storage/'.$key;
    }
}
