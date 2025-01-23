<?php

namespace App\Constants;

class Genders
{
    protected const items = [
        [
            'value' => 1,
            'title' => 'Masculino',
        ],
        [
            'value' => 2,
            'title' => 'Femenino',
        ],
        [
            'value' => 0,
            'title' => 'No especificado',
        ]
    ];

    public static function all(): array
    {
        return static::items;
    }

    public static function item(int $value): array
    {
        return collect(static::items)->firstWhere('value', $value);
    }

    public static function title(int $value): string
    {
        return collect(static::items)->firstWhere('value', $value)['title'];
    }

    public static function value(string $title): int
    {
        return collect(static::items)->firstWhere('title', $title)['value'];
    }

    public static function hasKey(int $key): bool
    {
        return collect(static::items)->contains('value', $key);
    }
}
