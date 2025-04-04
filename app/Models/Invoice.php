<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoiceReference',
        'invoiceImage',
        'amount',
        'paymentTerms',
        'creditDays',
        'dueDate',
        'linkedProject',
        'status',
        'clientName',
        'poNumber',
        'poDate',
        'invoiceDate',
        
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'linkedProject');
    }
}
