<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Ads extends Model {

    use Notifiable;

use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ads';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'file', 'no_secs_display', 'video_url'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
    public function user() {
        return $this->belongsToMany('App\User', 'ads_user', 'ads_id', 'user_id');
    }

    public function scopeSearchName($query, $value) {
        return $query->Where('name', 'LIKE', "%$value%");
    }

    public function scopeSearchMediaDuration($query, $value) {
        return $query->orWhere('no_secs_display', 'LIKE', "%$value%");
    }

}
