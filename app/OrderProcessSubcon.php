<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderProcessSubcon extends Model
{ 
    use HasApiTokens, SoftDeletes;

    protected $table = 'order_processes_subcon';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'order_process_subcon',	
        'order_process_subcon_description',	
        'employee_id',	
        'created_at',	
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}