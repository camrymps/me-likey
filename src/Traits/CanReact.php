<?php

namespace Camrymps\MeLikey\Traits;

use Illuminate\Database\Eloquent\Model;

use Camrymps\MeLikey\Reaction;
use Camrymps\MeLikey\Reactions\ReactionInterface;

trait CanReact
{

    /**
     * Performs a reaction, or removes an existing one.
     *
     * @param Model $model
     * @param ReactionInterface $reaction_object
     */
    public function react(Model $model, ReactionInterface $reaction_object)
    {
        // Get the namespace of the passed reaction object
        $reaction_type = get_class($reaction_object);

        // If the reaction type is the same as the exiting one made by this user, remove the existing reaction
        if ($this->has_reacted($model) && $this->get_reaction($model)->first()->type === $reaction_type) {
            // Remove exiting reaction
            $this->unreact($model);

            // Nothing has changed
            return null;
        } else { // If the reaction type is different, remove the old reaction and add the new one
            // Create a new reaction object
            $reaction = new Reaction;

            // If there is an existing reaction, use remove it and use its ID for the new reaction
            if ($this->has_reacted($model)) {
                // Give this reaction the same ID as the exiting reaction
                $reaction->id = $this->get_reaction($model)->first()->id;

                // Remove existing reaction
                $this->unreact($model);
            }

            // Set the reaction's user ID
            $reaction->{config('me-likey.user_foreign_key')} = $this->getKey();

            // Set the reaction's type
            $reaction->type = $reaction_type;

            // Save the reaction in the database
            $reaction = $model->reactions()->save($reaction);

            return $reaction;
        }
    }

    /**
     * Remove a reaction, performed by this entity, from a specific model.
     *
     * @param Model $model
     */
    private function unreact(Model $model)
    {
        $this->get_reaction($model)->delete();
    }

    /**
     * Gets all the reactions.
     */
    public function reactions()
    {
        return $this->hasMany(
            Reaction::class,
            config('me-likey.user_foreign_key'),
            $this->getKeyName()
        );
    }

    /**
     * Checks if this entity reacted to a specific model.
     *
     * @param Model $model
     */
    public function has_reacted(Model $model)
    {
        return $this->get_reaction($model)
            ->exists();
    }

    /**
     * Gets the reactions made by this entity to a specific model.
     *
     * @param Model $model
     */
    private function get_reaction(Model $model)
    {
        return $model->reactions()
            ->where('reactionable_id', $model->getKey())
            ->where('reactionable_type', $model->getMorphClass())
            ->where(config('me-likey.user_foreign_key'), $this->getKey());
    }
}
