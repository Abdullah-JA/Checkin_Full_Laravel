<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Elevator extends Model
{
    use HasFactory;
    public $table = 'elevators';
    public $timestamps = false;
    protected $fillable = ['number' , 'buildingName', 'buildingNumber'];

}
