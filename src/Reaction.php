<?php

namespace Camrymps\MeLikey;

use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    /**
     * @var Camrymps\MeLikey\Reaction
     */
    public $replaced = null;

    /**
     * @var bool
     */
    public $revoked = false;

    /**
     * @var array
     */
    public $appends = [
        'replaced',
        'revoked'
    ];

    /**
     * Get all of the models that own reactions.
     */
    public function reactionable()
    {
        return $this->morphTo();
    }

    /**
     * Get the model's replaced attribute.
     */
    public function getReplacedAttribute()
    {
        return $this->replaced;
    }

    /**
     * Get the model's revoked attribute.
     */
    public function getRevokedAttribute()
    {
        return $this->revoked;
    }
}
