<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PermissionsUser extends Model
{
    use HasApiTokens;

    protected $table = 'permissions_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item_id', 
        'category', 
        'description',
        'user_id'
    ]; 
}
