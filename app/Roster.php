<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Roster extends Model {

    use Notifiable;

use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rosters';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['contest_id', 'user_id', 'player_id', 'player_cap_amount'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

}
