<?php

namespace App\Http\Controllers\Maintainers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait Searchable
{
    /**
     * Apply search to the query with the specified fields.
     */
    protected function applySearch(Builder $query, Request $request, array $fields): Builder
    {
        $searchTerm = $this->getCleanSearchTerm($request);

        if (! $searchTerm) {
            return $query;
        }

        return $query->where(function ($q) use ($searchTerm, $fields) {
            $firstField = array_shift($fields);
            if ($firstField) {
                $q->where($firstField, 'like', "%{$searchTerm}%");
            }

            foreach ($fields as $field) {
                $q->orWhere($field, 'like', "%{$searchTerm}%");
            }

            if (is_numeric($searchTerm)) {
                $q->orWhere('id', (int) $searchTerm);
            }
        });
    }

    /**
     * Get the clean search term.
     */
    protected function getCleanSearchTerm(Request $request): ?string
    {
        $search = $request->input('search');

        if (! $search) {
            return null;
        }

        $searchTerm = trim($search);

        return $searchTerm !== '' ? $searchTerm : null;
    }
}
