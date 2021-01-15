<?php

namespace Tests\Feature;

use App\Reputation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReputationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_earns_points_when_they_create_a_thread()
    {
        $thread = create('App\Thread');

        $this->assertEquals(Reputation::THREAD_WAS_PUBLISHED, $thread->creator->reputation);
    }

    /** @test */
    public function a_user_lose_points_when_they_delete_a_thread()
    {
        $this->signIn();

        $thread = create('App\Thread', ['user_id' => auth()->id()]);

        $this->assertEquals(Reputation::THREAD_WAS_PUBLISHED, $thread->creator->reputation);

        $this->delete($thread->path());

        $this->assertEquals(0, $thread->creator->fresh()->reputation);
    }

    /** @test */
    public function a_user_earns_points_when_they_reply_to_a_thread()
    {
        $user = create('App\User');

        create('App\Reply', ['user_id' => $user->id]);

        $this->assertEquals(Reputation::REPLY_POSTED, $user->fresh()->reputation);
    }

    /** @test */
    public function a_user_loses_points_when_their_reply_to_a_thread_is_deleted()
    {
        $this->signIn();

        $reply = create('App\Reply', ['user_id' => auth()->id()]);

        $this->assertEquals(Reputation::REPLY_POSTED, $reply->owner->reputation);

        $this->delete("/replies/{$reply->id}");

        $this->assertEquals(0, $reply->owner->fresh()->reputation);
    }

    /** @test */
    public function a_user_earns_points_when_their_reply_is_marked_as_best()
    {
        $thread = create('App\Thread');

        $reply = $thread->addReply([
            'user_id' => create('App\User')->id,
            'body' => 'here is a reply.',
        ]);

        $thread->markBestReply($reply);

        $this->assertEquals(Reputation::REPLY_POSTED + Reputation::BEST_REPLY_AWARDED, $reply->owner->reputation);
    }

    /** @test */
    public function a_user_earns_points_when_their_reply_is_favorited()
    {
        $this->signIn();

        $thread = create('App\Thread');

        $reply = $thread->addReply([
            'user_id' => auth()->id(),
            'body' => 'Some reply',
        ]);

        $this->post("/replies/{$reply->id}/favorites");

        $this->assertEquals(Reputation::REPLY_POSTED + Reputation::REPLY_FAVORITED, $reply->owner->fresh()->reputation);
    }

    /** @test */
    public function a_user_loses_points_when_their_favorited_reply_is_unfavorited()
    {
        $this->signIn();

        $reply = create('App\Reply', ['user_id' => auth()->id()]);

        $this->post("/replies/{$reply->id}/favorites");

        $this->assertEquals(Reputation::REPLY_POSTED + Reputation::REPLY_FAVORITED, $reply->owner->fresh()->reputation);

        $this->delete("/replies/{$reply->id}/favorites");

        $this->assertEquals(Reputation::REPLY_POSTED, $reply->owner->fresh()->reputation);

    }

}
