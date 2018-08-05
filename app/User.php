<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Config;
use Auth;

class User extends Authenticatable {

    use Notifiable;

use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'dob',
        'password',
        'longitude',
        'latitude',
        'zipcode',
        'city',
        'state',
        'country',
        'user_pic',
        'gender',
        'points',
        'virtual_currency',
        'funds',
        'is_admin',
        'social_id',
        'social_type',
        'notification_status',
        'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public static function getCurrentUser() {
        return Auth::user();
    }

    public function setPasswordAttribute($password) {
        $this->attributes['password'] = bcrypt($password);
    }

    public function scopeSearchName($query, $value) {
        return $query->Where('name', 'LIKE', "%$value%");
    }

    public function scopeSearchEmail($query, $value) {
        return $query->OrWhere('email', 'LIKE', "%$value%");
    }

    public function scopeSearchPhone($query, $value) {
        return $query->OrWhere('phone', 'LIKE', "%$value%");
    }
    
    public function scopeSearchUserName($query, $value) {
        return $query->Where('username', 'LIKE', "%$value%");
    }

    public function scopeSearchUser($query, $value) {
        return $query->Where('name', 'LIKE', "%$value%")->OrWhere('email', 'LIKE', "%$value%")->OrWhere('phone', 'LIKE', "%$value%")->OrWhere('username', 'LIKE', "%$value%");
    }

    public function scopeNormalUser($query) {
        return $query->where('is_admin', Config::get('constant.NORMAL_USER_FLAG'));
    }
    
    public function scopeNotCurrentUser($query, $userId) {
        return $query->where('id', '<>', $userId);
    }
    
    public function scopeSort($query, $value) {
        return $query->orderBy($value['column'], $value['order']);
    }
    
    public function scopeExistUserInContest($query, $value) {
        return $query->where('users.id', $value);
    }
    
    public function scopeNotDeleted($query) {
        return $query->Where('status', Config::get('constant.NOT_DELETED'));
    }

    public function friendRequestStatus() {
        return $this->hasMany('App\Friend', 'requester_id');
    }

    public function contest() {
        return $this->hasMany('App\Contest', 'created_by');
    }

    public function league() {
        return $this->hasMany('App\League', 'created_by');
    }

    public function participatedInContest() {
        return $this->belongsToMany('App\Contest', 'contest_user')->withPivot('points_win', 'score', 'rank', 'team_id', 'is_paid', 'is_win');
    }

    public function contestListOfInvitation() {
        return $this->belongsToMany('App\Contest', 'contest_invited_user')->withPivot('invitation_status');
    }

    public function friend() {
        return $this->belongsToMany('App\User', 'friends', 'receiver_id', 'requester_id')->withPivot('status');
    }

    public function paymentDetail() {
        return $this->hasMany('App\PaymentDetail', 'user_id');
    }
    
    public function ads() {
        return $this->belongsToMany('App\Ads', 'ads_user', 'user_id', 'ads_id');
    }
    
    public function item() {
        return $this->belongsToMany('App\Item', 'users_power', 'user_id', 'item_id')->withPivot('gamecase_id','gamecase_bundle_id','used');
    }
}
