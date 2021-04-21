<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'departments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'department_name', 
        'department_contact', 
        'employee_id',
    ]; 

    protected $dates = ['deleted_at'];

    public function user(){
        return $this->hasMany('App\User', 'department_id', 'id');
    }

    public function permissions(){
        return $this->hasMany('App\PermissionsDepartment', 'department_id', 'id');
    }
}
