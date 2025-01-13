<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait RequestHelper
{

    public function select($keys): array
    {
        $attributes = parent::select($keys);
        return $this->transformKeysToSnakeCase($attributes);
    }

    public function all($keys = null): array
    {
        $attributes = parent::all($keys);
        return $this->transformKeysToSnakeCase($attributes);
    }

    private function transformKeysToSnakeCase(array $attributes): array
    {
        return collect($attributes)->mapWithKeys(function ($value, $key) {
            $snakeKey = Str::snake($key);
            if (is_array($value)) {
                $value = $this->transformKeysToSnakeCase($value);
            }
            return [$snakeKey => $value];
        })->toArray();
    }

    private function transformKeysToCamelCase(array $attributes): array
    {
        return collect($attributes)->mapWithKeys(function ($value, $key) {
            $camelKey = Str::camel($key);
            if (is_array($value)) {
                $value = $this->transformKeysToCamelCase($value);
            }
            return [$camelKey => $value];
        })->toArray();
    }
}
