<?php

namespace App\Constants;

class SacramentRoles
{
    protected const items = [
        [
            'value' => 1,
            'title' => 'Sujeto',
        ],
        [
            'value' => 2,
            'title' => 'Esposo',
        ],
        [
            'value' => 3,
            'title' => 'Esposa',
        ],
        [
            'value' => 4,
            'title' => 'Padrino',
        ],
        [
            'value' => 5,
            'title' => 'Madrina',
        ],
        [
            'value' => 6,
            'title' => 'Testigo',
        ],  
    ];

    public static function all(): array
    {
        return static::items;
    }

    public static function title(int $value): string
    {
        return collect(static::items)->firstWhere('value', $value)['title'];
    }

    public static function value(string $title): int
    {
        return collect(static::items)->firstWhere('title', $title)['value'];
    }
}
