<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComputationSheetMaterialsSubconSupplier extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'computation_sheet_material_subcon_supplier';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cs_id', 
        'cs_material_subcon_id',
        'cs_material_subcon_supplier_id',
        'supplier_sc_material_quantity',
        'supplier_sc_material_amount',
        'supplier_sc_material_total',
        'is_selected',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
