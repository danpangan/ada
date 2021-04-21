<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Municipalities extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'municipalities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'province_id', 
        'municipality_name',
        'is_customer_active',
        'is_supplier_active',
        'employee_id',
    ]; 

    protected $dates = ['deleted_at'];

    public function province(){
        return $this->belongsTo('App\Provinces');
    }
}
