<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomtypePricingplan extends Model
{
    use HasFactory;
    public $table = 'roomtype_pricingplan';
    protected $fillable = ['roomtype_id', 'pricingplan_id', 'DailyPrice', 'MonthlyPrice', 'YearlyPrice'];

    public function pricingplan()
    {
        return $this->belongsTo(Pricingplan::class, 'pricingplan_id');
    }
}
