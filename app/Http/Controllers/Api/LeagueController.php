<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\League;
use App\LeagueInvitedUser;
use App\ContestInvitedUser;
use App\ContestLevel;
use App\Game;
use App\Contest;
use App\ContestType;
use Validator;
use DB;
use App\User;
use App\UserDevice;
use Helpers;
use Config;

class LeagueController extends Controller {

    private $league;

    public function __construct(League $league) {
        $this->league = $league;
    }

    public function saveLeague(Request $request) {
        
        try {
            $validator = Validator::make($request->all(), [
                'game_id' => 'required|integer',
                'contest_type_id' => 'required|integer',
                'level_id' => 'required|integer',
                'league_name' => 'required|max:255|regex:/^(?![0-9]*$)[a-zA-Z0-9 ]+$/',
                'league_start_date' => 'required|date|date_format:Y-m-d|after:'.Carbon::yesterday(),
                'league_end_date' => 'required|date|date_format:Y-m-d|after:league_start_date',
                'league_min_participants' => 'required|integer|min:1',
                'created_by' => 'integer'
            ]);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => $validator->messages()->all()[0],
                            'code' => '20100'
                ]);
            }

            //same league name check
            $leagueData = League::select('league_name')->get();
            foreach ($leagueData as $key => $value) {
                
                if( $request->league_name == $value->league_name ) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'League name already exist.',
                                'code' => 400
                    ]);
                }
            }
           
            $request->invited_user_list = (!empty($request->invited_user_list)) ? explode(',', $request->invited_user_list) : [];
           
            if($request->league_min_participants > count($request->invited_user_list)) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Please enter minimum '.$request->league_min_participants.' as minimum participant.',
                            'code' => '400'
                ]);
            }

            DB::beginTransaction();
            
            $data = $request->all();
            $data['created_by'] = $request->user()->id;
            $league = $this->league->create($data);
           
            $newInvitedUser = [];
            foreach ($request->invited_user_list as $invitedUser) {
                 if(!LeagueInvitedUser::where('league_id', $league->id)->where('user_id', $invitedUser)->exists() && User::find($invitedUser) !== null) {
                    $league->userInLeague()->attach($invitedUser);
                    array_push($newInvitedUser, $invitedUser);
                } else {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'Something went wrong while inviting user.',
                                'code' => 400
                    ]);
                }
            }
            // Notofication to newly invited user for league
                $user_device = (!empty($newInvitedUser)) ? UserDevice::whereIn('user_id', $newInvitedUser)->get() : [];
                if(count($user_device) > 0) {
                    foreach ($user_device as $device) {

                        $user_detail = User::find($device->user_id);
                        if($user_detail->notification_status == 1) {
                            
                            $data = array(
                                        'notification_status' => 2,
                                        'message' => 'You are invited to join '.ucfirst($league->league_name),
                                        'league_id' => $league->id,
                                        'notification_type' => 'LeagueInvitation'
                                    );
                            Helpers::pushNotificationForiPhone($device->device_token, $data);
                        }
                    }
                }
             DB::commit();
             return response()->json([
                        'status' => '1',
                        'message' => 'League Saved successfully.',
                        'data' => [
                            // 'leagueDetail' => ''
                        ]
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Error creating league.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    public function updateLeague(Request $request, $id) {
        DB::beginTransaction();
        try {
            $validator = [
                'league_name' => 'required|max:255|regex:/^(?![0-9]*$)[a-zA-Z0-9 ]+$/'
                // 'league_start_date' => 'required|date|date_format:Y-m-d|after:'.Carbon::yesterday(),
                // 'league_end_date' => 'required|date|date_format:Y-m-d|after:league_start_date'
            ];
            if(isset($request->league_start_date)) {
                $validator['league_start_date'] = 'required|date|date_format:Y-m-d|after:'.Carbon::today();
            }
            if(isset($request->league_end_date)) {
                $validator['league_end_date'] = 'required|date|date_format:Y-m-d|after:'.Carbon::today();
            }
            if(isset($request->league_start_date) && isset($request->league_end_date) )
            {
                $validator['league_end_date'] = 'required|date|date_format:Y-m-d|after:league_start_date';
            }
            //new invited users
            $request->invited_user_list = (!empty($request->invited_user_list)) ? explode(',', $request->invited_user_list) : [];

            $validator = Validator::make($request->all(),$validator);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => $validator->messages()->all()[0],
                            'code' => '20100'
                ]);
            }
            //find league
            $league = League::find($id);

            $sDate = $request->league_start_date ? $request->league_start_date : $league->league_start_date;
            $eDate = $request->league_end_date ? $request->league_end_date : $league->league_end_date;
            //only start date
            if(isset($request->league_start_date)){
                if($sDate > $eDate){
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'Invalid Start date.',
                                'code' => 400
                    ]);
                }
            }
            else{
                $sDate = $sDate->toDateString();
            }
            //only end date
            if(isset($request->league_end_date)){
                //$endDate = $eDate;
                if($sDate > $eDate){
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'Invalid End date.',
                                'code' => 400
                    ]);
                }
            }else{
                $eDate = $eDate->toDateString();
            }
            //find league contest
            $leagueContest = Contest::where('league_id', $id)->get();

            // contest invitation
                $contestInvitedUser = [];
                foreach ($request->invited_user_list as $invitedContestUser) {
                    foreach ($leagueContest as $_leagueContest) {
                        if(!ContestInvitedUser::where('user_id', $invitedContestUser)->where('contest_id', $_leagueContest->id)->exists() && User::find($invitedContestUser) !== null) {

                        $_leagueContest->userListWhichInvited()->attach($invitedContestUser);
                        array_push($contestInvitedUser, $invitedContestUser);
                    } else {
                        DB::rollback();
                        return response()->json([
                                    'status' => '0',
                                    'message' => 'Something went wrong while inviting user.',
                                    'code' => 400
                        ]);
                    }
                    }
                }
            DB::commit();
            //$startDate = $league->league_start_date->toDateString();
            if($league != null){
                $oldInvitedUser = [];
                $oldinvitedUsers = LeagueInvitedUser::where('league_id',$league->id)->get();
                //new user check
                $newInvitedUser = [];
                foreach ($request->invited_user_list as $invitedUser) {
                     if(!LeagueInvitedUser::where('league_id', $league->id)->where('user_id', $invitedUser)->exists() && User::find($invitedUser) !== null) {

                        $league->userInLeague()->attach($invitedUser);
                        array_push($newInvitedUser, $invitedUser);
                    } else {
                        DB::rollback();
                        return response()->json([
                                    'status' => '0',
                                    'message' => 'Something went wrong while inviting user.',
                                    'code' => 400
                        ]);
                    }
                }
                
                if ($league->status == Config::get('constant.UPCOMING_CONTEST_STATUS')) {
                    League::where('id', $id)->update([
                                'league_name'=>  $request->league_name, 
                                'league_start_date'=>  $sDate,
                                'league_end_date'=> $eDate
                    ]);
                    DB::commit();
                    //send notification to old invited users for update league
                    foreach ($oldinvitedUsers as $oldUser) {
                        $user_device = UserDevice::where('user_id', $oldUser->user_id)->get();
                        if(count($user_device) > 0) {
                            foreach ($user_device as $device) {

                                $user_detail = User::find($device->user_id);
                                if($user_detail->notification_status == 1) {
                                    
                                    $data = array(
                                                'notification_status' => 2,
                                                'message' => 'Update league'.ucfirst($league->league_name),
                                                'league_id' => $league->id,
                                                'notification_type' => 'LeagueUpdate'
                                            );
                                    Helpers::pushNotificationForiPhone($device->device_token, $data);
                                }
                            }
                        }
                    }
                    //end
                    // Notofication to newly invited user for league
                    $user_device = (!empty($newInvitedUser)) ? UserDevice::whereIn('user_id', $newInvitedUser)->get() : [];
                    if(count($user_device) > 0) {
                        foreach ($user_device as $device) {
                            $user_detail = User::find($device->user_id);

                            if($user_detail->notification_status == 1) {
                                
                                $data = array(
                                            'notification_status' => 2,
                                            'message' => 'You are invited to join '.ucfirst($league->league_name),
                                            'league_id' => $league->id,
                                            'notification_type' => 'LeagueInvitation'
                                        );
                                Helpers::pushNotificationForiPhone($device->device_token, $data);
                            }
                        }
                    }
                    return response()->json([
                        'status' => '1',
                        'message' => 'League updated successfully.',
                        'data' => [
                            // 'leagueDetail' => ''
                        ]
                    ]);
                }else{
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'You can\'t perform this action.',
                                'code' => 400
                    ]);
                }
            }else{
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'League not found.',
                            'code' => 404
                ]);
            }
            // DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Error updating event detail.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }
    public function leagueListing(Request $request) {
        try {
            $league = League::join('games', 'games.id', '=', 'league.game_id')
                              ->join('contest_type', 'contest_type.id', '=', 'league.contest_type_id')
                              ->join('level', 'level.id', '=', 'league.level_id')
                              ->where('league.created_by', $request->user()->id)
                              ->whereIn('league.status',['upcoming','live'])
                              ->get(['games.name as game_name', 'contest_type.type as contest_type','level.name as level','league.*']);

            return response()->json([
                        'status' => '1',
                        'message' => 'League listing.',
                        'data' => [
                            'leagueList' => $league
                        ]
            ]);

        }catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error listing league.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    public function getLeague(Request $request, $id) {
        try {
            $user = $request->user();
            $league = $user->league()->where('id', $id)->where('status','<>', Config::get('constant.COMPLETED_CONTEST_STATUS'))->first();

            if ($league === null) {
                return response()->json([
                            'status' => '0',
                            'message' => 'League not found.',
                            'code' => 404
                ]);
            }else{
                $game = Game::find($league->game_id);
                $contest_type = ContestType::find($league->contest_type_id);
                $level = ContestLevel::find($league->level_id); 

                $league->game_name = $game->name;
                $league->contest_type = $contest_type->type;
                $league->level = $level->name;
            }
            
            return response()->json([
                        'status' => '1',
                        'message' => 'League detail.',
                        'data' => [
                            'league' => $league
                        ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error getting league detail.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }
}
