<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComputationSheetMaterialsSecondary extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'computation_sheet_materials_secondary';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cs_id', 
        'cs_order_materials_secondary_id',
        'cs_material_id',
        'cs_total_material_quantity',
        'cs_subtotal_material_amount',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
