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

    public function test_reaction_types_method()
    {
        $reaction_types = Reaction::types();

        $this->assertInstanceOf(Like::class, $reaction_types[0]);
        $this->assertInstanceOf(Dislike::class, $reaction_types[1]);

        $reaction_types_with_friendly_name = Reaction::types(true);

        $this->assertEquals('like', $reaction_types_with_friendly_name[0]);
        $this->assertEquals('dislike', $reaction_types_with_friendly_name[1]);
    }

    public function test_disabled_in_reaction_types_method()
    {
        config(['me-likey.disabled' => ['dislike']]);

        $reaction_types = Reaction::types();

        $this->assertEquals(1, count($reaction_types));
        $this->assertInstanceOf(Like::class, $reaction_types[0]);

        $reaction_types_with_friendly_name = Reaction::types(true);

        $this->assertEquals(1, count($reaction_types_with_friendly_name));
        $this->assertEquals('like', $reaction_types_with_friendly_name[0]);
    }

    public function test_disabled_reaction_types() {
        $user_1 = User::create(['username' => 'testing_1']);
        $user_2 = User::create(['username' => 'testing_2']);

        $post_1 = Post::create(['title' => 'Test 1']);

        $user_1->react($post_1, 'like');
        $user_2->react($post_1, 'dislike');

        config(['me-likey.disabled' => ['dislike']]);

        $this->assertEquals(1, $post_1->reactions()->count());
        $this->assertInstanceOf(Like::class, app($post_1->reactions()->first()->type));
        $this->assertEquals(0, $user_2->reactions()->count());

        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('Reaction type is disabled.');

        $user_1->react($post_1, 'dislike');
    }

    public function test_reaction_type_counts_helper_method() {
        $user_1 = User::create(['username' => 'testing_1']);
        $user_2 = User::create(['username' => 'testing_2']);

        $post_1 = Post::create(['title' => 'Test 1']);

        $user_1->react($post_1, 'like');
        $user_2->react($post_1, 'like');

        $friendly_named_type_counts = $post_1->get_reaction_type_counts(true);

        $this->assertEquals(2, $friendly_named_type_counts['like']);
        $this->assertEquals(0, $friendly_named_type_counts['dislike']);

        $type_counts = $post_1->get_reaction_type_counts();

        $this->assertEquals(2, $type_counts['Camrymps\MeLikey\Reactions\Like']);
        $this->assertEquals(0, $type_counts['Camrymps\MeLikey\Reactions\Dislike']);
    }
}
