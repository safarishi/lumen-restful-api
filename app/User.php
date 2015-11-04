<?php

namespace App;

use Jenssegers\Mongodb\Model as Eloquent;

class User extends Eloquent
{

    protected $connection = 'mongodb';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $collection  = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];
}
