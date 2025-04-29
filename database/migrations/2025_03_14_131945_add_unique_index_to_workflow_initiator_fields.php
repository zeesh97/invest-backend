<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        // Disable foreign key checks at the start
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Log::info('Disabled foreign key checks');

        try {
            $tableName = 'workflow_initiator_fields';
            $columnsToConvert = [
                'initiator_field_one_id',
                'initiator_field_two_id',
                'initiator_field_three_id',
                'initiator_field_four_id',
                'initiator_field_five_id'
            ];

            // 1. Drop only actual foreign keys
            $this->dropActualForeignKeys($tableName);

            // 2. Check and convert column types if needed
            foreach ($columnsToConvert as $column) {
                $this->convertColumnTypeIfNeeded($tableName, $column);
            }

            // 3. Handle the index with raw SQL
            $this->handleUniqueIndex($tableName);

            // 4. Recreate core foreign keys
            $this->recreateCoreForeignKeys();

            Log::info('Migration completed successfully');
        } catch (\Exception $e) {
            Log::error('Migration failed: ' . $e->getMessage());
            throw $e;
        } finally {
            // Always re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            Log::info('Re-enabled foreign key checks');
        }
    }

    private function dropActualForeignKeys(string $tableName): void
    {
        $foreignKeys = DB::select("
            SELECT
                CONSTRAINT_NAME,
                TABLE_NAME
            FROM
                INFORMATION_SCHEMA.TABLE_CONSTRAINTS
            WHERE
                TABLE_NAME = ?
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                AND CONSTRAINT_SCHEMA = DATABASE()
        ", [$tableName]);

        foreach ($foreignKeys as $fk) {
            try {
                DB::statement("ALTER TABLE `{$fk->TABLE_NAME}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                Log::info("Dropped foreign key: {$fk->CONSTRAINT_NAME}");
            } catch (\Exception $e) {
                Log::warning("Could not drop foreign key {$fk->CONSTRAINT_NAME}: " . $e->getMessage());
            }
        }
    }

    private function convertColumnTypeIfNeeded(string $tableName, string $column): void
    {
        $columnInfo = DB::select("
            SELECT DATA_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_NAME = ?
                AND COLUMN_NAME = ?
                AND TABLE_SCHEMA = DATABASE()
        ", [$tableName, $column]);

        if (!empty($columnInfo) && $columnInfo[0]->DATA_TYPE === 'varchar') {
            $tempColumn = "temp_{$column}";

            // Add temporary column
            if (!Schema::hasColumn($tableName, $tempColumn)) {
                Schema::table($tableName, function (Blueprint $table) use ($tempColumn) {
                    $table->unsignedInteger($tempColumn)->nullable();
                });
            }

            // Copy and convert data
            DB::statement("
                UPDATE {$tableName}
                SET {$tempColumn} = CAST({$column} AS UNSIGNED)
                WHERE {$column} REGEXP '^[0-9]+$' OR {$column} IS NULL
            ");

            // Drop original column
            Schema::table($tableName, function (Blueprint $table) use ($column) {
                $table->dropColumn($column);
            });

            // Rename temporary column
            Schema::table($tableName, function (Blueprint $table) use ($column, $tempColumn) {
                $table->renameColumn($tempColumn, $column);
            });
        }
    }

    private function handleUniqueIndex(string $tableName): void
    {
        $indexExists = DB::select("
            SHOW INDEX FROM {$tableName} WHERE Key_name = 'unique_combination'
        ");

        if (!empty($indexExists)) {
            try {
                DB::statement("ALTER TABLE `{$tableName}` DROP INDEX `unique_combination`");
                Log::info('Dropped existing unique_combination index');
            } catch (\Exception $e) {
                Log::warning('Could not drop unique_combination index: ' . $e->getMessage());
            }
        }

        // Create new unique index
        DB::statement("
            ALTER TABLE `{$tableName}`
            ADD UNIQUE `unique_combination` (
                `form_id`,
                `initiator_id`,
                `key_one`,
                `key_two`,
                `key_three`,
                `key_four`,
                `key_five`,
                `initiator_field_one_id`,
                `initiator_field_two_id`,
                `initiator_field_three_id`,
                `initiator_field_four_id`,
                `initiator_field_five_id`
            ) USING BTREE
        ");
        Log::info('Created new unique_combination index');
    }

    private function recreateCoreForeignKeys(): void
    {
        Schema::table('workflow_initiator_fields', function (Blueprint $table) {
            $table->foreign('workflow_id')
                ->references('id')->on('workflows')
                ->onUpdate('cascade');

            $table->foreign('form_id')
                ->references('id')->on('forms')
                ->onUpdate('cascade');

            $table->foreign('initiator_id')
                ->references('id')->on('users')
                ->onUpdate('cascade');
        });
        Log::info('Recreated core foreign keys');
    }

    public function down(): void
    {
        // Implement reverse migration if needed
    }
};
