<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestCategory extends Model
{
    use HasFactory;
    public $table = 'guestcategorys';
    protected $fillable = ['NameCategoryAr', 'NameCategoryEn', 'DiscountType', 'DiscountValue', 'OtherFeaturesIds', 'FacilityIds'];
}
