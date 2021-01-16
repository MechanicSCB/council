<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Stevebauman\Purify\Facades\Purify;

/**
 * * App\Reply.
 *
 * @property int $id
 * @property string $body
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Reply extends Model
{
    use Favoritable, RecordsActivity;

    protected $guarded = [];

    public function path()
    {
        return $this->thread->path().'#reply-'.$this->id;
    }

    protected $with = ['owner', 'favorites'];

    protected $appends = ['favoritesCount', 'isFavorited', 'isBest'];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($reply) {
            $reply->thread->increment('replies_count');

            Reputation::award($reply->owner, Reputation::REPLY_POSTED);
        });

        static::deleted(function ($reply) {
            $reply->thread->decrement('replies_count');

            Reputation::reduce($reply->owner, Reputation::REPLY_POSTED);
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public function wasJustPublished()
    {
        return $this->created_at->gt(Carbon::now()->subMinute());
    }

    public function mentionedUsers()
    {
//        preg_match_all('/\@([^\s\.]+)/', $this->body, $names);
        preg_match_all('/@([\w\-\_]+)/', $this->body, $names);

        return $names[1];
    }

    /**
     * @param $body
     */
    public function setBodyAttribute($body)
    {
        $this->attributes['body'] = preg_replace('/@([\w\-\_]+)/', '<a href="/profiles/$1">$0</a>', $body);
    }

    public function isBest()
    {
        return $this->thread->best_reply_id == $this->id;
    }

    public function getIsBestAttribute()
    {
        return $this->isBest();
    }

    public function getBodyAttribute($body)
    {
        return Purify::clean($body);
    }
}
