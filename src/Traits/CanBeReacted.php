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

    /**
     * Gets the count of each type of reaction on this model.
     *
     * @param bool $friendly_names
     */
    public function get_reaction_type_counts(bool $friendly_names = false)
    {
        $counts = [];

        foreach(Reaction::types() as $reaction_type) {
            if ($friendly_names) {
                $counts[$reaction_type->get_friendly_name()] = $this->reactions()->where('type', get_class($reaction_type))->count();
            } else {
                $counts[get_class($reaction_type)] = $this->reactions()->where('type', get_class($reaction_type))->count();
            }
        }

        return $counts;
    }
}
