<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierSCMaterial extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'supplier_sc_material';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sc_material_id', 
        'supplier_id', 
        'sc_material_code',
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
        return $this->hasMany('App\SCMaterialsTracking', 'supplier_sc_material_id', 'id');
    }
}
