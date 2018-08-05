<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeagueInvitedUser extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'league_invited_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['league_id', 'user_id'];

    /**
     * The attributes that are dates
     *
     * @var array
     */

}
