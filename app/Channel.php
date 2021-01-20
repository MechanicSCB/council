<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $guarded = [];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected $casts = [
        'archived' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('archived', false)
            ->orderBy('name', 'asc');
        });
    }

    public function threads()
    {
        return $this->hasMany('App\Thread');
    }

    public function archive()
    {
        $this->update(['archived' => true]);
    }
}
