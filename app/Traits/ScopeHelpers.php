<?php

namespace App\Traits;

trait ScopeHelpers
{
    public function scopeSearch($query, $term)
    {
        if (!$term) return $query;

        $searchFields = $this->searchable ?? ['nombre'];

        return $query->where(function($q) use ($term, $searchFields) {
            foreach ($searchFields as $field) {
                $q->orWhere($field, 'LIKE', "%{$term}%");
            }
        });
    }

    public function scopeOrderByName($query, $direction = 'asc')
    {
        return $query->orderBy('nombre', $direction);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
