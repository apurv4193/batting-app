<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Config;

class Contest extends Model {

    use Notifiable;

use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['league_id', 'game_id', 'contest_type_id', 'level_id', 'contest_name', 'contest_fees', 'roster_cap_amount', 'contest_start_time', 'contest_end_time', 'privacy', 'prize_distribution_plan_id', 'contest_min_participants', 'contest_max_participants', 'participated', 'banner', 'video_thumb', 'contest_video_link', 'prize', 'sponsored_by','sponsored_prize', 'sponsored_video_link', 'sponsored_image', 'sponsored_link', 'created_by', 'updated_by', 'status', 'cancel_by', 'cancellation_reason', 'result_declare_status', 'result_declare_date', 'is_teamwise'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'result_declare_date'];

    public function scopeSearchGameName($query, $value) {
        return $query->Where('name', 'LIKE', "%$value%");
    }

    public function scopeSearchContestType($query, $value) {
        return $query->orWhere('type', 'LIKE', "%$value%");
    }

    public function scopeSearchName($query, $value) {
        return $query->orWhere('contest_name', 'LIKE', "%$value%");
    }

    public function scopeSearchContestFees($query, $value) {
        return $query->orWhere('contest_fees', 'LIKE', "%$value%");
    }

    public function scopeSearchStartTime($query, $value) {
        return $query->orWhere('contest_start_time', 'LIKE', "%$value%");
    }

    public function scopeSearchEndTime($query, $value) {
        return $query->orWhere('contest_end_time', 'LIKE', "%$value%");
    }

    public function scopeSearchPrivacy($query, $value) {
        return $query->orWhere('privacy', 'LIKE', "%$value%");
    }

    public function scopeSearchStatus($query, $value) {
        return $query->orWhere('contests.status', 'LIKE', "%$value%");
    }

    public function scopeOnGoing($query) {
        return $query->where([
                    ['contest_start_time', '<=', date('Y-m-d H:i:s')],
                    ['contest_end_time', '>=', date('Y-m-d H:i:s')]
        ]);
    }

    public function scopeLive($query) {
        return $query->where([
                    ['status', Config::get('constant.LIVE_CONTEST_STATUS')]
        ]);
    }

    public function scopeUpcoming($query) {
        return $query->where([
                    ['contest_start_time', '>', date('Y-m-d H:i:s')],
        ]);
    }

    public function scopeUpcomingContest($query) {
        return $query->where([
                    ['status', Config::get('constant.UPCOMING_CONTEST_STATUS')],
                    ['contest_start_time', '>', date('Y-m-d H:i:s')]
        ]);
    }

    public function scopeFilterGame($query, $value) {
        return $query->where([
                    ['game_id', $value],
        ]);
    }

    public function scopeFilterLevel($query, $value) {
        return $query->where([
                    ['level_id', $value],
        ]);
    }

    public function scopeFilterType($query, $value) {
        return $query->where([
                    ['contest_type_id', $value],
        ]);
    }

    public function scopeSearchContest($query, $value) {
        return $query->where('contest_name', 'LIKE', "%$value%")
                        ->orWhere('contest_fees', 'LIKE', "%$value%")
                        ->orWhere('prize', 'LIKE', "%$value%")
                        ->orWhere('contest_max_participants', 'LIKE', "%$value%");
    }

    public function scopeSort($query, $value) {
        return $query->orderBy($value['column'], $value['order']);
    }

    public function scopeExceptPast($query) {
        return $query->where([
                    ['contest_end_time', '>=', date('Y-m-d H:i:s')],
        ]);
    }

    public function scopeExceptPastContest($query) {
        return $query->where('status', Config::get('constant.UPCOMING_CONTEST_STATUS'))
                        ->orWhere('status', Config::get('constant.LIVE_CONTEST_STATUS'))
                        ->orWhere('status', Config::get('constant.CONTEST_LOCKED_CONTEST_STATUS'))
                        ->orWhere('status', Config::get('constant.ROSTER_LOCKED_CONTEST_STATUS'));
    }

    public function scopeCompletedContest($query) {
        return $query->where('status', Config::get('constant.COMPLETED_CONTEST_STATUS'));
    }
    
    public function scopeHistory($query) {
        return $query->where('status', Config::get('constant.COMPLETED_CONTEST_STATUS'))
                        ->orWhere('status', Config::get('constant.PENDING_CONTEST_STATUS'));
    }

    public function scopePublicContest($query) {
        return $query->where('privacy', Config::get('constant.PUBLIC_CONTEST'));
    }

    public function game() {
        return $this->belongsTo('App\Game');
    }

    public function contestType() {
        return $this->belongsTo('App\ContestType');
    }

    public function contestLevel() {
        return $this->belongsTo('App\ContestLevel', 'level_id');
    }

    public function userInContest() {
        return $this->belongsToMany('App\User', 'contest_user')->withPivot('points_win', 'score', 'rank', 'team_id', 'is_paid','is_win');
    }

    public function scopeByOrder() {
        return $query->orderBy('is_win', 'desc')->orderBy('rank','asc');
    }

    public function userListWhichInvited() {
        return $this->belongsToMany('App\User', 'contest_invited_user','contest_id', 'user_id')->withPivot('invitation_status');
    }

    public function contestInvitedUser() {
        return $this->hasMany('App\ContestInvitedUser', 'contest_id');
    }

    public function player() {
        return $this->belongsToMany('App\Players', 'rosters', 'contest_id', 'player_id')->withPivot('user_id', 'player_cap_amount');
    }

    public function team() {
        return $this->belongsToMany('App\Team', 'contest_user', 'contest_id', 'team_id')->withPivot('points_win', 'score', 'rank', 'team_id', 'is_paid');
    }

    public function playerCapAmountSum() {
        return $this->player->sum('pivot.player_cap_amount');
    }

    public function contestScoreImages() {
        return $this->hasMany('App\ContestScoreImages', 'contest_id');
    }

    public function userUsedPower() {
        return $this->belongsToMany('App\User', 'users_used_power', 'contest_id', 'user_id')->withPivot('user_power_id', 'item_id', 'points', 'remaining_pre_contest_substitution', 'remaining_contest_substitution');
    }
}
