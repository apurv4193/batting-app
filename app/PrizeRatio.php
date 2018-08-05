<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class PrizeRatio extends Model {

    use Notifiable;

use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'prize_distribution_ratio';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['prize_distribution_plan_id', 'ratio'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function scopeGetPrizeRatio($query, $value) {
        return $query->where('prize_distribution_plan_id', $value)->get();
    }

    public function scopeDeleteRatio($query, $value) {
            return $query->where('prize_distribution_plan_id', $value)->get();
    }

}
