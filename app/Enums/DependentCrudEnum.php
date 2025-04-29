<?php

namespace App\Enums;

enum DependentCrudEnum: string
{
    case SALES_ORGANIZATION = 'saf_sd_sales_organization';
    case DISTRIBUTION_CHANNEL = 'saf_sd_distribution_channel';
    case DIVISION = 'saf_sd_division';
    case SALES_OFFICE = 'saf_sd_sales_office';
    case SALES_GROUP = 'saf_sd_sales_group';
    case PURCHASING_ORG = 'saf_mm_purchasing_org';
    case PURCHASING_GROUP = 'saf_mm_purchasing_group';
    case STORAGE_LOCATION = 'saf_mm_storage_location';
    case PURCHASING_DOCUMENT = 'saf_mm_purchasing_document';
    case MOVEMENT_TYPE = 'saf_mm_movement_type';
    case PLANNING_PLANT = 'saf_pm_planning_plant';
    case MAINTENANCE_PLANT = 'saf_pm_maintenance_plant';
    case WORK_CENTER = 'saf_pm_work_center';
    case PROFIT_CENTER = 'saf_fico_profit_center';
    case COST_CENTER = 'saf_fico_cost_center'; // Note: Cost Center appears twice, consider prefixing or distinguishing.
    case HR_PERSONNEL_AREA = 'saf_hr_personnel_area';
    case HR_SUB_AREA = 'saf_hr_sub_area';
    case HR_COST_CENTER = 'saf_hr_cost_center'; // Consider prefixing to distinguish from FICO Cost Center.
    case HR_EMPLOYEE_GROUP = 'saf_hr_employee_group';
    case HR_EMPLOYEE_SUP_GROUP = 'saf_hr_employee_sup_group';
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::SALES_ORGANIZATION => 'Sales Organization',
            self::DISTRIBUTION_CHANNEL => 'Distribution Channel',
            self::DIVISION => 'Division',
            self::SALES_OFFICE => 'Sales Office',
            self::SALES_GROUP => 'Sales Group',
            self::PURCHASING_ORG => 'Purchasing Organization',
            self::PURCHASING_GROUP => 'Purchasing Group',
            self::STORAGE_LOCATION => 'Storage Location',
            self::PURCHASING_DOCUMENT => 'Purchasing Document',
            self::MOVEMENT_TYPE => 'Movement Type',
            self::PLANNING_PLANT => 'Planning Plant',
            self::MAINTENANCE_PLANT => 'Maintenance Plant',
            self::WORK_CENTER => 'Work Center',
            self::PROFIT_CENTER => 'Profit Center',
            self::COST_CENTER => 'FICO Cost Center', // Clarified label
            self::HR_PERSONNEL_AREA => 'Personnel Area',
            self::HR_SUB_AREA => 'Sub Area',
            self::HR_COST_CENTER => 'HR Cost Center', // Clarified label
            self::HR_EMPLOYEE_GROUP => 'Employee Group',
            self::HR_EMPLOYEE_SUP_GROUP => 'Employee Supervisor Group',
        };
    }


    // ... other helpful methods if needed ...

    public static function tryFromLabel(string $label): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->label() === $label) {
                return $case;
            }
        }
        return null;
    }

}
