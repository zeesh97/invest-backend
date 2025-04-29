<?php

namespace Database\Seeders;

use App\Models\Subscriber;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscribersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Subscriber::find(1)) {
            $subscribers = [
                ['name' => 'Subscriber 1'],
                ['name' => 'Subscriber 2'],
                ['name' => 'Subscriber 3'],
                ['name' => 'Subscriber 4'],
                ['name' => 'Subscriber 5']
            ];

            foreach ($subscribers as $subscriber) {
                $subscriberId = DB::table('subscribers')->insertGetId($subscriber);

                $userIds = range(1, 2);
                DB::table('subscriber_user')->insert(
                    array_map(function ($userId) use ($subscriberId) {
                        return ['subscriber_id' => $subscriberId, 'user_id' => $userId];
                    }, $userIds)
                );
            }
        }
    }
}
