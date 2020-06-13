<?php

namespace Camrymps\MeLikey;

use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    /**
     * @var Camrymps\MeLikey\Reaction
     */
    public $replaced = null;

    /**
     * @var bool
     */
    public $revoked = false;

    /**
     * @var array
     */
    public $appends = [
        'replaced',
        'revoked'
    ];

    /**
     * Get all of the models that own reactions.
     */
    public function reactionable()
    {
        return $this->morphTo();
    }

    /**
     * Lists, and sorts, all of the reaction types (like, dislike, etc.).
     *
     * @param bool $friendly_names
     */
    public static function types($friendly_names = false)
    {
        $reaction_types = [];
        $reaction_paths = glob(__DIR__ . '/Reactions/*[!Trait].php');

        foreach($reaction_paths as $reaction_path) {
            $path_parts = explode('/', $reaction_path);
            $reaction_filename = end($path_parts);
            $reaction_type = \Camrymps\MeLikey\Reactions::class . '\\' . substr($reaction_filename, 0, strpos($reaction_filename, '.php'));

            if ($friendly_names) {
                array_push($reaction_types, app($reaction_type)->get_friendly_name());
            } else {
                array_push($reaction_types, app($reaction_type));
            }
        }

        usort($reaction_types, function($cur, $nxt) use ($friendly_names) {
            $csp_index = $friendly_names ? $cur : $cur->get_friendly_name();
            $nsp_index = $friendly_names ? $nxt : $nxt->get_friendly_name();

            $csp = array_key_exists($csp_index, config('me-likey.sort_positions')) ? config('me-likey.sort_positions')[$csp_index] : 0;
            $nsp = array_key_exists($nsp_index, config('me-likey.sort_positions')) ? config('me-likey.sort_positions')[$nsp_index] : 0;

            if ($csp == $nsp) {
                return 0;
            }

            return ($csp < $nsp) ? -1 : 1;
        });

        return $reaction_types;
    }

    /**
     * Get the model's replaced attribute.
     */
    public function getReplacedAttribute()
    {
        return $this->replaced;
    }

    /**
     * Get the model's revoked attribute.
     */
    public function getRevokedAttribute()
    {
        return $this->revoked;
    }
}
