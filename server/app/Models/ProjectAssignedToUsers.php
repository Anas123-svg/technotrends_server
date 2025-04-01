<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectAssignedToUsers extends Model
{
    protected $table = 'project_assigned_to_users';

    protected $fillable = [
        'project_id',
        'user_id',
        'statusByUser',
        'reason',
    ];
}
