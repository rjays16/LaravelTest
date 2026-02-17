<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait CommonQueryScopes
{
    public function scopeFilterByDate(Builder $query, ?string $date): Builder
    {
        if ($date) {
            return $query->whereDate('date', $date);
        }
        return $query;
    }

    public function scopeSearchByTitle(Builder $query, ?string $search): Builder
    {
        if ($search) {
            return $query->where('title', 'like', '%' . $search . '%');
        }
        return $query;
    }

    public function scopeFilterByLocation(Builder $query, ?string $location): Builder
    {
        if ($location) {
            return $query->where('location', 'like', '%' . $location . '%');
        }
        return $query;
    }
}
