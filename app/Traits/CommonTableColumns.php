<?php
namespace App\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CommonTableColumns
{
    public function commonColumns()
    {
        Blueprint::macro('commonColumns', function () {
            $this->id();
            $this->string('sequence_no', 255)->nullable()->unique();
            $this->string('request_title', 255)->nullable();
            $this->foreignId('workflow_id')->nullable()
                ->references('id')->on('workflows')->onUpdate('cascade');
            $this->foreignId('location_id')->nullable()
                ->references('id')->on('locations')
                ->onUpdate('cascade');
            $this->foreignId('department_id')->nullable()
                ->references('id')->on('departments')
                ->onUpdate('cascade');
            $this->foreignId('section_id')->nullable()
                ->references('id')->on('sections')
                ->onUpdate('cascade');
            $this->foreignId('designation_id')->nullable()
                ->references('id')->on('designations')
                ->onUpdate('cascade');
            $this->foreignId('created_by')->nullable()
                ->references('id')->on('users')
                ->onUpdate('cascade');
            $this->foreignId('updated_by')->nullable()
                ->references('id')->on('users')
                ->onUpdate('cascade');
            $this->timestamp('draft_at')->nullable();
            $this->timestamp('task_status_at')->nullable();
            $this->foreignId('task_status')->nullable()
            ->references('id')->on('task_status_names')
            ->onUpdate('cascade');
            $this->string('status')->nullable();
            $this->boolean('comment_status')->nullable()->default(false);
            $this->softDeletes();
            $this->timestamps();
        });
    }
}
