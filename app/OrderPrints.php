<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderPrints extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'order_prints';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id', 
        'material_id', 
        'order_print_item',
        'order_print_machine_id',
        'order_print_front',
        'order_print_front_regular',
        'order_print_front_special',
        'order_print_back',
        'order_print_back_regular',
        'order_print_back_special',
        'order_print_regular',
        'order_print_special',
        'order_print_cutsize',
        'order_print_outs',
        'order_print_pages',
        'order_print_wit',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];

    public function supplier_materials(){
        return $this->hasMany('App\SupplierMaterial', 'material_id', 'material_id');
    }

    public function material(){
        return $this->hasOne('App\Materials', 'id', 'material_id');
    }

    public function order(){
        return $this->hasOne('App\Orders', 'id', 'order_id');
    }
}
