<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Filters\ThreadFilters;
use App\Rules\Recaptcha;
use App\Thread;
use App\Trending;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThreadController extends Controller
{
    /**
     * Create a new ThreadController instance.
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Channel|null $channel
     * @param ThreadFilters $filters
     * @param Trending $trending
     * @return View
     */
    public function index(Channel $channel, ThreadFilters $filters, Trending $trending)
    {
        $threads = $this->getThreads($filters, $channel);

        if (request()->wantsJson()) {
            return $threads;
        }

        return view('threads.index', [
            'threads' => $threads,
            'trending' => $trending->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('threads.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Recaptcha $recaptcha
     * @return \Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Recaptcha $recaptcha)
    {
//        dd(__METHOD__, \request()->all());

        request()->validate([
            'title' => 'required|spamfree',
            'body' => 'required|spamfree',
            'channel_id' => 'required|exists:channels,id',
            'g-recaptcha-response' => [$recaptcha],
        ]);

        $thread = Thread::create([
            'user_id' => auth()->id(),
            'channel_id' => request('channel_id'),
            'title' => request('title'),
            'body' => request('body'),
        ]);

        if (request()->wantsJson()) {
            return response($thread, 201);
        }

        return redirect($thread->path())
            ->with('flash', 'Your thread has been published!');
    }

    /**
     * Display the specified resource.
     *
     * @param $channelId
     * @param \App\Thread $thread
     * @param Trending $trending
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|View
     */
    public function show($channelId, Thread $thread, Trending $trending)
    {
        if (auth()->check()) {
            auth()->user()->read($thread);
        }

        $trending->push($thread);

        $thread->increment('visits');

        return view('threads.show', compact('thread'));
    }

    public function update($channel, Thread $thread)
    {
        // authorization
        $this->authorize('update', $thread);

        $thread->update(request()->validate([
            'title' => 'required|spamfree',
            'body' => 'required|spamfree',
        ]));

        return $thread;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Channel $channel
     * @param \App\Thread $thread
     */
    public function destroy($channel, Thread $thread)
    {
        $this->authorize('update', $thread);

//        if($thread->user_id != auth()->id()){
//           abort(403, 'You do not have permission to do this.');
//        }
        $thread->delete();

        if (\request()->wantsJson()) {
            return response([], 204);
        }

        return redirect('/threads');
    }

    /**
     * @param ThreadFilters $filters
     * @param Channel|null $channel
     * @return mixed
     */
    protected function getThreads(ThreadFilters $filters, ?Channel $channel)
    {
        $threads = Thread::orderBy('pinned', 'DESC')
            ->latest()
            ->filter($filters);

        if ($channel->exists) {
            $threads->where('channel_id', $channel->id);
        }

        $threads = $threads->paginate(25);

        return $threads;
    }
}
