<?php

namespace App\Console\Commands;

use App\Models\Form;
use App\Models\WorkflowInitiatorField;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class BackfillWorkflowInitiatorFields extends Command
{
    protected $signature = 'workflows:backfill-initiator-fields';
    protected $description = 'Backfill form initiator field data into workflow_initiator_fields';

    public function handle()
    {
        $this->info('Starting backfill of workflow initiator fields...');

        $chunkSize = 200;
        $totalForms = Form::count();

        if ($totalForms === 0) {
            $this->warn('No forms found to process.');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($totalForms);
        $updatedRecordsCount = 0;

        try {
            DB::transaction(function () use ($chunkSize, $bar, &$updatedRecordsCount) {

                Form::chunkById($chunkSize, function ($forms) use ($bar, &$updatedRecordsCount) {
                    foreach ($forms as $form) {
                        // Ensure related workflow_initiator_fields exists
                        $workflowField = WorkflowInitiatorField::where('form_id', $form->id)->first();

                        if (!$workflowField) {
                            // You could log this or track skipped forms if needed
                            $bar->advance();
                            continue;
                        }

                        $updateData = [
                            'initiator_field_one_id'   => $form->getRawOriginal('initiator_field_one_id'),
                            'initiator_field_two_id'   => $form->getRawOriginal('initiator_field_two_id'),
                            'initiator_field_three_id' => $form->getRawOriginal('initiator_field_three_id'),
                            'initiator_field_four_id'  => $form->getRawOriginal('initiator_field_four_id'),
                            'initiator_field_five_id'  => $form->getRawOriginal('initiator_field_five_id'),
                        ];

                        $affected = WorkflowInitiatorField::where('form_id', $form->id)->update($updateData);

                        $updatedRecordsCount += $affected;
                        $bar->advance();
                    }
                });
            });

            $bar->finish();
            $this->newLine(2);
            $this->info("✅ Backfill completed successfully.");
            $this->info("Forms processed: {$totalForms}");
            $this->info("Records updated: {$updatedRecordsCount}");

            return Command::SUCCESS;

        } catch (Throwable $e) {
            $bar->finish();
            $this->newLine(2);
            $this->error('❌ An error occurred. Transaction rolled back.');
            $this->error('Error: ' . $e->getMessage());

            // Optional: log for later debugging
            // \Log::error('Backfill error', ['exception' => $e]);

            return Command::FAILURE;
        }
    }
}
