<?php

namespace App\Http\Helpers;

class LocationHelper
{
    public static function all()
    {
        return [
            [ 'id' => 1, 'name' => 'Head Office'],
            [ 'id' => 2, 'name' => 'Karachi Plant'],
            [ 'id' => 3, 'name' => 'Pezu Plant'],
            [ 'id' => 4, 'name' => 'Area Office'],
            [ 'id' => 5, 'name' => 'Islamabad'],
            [ 'id' => 6, 'name' => 'Lahore'],
            [ 'id' => 7, 'name' => 'Peshawar'],
            [ 'id' => 8, 'name' => 'Multan'],
            [ 'id' => 9, 'name' => 'Faisalabad'],
            [ 'id' => 10,' name' => 'Quetta'],
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
