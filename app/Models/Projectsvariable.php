<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projectsvariable extends Model
{
    use HasFactory;
    public $table='projectsvariables';
    protected $hidden = ['projectPassword','created_at','updated_at'];
}
