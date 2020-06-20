<?php

return [

    /**
     * Reactions table name
     */
    'reactions_table' => 'reactions',

    /**
     * Reaction model name
     */
    'reaction_model' => \Camrymps\MeLikey\Reaction::class,

    /*
     * The user table's foreign key name
     */
    'user_foreign_key' => 'user_id',

    /**
     * Sort order for reaction types
     *
     * @see \Camrymps\MeLikey\Reaction::types()
     */
    'sort_positions' => [
        'like' => 1,
        'dislike' => 2
    ],

    /**
     * Disabled reaction types
     */
    'disabled' => []
];
