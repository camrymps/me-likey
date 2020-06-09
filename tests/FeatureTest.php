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

        $reaction_1 = $user->react($post_1, 'dislike');
        $reaction_2 = $user->react($post_2, 'like');

        $this->assertInstanceOf(Reaction::class, $reaction_1);
        $this->assertInstanceOf(Dislike::class, app($reaction_1->type));

        $this->assertInstanceOf(Reaction::class, $reaction_2);
        $this->assertInstanceOf(Like::class, app($reaction_2->type));
    }

    public function test_reaction_replacement()
    {
        $user = User::create(['username' => 'testing']);

        $post = Post::create(['title' => 'Test']);

        $user->react($post, 'like');
        $reaction = $user->react($post, 'dislike');

        $stored_reaction = $user->reactions()
            ->where('reactionable_type', Post::class)
            ->where('reactionable_id', $post->id)
            ->first();

        $this->assertInstanceOf(Reaction::class, $reaction);

        $this->assertInstanceOf(Reaction::class, $reaction->replaced);
        $this->assertInstanceOf(Like::class, app($reaction->replaced->type));

        $this->assertInstanceOf(Reaction::class, $stored_reaction);
        $this->assertInstanceOf(Dislike::class, app($stored_reaction->type));

        $this->assertEquals($post->id, $stored_reaction->id);
    }

    public function test_reaction_removal()
    {
        $user = User::create(['username' => 'testing']);

        $post = Post::create(['title' => 'Test']);

        $user->react($post, 'like');
        $reaction = $user->react($post, 'like');

        $this->assertInstanceOf(Like::class, app($reaction->type));
        $this->assertTrue($reaction->revoked);
        $this->assertFalse($user->has_reacted($post));
    }

    public function test_reaction_retrieval()
    {
        $user = User::create(['username' => 'testing']);

        $post_1 = Post::create(['title' => 'Test 1']);
        $post_2 = Post::create(['title' => 'Test 2']);
        $post_3 = Post::create(['title' => 'Test 3']);

        $user->react($post_1, 'dislike');
        $user->react($post_2, 'like');
        $user->react($post_3, 'like');

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

    public function test_reaction_trait()
    {
        $like = app(Like::class);
        $dislike = app(Dislike::class);

        $this->assertEquals('like', $like->get_friendly_name());
        $this->assertEquals('dislike', $dislike->get_friendly_name());
    }

    public function test_commands()
    {
        \Artisan::call('reaction:create SuperLike');

        $new_reaction_path = \dirname(__DIR__) . '/src/Reactions/SuperLike.php';

        $this->assertTrue(\File::exists($new_reaction_path));

        \File::delete($new_reaction_path);
    }
}
