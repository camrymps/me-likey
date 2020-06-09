<?php

namespace Camrymps\MeLikey\Traits;

use Illuminate\Database\Eloquent\Model;

trait CanBeReacted
{
    /**
     * Get all of the reactions linked to this model.
     */
    public function reactions()
    {
        return $this->morphMany(config('me-likey.reaction_model'), 'reactionable');
    }
}
