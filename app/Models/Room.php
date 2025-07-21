<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    public $table='rooms';

    public function floor() {
      return $this->belongsTo('App/Models/Floor');
    }

    public function isRoomReserved() {
      return $this->roomStatus == 2;
    }
    public function isRoomReady() {
      return $this->roomStatus == 1;
    }
    public function isRoomMaintenance() {
      return $this->roomStatus == 4;
    }
    public function isRoomPrepare() {
      return $this->roomStatus == 3;
    }
}
