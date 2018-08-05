<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\UserDevice;
use App\Contest;
use App\ContestUser;
use App\ContestType;
use App\UsersPower;
use App\UsersUsedPower;
use DB;
use App\Roster;
use Helpers;
use Config;

class VerifyUserContest extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verify:contest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for getting event according to time';

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

        $date = date('Y-m-d H:i:00');
        $allContest = Contest::where('contest_start_time', '>', $date)->get();
        
        $user_ids = [];
        $contest = [];
        foreach ($allContest as $key => $value) {
            $difference = Helpers::differenceInHIS($value->contest_start_time, $date);
        
            if ($difference['hours'] == 3 && $difference['minutes'] == 0) {
                $contestUsers = ContestUser::where('contest_id', $value->id)->get();

                foreach ($contestUsers as $k => $v) {
                    $user = User::where('id', $v->user_id)->first();

                    if ($v->is_paid == 0) {
                        $user_device = UserDevice::where('user_id', $v->user_id)->get();
                        if (count($user_device) > 0) {
                            foreach ($user_device as $device) {
                                $user_detail = User::find($device->user_id);

                                if ($user_detail->notification_status == 1) {
                                    $data = array(
                                        'notification_status' => 2,
                                        'message' => 'You forgot to buy-in for ' . ucfirst($value->contest_name) . ' Event. Please buy-in to play in this event.',
                                        'contest_id' => $value->id,
                                        'notification_type' => 'BuyInNotDone'
                                    );
                                    Helpers::pushNotificationForiPhone($device->device_token, $data);
                                }
                            }
                        }
                    }
                }

                $contestUsers = ContestUser::where('contest_id', $value->id)->get();

                foreach ($contestUsers as $k => $v) {

                    $roster = Roster::where('contest_id', $value->id)->where('user_id', $v->user_id)->get();

                    //$user = User::where('id', $v->user_id)->first();

                    if ( count($roster) == 0) {
                        $user_device = UserDevice::where('user_id', $v->user_id)->get();
                        if (count($user_device) > 0) {
                            foreach ($user_device as $device) {
                                $user_detail = User::find($device->user_id);

                                if ($user_detail->notification_status == 1) {
                                    $data = array(
                                        'notification_status' => 2,
                                        'message' => 'You forgot to add player in roster for ' . $value->contest_name . ' Event. Please add player to play in this event.',
                                        'contest_id' => $value->id,
                                        'notification_type' => 'PlayerNotAdded'
                                    );
                                    Helpers::pushNotificationForiPhone($device->device_token, $data);
                                }
                            }
                        }
                    } else {
                    
                        $contestUsers = ContestUser::where('contest_id', $value->id)->get();

                        foreach ($contestUsers as $k => $v) {
                            $user = User::where('id', $v->user_id)->first();

                            $user_device = UserDevice::where('user_id', $v->user_id)->get();
                            if (count($user_device) > 0) {
                                foreach ($user_device as $device) {
                                    $user_detail = User::find($device->user_id);

                                    if ($user_detail->notification_status == 1) {
                                        $data = array(
                                            'notification_status' => 2,
                                            'message' => ucfirst($value->contest_name) . ' Event has been locked.',
                                            'contest_id' => $value->id,
                                            'notification_type' => 'ContestLocked'
                                        );
                                        Helpers::pushNotificationForiPhone($device->device_token, $data);
                                    }
                                }
                            }
                        }
                    }
                }
                Contest::where('id', $value->id)->update(['status' => 'contest-locked']);
            }

            // 3 hours
            // 1 hour

            if ($difference['hours'] == 1 && $difference['minutes'] == 0) {
                $contestUsers = ContestUser::where('contest_id', $value->id)->get();
                foreach ($contestUsers as $k => $v) {
                    if ($v->is_paid == 0) {
                        //Remove contest 
                        $contestUserRemove = ContestUser::where('user_id', $v->user_id)->where('contest_id', $value->id)->forceDelete();
                        $user_device = UserDevice::where('user_id', $v->user_id)->get();
                        if (count($user_device) > 0) {
                            foreach ($user_device as $device) {
                                $user_detail = User::find($device->user_id);

                                if ($user_detail->notification_status == 1) {
                                    $data = array(
                                        'notification_status' => 2,
                                        'message' => 'You are removed from ' . ucfirst($value->contest_name) . ' Event. Because you forget to buy-in for this event.',
                                        'contest_id' => $value->id,
                                        'notification_type' => 'UserRemovedForBuyIn'
                                    );
                                    Helpers::pushNotificationForiPhone($device->device_token, $data);
                                }
                            }
                        }
                    }
                }

                $contestUsers = ContestUser::where('contest_id', $value->id)->get();

                foreach ($contestUsers as $k => $v) {
                    $roster = Roster::where('contest_id', $value->id)->where('user_id', $v->user_id)->get();
                    if (count($roster) == 0) {
                        ContestUser::where('user_id', $v->user_id)->where('contest_id', $value->id)->forceDelete();
                        $user_device = UserDevice::where('user_id', $v->user_id)->get();
                        if (count($user_device) > 0) {
                            foreach ($user_device as $device) {
                                $user_detail = User::find($device->user_id);

                                if ($user_detail->notification_status == 1) {
                                    $data = array(
                                        'notification_status' => 2,
                                        'message' => 'You are removed from ' . ucfirst($value->contest_name) . ' Event. Because you forget to add player for this event.',
                                        'contest_id' => $value->id,
                                        'notification_type' => 'UserRemovedForRoster'
                                    );
                                    Helpers::pushNotificationForiPhone($device->device_token, $data);
                                }
                            }
                        }
                    }
                }
                
                if ($value->contest_min_participants > count($contestUsers)) {
                    //cancel contest
                    $contestDelete = Contest::where('id', $value->id)->update(['status' => 'cancelled', 'cancel_by' => 'admin', 'cancellation_reason' => 'Contest has been canceled due to lack of participants']);

                    $contestUsers = ContestUser::where('contest_id', $value->id)->get();
                    foreach ($contestUsers as $k => $v) {
                        $user = User::where('id', $v->user_id)->first();
                        // add points back to user account
                        if($v->is_paid == 1) {

                            $points = $user->points + $value->contest_fees;
                            $addUserPoints = User::where('id', $user->id)->update(['points' => $points]);
                            
                        }

                        $user_used_power = UsersUsedPower::where('contest_id', $value->id)->where('user_id', $user->id)->first();

                        if (!is_null($user_used_power)) {
                            UsersPower::where('id', $user_used_power->user_power_id)->update(['used' => 0]);
                            $user_used_power->forceDelete();
                        }

                        $user_device = UserDevice::where('user_id', $v->user_id)->get();
                        if (count($user_device) > 0) {
                            foreach ($user_device as $device) {
                                $user_detail = User::find($device->user_id);

                                if ($user_detail->notification_status == 1) {
                                    $data = array(
                                        'notification_status' => 2,
                                        'message' => ucfirst($value->contest_name) . ' Event has been cancelled due to lack of participants',
                                        'contest_id' => $value->id,
                                        'notification_type' => 'ContestCanceled'
                                    );
                                    Helpers::pushNotificationForiPhone($device->device_token, $data);
                                }
                            }
                        }
                    }
                    continue;
                } else {
                    $contestUsers = ContestUser::where('contest_id', $value->id)->get();
                    
                    foreach ($contestUsers as $users) {
                        $user_ids[] = $users->user_id;
                
                        $user_device = UserDevice::where('user_id', $users->user_id)->get();
                        if (count($user_device) > 0) {

                            foreach ($user_device as $device) {
                                $user_detail = User::find($device->user_id);

                                if ($user_detail->notification_status == 1) {
                                    $data = array(
                                        'notification_status' => 2,
                                        'message' => ucfirst($value->contest_name) . ' Roster has been locked.',
                                        'contest_id' => $value->id,
                                        'notification_type' => 'RosterLocked'
                                    );
                                    Helpers::pushNotificationForiPhone($device->device_token, $data);
                                }
                            }
                        }
                    }
                    
                }

                Contest::where('id', $value->id)->update(['status' => 'roster-locked']);
                
                $postData = array('groupName' => $value->contest_name, 'groupMemberList' => $user_ids, 'admin' => $value->created_by);
                $jsonData = json_encode($postData);
              
                $curlObj = curl_init();

                curl_setopt($curlObj, CURLOPT_URL, 'https://apps.applozic.com/rest/ws/group/v2/create');
                curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curlObj, CURLOPT_HEADER, 0);
                curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json', 'Apz-AppId:inexture3bb6f81e1e4a5c9f68934f', 'Apz-Token:BASIC eWFzaGsuaW5leHR1cmVAZ21haWwuY29tOmluZXh0dXJlMTIzPw=='));
                curl_setopt($curlObj, CURLOPT_POST, 1);
                curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

                $result = curl_exec($curlObj);
                $jsonRes = json_decode($result);
                
                Contest::where('id', $value->id)->update(['group_id' => $jsonRes->response->id]);
            }
        }
    }

}
