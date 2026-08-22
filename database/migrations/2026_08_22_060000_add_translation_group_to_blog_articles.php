<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Serp Agent sends each language as its own delivery, tied together by a
 * translation group. An article here is a single record holding every
 * language, so the group is what tells a Russian delivery which article it
 * belongs to instead of becoming a second one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_articles', function (Blueprint $table) {
            $table->string('translation_group_id', 191)->nullable()->after('external_id');
            $table->index('translation_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('blog_articles', function (Blueprint $table) {
            $table->dropIndex(['translation_group_id']);
            $table->dropColumn('translation_group_id');
        });
    }
};
