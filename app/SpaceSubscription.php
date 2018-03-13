<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpaceSubscription extends Model
{
    protected $fillable = [
        'userID',
        'spaceID',
        'subscriptionID'
    ];
}
