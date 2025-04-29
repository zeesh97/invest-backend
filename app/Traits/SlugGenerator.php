<?php

namespace App\Traits;

trait SlugGenerator
{
    public function generateSlug(string $class): string
    {
        $lastPart = substr(strrchr($class, '\\'), 1);

        if (strtoupper($lastPart) === $lastPart) {
            $lastPart = strtolower($lastPart);
        } else {
            $lastPart = preg_replace_callback('/([A-Z])/', function($matches) {
                static $first = true;
                if ($first) {
                    $first = false;
                    return strtolower($matches[0]);
                } else {
                    return '-' . strtolower($matches[0]);
                }
            }, $lastPart);
        }

        return $lastPart;
    }
}
