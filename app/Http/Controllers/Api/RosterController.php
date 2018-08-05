<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Roster;
use App\Players;
use App\Contest;
use App\GamesPlayers;
use App\ContestUser;
use App\Team;
use Helpers;
use Config;
use DB;
use Carbon\Carbon;

class RosterController extends Controller {

    public function __construct() {
        $this->contestThumbImageUploadPath = Config::get('constant.CONTEST_THUMB_IMAGE_UPLOAD_PATH');
        $this->playersThumbImageUploadPath = Config::get('constant.PLAYERS_THUMB_IMAGE_UPLOAD_PATH');
        $this->teamThumbImageUploadPath = Config::get('constant.TEAM_THUMB_IMAGE_UPLOAD_PATH');
    }

    /**
     * Get data for create roster and roster detail.
     *
     * @return \App\Roster The currently authorized users roster detail with player for given contest id.
     * @see \App\Contest
     * @see \App\User
     * @see \App\Roster
     * @see \App\Players
     * @Post("/")
     * @Transaction({
     *     @Request( {"contest_id": 44} ),
     *     @Response( {"status": "1","message": "Success.","data": {"roster": [{"id": 44,"game_id": 1,"contest_type_id": 4,"level_id": 3,"contest_name": "Contest Name (Updated)","contest_fees": 100,"roster_cap_amount": 2500,"contest_start_time": "2017-11-24 04:54:34","contest_end_time": "2017-12-19 18:54:37","privacy": "private","prize_distribution_plan_id": 1,"contest_min_participants": 100,"contest_max_participants": "1000","participated": 0,"banner": "http://local.batting-app.com:8012/uploads/contest/thumb/d7Osxihv0ROeqt7XMFbA.jpg","contest_video_link": "https://www.twitch.com/cricket","prize": 0,"created_by": 2,"status": "upcoming","created_at": "2017-10-30 11:33:13","updated_at": "2017-10-31 09:35:38","deleted_at": null,"remained_cap_amount": 1500,"startIn": "545:57:13","player": [{"id": 1,"name": "Viral Kohli","game_id": 1,"description": "Description","profile_image": "http://local.batting-app.com:8012/uploads/players/thumb/players_1509527352.jpg","cap_amount": "1000.00","win": 100,"loss": 0,"created_at": "2017-11-01 09:09:12","updated_at": "2017-11-01 09:09:12","deleted_at": null,"pivot": {"contest_id": 44,"player_id": 1,"user_id": 2,"player_cap_amount": "1000.00"}}]}]}} )
     *     @Response( {"status": "0",'message': 'Data not found.','code' => 404} )
     *     @Response( {"status": "0",'message': 'You are not participated in this contest..','code' => 400} )
     *     @Response( {"status": "0",'message': 'Contest is no longer available.','code' => 410000} )
     *     @Response( {"status": "0",'message': 'Error getting roster detail.','code' => $e->getStatusCode()} )
     * })
     */
    public function roster(Request $request) {
        try {

            $userId = $request->user()->id;
            $contest = Contest::find($request->contest_id);

            if ($contest === null) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Data not found.',
                            'code' => 404
                ]);
            }

            // If contest is playing as a player wise (Based on player selection in roster)
            if($contest->is_teamwise == 0)
            {
                $roster = $contest->with(['player' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                    // $query->where('status',Config::get('constant.ACTIVE_STATUS_FLAG'));
                }])->with(['userUsedPower' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }])
                ->where('id', $request->contest_id)
                ->get()->each(function ($roster) {
                    $roster->remained_cap_amount = $roster->roster_cap_amount - $roster->playerCapAmountSum();
                    $roster->banner = ($roster->banner != NULL && $roster->banner != '') ? url($this->contestThumbImageUploadPath . $roster->banner) : '';
                    $difference = Helpers::differenceInHIS($roster->contest_start_time, date('Y-m-d H:i:s')); // Get date difference in Hours / minutes and seconds
                    $roster->startIn = ($difference['hours'] < 0) ? '00:00:00' : $difference['hours'] . ':' . sprintf("%02d", $difference['minutes']) . ':' . sprintf("%02d", $difference['seconds']);
                });
            }
            else
            {
                // If contest is playing as a team wise (Based on team selection in roster)
                $roster = $contest->with(['team' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }])->with(['userUsedPower' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }])
                ->where('id', $request->contest_id)
                ->get()->each(function ($roster) {
                    $roster->remained_cap_amount = $roster->roster_cap_amount - ((count($roster->team) > 0) ? $roster->team[0]->team_cap_amount : 0);
                    $roster->banner = ($roster->banner != NULL && $roster->banner != '') ? url($this->contestThumbImageUploadPath . $roster->banner) : '';
                    $difference = Helpers::differenceInHIS($roster->contest_start_time, date('Y-m-d H:i:s')); // Get date difference in Hours / minutes and seconds
                    $roster->startIn = ($difference['hours'] < 0) ? '00:00:00' : $difference['hours'] . ':' . sprintf("%02d", $difference['minutes']) . ':' . sprintf("%02d", $difference['seconds']);
                });
            }

            $contestUserData = ContestUser::where('contest_id', $request->contest_id)->where('user_id', $userId)->first();

            $roster[0]->userBuying = ($contestUserData && isset($contestUserData->is_paid) && $contestUserData->is_paid == 1) ? 1 : 0;

            // Contest is cancelled or completed
            if ($roster[0]->status == Config::get('constant.COMPLETED_CONTEST_STATUS') || $roster[0]->status == Config::get('constant.CANCELLED_CONTEST_STATUS') || $roster[0]->status == Config::get('constant.PENDING_CONTEST_STATUS'))
            {
                return response()->json([
                    'status' => '0',
                    'message' => 'Event is no longer available.',
                    'code' => 410000 //error code: Gone
                ]);
            }

            // If contest is playing as a player wise (Based on player selection in roster)
            if($contest->is_teamwise == 0)
            {
                foreach ($roster[0]->player as $player)
                {
                    $player->profile_image = ($player->profile_image != NULL && $player->profile_image != '') ? url($this->playersThumbImageUploadPath . $player->profile_image) : '';

                    $games = GamesPlayers::where('game_id',$roster[0]->game_id)->where('player_id',$player->id)->first();

                    if($games) {
                        $player->cap_amount = (float) $games->cap_amount;
                        $player->win = (float) $games->win;
                        $player->loss = (float) $games->loss;
                    } else {
                        return response()->json([
                            'status' => '0',
                            'message' => 'This player is not participated in any game.',
                            'code' => '400'
                        ]);
                    }
                    $player->pivot->player_cap_amount = (float) $player->pivot->player_cap_amount;
                }
            }
            else
            {
                foreach ($roster[0]->team as $team) {
                    $team->team_image = ($team->team_image != NULL && $team->team_image != '') ? url($this->teamThumbImageUploadPath . $team->team_image) : '';
                }
            }

            if( count($roster[0]->userUsedPower) > 0 ){
               // unset($roster[0]->userUsedPower);
                $roster[0]->userUsedPower[0] = $roster[0]->userUsedPower[0]->pivot;
            }
            else
            {
                $roster[0]->userUsedPower = [];
            }
            return response()->json([
                        'status' => '1',
                        'message' => 'Success.',
                        'data' => [
                            'roster' => $roster
                        ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error getting roster detail.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Save roster detail for user participated contest.
     *
     * To do: Response /
     *
     * @param
     * @return
     * @throws Exception If there was an error
     * @see \App\Roster
     * @Post("/")
     * @Parameters({
     *     @Parameter("contest_id", description="Particiapted users contest id", type="integer"),
     *     @Parameter("roster_player", description="Array of player for contest roster", type="array"),
     * })
     * @Transaction({
     *     @Request( {"contest_id": "44","roster_player": ["1"] } ),
     *     @Response( {"status": "1","message": "Success.","data": []} ),
     *     @Response( {"status": "0",'message': 'Contest data not found.','code' => 404} )
     *     @Response( {"status": "0",'message': 'You are not participated in this contest.','code' => 400} )
     *     @Response( {"status": "0",'message': 'You have already created roster.','code' => 400} )
     *     @Response( {"status": "0",'message': 'Timeout.','code' => 423} )
     *     @Response( {"status": "0",'message': 'Contest is no longer available.','code' => 410000} )
     *     @Response( {"status": "0",'message': 'You must have to select 1 player for this contest.','code' => 400} )
     *     @Response( {"status": "0",'message': 'You can\'t select single player for multiple time.','code' => 20100} )
     *     @Response( {"status": "0",'message': 'You have exceeded maximum cap amount.','code' => 400} )
     *     @Response( {"status": "0",'message': 'Something went wrong while selecting player. Please select again.','code' => 400} )
     *     @Response( {"status": "0","message": "Error saving roster.","code": $e->getStatusCode()} )
     * })
     */
    public function saveRoster(Request $request) {
        DB::beginTransaction();
        try {
            $userId = $request->user()->id;

            $contest = Contest::find($request->contest_id);

            if ($contest === null) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Event Data not found.',
                            'code' => 404
                ]);
            }

            $isParticipant = $contest->userInContest()->existUserInContest($userId)->count();

            // Not participant
            if ($isParticipant == 0) {
                if($contest->contest_max_participants <= $contest->participated) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'Event exceeded max participant.',
                                'code' => 400
                    ]);
                }
                $contest->userInContest()->attach(['user_id' => $request->user()->id]);

                // Increased participated user's count in  contest
                $participated = $contest->participated + 1;

                $contest->fill(array_filter(['participated' => $participated]));
                $contest->save();
            }

            $contestWithPlayer = $contest->with(['player' => function ($query) use ($userId) {
                            $query->where('user_id', $userId);
                        }])
                        ->where('id', $request->contest_id)
                        ->get();

            // If already created roster
            if (count($contestWithPlayer[0]->player) != 0) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You have already created roster.',
                            'code' => 400
                ]);
            }

            // Contest is cancelled or completed
            if ($contestWithPlayer[0]->status == Config::get('constant.COMPLETED_CONTEST_STATUS') || $contestWithPlayer[0]->status == Config::get('constant.CANCELLED_CONTEST_STATUS') || $contestWithPlayer[0]->status == Config::get('constant.PENDING_CONTEST_STATUS')) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Event is no longer available.',
                            'code' => 410000 //error code: Gone
                ]);
            }

            // In Seconds
            $remainingTime = Helpers::differenceInHIS($contestWithPlayer[0]->contest_start_time, Carbon::now())['difference'];
            if(Helpers::eligibleToAccessRoster($remainingTime, $request->user()->id, $contestWithPlayer[0]) === false) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Timeout.',
                            'code' => 423
                ]);
            }

            if($contest->is_teamwise == 0) {
                // If player selected more than or less than contest type player
                $contestPlayer = Helpers::getContestTypePlayer($contest->contest_type_id);
                if ($contestPlayer != count($request->roster_player)) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'You must have to select ' . $contestPlayer . ' player for this event.',
                                'code' => 400
                    ]);
                }
                $playerCapAmount = 0;
                foreach ($request->roster_player as $player) {
                    $playerDetail = Players::find($player);
                    if (count($playerDetail) != 0 || ($playerDetail && $playerDetail->status == Config::get('constant.NOT_DELETED'))) {

                        $playerExist = Roster::where([
                                ['contest_id', $request->contest_id],
                                ['user_id', $request->user()->id],
                                ['player_id', $playerDetail->id]
                            ])->first();

                        // If user try to add player multiple time in contest roster
                        if($playerExist) {
                            DB::rollback();
                            return response()->json([
                                        'status' => '0',
                                        'message' => 'You can\'t select single player for multiple time.',
                                        'code' => 20100
                            ]);
                        }
                        //player games for getting cap amount
                        $games = GamesPlayers::where('game_id',$contestWithPlayer[0]->game_id)->where('player_id',$player)->first();
                        if( $games ) {
                            $playerDetail->cap_amount = (float) $games->cap_amount;
                        } else {
                            return response()->json([
                                'status' => '0',
                                'message' => 'This player is not participated in any game.',
                                'code' => '400'
                            ]);
                        }

                        $playerCapAmount = $playerCapAmount + $playerDetail->cap_amount;
                        // Insert into roster
                        Roster::create([
                            'contest_id' => $contestWithPlayer[0]->id,
                            'user_id' => $userId,
                            'player_id' => $playerDetail->id,
                            'player_cap_amount' => $playerDetail->cap_amount
                        ]);

                        // Exceeded max cap amount of contest
                        if ($playerCapAmount > $contestWithPlayer[0]->roster_cap_amount) {
                            DB::rollback();
                            return response()->json([
                                        'status' => '0',
                                        'message' => 'You have exceeded maximum cap amount.',
                                        'code' => 400
                            ]);
                        }
                    } else { // Player not found (Unauthorized action)
                        DB::rollback();
                        return response()->json([
                                    'status' => '0',
                                    'message' => 'Something went wrong while selecting player. Please select again.',
                                    'code' => 400
                        ]);
                    }
                }
            } else {
                // Update team id for user (For his contest)
                $userContest = ContestUser::where('contest_id', $contest->id)->where('user_id', $request->user()->id)->first();
                $userContest->fill(array_filter(['team_id' => $request->team_id]));
                $userContest->save();

                $team = Team::find($request->team_id);
                if($team === null) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'Invalid input parameter.',
                                'code' => 400
                    ]);
                }

                $playerList = $team->players()->wherePivot('team_id', $request->team_id)->get();

                foreach ($playerList as $player) {
                    $contest->player()->attach($player->id, ['user_id'=> $request->user()->id, 'player_cap_amount' => $player->pivot->team_player_cap_amount]);
                }
            }


            DB::commit();
            return response()->json([
                        'status' => '1',
                        'message' => 'Success.',
                        'data' => []
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Error saving roster.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Update roster detail for user participated contest.
     *
     * To do: Response /
     *
     * @param
     * @return
     * @throws Exception If there was an error
     * @see \App\Roster
     * @Post("/")
     * @Parameters({
     *     @Parameter("contest_id", description="Particiapted users contest id", type="integer"),
     *     @Parameter("roster_player", description="Array of player for contest roster", type="array of object"),
     * })
     * @Transaction({
     *     @Request( {"contest_id": "44","roster_player": [{"player_id":"1","roster_cap_amount":"100"},{"player_id":"2","roster_cap_amount":""},{"player_id":"3","roster_cap_amount":""},{"player_id":"4","roster_cap_amount":""},{"player_id":"5","roster_cap_amount":""},{"player_id":"6","roster_cap_amount":""}]} ),
     *     @Response( {"status": "1","message": "Success.","data": []} ),
     *     @Response( {"status": "0",'message': 'Contest data not found.','code' => 404} )
     *     @Response( {"status": "0",'message': 'You are not participated in this contest.','code' => 400} )
     *     @Response( {"status": "0",'message': 'You have to create roster first!','code' => 400} )
     *     @Response( {"status": "0",'message': 'Timeout.','code' => 423} )
     *     @Response( {"status": "0",'message': 'Contest is no longer available.','code' => 410000} )
     *     @Response( {"status": "0",'message': 'You must have to select 1 player for this contest.','code' => 400} )
     *     @Response( {"status": "0",'message': 'You can\'t select single player for multiple time.','code' => 20100} )
     *     @Response( {"status": "0",'message': 'You have exceeded maximum cap amount.','code' => 400} )
     *     @Response( {"status": "0",'message': 'Something went wrong while selecting player. Please select again.','code' => 400} )
     *     @Response( {"status": "0","message": "Error updating roster.","code": $e->getStatusCode()} )
     * })
     */
    public function updateRoster(Request $request) {
        DB::beginTransaction();
        try {
            $userId = $request->user()->id;

            $contest = Contest::find($request->contest_id);

            if ($contest === null) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Event Data not found.',
                            'code' => 404
                ]);
            }

            $isParticipant = $contest->userInContest()->existUserInContest($userId)->count();

            // Not participant
            if ($isParticipant == 0) {
                if($contest->contest_max_participants <= $contest->participated) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'Event exceeded max participant.',
                                'code' => 400
                    ]);
                }
                $contest->userInContest()->attach(['user_id' => $request->user()->id]);

                // Increased participated user's count in  contest
                $participated = $contest->participated + 1;

                $contest->fill(array_filter(['participated' => $participated]));
                $contest->save();
            }

            $contestWithPlayer = $contest->with(['player' => function ($query) use ($userId) {
                            $query->where('user_id', $userId);
                        }])
                        ->where('id', $request->contest_id)
                        ->get();

            // If contest not created yet
            if (count($contestWithPlayer[0]->player) == 0) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You have to create roster first!',
                            'code' => 400
                ]);
            }

            // Contest is cancelled or completed
            if ($contestWithPlayer[0]->status == Config::get('constant.COMPLETED_CONTEST_STATUS') || $contestWithPlayer[0]->status == Config::get('constant.CANCELLED_CONTEST_STATUS') || $contestWithPlayer[0]->status == Config::get('constant.PENDING_CONTEST_STATUS')) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Event is no longer available.',
                            'code' => 410000 //error code: Gone
                ]);
            }

            // In Seconds
            $remainingTime = Helpers::differenceInHIS($contestWithPlayer[0]->contest_start_time, Carbon::now())['difference'];
            if(Helpers::eligibleToAccessRoster($remainingTime, $request->user()->id, $contestWithPlayer[0]) === false) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Timeout.',
                            'code' => 423
                ]);
            }

            if ($contest->is_teamwise == 0) {
                // If player selected more than or less than contest type player
                $contestPlayer = Helpers::getContestTypePlayer($contestWithPlayer[0]->contest_type_id);
                if ($contestPlayer != count($request->roster_player)) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'You must have to select ' . $contestPlayer . ' player for this event.',
                                'code' => 400
                    ]);
                }

                // Delete player from user's roster
                Roster::where([
                        ['contest_id', $request->contest_id],
                        ['user_id', $request->user()->id]
                    ])->forcedelete();

                $playerCapAmount = 0;
                foreach ($request->roster_player as $player) {

                    $playerDetail = Players::where('id', $player['player_id'])->where('status', Config::get('constant.ACTIVE_STATUS_FLAG'))->first();

                    if (count($playerDetail) != 0) {

                        $playerExist = Roster::where([
                                ['contest_id', $request->contest_id],
                                ['user_id', $request->user()->id],
                                ['player_id', $playerDetail->id]
                            ])->first();

                        // If user try to add player multiple time in contest roster
                        if($playerExist) {
                            DB::rollback();
                            return response()->json([
                                        'status' => '0',
                                        'message' => 'You can\'t select single player for multiple time.',
                                        'code' => 20100
                            ]);
                        }
                        //player games for getting cap amount
                        $games = GamesPlayers::where('game_id',$contestWithPlayer[0]->game_id)->where('player_id',$player['player_id'])->first();
                        if( $games ) {
                            $playerDetail->cap_amount = (float) $games->cap_amount;

                        }
                        else {
                            return response()->json([
                                'status' => '0',
                                'message' => 'This player is not participated in any game.',
                                'code' => '400'
                            ]);
                        }
                        $playerCapAmount = (isset($player['roster_cap_amount']) && $player['roster_cap_amount'] > 0) ? ($playerCapAmount + $player['roster_cap_amount']) : ($playerCapAmount + $playerDetail->cap_amount);
                        // Insert into roster
                        Roster::create([
                            'contest_id' => $contestWithPlayer[0]->id,
                            'user_id' => $userId,
                            'player_id' => $playerDetail->id,
                            'player_cap_amount' => ((isset($player['roster_cap_amount']) && $player['roster_cap_amount'] > 0) ? $player['roster_cap_amount'] : $playerDetail->cap_amount)
                        ]);

                        // Exceeded max cap amount of contest
                        if ($playerCapAmount > $contestWithPlayer[0]->roster_cap_amount) {
                            DB::rollback();
                            return response()->json([
                                        'status' => '0',
                                        'message' => 'You have exceeded maximum cap amount.',
                                        'code' => 400
                            ]);
                        }
                    } else { // Player not found (Unauthorized action)
                        DB::rollback();
                        return response()->json([
                                    'status' => '0',
                                    'message' => 'Something went wrong while selecting player. Please select again.',
                                    'code' => 400
                        ]);
                    }
                }
            } else {
                // Update team id for user (For his contest)
                $userContest = ContestUser::where('contest_id', $contest->id)->where('user_id', $request->user()->id)->first();
                $userContest->fill(array_filter(['team_id' => $request->team_id]));
                $userContest->save();

                $team = Team::find($request->team_id);
                if($team === null) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'Invalid input parameter.',
                                'code' => 400
                    ]);
                }

                $contest->player()->detach();
                $playerList = $team->players()->wherePivot('team_id', $request->team_id)->get();
                foreach ($playerList as $player) {
                    $contest->player()->attach($player->id, ['user_id'=> $request->user()->id, 'player_cap_amount' => $player->pivot->team_player_cap_amount]);
                }
            }
            DB::commit();
            return response()->json([
                        'status' => '1',
                        'message' => 'Success.',
                        'data' => []
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Error saving roster.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Get player detail of roster of user for given contest id.
     *
     * @param Request $request The current request
     * @return \App\Players Players \App\Players object for given contest id for current user
     * @throws Exception If there was an error
     * @see \App\Players
     * @Get("/{contestId}")
     * @Transaction({
     *     @Request(),
     *     @Response( {"status": "1","message": "Success.","data": {"rosterPlayer": [{"id": 1,"name": "Viral Kohli","game_id": 1,"description": "Description","profile_image": "http://local.batting-app.com:8012/uploads/players/thumb/players_1509527352.jpg","cap_amount": 1000,"win": 100,"loss": 0,"created_at": "2017-11-01 09:09:12","updated_at": "2017-11-01 09:09:12","deleted_at": null,"pivot": {"contest_id": 44,"player_id": 1,"user_id": 2,"player_cap_amount": 900}}]}} ),
     *     @Response( {"status": "0",'message': 'Contest data not found.','code' => 404} )
     *     @Response( {"status": "0",'message': 'You have not participated in this contest.','code' => 400} )
     *     @Response( {"status": "0",'message': 'Error fetching roster detail.','code' => $e->getStatusCode()} )
     * })
     */
    public function rosterListing(Request $request, $contestId) {
        try {
            $contest = Contest::find($contestId);

            if ($contest === null) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Event Data not found.',
                            'code' => 404
                ]);
            }

            $isParticipant = $contest->userInContest()->existUserInContest($request->user()->id)->count();

            // Not participant
            if ($isParticipant == 0) {
                return response()->json([
                            'status' => '0',
                            'message' => 'You have not participated in this event.',
                            'code' => 400
                ]);
            }

            if($contest->is_teamwise == 0) {
                $rosterPlayer = $contest->player()->wherePivot('user_id', $request->user()->id)->where('status',Config::get('constant.ACTIVE_STATUS_FLAG'))->get()->each(function ($rosterPlayer) use ($contest){
                    $rosterPlayer->profile_image = ($rosterPlayer->profile_image != NULL && $rosterPlayer->profile_image != '') ? url($this->playersThumbImageUploadPath . $rosterPlayer->profile_image) : '';

                    $games = GamesPlayers::where('player_id',$rosterPlayer->id)->where('game_id',$contest->game_id)->first();

                    $rosterPlayer->cap_amount = (float) $games->cap_amount;
                    $rosterPlayer->pivot->player_cap_amount = (float) $rosterPlayer->pivot->player_cap_amount;
                });
            } else {
                $rosterPlayer = $contest->team()->wherePivot('user_id', $request->user()->id)->get()->each(function ($rosterPlayer) {
                    $rosterPlayer->team_image = ($rosterPlayer->team_image != NULL && $rosterPlayer->team_image != '') ? url($this->teamThumbImageUploadPath . $rosterPlayer->team_image) : '';
                });
            }

            return response()->json([
                        'status' => '1',
                        'message' => 'Success.',
                        'data' => [
                            'rosterPlayer' => $rosterPlayer
                        ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error fetching roster detail.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

}
