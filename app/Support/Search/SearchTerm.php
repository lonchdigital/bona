<?php

namespace App\Support\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SearchTerm
{
    public static function normalize(string $query): string
    {
        return Str::of($query)
            ->lower()
            ->replaceMatches('/[^\p{L}\p{N}-]+/u', ' ')
            ->squish()
            ->limit(160, '')
            ->toString();
    }

    /** @return array<int, string> */
    public static function tokens(string $query): array
    {
        return collect(preg_split('/\s+/u', self::normalize($query), -1, PREG_SPLIT_NO_EMPTY))
            ->filter(fn (string $token) => mb_strlen($token) >= 3)
            ->unique()
            ->take(8)
            ->values()
            ->all();
    }

    /**
     * Keep the full word, then allow its last one or two letters to vary.
     * This deliberately small Ukrainian/Russian morphology rule makes
     * "панелі" match "панель" and "ручки" match "ручка" without turning a
     * product search into an overly broad fuzzy search.
     *
     * @return array<int, string>
     */
    public static function variants(string $token): array
    {
        $variants = [$token];
        $length = mb_strlen($token);

        if ($length >= 5) {
            $variants[] = mb_substr($token, 0, -1);
        }

        if ($length >= 6) {
            $variants[] = mb_substr($token, 0, -2);
        }

        return array_values(array_unique(array_filter(
            $variants,
            fn (string $variant) => mb_strlen($variant) >= 4
        )));
    }

    public static function applyToProducts(Builder $query, string $search): Builder
    {
        $rawSearch = trim($search);
        $tokens = self::tokens($search);

        $query->where(function (Builder $searchQuery) use ($rawSearch, $tokens) {
            $rawPattern = '%'.$rawSearch.'%';
            $upperPattern = '%'.mb_strtoupper($rawSearch).'%';

            $searchQuery->where(function (Builder $phraseQuery) use ($rawPattern, $upperPattern) {
                $phraseQuery
                    ->where('name', 'like', $rawPattern)
                    ->orWhere('sku', 'like', $rawPattern)
                    ->orWhereRaw('UPPER(name) LIKE ?', [$upperPattern])
                    ->orWhereRaw('UPPER(sku) LIKE ?', [$upperPattern]);
            });

            if ($tokens === []) {
                return;
            }

            $searchQuery->orWhere(function (Builder $tokensQuery) use ($tokens) {
                foreach ($tokens as $token) {
                    $tokensQuery->where(function (Builder $tokenQuery) use ($token) {
                        foreach (self::caseVariants($token) as $variant) {
                            $pattern = '%'.$variant.'%';

                            $tokenQuery
                                ->orWhere('name->uk', 'like', $pattern)
                                ->orWhere('name->ru', 'like', $pattern)
                                ->orWhere('sku', 'like', $pattern)
                                ->orWhereHas('brand', fn (Builder $relation) => self::whereTranslatedLike($relation, 'name', $pattern))
                                ->orWhereHas('productType', fn (Builder $relation) => self::whereTranslatedLike($relation, 'name', $pattern))
                                ->orWhereHas('productTypes', fn (Builder $relation) => self::whereTranslatedLike($relation, 'name', $pattern))
                                ->orWhereHas('categories', fn (Builder $relation) => self::whereTranslatedLike($relation, 'name', $pattern));
                        }
                    });
                }
            });
        });

        return $query;
    }

    /** @param array<int, string> $columns */
    public static function applyToTranslatedColumns(Builder $query, string $search, array $columns): Builder
    {
        $rawSearch = trim($search);
        $tokens = self::tokens($search);

        $query->where(function (Builder $searchQuery) use ($rawSearch, $tokens, $columns) {
            $searchQuery->where(function (Builder $phraseQuery) use ($rawSearch, $columns) {
                foreach ($columns as $column) {
                    $phraseQuery
                        ->orWhere($column, 'like', '%'.$rawSearch.'%')
                        ->orWhereRaw('UPPER('.$column.') LIKE ?', ['%'.mb_strtoupper($rawSearch).'%']);
                }
            });

            if ($tokens === []) {
                return;
            }

            $searchQuery->orWhere(function (Builder $tokensQuery) use ($tokens, $columns) {
                foreach ($tokens as $token) {
                    $tokensQuery->where(function (Builder $tokenQuery) use ($token, $columns) {
                        foreach (self::caseVariants($token) as $variant) {
                            foreach ($columns as $column) {
                                self::whereTranslatedLike($tokenQuery, $column, '%'.$variant.'%', true);
                            }
                        }
                    });
                }
            });
        });

        return $query;
    }

    /** @return array<int, string> */
    private static function caseVariants(string $token): array
    {
        return collect(self::variants($token))
            ->flatMap(function (string $variant) {
                return [
                    $variant,
                    mb_convert_case($variant, MB_CASE_TITLE, 'UTF-8'),
                    mb_strtoupper($variant),
                ];
            })
            ->unique()
            ->values()
            ->all();
    }

    private static function whereTranslatedLike(Builder $query, string $column, string $pattern, bool $or = false): void
    {
        $method = $or ? 'orWhere' : 'where';

        $query->{$method}(function (Builder $translated) use ($column, $pattern) {
            $translated
                ->where($column.'->uk', 'like', $pattern)
                ->orWhere($column.'->ru', 'like', $pattern);
        });
    }
}
