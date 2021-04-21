<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Products extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ss_reference_no',
        'order_item_code',
        'product_code', 
        'product_part_number', 
        'product_name', 
        'product_category_id',
        'product_classification_id',
        'product_type_code_id',
        'product_stock_quantity',
        'product_stock_quantity_unit',
        'product_customer',
        'product_location',
        'product_last_in',
        'product_last_out',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
