<?php

namespace Database\Seeders;

use App\Models\Condition;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CRFConditionsAddedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(!Condition::where('name', 'CRF is Capital')->first())
        {
            Condition::insert([
                [
                    'name' => 'CRF is Capital',
                    'form_id' => 4,
                    'created_at' => Carbon::now(),
                ]
            ]);
        }
        if(!Condition::where('name', 'CRF is Revenue')->first())
        {
            Condition::insert([
                [
                    'name' => 'CRF is Revenue',
                    'form_id' => 4,
                    'created_at' => Carbon::now(),
                ]
            ]);
        }
        if(!Condition::where('name', 'Purchasing')->first())
        {
            Condition::insert([
                [
                    'name' => 'Purchasing',
                    'form_id' => 4,
                    'created_at' => Carbon::now(),
                ]
            ]);
        }
        // if(!Condition::where('name', 'Internal Transfer')->first())
        // {
        //     Condition::insert([
        //         [
        //             'name' => 'Internal Transfer',
        //             'form_id' => 4,
        //             'created_at' => Carbon::now(),
        //         ]
        //     ]);
        // }
        // if(!Condition::where('name', 'Internal Inventory')->first())
        // {
        //     Condition::insert([
        //         [
        //             'name' => 'Internal Inventory',
        //             'form_id' => 4,
        //             'created_at' => Carbon::now(),
        //         ]
        //     ]);
        // }
        $record1 = Condition::where('form_id', 4)->where('name', 'Internal Transfer')->first();
        $record2 = Condition::where('form_id', 4)->where('name', 'Internal Inventory')->first();
        if($record1)
        {
            $record1->delete();
        }
        if($record2)
        {
            $record2->delete();
        }

        $record = Condition::find(5);

        if($record)
        {
            $record->delete();
        }
        $record = Condition::find(6);

        if($record)
        {
            $record->delete();
        }
        $record = Condition::find(9);

        if($record)
        {
            $record->delete();
        }

        $record = Condition::where('id', 7)->where('name', 'CRF is Capital')->first();

        if($record)
        {
            $record->update(
                [
                    'name' => '(Expense Nature is (Capital) and value is > X) Or (Expense Nature is (Revenue) and value is > Y)',
                    'form_id' => 4,
                    'updated_at' => Carbon::now(),
                ]
            );
        }

        $record = Condition::where('id', 8)->where('name', 'CRF is Revenue')->first();

        if($record)
        {
            $record->update(
                [
                    'name' => '(Expense Nature is Capital) or (Expense nature is Revenue and value is > 5 lakh)',
                    'form_id' => 4,
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}
