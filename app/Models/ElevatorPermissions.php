<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElevatorPermissions extends Model
{
    use HasFactory;
    public $table = 'elevator_permissions';
    public $timestamps = false;
    protected $fillable = ['id_elevator','permission'];

}
