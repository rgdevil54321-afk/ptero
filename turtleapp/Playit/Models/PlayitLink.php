<?php

namespace Turtle\Playit\Models;

use Illuminate\Database\Eloquent\Model;

class PlayitLink extends Model
{
    protected $table = 'playit_links';

    protected $guarded = [];

    protected $casts = [
        'server_id' => 'integer',
        'port' => 'integer',
        'last_synced_at' => 'datetime',
    ];
}
