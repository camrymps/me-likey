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
     * Get all of the models that own reactions.
     */
    public function reactionable()
    {
        return $this->morphTo();
    }
}
