<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComputationSheetMaterialsPrimary extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'computation_sheet_materials_primary';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cs_id', 
        'cs_order_materials_primary_id',
        'cs_material_id',
        'cs_material_outs',
        'cs_material_prm',
        'cs_material_actual',
        'cs_material_total_actual',
        'cs_material_actual_rate',
        'cs_total_material_quantity',
        'cs_subtotal_material_amount',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
