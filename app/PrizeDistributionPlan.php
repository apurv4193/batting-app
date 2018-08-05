<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Config;

class PrizeDistributionPlan extends Model {

    use Notifiable;

use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'prize_distribution_plan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'winner', 'status'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function scopeSearchName($query, $value) {
        return $query->Where('name', 'LIKE', "%$value%");
    }

    public function scopeSearchWinner($query, $value) {
        return $query->orWhere('winner', 'LIKE', "%$value%");
    }

    public function scopeNotDeleted($query) {
        return $query->Where('status', Config::get('constant.NOT_DELETED'));
    }
    
    public function contests() {
        return $this->hasMany('App\Contest');
    }
}
