<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierContacts extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'supplier_contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'supplier_id', 
        'supplier_contact_name', 
        'supplier_contact_email',
        'employee_id',
    ]; 

    protected $dates = ['deleted_at'];
}
