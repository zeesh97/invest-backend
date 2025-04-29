<?php

namespace App\Traits;

use App\Actions\AutoAssignTaskAction;
use App\Enums\FormEnum;
use App\Http\Helpers\Helper;
use App\Http\Helpers\SubscriptionHelper;
use App\Jobs\ApprovedDisapprovedJob;
use App\Mail\ApprovedDisapprovedEmail;
use App\Models\ApprovalStatus;
use App\Models\AssignTask;
use App\Models\Attachment;
use App\Models\AutoAssignTask;
use App\Models\Comment;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Form;
use App\Models\Forms\Deployment;
use App\Models\Location;
use App\Models\Scopes\FormDataAccessScope;
use App\Models\Section;
use App\Models\TaskStatusName;
use App\Models\User;
use App\Models\UserAccessLevel;
use App\Models\Workflow;
use Auth;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Log;
use Mail;

trait CommonFormRelationships
{
    public function getRelationshipNames(): array
    {
        return [
            'assignedTasks',
            'taskStatusName',
            'workflow',
            'user',
            'updatedBy',
            'department',
            'location',
            'designation',
            'section',
            'taskStatus',
            'assignedTask',
            'comments',
        ];
    }
    protected static function booted(): void
    {
        /* Row level Restrict data to users  */
        static::addGlobalScope(new FormDataAccessScope);
        /* End Row level Restrict data to users  */

        /* Transaction usage update */
        static::created(function ($model) {
            SubscriptionHelper::updateTransactionUsage(1);

            if (
                $model->isDirty('status') &&
                $model->getOriginal('status') !== $model->status &&
                $model->status === "Draft"
            ) {
                activity()
                    ->performedOn($model)
                    ->createdAt(now())
                    ->event('Draft')
                    ->log('Form saved as Draft');
            }
            if (
                $model->isDirty('status') &&
                $model->getOriginal('status') !== $model->status &&
                $model->status !== "Draft"
            ) {
                activity()
                    ->performedOn($model)
                    ->createdAt(now())
                    ->event('Created')
                    ->log('Form submitted for Approval');
            }
        });
        /* End Transaction usage update */

        static::updated(function (mixed $model) {
            $modelName = get_class($model);
            $formId = FormEnum::getIdByModelName($modelName);
            $form = Form::find($formId);

            $createdBy = User::find($model->created_by);
            if (
                $model->isDirty('status') &&
                $model->getOriginal('status') !== $model->status
            ) {
                if ($createdBy) {
                    $formName = $form->name ?? FormEnum::getNameByModel($modelName);
                    $formSlug = $form->slug ?? null;

                    $emailData = [
                        'form_name'     => $formName,
                        'status'        => $model->status,
                        'request_title' => $model->request_title,
                        'name'          => $createdBy->name,
                        'email'         => $createdBy->email,
                        'employee_no'   => $createdBy->employee_no ?? 'N/A',
                        'slug'          => $formSlug,
                        'key'           => $model->getKey(),
                    ];


                    Mail::to($createdBy->email)->queue(new ApprovedDisapprovedEmail($emailData));
                    Log::info("Queued approval email for {$modelName} ID {$model->getKey()} to {$createdBy->email}");
                }
            }
            if (
                $model->isDirty('status') &&
                $model->getOriginal('status') !== $model->status &&
                $model->status === "Approved"
            ) {
                $createdBy = User::find($model->created_by);
                if ($createdBy) {
                    $isDefined = AutoAssignTask::where("form_id", $formId)
                        ->where("user_id", $model->created_by)
                        ->first();

                    if ($createdBy && $isDefined) {
                        DB::afterCommit(function () use ($isDefined, $model, $modelName, $createdBy) {
                            AutoAssignTaskAction::handle($isDefined, $model, $modelName, $createdBy->id);
                        });
                    }
                } else {
                    Log::warning("Auto task assignment skipped because no created by user found.");
                }
            }
            if (
                $model->isDirty('status') || $model->isDirty('draft_at')
            ) {
                if ($model->status === 'Draft' && $model->getOriginal('status') !== 'Draft') {
                    activity()
                        ->performedOn($model)
                        ->createdAt(now())
                        ->event('Draft')
                        ->log('Form submitted as Draft');
                }
                if ($model->status === 'Draft' && $model->getOriginal('status') === 'Draft') {
                    activity()
                        ->performedOn($model)
                        ->createdAt(now())
                        ->event('Draft')
                        ->log('Form re-submitted as Draft');
                }
            }
            if ($model->status === 'Pending' && $model->getOriginal('status') !== $model->status) {
                activity()
                    ->performedOn($model)
                    ->createdAt(now())
                    ->event('Updated')
                    ->log('Form submitted for Approval');
            }

            if (
                $model->isDirty() && (!$model->isDirty('status') == $model->status) && !$model->isDirty('draft_at')
            ) {
                activity()
                    ->performedOn($model)
                    ->createdAt(now())
                    ->event('Edited')
                    ->log('Form edited and saved');
            }
            if ($model->status === 'Return' && $model->getOriginal('status') !== $model->status) {
                activity()
                    ->performedOn($model)
                    ->createdAt(now())
                    ->event('Return')
                    ->log('Form was Returned for revisions');
            }
            if ($model->status === 'Disapproved' && $model->getOriginal('status') !== $model->status) {
                activity()
                    ->performedOn($model)
                    ->createdAt(now())
                    ->event('Disapproved')
                    ->log('Form was Rejected/Disapproved');
            }
        });
        static::updated(function ($model) {
            DB::afterCommit(function () use ($model) {
                if ($model->wasChanged('status')) {
                    activity()
                        ->performedOn($model)
                        ->event($model->status)
                        ->log("Form status changed to {$model->status}");
                }
            });
        });
    }
    public function getCreatedAtAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        $timezone = Helper::appTimezone();
        return Carbon::parse($value)->timezone($timezone)->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        $timezone = Helper::appTimezone();
        return Carbon::parse($value)->timezone($timezone)->format('d-m-Y H:i:s');
    }

    public function getDraftAtAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        $timezone = Helper::appTimezone();
        return Carbon::parse($value)->timezone($timezone)->format('d-m-Y H:i:s');
    }

    public function getTaskStatusAtAttribute($value): ?String
    {
        if (!$value) {
            return null;
        }
        $timezone = Helper::appTimezone();
        return Carbon::parse($value)->timezone($timezone)->format('d-m-Y H:i:s');
    }
    public function attachables(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'attachable_id', 'id');
    }
    public function approvalStatuses(): HasMany
    {
        return $this->hasMany(ApprovalStatus::class, 'key', 'id')
            ->where('form_id', $this->getModelId());
    }

    public function assignedTasks(): MorphMany
    {
        return $this->morphMany(AssignTask::class, 'assignable');
    }
    public function taskStatusName(): BelongsTo
    {
        return $this->belongsTo(TaskStatusName::class, 'task_status');
    }
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function taskStatus(): BelongsTo
    {
        return $this->belongsTo(TaskStatusName::class, 'task_status');
    }

    public function assignedTask(): MorphOne
    {
        return $this->morphOne(AssignTask::class, 'assignable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
    public function deployments(): HasMany
    {
        $modelName = get_class($this);
        $formId = FormEnum::getIdByModelName($modelName);

        return $this->hasMany(Deployment::class, 'reference_details', 'id')
            ->where('reference_form_id', $formId)
            ->with(['user:id,name,email,employee_no', 'deploymentDetail', 'referenceForm:id,name,slug'])
            ->select([
                'id',
                'reference_form_id',
                'reference_details',
                'sequence_no',
                'request_title',
                'created_by',
                'created_at'
            ]);
    }
    // public function referenceDetails(): MorphTo
    // {
    //     return $this->morphTo();
    // }
}
