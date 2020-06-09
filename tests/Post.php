<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;

use Camrymps\MeLikey\Traits\CanBeReacted;

class Post extends Model
{
    use CanBeReacted;

    protected $fillable = ['title'];
}
