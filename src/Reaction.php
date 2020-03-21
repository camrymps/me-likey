<?php

namespace Camrymps\MeLikey;

use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    protected $casts = ['type' => 'object'];

    /**
     * Get all of the models that own reactions.
     */
    public function reactionable()
    {
        return $this->morphTo();
    }
}
