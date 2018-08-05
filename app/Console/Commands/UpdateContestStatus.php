<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\UserDevice;
use App\Contest;
use App\ContestUser;
use App\ContestType;
use DB;
use App\Roster;
use Helpers;
use Config;

class UpdateContestStatus extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batting:updateContestStatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To update event status.';

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

        $allContest = Contest::whereNotIn('status', [Config::get('constant.COMPLETED_CONTEST_STATUS'), Config::get('constant.CANCELLED_CONTEST_STATUS'), Config::get('constant.PENDING_CONTEST_STATUS')])->get();

        foreach ($allContest as $contest) {

            if ($contest->contest_start_time <= date('Y-m-d H:i:s') && $contest->contest_end_time >= date('Y-m-d H:i:s') && $contest->status != Config::get('constant.LIVE_CONTEST_STATUS')) {

                $contestUsers = ContestUser::where('contest_id', $contest->id)->get();
                foreach ($contestUsers as $users) {

                    $user_device = UserDevice::where('user_id', $users->user_id)->get();
                    if (count($user_device) > 0) {
                        foreach ($user_device as $device) {

                            $user_detail = User::find($device->user_id);

                            if ($user_detail->notification_status == 1) {
                                $data = array(
                                    'notification_status' => 2,
                                    'message' => ucfirst($contest->contest_name) . ' Event started.',
                                    'contest_id' => $contest->id,
                                    'notification_type' => 'ContestStarts'
                                );
                                Helpers::pushNotificationForiPhone($device->device_token, $data);
                            }
                        }
                    }
                }
                Contest::where('id', $contest->id)->update(['status' => 'live']);
            }

            if ($contest->contest_end_time < date('Y-m-d H:i:s')) {
                $user_device = UserDevice::where('user_id', $contest->created_by)->get();

                if (count($user_device) > 0) {
                    foreach ($user_device as $device) {

                        $user_detail = User::find($contest->created_by);

                        if ($user_detail->notification_status == 1) {
                            $data = array(
                                'notification_status' => 2,
                                'message' => 'Image upload fail for Event ' . ucfirst($contest->contest_name),
                                'contest_id' => $contest->id,
                                'notification_type' => 'UploadScoringImage'
                            );
                            Helpers::pushNotificationForiPhone($device->device_token, $data);
                        }
                    }
                }
                Contest::where('id', $contest->id)->update(['status' => 'pending']);
            }
        }
    }

}
