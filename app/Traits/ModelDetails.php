<?php

namespace App\Traits;

trait ModelDetails
{
    protected function getModelName()
    {
        $part = strrchr(get_class(), '\\');
        return $part === false ? get_class() : substr($part, 1, -strlen('Controller'));
    }
    protected function getModel()
    {
        return 'App\\Models\\Forms\\' . $this->getModelName();
    }
    public function getTableName()
    {
        $modelClass = $this->getModel();
        $modelInstance = new $modelClass();

        // Get the table name
        $tableName = $modelInstance->getTable();

        // Now you can use the table name as needed
        return $tableName;
    }

    public function getResource()
    {
        $resourceClass = 'App\Http\Resources\\' . $this->getModelName() . 'Resource';
        if (!class_exists($resourceClass)) {
            throw new \Exception("Resource class {$resourceClass} does not exist.");
        }
        return $resourceClass;
    }
    public function getIndexResource()
    {
        $resourceClass = "App\Http\Resources\Index{$this->getModelName()}Resource";
        if (!class_exists($resourceClass)) {
            throw new \Exception("Resource class {$resourceClass} does not exist.");
        }
        return $resourceClass;
    }
}
