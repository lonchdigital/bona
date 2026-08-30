<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The faqs table holds every question twice: sixteen rows for eight questions.
 * That is why the home page renders sixteen accordion items and its FAQPage
 * markup listed each question twice.
 *
 * Rows that are byte for byte the same question and answer on the same page
 * are collapsed to the one with the lowest id. Anything that differs, even by
 * a character, is left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        $keep = [];
        $remove = [];

        foreach (DB::table('faqs')->orderBy('id')->get() as $row) {
            $fingerprint = implode('|', [
                $row->page_type ?? '',
                $row->question ?? '',
                $row->answer ?? '',
            ]);

            if (isset($keep[$fingerprint])) {
                $remove[] = $row->id;

                continue;
            }

            $keep[$fingerprint] = $row->id;
        }

        if ($remove) {
            DB::table('faqs')->whereIn('id', $remove)->delete();
        }
    }

    /**
     * The duplicates carried no information of their own, so there is nothing
     * to restore.
     */
    public function down(): void {}
};
