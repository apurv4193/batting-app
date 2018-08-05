<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Players extends Model {

    use Notifiable;

use SoftDeletes;

    /*
     *  Players Mysql table Name
     */

    protected $table = 'players';
    /*
     * Players Mysql table fileds(Colume) Name
     */
    protected $fillable = ['name', 'description', 'profile_image','status'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function scopeSearchName($query, $value) {
        return $query->Where('name', 'LIKE', "%$value%");
    }

    public function scopeSearch($query, $value) {
        return $query->Where('name', 'LIKE', "%$value%")
                    ->orWhere('cap_amount', 'LIKE', "%$value%")
                    ->orWhere('win', 'LIKE', "%$value%")
                    ->orWhere('loss', 'LIKE', "%$value%");
    }
    
    public function scopeSort($query, $value) {
        return $query->orderBy($value['column'], $value['sort']);
    }

    public function contest() {
        return $this->belongsToMany('App\Contest', 'rosters', 'player_id', 'contest_id');
    }

    public function game() {
        return $this->belongsToMany('App\Game', 'games_players', 'player_id', 'game_id')->withPivot('cap_amount', 'win', 'loss');
    }

}
