<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\League;
use App\LeagueInvitedUser;
use App\User;
use App\UserDevice;
use DB;
use App\Roster;
use Helpers;
use Config;

class UpdateLeagueStatus extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batting:updateLeagueStatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To update league status.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

        $allLeague = League::where('status', Config::get('constant.UPCOMING_CONTEST_STATUS'))->orWhere('status', Config::get('constant.LIVE_CONTEST_STATUS'))->get();

        foreach ($allLeague as $league) 
        {
            if ($league->league_start_date <= date('Y-m-d') && $league->league_end_date >= date('Y-m-d')) {

                $leagueUsers = LeagueInvitedUser::where('league_id', $league->id)->get();
                foreach ($leagueUsers as $users) {

                    $user_device = UserDevice::where('user_id', $users->user_id)->get();
                    if (count($user_device) > 0) {
                        foreach ($user_device as $device) {

                            $user_detail = User::find($device->user_id);

                            if ($user_detail->notification_status == 1) {
                                $data = array(
                                    'notification_status' => 2,
                                    'message' => ucfirst($league->league_name) . ' League started.',
                                    'league_id' => $league->id,
                                    'notification_type' => 'LeagueStarts'
                                );
                                Helpers::pushNotificationForiPhone($device->device_token, $data);
                            }
                        }
                    }
                }
                League::where('id', $league->id)->update(['status' => Config::get('constant.LIVE_CONTEST_STATUS')]);
            }

            if ($league->league_end_date < date('Y-m-d')) {

                $leagueUsers = LeagueInvitedUser::where('league_id', $league->id)->get();
                foreach ($leagueUsers as $users) {

                    $user_device = UserDevice::where('user_id', $users->user_id)->get();
                    if (count($user_device) > 0) {
                        foreach ($user_device as $device) {

                            $user_detail = User::find($device->user_id);

                            if ($user_detail->notification_status == 1) {
                                $data = array(
                                    'notification_status' => 2,
                                    'message' => ucfirst($league->league_name) . ' League completed.',
                                    'league_id' => $league->id,
                                    'notification_type' => 'LeagueCompleted'
                                );
                                Helpers::pushNotificationForiPhone($device->device_token, $data);
                            }
                        }
                    }
                }
                League::where('id', $league->id)->update(['status' => Config::get('constant.COMPLETED_CONTEST_STATUS')]);
            }
        }
    }

}
