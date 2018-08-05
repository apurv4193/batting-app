<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model {

    use Notifiable;

use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'item_image', 'points', 'description', 'pre_contest_substitution', 'contest_substitution'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function scopeSearchName($query, $value) {
        return $query->Where('name', 'LIKE', "%$value%");
    }

    public function scopePoints($query, $value) {
        return $query->orWhere('points', 'LIKE', "%$value%");
    }
    
    public function gameCase() {
        return $this->belongsToMany('App\GameCase', 'gamecase_items', 'item_id', 'gamecase_id')->withPivot('possibility');
    }

    public function user() {
        return $this->belongsToMany('App\User', 'users_power', 'item_id', 'user_id')->withPivot('gamecase_id','gamecase_bundle_id','used');
    }
}
