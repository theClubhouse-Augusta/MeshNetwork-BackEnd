<?php

namespace App;

class Workspace extends Model {

    protected $hidden = [
        'stripe',
    ];
    
    protected $fillable = [
        'name', 
        'email', 
        'password',
        'city',
        'address',
        'state',
        'zipcode',
        'website',
        'phone_number',
        'description',
        'facebook',
        'instagram',
        'linkedin'
    ];

}
