<?php

namespace App\Http\Controllers\Admin;

use App\Channel;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class ChannelController extends Controller
{
    public function index()
    {
        return view('admin.channels.index')->with('channels', Channel::with('threads')->get());
    }

    public function create()
    {
        return view('admin.channels.create', ['channel' => new Channel]);
    }

    /**
     * Show the form to edit an existing channel.
     *
     * @param Channel $channel
     * @return \Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function edit(Channel $channel)
    {
        return view('admin.channels.edit', compact('channel'));
    }

    /**
     * Update an existing channel.
     *
     * @param Channel $channel
     * @return \Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function update(Channel $channel)
    {
        $channel->update(
            request()->validate([
                'name' => ['required', Rule::unique('channels')->ignore($channel->id)],
                'description' => 'required',
                'archived' => 'required|boolean',
            ])
        );

        cache()->forget('channels');

        if (request()->wantsJson()) {
            return response($channel, 200);
        }

        return redirect(route('admin.channels.index'))
            ->with('flash', 'Your channel has been updated!');
    }

    public function store()
    {
        $data = request()->validate([
            'name' => 'required|unique:channels',
            'description' => 'required',
        ]);

        $channel = Channel::create($data + ['slug' => str_slug($data['name'])]);

        Cache::forget('channels');

        if (request()->wantsJson()) {
            return response($channel, 201);
        }

        return redirect(route('admin.channels.index'))
            ->with('flash', 'Your channel has been created!');
    }
}
