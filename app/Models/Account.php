<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['ClientId', 'type', 'Number', 'Amount', 'Date', 'Time', 'UserId'];

    public function client()
    {
        return $this->belongsTo(Client::class, 'ClientId');
    }

    public function user()
    {
        return $this->belongsTo(ServiceEmployee::class, 'UserId');
    }
}

