<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintsJcReference extends Model
{
    use HasFactory;

    protected $table = 'table_complaints_jc_reference';

    protected $fillable = [
        'complaint_id',
        'jcReference',
        'jcDate',
        'isJcDateEdited',
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class, 'complaint_id');
    }
}