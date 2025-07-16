<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherFeatures extends Model
{
    use HasFactory;
    public $table = 'otherfeatures';
    protected $fillable = ['NameAr', 'NameEn'];

}
