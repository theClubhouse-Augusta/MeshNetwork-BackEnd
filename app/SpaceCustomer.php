<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpaceCustomer extends Model
{
    protected $fillable = [
        'userID',
        'spaceID',
        'customerID'
    ];
}
