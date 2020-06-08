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
     * @param string $reaction_friendly_name
     */
    public function react(Model $model, string $reaction_friendly_name)
    {
        $reactions = $this->get_reactions($model);

        // Get the namespace of the passed reaction object
        $reaction_type_class = app(\Camrymps\MeLikey\Reactions::class . '\\' . ucfirst($reaction_friendly_name));
        $reaction_type = get_class($reaction_type_class);

        // If the reaction type is the same as the exiting one made by this user, remove the existing reaction (revoke)
        if ($this->has_reacted($model) && $reactions->first()->type === $reaction_type) {
            $revoked_reaction = $reactions->first();

            $revoked_reaction->revoked = true;

            // Remove exiting reaction
            $this->unreact($model);

            return $revoked_reaction;
        } else {
            // Create a new reaction object
            $reaction = new Reaction;

            // If there is an existing reaction, remove it and use its ID for the new reaction (replace)
            if ($this->has_reacted($model)) {
                $replaced_reaction = $reactions->first();

                // Give this reaction the same ID as the exiting reaction
                $reaction->id = $replaced_reaction->id;

                // Remove existing reaction
                $this->unreact($model);

                $reaction->replaced = $replaced_reaction;
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
        // $this->get_reaction($model)->delete();
        $reaction = $this->get_reactions($model)->first();

        if (!is_null($reaction)) {
            $reaction->delete();
        }
    }

    /**
     * Gets all the reactions.
     */
    public function reactions()
    {
        return $this->hasMany(
            config('me-likey.reaction_model'),
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
        $reactions = $this->get_reactions($model);

        return !is_null($reactions) && $reactions->exists() ? true : false;
    }

    /**
     * Gets the reactions made by this entity on a specific model.
     *
     * @param Model $model
     */
    private function get_reactions(Model $model)
    {
        return !is_null($this->reactions()) ?
            $this->reactions()
                ->where('reactionable_id', $model->getKey())
                ->where('reactionable_type', $model->getMorphClass())
                ->where(config('me-likey.user_foreign_key'), $this->getKey())
            : null;
    }
}
