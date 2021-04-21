<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class SCMaterialsTracking extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'sc_materials_tracking';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sc_material_id', 
        'supplier_sc_material_id', 
        'sc_material_flow',
        'sc_material_quantity',
        'sc_material_quantity_unit',
        'sc_material_unit_amount',
        'sc_material_unit_amount_currency',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
