<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class SCMaterials extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'sc_materials';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sc_material_name', 
        'sc_order_part_no', 
        'sc_material_classification_id', 
        'sc_material_type_id',
        'sc_material_last_in',
        'sc_material_last_out',
        'sc_material_location',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
