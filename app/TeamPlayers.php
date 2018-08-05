<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TeamPlayers extends Model
{
   use Notifiable;

   /*
     *  Teams Players Mysql table Name
     */

    protected $table = 'teams_players';
    /*
     * Players Mysql table fileds(Colume) Name
     */
    protected $fillable = ['team_id', 'player_id', 'team_player_cap_amount'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $dates = [];

}
