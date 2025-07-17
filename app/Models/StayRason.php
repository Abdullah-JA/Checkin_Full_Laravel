<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StayRason extends Model
{
    use HasFactory;
    public $table = 'stayreasons';
    protected $fillable = ['NameAr', 'NameEn'];
}
