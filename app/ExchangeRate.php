<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExchangeRate extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'exchange_rate';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'conversion_id', 
        'rate', 
        'employee_id',
    ]; 
}
