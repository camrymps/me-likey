<?php

namespace Camrymps\MeLikey\Reactions;

trait ReactionTrait
{
    /**
     * Gets the "friendly name" of this reaction.
     */
    public function get_friendly_name()
    {
        $class_name = static::class;
        $class_name_parts = explode('\\', $class_name);

        return strtolower(end($class_name_parts));
    }
}
