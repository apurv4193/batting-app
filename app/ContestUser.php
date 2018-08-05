<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use DB;

class ContestUser extends Model {

    use Notifiable;

    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contest_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['contest_id', 'user_id', 'team_id', 'is_paid', 'points_win', 'score', 'rank', 'is_win'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function scopeSearchName($query, $value) {
        return $query->orWhere('contest_name', 'LIKE', "%$value%");
    }

    public function scopeSearchStartTime($query, $value) {
        return $query->orWhere('contest_start_time', 'LIKE', "%$value%");
    }

    public function scopeSearchEndTime($query, $value) {
        return $query->orWhere('contest_end_time', 'LIKE', "%$value%");
    }
}
