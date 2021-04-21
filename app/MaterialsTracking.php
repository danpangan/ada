<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialsTracking extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'materials_tracking';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'material_id', 
        'supplier_material_id', 
        'material_flow',
        'material_quantity',
        'material_quantity_unit',
        'material_unit_amount',
        'material_unit_amount_currency',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
