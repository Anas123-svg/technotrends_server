<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceJcReference extends Model
{
    use HasFactory;
    protected $table = 'invoices_jc_reference';

    protected $fillable = [
        'invoice_id',
        'jcReference',
        'jcDate',
        'isJcDateEdited',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
