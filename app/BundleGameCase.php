<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class BundleGameCase extends Model {

    use Notifiable;

use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'bundle_game_case';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['game_case_bundle_id', 'game_case_id'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function scopeGetBundleGameCase($query, $value) {
        return $query->where('game_case_bundle_id', $value)->get();
    }
    public function scopeDeleteRecord($query, $value) {
        return $query->where('game_case_bundle_id', $value)->delete();
    }

}
