<?php

namespace App\Enums;

enum FormEnum: int
{
    case QUALITY_ASSURANCE = 1;
    case SCRF = 2;
    case DEPLOYMENT = 3;
    case CRF = 4;
    case MOBILE_REQUISITION = 5;
    case MASTER_DATA_MANAGEMENT_FORM = 6;
    case SAP_ACCESS_FORM = 7;

    public function name(): string
    {
        return match($this) {
            self::QUALITY_ASSURANCE => 'Quality Assurance',
            self::SCRF => 'SCRF',
            self::DEPLOYMENT => 'Deployment',
            self::CRF => 'CRF',
            self::MOBILE_REQUISITION => 'Mobile Requisition Form',
            self::MASTER_DATA_MANAGEMENT_FORM => 'Master Data Management',
            self::SAP_ACCESS_FORM => 'Sap Access Form',
        };
    }

    public function identity(): string
    {
        return match($this) {
            self::QUALITY_ASSURANCE => 'App\\Models\\Forms\\QualityAssurance',
            self::SCRF => 'App\\Models\\Forms\\SCRF',
            self::DEPLOYMENT => 'App\\Models\\Forms\\Deployment',
            self::CRF => 'App\\Models\\Forms\\CRF',
            self::MOBILE_REQUISITION => 'App\\Models\\Forms\\MobileRequisition',
            self::MASTER_DATA_MANAGEMENT_FORM => 'App\\Models\\Forms\\MasterDataManagementForm',
            self::SAP_ACCESS_FORM => 'App\\Models\\Forms\\SapAccessForm',
        };
    }
    public static function getIdByModelName(string $modelName): ?int
    {
        foreach (self::cases() as $form) {
            if ($form->identity() === $modelName) {
                return $form->value;
            }
        }
        return null;
    }
    public static function getModelById(int $id): ?string
    {
        foreach (self::cases() as $form) {
            if ($form->value === $id) {
                return $form->identity();
            }
        }
        return null;
    }
    public static function getNameById(int $id): ?string
    {
        foreach (self::cases() as $form) {
            if ($form->value === $id) {
                return $form->name();
            }
        }
        return null;
    }
    public static function getNameByModel(string $modelName): ?string
    {
        foreach (self::cases() as $form) {
            if ($form->identity() === $modelName) {
                return $form->name();
            }
        }
        return null;
    }
}
