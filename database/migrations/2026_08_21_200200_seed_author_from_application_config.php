<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The site already knew one author, kept as three loose rows in
 * application_configs and rendered under every blog article. This lifts that
 * data into the authors table so the author gets a page of their own, and
 * leaves the original rows untouched.
 */
return new class extends Migration
{
    private const SLUG = 'oksana-honchar';

    public function up(): void
    {
        if (DB::table('authors')->where('slug', self::SLUG)->exists()) {
            return;
        }

        $config = DB::table('application_configs')
            ->whereIn('config_name', ['authorName', 'authorDescription', 'authorAvatar'])
            ->pluck('config_data', 'config_name');

        $name = $this->decode($config['authorName'] ?? null);

        if (! $name) {
            // Nothing worth carrying over; the admin panel can create the
            // author from scratch.
            return;
        }

        $jobTitle = $this->decode($config['authorDescription'] ?? null);
        $photoPath = $this->decodeScalar($config['authorAvatar'] ?? null);

        DB::table('authors')->insert([
            'creator_id' => DB::table('users')->where('role_id', 1)->orderBy('id')->value('id'),
            'slug' => self::SLUG,
            'name' => json_encode($name, JSON_UNESCAPED_UNICODE),
            'job_title' => $jobTitle ? json_encode($jobTitle, JSON_UNESCAPED_UNICODE) : null,
            'short_description' => $jobTitle ? json_encode($jobTitle, JSON_UNESCAPED_UNICODE) : null,
            'photo_path' => $photoPath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('authors')->where('slug', self::SLUG)->delete();
    }

    /**
     * config_data is a json column, so the value arrives either already decoded
     * or as a json string depending on the driver.
     */
    private function decode(mixed $value): ?array
    {
        if (is_array($value)) {
            return $value ?: null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? ($decoded ?: null) : null;
        }

        return null;
    }

    private function decodeScalar(mixed $value): ?string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (is_string($decoded)) {
                return $decoded;
            }

            return $value !== '' ? $value : null;
        }

        return null;
    }
};
