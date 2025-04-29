<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
            if (Schema::hasTable('srf_equipments')) {
            Schema::rename('srf_equipments', 'crfs');
           $data = App\Models\Form::find(4);
           if($data)
           {
            $data->update([
                'name' => 'CRF',
                'identity' => 'App\Models\Forms\CRF'
            ]);
           }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('crfs')) {
            Schema::rename('crfs', 'srf_equipments');
        }
    }
};
