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
     * Lists, and sorts all of the reaction types (like, dislike, etc.).
     *
     * @param bool $friendly_names
     * @param bool $include_disabled
     */
    public static function types(bool $friendly_names = false, bool $include_disabled = false)
    {
        $reaction_types = [];
        $reaction_paths = glob(__DIR__ . '/Reactions/*[!Trait].php');

        foreach($reaction_paths as $reaction_path) {
            $path_parts = explode('/', $reaction_path);
            $reaction_filename = end($path_parts);
            $reaction_type = app(\Camrymps\MeLikey\Reactions::class . '\\' . substr($reaction_filename, 0, strpos($reaction_filename, '.php')));

            if ($include_disabled) {
                array_push($reaction_types, $reaction_type);
            } else {
                if (!in_array($reaction_type->get_friendly_name(), config('me-likey.disabled'))) {
                    array_push($reaction_types, $reaction_type);
                }
            }
        }

        usort($reaction_types, function($cur, $nxt) {
            $csp_index = $cur->get_friendly_name();
            $nsp_index = $nxt->get_friendly_name();

            $csp = array_key_exists($csp_index, config('me-likey.sort_positions')) ? config('me-likey.sort_positions')[$csp_index] : 0;
            $nsp = array_key_exists($nsp_index, config('me-likey.sort_positions')) ? config('me-likey.sort_positions')[$nsp_index] : 0;

            if ($csp == $nsp) {
                return 0;
            }

            return ($csp < $nsp) ? -1 : 1;
        });

        if ($friendly_names) {
            $reaction_types = array_map(function($reaction_type) {
                return $reaction_type->get_friendly_name();
            }, $reaction_types);
        }

        return $reaction_types;
    }

    /**
     * Get a reaction type by friendly name.
     *
     * @param string $friendly_name
     */
    public static function get_type_by_friendly_name(string $friendly_name)
    {
        $reaction_types = self::types(false, true);

        foreach($reaction_types as $reaction_type) {
            if ($reaction_type->get_friendly_name() === $friendly_name) {
                return $reaction_type;
            }
        }

        return null;
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
