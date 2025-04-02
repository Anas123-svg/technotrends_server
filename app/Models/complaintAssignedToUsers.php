<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class complaintAssignedToUsers extends Model
{
    
    protected $table = 'complaints_assigned_to_users';

    protected $fillable = [
        'complaint_id',
        'user_id',
        'statusByUser',
        'reason',
    ];
}
