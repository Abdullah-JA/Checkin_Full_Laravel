<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PermissionsName;

class UserPermission extends Model
{
    use HasFactory;

    protected $table = 'userpermissions';

    protected $fillable = ['UserId', 'PermissionId'];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserId');
    }

    public function permission()
    {
        return $this->belongsTo(PermissionsName::class, 'PermissionId');
    }
}
