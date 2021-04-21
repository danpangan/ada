<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuotationCS extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'quotation_cs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quotation_id',  
        'cs_id',
        'cs_control_no',  
        'cs_qty',
        'cs_unit_amount',  
        'cs_unit_amount_currency',  
        'cs_unit_amount_php',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];

    public function cs(){
        return $this->hasMany('App\ComputationSheet', 'cs_client_id', 'cs_id');
    }
}
