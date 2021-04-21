<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientDetails extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'client_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id', 
        'client_name', 
        'client_province_id',
        'client_municipality_id',
        'client_address',
        'client_detail_status',
        'employee_id',
    ]; 

    protected $dates = ['deleted_at'];
}
