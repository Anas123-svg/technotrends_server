<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Head extends Model
{
    use HasFactory, HasApiTokens;

    protected $table = 'head';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'department',
        'status',
    ];

    protected $hidden = [
        'password',
    ];
/*    public function complaints()
{
    return $this->hasMany(Complaint::class, 'assignedHead');
}

public function projects()
{
    return $this->hasMany(Project::class, 'assignedHead');
}*/

}
