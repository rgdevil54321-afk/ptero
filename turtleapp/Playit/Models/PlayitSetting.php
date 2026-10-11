<?php

namespace Turtle\Playit\Models;

use Illuminate\Database\Eloquent\Model;

class PlayitSetting extends Model
{
    protected $table = 'playit_settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public $timestamps = true;
}
