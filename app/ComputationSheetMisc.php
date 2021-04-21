<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComputationSheetMisc extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'computation_sheet_misc';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cs_id', 
        'cs_is_label',
        'cs_is_package',
        'cs_is_transpo',
        'cs_is_layout',
        'cs_is_film',
        'cs_is_plate',
        'cs_is_diecut',
        'cs_is_peza',
        'cs_is_package_label',
        'cs_is_item_label',
        'cs_is_packaging_cartoon',
        'cs_is_packaging_others',
        'cs_packaging_cartoon_qty_per_box',
        'cs_packing_others_desc',
        'cs_transpo_desc',
        'cs_layout_desc',
        'cs_film_desc',
        'cs_plate_desc',
        'cs_diecut_desc',
        'cs_peza_desc',
        'cs_qty_label',
        'cs_qty_package',
        'cs_qty_transpo',
        'cs_qty_layout',
        'cs_qty_film',
        'cs_qty_plate',
        'cs_qty_diecut',
        'cs_qty_peza',
        'cs_unit_amt_label',
        'cs_unit_amt_package',
        'cs_unit_amt_transpo',
        'cs_unit_amt_layout',
        'cs_unit_amt_film',
        'cs_unit_amt_plate',
        'cs_unit_amt_diecut',
        'cs_unit_amt_peza',
        'cs_total_amt_label',
        'cs_total_amt_package',
        'cs_total_amt_transpo',
        'cs_total_amt_layout',
        'cs_total_amt_film',
        'cs_total_amt_plate',
        'cs_total_amt_diecut',
        'cs_total_amt_peza',
        'cs_miscellaneous_total',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
