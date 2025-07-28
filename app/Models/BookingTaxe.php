<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingTaxe extends Model
{
    use HasFactory;
    public $table = 'bookingtaxes';
    protected $fillable = ['BookingId', 'TaxId'];
}
