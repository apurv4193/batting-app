<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GameCaseBundle extends Model {

    use Notifiable;

use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'gamecase_bundle';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'gamecase_slug','gamecase_image', 'size', 'price', 'description'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function gameCase() {
        return $this->belongsTo('App\GameCase', 'id');
    }

    public function scopeSearchName($query, $value) {
        return $query->Where('name', 'LIKE', "%$value%");
    }

    public function scopeSearchBundlePrice($query, $value) {
        return $query->orWhere('price', 'LIKE', "%$value%");
    }

    // Get game case bundle raw which contain this item
    public function getGameCaseBundleWithThisItemData($itemId) {
        return GameCaseBundle::whereRaw('FIND_IN_SET(' . $itemId . ',`gamecase_ids`)')->get();
    }

}
