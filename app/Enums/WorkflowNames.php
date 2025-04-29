<?php

namespace App\Enums;

class WorkflowNames
{
    const SOFTWARECATEGORY = 'App\Models\SoftwareCategory';
    const SOFTWARESUBCATEGORY = 'App\Models\SoftwareSubcategory';
    const DEPARTMENT = 'App\Models\Department';
    const DESIGNATION = 'App\Models\Designation';
    const LOCATION = 'App\Models\Location';
    const SECTION = 'App\Models\Section';

        /**
     * Get an array representation of the enum values.
     *
     * @return array
     */
    public static function toArray()
    {
        return [
            self::SOFTWARECATEGORY => 'Software Category',
            self::SOFTWARESUBCATEGORY => 'Software Subcategory',
            self::DEPARTMENT => 'Department',
            self::DESIGNATION => 'Designation',
            self::LOCATION => 'Location',
            self::SECTION => 'Section',
        ];
    }
}
