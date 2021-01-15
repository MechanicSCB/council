<?php

namespace Tests\Feature;

use App\Thread;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

//class SearchTest extends TestCase
//{
//    use RefreshDatabase;
//
//    /** @test */
//    public function a_user_can_search_threads()
//    {
//        config(['scout.driver' => 'algolia']);
//
//        $search = 'foobar';
//
//        create('App\Thread', [], 2);
//        create('App\Thread', ['body' => "A thread with {$search} term"], 2);
//
//        do{
//            sleep(.25);
//
//            $results = $this->getJson("/threads/search?q={$search}")->json()['data'];
//        }while(empty($results));
//
//
//        $this->assertCount(2, $results);
//
//        Thread::latest()->take(4)->unsearchable();
//    }
//}
