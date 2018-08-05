<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GameCaseItems extends Model {

    use Notifiable;

use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'gamecase_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['gamecase_id', 'item_id', 'possibility', 'alternate_possibility', 'alternate_item_id'];

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
        return $query->Where('price', 'LIKE', "%$value%");
    }

}
