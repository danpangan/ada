<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierMaterial extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'supplier_material';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'material_id', 
        'supplier_id', 
        'material_code',
        'employee_id',
        'created_at',
        'updated_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];

    public function supplier(){
        return $this->hasOne('App\Suppliers', 'id', 'supplier_id');
    }

    public function tracking(){
        return $this->hasMany('App\MaterialsTracking', 'supplier_material_id', 'id');
    }
}
