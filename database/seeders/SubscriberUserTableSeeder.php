<?php

namespace Database\Seeders;

use App\Models\Subscriber;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriberUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Subscriber::first()) {
            $subscriberUserAssignments = [
                ['subscriber_id' => 1, 'user_id' => 1],
                ['subscriber_id' => 1, 'user_id' => 2]
            ];

            DB::table('subscriber_user')->insert($subscriberUserAssignments);
        }
    }
}
