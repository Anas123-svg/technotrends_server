<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaintReference',
        'complaintImage',
        'clientName',
        'clientPhone',
        'title',
        'description',
        'dueDate',
        'jcImage',
        'photos',
        'priority',
        'remarks',
        'status',
        'poNumber',
        'poDate',
        'visitDates',
        'jcDate',
        'quotation',
        'quotationDate',
        'remarksDate',
        'isJcDateEdited',
        'isQuotationDateEdited',
        'isRemarksDateEdited',
        'isPoDateEdited',
        'isDueDateEdited',
        'createdBy',

    ];

    protected $casts = [
        'photos' => 'array',
        'visitDates' => 'array',
        'jcReference' => 'array',
        'dcReference' => 'array',
    ];



    /*public function head()
    {
        return $this->belongsTo(Head::class, 'assignedHead');
    }*/

    public function users()
    {
        return $this->belongsToMany(User::class, 'complaints_assigned_to_users')->withTimestamps();
    }


    public function complaintAssignedUsers()
    {
        return $this->hasMany(complaintAssignedToUsers::class,'complaint_id');
    }

    public function jcReferences()
    {
        return $this->hasMany(ComplaintsJcReference::class);
    }

    public function dcReferences()
    {
        return $this->hasMany(ComplaintsDcReference::class);
    }


    
}
