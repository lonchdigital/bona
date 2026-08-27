<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * How strongly a slide's image is darkened, as a percentage.
     *
     * Zero leaves every existing slide exactly as it looks now, so this can go
     * live before anyone has touched the new control.
     */
    public function up(): void
    {
        Schema::table('home_page_slides', function (Blueprint $table) {
            $table->unsignedTinyInteger('overlay_opacity')
                ->default(0)
                ->after('slide_image_path_mobile');
        });
    }

    public function down(): void
    {
        Schema::table('home_page_slides', function (Blueprint $table) {
            $table->dropColumn('overlay_opacity');
        });
    }
};
