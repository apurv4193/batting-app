<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GamesPlayers extends Model {

    use Notifiable;

    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'games_players';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['game_id', 'player_id', 'cap_amount', 'win', 'loss'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function contest() {
        return $this->belongsToMany('App\Contest', 'rosters', 'player_id', 'contest_id');
    }

    public function game() {
        return $this->belongsTo('App\Game');
    }

    public function players() {
        return $this->hasMany('App\Players');
    }

    public function scopeSearchName($query, $value) {

        return $query->where('games.name', 'LIKE', "%$value%");
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

    

    public function scopeSearchCapAmount($query, $value) {

        return $query->where('games_players.cap_amount', 'LIKE', "%$value%");
    }
}
