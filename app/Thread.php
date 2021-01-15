<?php

namespace App;

use App\Events\ThreadReceivedNewReply;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Stevebauman\Purify\Facades\Purify;

/**
 * * App\Thread
 *
 * @property int $id
 * @property int $user_id
 * @property int $channel_id
 * @property string $title
 * @property string $body
 */
class Thread extends Model
{
    use RecordsActivity, Searchable;

    protected $guarded = [];

    protected $with = ['creator', 'channel'];

    protected $appends = ['isSubscribedTo'];

    protected $casts = [
        'locked' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($thread) {
            $thread->replies->each->delete();

            Reputation::reduce($thread->creator, Reputation::THREAD_WAS_PUBLISHED);
        });

        static::created(function ($thread) {
            $thread->update(['slug' => $thread->title]);

            Reputation::award($thread->creator, Reputation::THREAD_WAS_PUBLISHED);
        });
    }

    public function path()
    {
        return "/threads/{$this->channel->slug}/{$this->slug}";
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * @param $reply
     * @return Model
     */
    public function addReply($reply)
    {
        $reply = $this->replies()->create($reply);

        event(new ThreadReceivedNewReply($reply));

        return $reply;
    }

    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }

    public function subscriptions()
    {
        return $this->hasMany(ThreadSubscription::class);
    }

    /**
     * @param int|null $userId
     * @return $this
     */
    public function subscribe($userId = null)
    {
        $this->subscriptions()->create([
            'user_id' => $userId ?: auth()->id(),
        ]);

        return $this;
    }

    public function unsubscribe($userId = null)
    {
        return $this->subscriptions()
            ->where('user_id', $userId ?: auth()->id())
            ->delete();
    }

    public function getIsSubscribedToAttribute()
    {
        return $this->subscriptions()
            ->where('user_id', auth()->id())
            ->exists();
    }

    /**
     * Determine if the thread has been updated since the user last read it.
     *
     * @param User|null $user
     * @return bool
     * @throws \Exception
     */
    public function hasUpdatesFor(User $user = null)
    {
        $key = $user->visitedThreadCacheKey($this);

        return $this->updated_at > cache($key);
    }

    public function setSlugAttribute($value)
    {
        $slug = $original = str_slug($value);
        $count = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$original}-" . $count++;
        }

//        if (static::where('slug',$slug)->exists()) {
//            $slug = "{$original}-" . $this->id;
//        }

        $this->attributes['slug'] = $slug;
    }

    public function markBestReply(Reply $reply)
    {
        $this->best_reply_id = $reply->id;

        $this->save();

        Reputation::award($reply->owner, Reputation::BEST_REPLY_AWARDED);
    }

    public function getBodyAttribute($body)
    {
        return Purify::clean($body);
    }

}
