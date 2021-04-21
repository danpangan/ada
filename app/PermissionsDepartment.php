<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PermissionsDepartment extends Model
{
    use HasApiTokens;

    protected $table = 'permissions_department';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'department_id',
        'item_id', 
        'category', 
        'description',
    ]; 
}
