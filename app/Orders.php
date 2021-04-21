<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Orders extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_item_code', 
        'order_item_description',
        'order_currency',
        'order_part_number',
        'order_category',
        'order_item_type_code',
        'order_item_classification',
        'order_length',
        'order_length_unit',
        'order_width',
        'order_width_unit',
        'order_gaplr',
        'order_gaplr_unit',
        'order_gaptb',
        'order_gaptb_unit',
        'order_remarks',
        'employee_id',
        'created_at',
        'updated_at', 
    ]; 

    protected $dates = ['deleted_at'];

    public function computation_sheets()
    {
        return $this->hasMany('App\ComputationSheet', 'client_order_id', 'cs_id');
    }
}
