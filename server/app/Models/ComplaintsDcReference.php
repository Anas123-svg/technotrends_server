<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ComplaintsDcReference extends Model
{
    use HasFactory;

    protected $table = 'table_complaints_dc_reference';

    protected $fillable = [
        'complaint_id',
        'dcReference',
        'dcDate',
        'isDcDateEdited',
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class, 'complaint_id');
    }
}
