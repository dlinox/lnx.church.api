<?php

namespace App\Constants;

class DocumentTypes
{
    protected const items = [
        [
            'value' => '01',
            'title' => 'DNI',
        ],
        [
            'value' => '04',
            'title' => 'Carnet de extranjería',
        ],

        [
            'value' => "06",
            'title' => 'RUC',
        ],
        
        [
            'value' => '07',
            'title' => 'Pasaporte',
        ],
        [
            'value' => '11',
            'title' => 'Partida de nacimiento-identidad',
        ],
        [
            'value' => '00',
            'title' => 'Otro',
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
    // Haskey
    public static function hasKey(string $key): bool
    {
        return collect(static::items)->contains('value', $key);
    }

}
