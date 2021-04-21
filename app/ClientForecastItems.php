<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientForecastItems extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'client_forecast_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id', 
        'forecast_id', 
        'forecast_month', 
        'forecast_year', 
        'forecast_quantity', 
        'forecast_quantity_unit', 
        'employee_id',
    ]; 

    protected $dates = ['deleted_at'];

    public function forecast(){
        return $this->belongsTo('App\ClientForecast');
    }
}
