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
    public function a_user_earns_points_when_they_reply_to_a_thread()
    {
        $user = create('App\User');

        create('App\Reply', ['user_id' => $user->id]);

        $this->assertEquals(Reputation::REPLY_POSTED, $user->fresh()->reputation);
    }

    /** @test */
    public function a_user_earns_points_when_their_reply_is_marked_as_best()
    {
        $thread = create('App\Thread');

        $reply =$thread->addReply([
            'user_id' => create('App\User')->id,
            'body' => 'here is a reply.',
        ]);

        $thread->markBestReply($reply);

        $this->assertEquals(Reputation::REPLY_POSTED + Reputation::BEST_REPLY_AWARDED, $reply->owner->reputation);
    }

}
