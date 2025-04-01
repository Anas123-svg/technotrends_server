<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectsDcReference extends Model
{
    use HasFactory;
    protected $table = 'table_projects_dc_reference';

    protected $fillable = [
        'project_id',
        'dcReference',
        'dcDate',
        'isDcDateEdited',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
