<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AdsUser extends Model {

    use Notifiable;

    /**
     * 
     */
    protected $table = 'ads_user';

    /*
     * Players Mysql table fileds(Colume) Name
     */
    protected $fillable = ['ads_id', 'user_id'];

}
