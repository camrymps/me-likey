<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;

use Camrymps\MeLikey\Traits\CanReact;

class User extends Model
{
    use CanReact;

    protected $fillable = ['username'];
}
