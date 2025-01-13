<?php

namespace App\Constants;

class MinisterTypes
{
    protected const items = [
        [
            'value' => '001',
            'title' => 'Parroco',
        ],
        [
            'value' => '002',
            'title' => 'Vicario',
        ],
        [
            'value' => '003',
            'title' => 'Diácono',
        ],
        [
            'value' => '004',
            'title' => 'Ministro',
        ],
        [
            'value' => '006',
            'title' => 'Sacristán',
        ],
        [
            'value' => '007',
            'title' => 'Secretario',
        ],
        [
            'value' => '008',
            'title' => 'Catequista',
        ],
        [
            'value' => '011',
            'title' => 'Acólito',
        ],
        [
            'value' => '012',
            'title' => 'Monaguillo',
        ],
    ];

    public static function all(): array
    {
        return static::items;
    }

    public static function item(string $value): array
    {
        return collect(static::items)->firstWhere('value', $value);
    }

    public static function title(string $value): string
    {
        return collect(static::items)->firstWhere('value', $value)['title'];
    }

    public static function value(string $title): string
    {
        return collect(static::items)->firstWhere('title', $title)['value'];
    }
}
