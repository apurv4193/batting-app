<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Config;

class Team extends Model {

    use Notifiable;

use SoftDeletes;

    /*
     *  Teams Mysql table Name
     */

    protected $table = 'teams';
    /*
     * Players Mysql table fileds(Colume) Name
     */
    protected $fillable = ['name', 'game_id', 'contest_type_id', 'team_image', 'team_cap_amount', 'win', 'loss', 'status'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function scopeSearchName($query, $value) {
        return $query->Where('teams.name', 'LIKE', "%$value%");
    }

    public function scopeSearchGameName($query, $value) {
        return $query->orWhere('games.name', 'LIKE', "%$value%");
    }

    public function scopeSearchContestType($query, $value) {
        return $query->orWhere('type', 'LIKE', "%$value%");
    }
    
    public function scopeSearchWin($query, $value) {
        return $query->orWhere('win', 'LIKE', "%$value%");
    }

    public function scopeSearchLoss($query, $value) {
        return $query->orWhere('loss', 'LIKE', "%$value%");
    }

    public function scopeSearchTeamCapAmount($query, $value) {
        return $query->orWhere('team_cap_amount', 'LIKE', "%$value%");
    }

    public function scopeTeamWithContestType($query, $value) {
        return $query->where('contest_type_id', $value);
    }

    public function scopeSearch($query, $value) {
        return $query->Where('name', 'LIKE', "%$value%")
                    ->orWhere('team_cap_amount', 'LIKE', "%$value%")
                    ->orWhere('win', 'LIKE', "%$value%")
                    ->orWhere('loss', 'LIKE', "%$value%");
    }
    
    public function scopeSort($query, $value) {
        return $query->orderBy($value['column'], $value['sort']);
    }
    
    public function scopeNotDeleted($query) {
        return $query->Where('status', Config::get('constant.NOT_DELETED'));
    }

    public function players() {
        return $this->belongsToMany('App\Players', 'teams_players', 'team_id', 'player_id')->withPivot('team_player_cap_amount');
    }

    public function game() {
        return $this->belongsTo('App\Game');
    }

    public function contestType() {
        return $this->belongsTo('App\ContestType');
    }
}
