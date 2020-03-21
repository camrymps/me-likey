<?php

namespace Camrymps\MeLikey\Traits;

use Illuminate\Database\Eloquent\Model;

use Camrymps\MeLikey\Reaction;
use Camrymps\MeLikey\Reactions\ReactionInterface;

trait CanBeReacted
{
    /**
     * Get all of the reactions linked to this model.
     */
    public function reactions()
    {
        return $this->morphMany(Reaction::class, 'reactionable');
    }
}