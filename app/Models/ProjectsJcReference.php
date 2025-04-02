<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ProjectsJcReference extends Model
{
    use HasFactory;
    protected $table = 'table_projects_jc_reference';

    protected $fillable = [
        'project_id',
        'jcReference',
        'jcDate',
        'isJcDateEdited',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

}
