<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientForecast extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'client_forecast';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id', 
        'forecast_number',
        'client_number', 
        'employee_id',
    ]; 

    protected $dates = ['deleted_at'];

    public function items(){
        return $this->hasMany('App\ClientForecastItems', 'forecasts_id', 'id');
    }
}
