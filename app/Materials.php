<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Materials extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'materials';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'material_description', 
        'material_classification_id', 
        'material_type_id',
        'material_length',
        'material_length_unit',
        'material_width',
        'material_width_unit',
        'material_thickness',
        'material_thickness_unit',
        'material_last_in',
        'material_last_out',
        'material_location',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
