<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientDetailContacts extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'client_detail_contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_detail_id',
        'client_pic_title',
        'client_pic',
        'client_pic_contact',
        'client_pic_email',
        'client_pic_department',
        'employee_id',
    ]; 

    protected $dates = ['deleted_at'];
}
