<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\User;
use DB;
use Config;

class Game extends Model {

    use Notifiable;

use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'games';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'status'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function scopeSearchName($query, $value) {
        return $query->Where('name', 'LIKE', "%$value%");
    }

    public function contests() {
        return $this->hasMany('App\Contest');
    }

    public function scopeNotDeleted($query) {
        return $query->Where('status', Config::get('constant.ACTIVE_STATUS_FLAG'));
    }
    /**
     * Get upcoming contest count of game
     * @return type
     */
    public function contestsCount() {
        $friendList = DB::table('friends')
                        ->where('receiver_id', User::getCurrentUser()->id)
                        ->select(
                            'requester_id'
                        )->get();
        return $this->contests()
                        ->where('contest_start_time', '>', date('Y-m-d H:i:s'))
                        ->where('privacy', 'public')
                        ->orWhere(function($query) use($friendList) {
                            $query->where('privacy', 'friend-only')
                                    ->whereIn('created_by', collect($friendList)->map(function($x){ return (array) $x; })->toArray());
                        })
                        ->selectRaw('game_id, count(*) as aggregate')
                        ->groupBy('game_id');
    }

    public function players() {
        return $this->belongsToMany('App\Players', 'games_players', 'game_id', 'player_id')->withPivot('cap_amount', 'win', 'loss');
    }
}
