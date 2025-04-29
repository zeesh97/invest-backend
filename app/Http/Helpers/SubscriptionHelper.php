<?php

namespace App\Http\Helpers;

use App\Models\Form;
use App\Models\SetupField;
use App\Models\Subscription;
use App\Models\WorkflowInitiatorField;
use DB;
use Exception;
use Log;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionHelper
{
    public static function updateTransactionUsage(int $entryCount)
    {
        $subscription = Subscription::whereNull('expired_at')
            ->whereRaw('usage_number_of_transactions < number_of_transactions')
            ->first();

        if (!$subscription) {
            Log::error("No active subscription or transaction limit exceeded for upload.");
            throw new Exception("Transaction limit exceeded or no active subscription available.");
        }

        DB::transaction(function () use ($subscription, $entryCount) {
            $subscription->increment('usage_number_of_transactions', $entryCount);

            Log::info("Subscription transaction usage updated.", [
                'subscription_id' => $subscription->id,
                'new_usage' => $subscription->usage_number_of_transactions,
            ]);
        });
    }
    public static function updateSubscriptionUsage(float $sizeMb)
    {
        $subscription = Subscription::whereNull('expired_at')
            ->whereRaw('usage_data_mb < data_mb')
            ->first();

        if (!$subscription) {
            Log::error("No active subscription or storage limit exceeded for upload.");
            throw new Exception("Storage limit exceeded or no active subscription available.");
        }

        DB::transaction(function () use ($subscription, $sizeMb) {
            $subscription->increment('usage_data_mb', $sizeMb);

            Log::info("Subscription storage usage updated.", [
                'subscription_id' => $subscription->id,
                'new_usage_mb' => $subscription->usage_data_mb,
            ]);
        });
    }

    public static function updateUsersLimitUsage(float $usersCount)
    {
        $subscription = Subscription::whereNull('expired_at')
            ->whereRaw('usage_total_users < total_users')
            ->first();

        if (!$subscription) {
            Log::error("No active subscription or Total Users limit exceeded for upload.");
            throw new Exception("Total users limit exceeded or no active subscription available.");
        }

        DB::transaction(function () use ($subscription, $usersCount) {
            $subscription->increment('usage_total_users', $usersCount);

            Log::info("Subscription Total users limit updated.", [
                'subscription_id' => $subscription->id,
                'new_users_limit' => $subscription->usage_total_users,
            ]);
        });
    }
}
