<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceDcReference extends Model
{
    use HasFactory;
    protected $table = 'invoices_dc_reference';

    protected $fillable = [
        'invoice_id',
        'dcReference',
        'dcDate',
        'isDcDateEdited',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
