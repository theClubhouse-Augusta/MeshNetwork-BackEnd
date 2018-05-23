<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
  protected $fillable = [
    'name',
    'employeeCount',
    'description',
    'url',
    'userID',
    'facebook',
    'instagram',
    'pinterest',
    'twitter',
    'youtube',
    'linkedin',
    'snapchat',
    'discord',
    'foundingDate',
    'zipcode',
    'city',
    'email',
    'state',
    'address',
  ];
}
