<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingSources extends Model
{
    use HasFactory;
    public $table = 'bookingsources';
    protected $fillable = ['NameAr', 'NameEn'];

}
