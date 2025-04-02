<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
class User extends Model
{
    use HasFactory, HasApiTokens;

    protected $table = 'user';

    protected $fillable = [
        'name',
        'email',
        'password',
        'head_id',
        'phone',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    public function head()
    {
        return $this->belongsTo(Head::class);
    }
    public function complaints()
{
    return $this->belongsToMany(Complaint::class, 'complaints_assigned_to_users');
}

public function projects()
{
    return $this->belongsToMany(Project::class, 'project_assigned_to_users');
}

}
