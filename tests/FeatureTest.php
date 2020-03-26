<?php

namespace Tests;

use Camrymps\MeLikey\Reaction;
use Camrymps\MeLikey\Reactions\{
    Dislike, Like
};

class FeatureTest extends TestCase
{

    public function test_reaction_types()
    {
        $user = User::create(['username' => 'testing']);

        $post_1 = Post::create(['title' => 'Test 1']);
        $post_2 = Post::create(['title' => 'Test 2']);

        $reaction_1 = $user->react($post_1, new Dislike);
        $reaction_2 = $user->react($post_2, new Like);

        $this->assertInstanceOf(Reaction::class, $reaction_1);
        $this->assertInstanceOf(Dislike::class, app($reaction_1->type));

        $this->assertInstanceOf(Reaction::class, $reaction_2);
        $this->assertInstanceOf(Like::class, app($reaction_2->type));
    }

    public function test_reaction_replacement()
    {
        $user = User::create(['username' => 'testing']);

        $post = Post::create(['title' => 'Test']);

        $user->react($post, new Like);
        $reaction = $user->react($post, new Dislike);

        $stored_reaction = $user->reactions()
            ->where('reactionable_type', Post::class)
            ->where('reactionable_id', $post->id)
            ->first();

        $this->assertInstanceOf(Reaction::class, $reaction);

        $this->assertInstanceOf(Like::class, app($reaction->replaced));

        $this->assertInstanceOf(Reaction::class, $stored_reaction);
        $this->assertInstanceOf(Dislike::class, app($stored_reaction->type));

        $this->assertEquals($post->id, $stored_reaction->id);
    }

    public function test_reaction_removal()
    {
        $user = User::create(['username' => 'testing']);

        $post = Post::create(['title' => 'Test']);

        $user->react($post, new Like);
        $reaction = $user->react($post, new Like);

        $this->assertNull($reaction);
        $this->assertFalse($user->has_reacted($post));
    }

    public function test_reaction_retrieval()
    {
        $user = User::create(['username' => 'testing']);

        $post_1 = Post::create(['title' => 'Test 1']);
        $post_2 = Post::create(['title' => 'Test 2']);
        $post_3 = Post::create(['title' => 'Test 3']);

        $user->react($post_1, new Dislike);
        $user->react($post_2, new Like);
        $user->react($post_3, new Like);

        $reactions = $user->reactions();

        $first_reaction = $reactions->first();
        $second_reaction = $reactions->skip(1)->first();
        $third_reaction = $reactions->skip(2)->first();

        $this->assertInstanceOf(Reaction::class, $first_reaction);
        $this->assertInstanceOf(Reaction::class, $second_reaction);
        $this->assertInstanceOf(Reaction::class, $third_reaction);

        $this->assertInstanceOf(Dislike::class, app($first_reaction->type));
        $this->assertInstanceOf(Like::class, app($second_reaction->type));
        $this->assertInstanceOf(Like::class, app($third_reaction->type));
    }

}
