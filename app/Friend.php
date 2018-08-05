<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Config;

class Friend extends Model {

    use Notifiable;

use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'friends';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['receiver_id', 'requester_id', 'status'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function scopeNotDeletedStatus($query) {
        return $query->whereNotIn('status', [Config::get('constant.REQUEST_DELETED_STATUS'), Config::get('constant.REQUEST_DELETED_BY_STATUS')]);
    }
}
