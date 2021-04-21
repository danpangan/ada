<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeCode extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'type_code';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_code', 
        'type_code_description', 
        'is_rm',
        'is_sc',
        'is_fg',
        'employee_id',
        'created_at',
    ]; 

    protected $dates = ['deleted_at'];
}
