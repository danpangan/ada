<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'clients';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_name', 
        'client_short_name',
        'client_order_description',
        'client_number',
        'client_no',
        'client_province_id',
        'client_municipality_id',
        'client_address', 
        'client_contact',
        'client_fax',
        'client_pay_terms',
        'start_date',
        'is_temporary',
        'is_active',
        'isSameAsCustomerDetails',
        'isSameAsBillingDetails',
        'isCustomerConsignee',
        'employee_id',
    ]; 

    protected $dates = ['deleted_at'];

    public function clientDetails(){
        return $this->hasMany('App\ClientDetails', 'client_id', 'id');
    }
}
