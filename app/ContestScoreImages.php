<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class ContestScoreImages extends Model
{
   use Notifiable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contest_score_images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['contest_id', 'contest_image', 'is_rejected'];

    /**
     * The attributes that are dates
     *
     * @var array
     */

    public function contest() {
        return $this->belongsTo('App\Contest');
    }
}
