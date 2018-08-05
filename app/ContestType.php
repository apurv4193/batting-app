<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ContestType extends Model {

    use Notifiable;

use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contest_type';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type', 'contest_cap_amount'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function scopeSearchType($query, $value) {
        return $query->Where('type', 'LIKE', "%$value%");
    }

    public function scopeSearchCapAmount($query, $value) {
        return $query->Where('contest_cap_amount', 'LIKE', "%$value%");
    }

    public function contest() {
        return $this->hasMany('App\Contest');
    }

}
