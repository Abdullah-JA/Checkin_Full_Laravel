<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $table = 'invoices';
    protected $fillable = ['InvoiceNumber', 'InvoiceDate', 'TotalAmount', 'Discount', 'Tax', 'FinalAmount', 'ClientId', 'BookingId', 'Description', 'CreatedBy'];
}
