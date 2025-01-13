<?php

namespace App\Constants;

class Sacraments
{
    protected const items = [
        [
            'value' => '1',
            'title' => 'Bautismo',
        ],
        [
            'value' => '2',
            'title' => 'Confirmación',
        ],
        // [
        //     'value' => 3,
        //     'title' => 'Eucaristía',
        // ],
        [
            'value' => '4',
            'title' => 'Matrimonio',
        ],
        // [
        //     'value' => 5,
        //     'title' => 'Orden Sacerdotal',
        // ],
        // [
        //     'value' => 6,
        //     'title' => 'Unción de los Enfermos',
        // ],
        // [
        //     'value' => 7,
        //     'title' => 'Penitencia',
        // ],
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

    public static function value(string $title): string
    {
        return collect(static::items)->firstWhere('title', $title)['value'];
    }
}
