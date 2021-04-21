<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderMaterialsSecondary extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'order_materials_secondary';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id', 
        'material_id',
        'order_material_length',
        'order_material_length_unit',
        'order_material_width',
        'order_material_width_unit',
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
