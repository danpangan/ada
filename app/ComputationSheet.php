<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComputationSheet extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'computation_sheet';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cs_control_number',
        'cs_version_number',
        'cs_client_id',
        'client_order_id',
        'cs_qty',
        'cs_qty_unit',
        'cs_currency',
        'cs_material_subtotal',
        'cs_material_subtotal_php',
        'cs_material_loss',
        'cs_material_loss_php',
        'cs_material_total',
        'cs_material_total_php',
        'cs_printing_subtotal',
        'cs_printing_plate',
        'cs_printing_plate_amount',
        'cs_printing_color',
        'cs_printing_color_amount',
        'cs_printing_total',
        'cs_printing_total_php',
        'cs_process_total',
        'cs_process_total_php',
        'cs_miscellaneous_total',
        'cs_miscellaneous_total_php',
        'cs_total_amount',
        'cs_total_amount_php',
        'cs_markup_percentage',
        'cs_markup_piece',
        'cs_markup_total',
        'cs_markup_profit',
        'cs_status',
        'cs_is_valid',
        'cs_remarks',
        'employee_id',
        'created_at',
        'updated_at',  
    ]; 

    protected $dates = ['deleted_at'];

    public function computation_sheets()
    {
        return $this->belongsTo('App\Orders');
    }

    public function order(){
        return $this->hasOne('App\Orders', 'id', 'client_order_id');
    }
}
