<?php

namespace Turtle\Playit\Models;

use Illuminate\Database\Eloquent\Model;

class PlayitAgent extends Model
{
    protected $table = 'playit_agents';

    protected $guarded = [];

    protected $hidden = ['secret_key'];

    protected $casts = [
        'server_id' => 'integer',
        'port' => 'integer',
        'last_synced_at' => 'datetime',
    ];

    public function isAssigned(): bool
    {
        return !empty($this->server_uuid);
    }
}
