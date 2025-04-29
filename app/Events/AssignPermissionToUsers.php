<?php
namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssignPermissionToUsers
{
    use Dispatchable, SerializesModels;

    public array $users;
    public mixed $model;
    public int $key;

    /**
     * Create a new event instance.
     */
    public function __construct(array $users, mixed $model, int $key)
    {
        $this->users = $users;
        $this->model = $model;
        $this->key = $key;
    }
}
