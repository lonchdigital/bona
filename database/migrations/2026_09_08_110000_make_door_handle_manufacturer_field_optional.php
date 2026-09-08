<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The accessories product type is shared by door handles and mouldings.
     * Keep the handle manufacturer field attached (and available as a filter),
     * but do not require an unrelated moulding to have a handle manufacturer.
     */
    public function up(): void
    {
        $this->matchingFieldIds()->each(
            fn (int $id) => DB::table('product_fields')
                ->where('id', $id)
                ->update(['is_mandatory' => false])
        );
    }

    public function down(): void
    {
        $this->matchingFieldIds()->each(
            fn (int $id) => DB::table('product_fields')
                ->where('id', $id)
                ->update(['is_mandatory' => true])
        );
    }

    private function matchingFieldIds(): Collection
    {
        $expectedNames = [
            'виробник дверних ручок',
            'производитель дверных ручек',
        ];

        return DB::table('product_fields')
            ->select(['id', 'field_name'])
            ->orderBy('id')
            ->get()
            ->filter(function (object $field) use ($expectedNames): bool {
                $names = json_decode((string) $field->field_name, true);

                if (! is_array($names)) {
                    return false;
                }

                return collect($names)->contains(
                    fn (mixed $name): bool => in_array(
                        mb_strtolower(trim((string) $name)),
                        $expectedNames,
                        true,
                    )
                );
            })
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id);
    }
};
