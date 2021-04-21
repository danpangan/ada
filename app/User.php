<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'employee_number', 
        'username', 
        'password', 
        'salt', 
        'email', 
        'first_name',
        'middle_name',
        'last_name',
        'address',
        'contact',
        'birthdate',
        'gender',
        'department_id',
        'user_role',
        'employee_id'
    ];

    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function department(){
        return $this->hasOne('App\Department', 'id', 'department_id');
    }

    public function permissions(){
        return $this->hasMany('App\PermissionsUser', 'user_id', 'id');
    }
}
