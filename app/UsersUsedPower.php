<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UsersUsedPower extends Model {

    use Notifiable;

use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users_used_power';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'contest_id', 'user_power_id', 'item_id', 'points', 'remaining_pre_contest_substitution', 'remaining_contest_substitution'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
    public function item() {
        return $this->belongsTo('App\Item', 'item_id');
    }

}
