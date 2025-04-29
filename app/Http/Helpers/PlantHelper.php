<?php

namespace App\Http\Helpers;

class PlantHelper
{
    public static function all(): array
    {
        return [
            ['id' => 1, 'name' => 'PIBT'],
            ['id' => 2, 'name' => 'QICT'],
            ['id' => 3, 'name' => 'LCHO'],
            ['id' => 4, 'name' => 'LCKP'],
            ['id' => 5, 'name' => 'LCPZ'],
            ['id' => 6, 'name' => 'LKPT'],
            ['id' => 7, 'name' => 'PKGP'],
            ['id' => 8, 'name' => 'PGPZ'],
        ];
    }

    public static function findById($id): array|null
    {
        return collect(self::all())->firstWhere('id', $id);
    }

    public static function findByName($name): array|null
    {
        return collect(self::all())->firstWhere('name', $name);
    }
}
