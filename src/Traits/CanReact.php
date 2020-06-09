<?php

namespace Camrymps\MeLikey\Traits;

use Illuminate\Database\Eloquent\Model;

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
        $reaction_model = app(config('me-likey.reaction_model'));

        // Get the namespace of the passed reaction object
        $reaction_type_class = app(\Camrymps\MeLikey\Reactions::class . '\\' . ucfirst($reaction_friendly_name));
        $reaction_type = get_class($reaction_type_class);

        // If the reaction type is the same as the exiting one made by this user, remove the existing reaction (revoke)
        if ($this->has_reacted($model)) {
            // Get the reaction that currently exists on this model
            $current_reaction = $this->get_reactions($model, true)->first();

            // If the current reaction type is the same as the new reaction type...
            if ($current_reaction->type === $reaction_type) {
                $current_reaction->revoked = true;

                // Remove exiting reaction
                $this->unreact($model);

                return $current_reaction;
            } else { // There is an existing reaction. Remove the existing reaction and replace with the new one
                // Create a new reaction object
                $new_reaction = new $reaction_model;

                // Give this reaction the same ID as the exiting reaction
                $new_reaction->id = $current_reaction->id;

                // Set the reaction's user ID
                $new_reaction->{config('me-likey.user_foreign_key')} = $this->getKey();

                // Set the reaction's type
                $new_reaction->type = $reaction_type;

                $new_reaction->replaced = $current_reaction;

                // Remove existing reaction
                $this->unreact($model);

                // Save the new reaction in the database
                $reaction = $model->reactions()->save($new_reaction);

                return $reaction;
            }
        } else {
            // Create a new reaction object
            $new_reaction = new $reaction_model;

            // Set the reaction's user ID
            $new_reaction->{config('me-likey.user_foreign_key')} = $this->getKey();

            // Set the reaction's type
            $new_reaction->type = $reaction_type;

            // Save the reaction in the database
            $reaction = $model->reactions()->save($new_reaction);

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
        $reaction = $this->get_reactions($model, true)->first();

        if ($reaction) {
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

        return $reactions->exists() ? true : false;
    }

    /**
     * Gets the reactions made by this entity on a specific model.
     *
     * @param Model $model
     * @param bool $include_user_filter
     */
    private function get_reactions(Model $model, bool $include_user_filter = false)
    {
        $reactions = $this->reactions()
            ->where('reactionable_id', $model->getKey())
            ->where('reactionable_type', $model->getMorphClass());

        if ($include_user_filter) {
            return $reactions
                ->where(config('me-likey.user_foreign_key'), $this->getKey());
        }

        return $reactions;
    }
}
