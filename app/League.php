<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Config;

class League extends Model {

    use Notifiable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'league';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['game_id', 'contest_type_id', 'level_id', 'league_name', 'league_start_date', 'league_end_date', 'league_min_participants', 'created_by', 'status'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['league_start_date', 'league_end_date'];

    public function userInLeague() {
        return $this->belongsToMany('App\User', 'league_invited_user');
    }

    public function contest() {
        return $this->hasMany('App\Contest', 'league_id');
    }

    public function leagueInvitedUser() {
        return $this->hasMany('App\LeagueInvitedUser', 'league_id');
    }

}
