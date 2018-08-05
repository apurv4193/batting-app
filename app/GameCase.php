<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GameCase extends Model {

    use Notifiable;

use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'gamecase';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'photo', 'price', 'description'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function gameCaseBundle() {
        return $this->hasMany('App\GameCaseBundle');
    }
    
    public function scopeSearchName($query, $value) {
        return $query->Where('name', 'LIKE', "%$value%");
    }
    
    public function scopeSearchPrice($query, $value) {
        return $query->orWhere('price', 'LIKE', "%$value%");
    }

    public function item() {
        return $this->belongsToMany('App\Item', 'gamecase_items', 'gamecase_id', 'item_id')->withPivot('possibility');
    }
    
}
