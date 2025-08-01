<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Serviceemployee extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    public $table = 'serviceemployees';
    protected $hidden = ['password','created_at','updated_at'];
}
