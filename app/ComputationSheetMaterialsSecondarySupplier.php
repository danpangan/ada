<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComputationSheetMaterialsSecondarySupplier extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'computation_sheet_materials_secondary_supplier';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cs_id', 
        'cs_materials_secondary_id',
        'cs_materials_secondary_supplier_id',
        'supplier_material_quantity',
        'supplier_material_amount',
        'supplier_material_total',
        'is_selected',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
