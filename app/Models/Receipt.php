<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $table = 'receipts';

    protected $fillable = ['ReceiptNumber', 'ReceiptDate', 'Amount', 'EmployeeId', 'Description', 'ClientId', 'BookingId'];

    public function employee()
    {
        return $this->belongsTo(ServiceEmployee::class, 'EmployeeId');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'ClientId');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'BookingId');
    }
}
