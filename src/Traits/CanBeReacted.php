<?php

namespace Camrymps\MeLikey\Traits;

use Illuminate\Database\Eloquent\Model;
use Camrymps\MeLikey\Reaction;

trait CanBeReacted
{
    /**
     * Get all of the reactions linked to this model.
     */
    public function reactions()
    {
        return $this->morphMany(
            config('me-likey.reaction_model'),
            'reactionable'
        )->whereNotIn('type', array_map(function($disabled_friendly_name) {
            return get_class(Reaction::get_type_by_friendly_name($disabled_friendly_name));
        }, config('me-likey.disabled')));
    }
}
