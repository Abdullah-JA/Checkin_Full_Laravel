<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxName extends Model
{
    use HasFactory;
    protected $table = 'taxnames';
    protected $fillable = ['type', 'value', 'name_ar', 'name_en', 'optional'];
}
