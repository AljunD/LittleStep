<?php

namespace App\Support;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Search
{
    /**
     * Keep only rows where every word of the term matches at least one column.
     * Use "relation.column" (e.g. "user.email") to search a related model.
     * LIKE wildcards in the term are matched literally.
     */
    public static function apply(Builder $query, ?string $term, array $columns): Builder
    {
        $words = preg_split('/\s+/', trim((string) $term), -1, PREG_SPLIT_NO_EMPTY);

        foreach ($words as $word) {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word).'%';

            $query->where(function (Builder $group) use ($columns, $pattern) {
                foreach ($columns as $column) {
                    if (str_contains($column, '.')) {
                        $group->orWhereHas(
                            Str::beforeLast($column, '.'),
                            fn (Builder $related) => self::whereLike($related, Str::afterLast($column, '.'), $pattern)
                        );
                    } else {
                        $group->orWhere(fn (Builder $own) => self::whereLike($own, $column, $pattern));
                    }
                }
            });
        }

        return $query;
    }

    private static function whereLike(Builder $query, string $column, string $pattern): void
    {
        $wrapped = $query->getQuery()->getGrammar()->wrap($query->qualifyColumn($column));
        $query->whereRaw("{$wrapped} LIKE ? ESCAPE '!'", [$pattern]);
    }
}
