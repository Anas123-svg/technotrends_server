<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'poNumber',
        'poImage',
        'clientName',
        'clientPhone',
        'surveyPhotos',
        'quotationReference',
        'quotationImage',
        'jcImage',
        'dcImage',
        'status',
        'remarks',
        'dueDate',
        'poDate',
        'surveyDate',
        'quotationDate',
        'remarksDate',
        'isQuotationDateEdited',
        'isRemarksDateEdited',
        'isSurveyDateEdited',
        'isPoDateEdited',
        'isDueDateEdited',
        'createdBy',

    ];

    protected $casts = [
        'surveyPhotos' => 'array'
        ];

    /*public function head()
    {
        return $this->belongsTo(Head::class, 'assignedHead');
    }*/

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_assigned_to_users');
    }

    public function projectAssignedUsers()
{
    return $this->hasMany(ProjectAssignedToUsers::class);
}


public function jcReferences()
{
    return $this->hasMany(ProjectsJcReference::class);
}

public function dcReferences()
{
    return $this->hasMany(ProjectsDcReference::class);
}

public function toArray()
{
    $array = parent::toArray();

    if (isset($array['jc_references'])) {
        $array['jcReferences'] = $array['jc_references'];
        unset($array['jc_references']);
    }

    if (isset($array['dc_references'])) {
        $array['dcReferences'] = $array['dc_references'];
        unset($array['dc_references']);
    }

    return $array;
}

}
