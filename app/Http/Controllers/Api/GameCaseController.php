<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Contest;
use App\UsersUsedPower;
use App\UsersPower;
use App\GameCase;
use App\GameCaseBundle;
use App\GameCaseItems;
use App\Roster;
use App\Players;
use App\GamesPlayers;
use App\ContestUser;
use App\Team;
use App\Item;
use App\User;
use App\VirtualCurrencyHistory;
use Config;
use DB;
use Validator;
use Helpers;
use Carbon\Carbon;

class GameCaseController extends Controller {

    public function __construct() {
        $this->itemThumbImageUploadPath = Config::get('constant.ITEM_THUMB_IMAGE_UPLOAD_PATH');
        $this->contestThumbImageUploadPath = Config::get('constant.CONTEST_THUMB_IMAGE_UPLOAD_PATH');
        $this->playersThumbImageUploadPath = Config::get('constant.PLAYERS_THUMB_IMAGE_UPLOAD_PATH');
        $this->teamThumbImageUploadPath = Config::get('constant.TEAM_THUMB_IMAGE_UPLOAD_PATH');
    }

    /**
     * Update roster detail for user participated contest.
     *
     * @param
     * @return
     * @throws Exception If there was an error
     * @see \App\UsersUsedPower
     * @see \App\UsersPower
     * @Post("/")
     * @Parameters({
     *     @Parameter("contest_id", description="Contest id of current user", type="integer"),
     *     @Parameter("user_power_id", description="Id of users power", type="integer"),
     * })
     * @Transaction({
     *     @Request( {"contest_id":"44","user_power_id":"1"} ),
     *     @Response( {"status": "1","message": "Success.","data": []} ),
     *     @Response( {"status": "0",'message': 'Contest data not found.','code' => 404000} )
     *     @Response( {"status": "0",'message': 'Contest is no longer available.','code' => 410000} )
     *     @Response( {"status": "0",'message': 'You have not entered this event.','code' => 403023} )
     *     @Response( {"status": "0",'message': 'You have already used power or not available.','code' => 410000} )
     *     @Response( {"status": "0",'message': 'You can use power once for contest.','code' => 403023} )
     *     @Response( {"status": "0","message": "Error while use power.": $e->getStatusCode()} )
     * })
     */
    public function usePower(Request $request) {
        try {

            $userId = $request->user()->id;

            $contest = Contest::find($request->contest_id);

            if ($contest === null) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Event Data not found.',
                            'code' => 404000 //error code: Not found
                ]);
            }

            /*// Contest is cancelled or completed
            if ($contest->status == Config::get('constant.COMPLETED_CONTEST_STATUS') || $contest->status == Config::get('constant.CANCELLED_CONTEST_STATUS')) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Contest is no longer available.',
                            'code' => 410000 //error code: Gone
                ]);
            }*/

            $isParticipant = $contest->userInContest()->existUserInContest($userId)->count();

            // Not participant
            if ($isParticipant == 0) {
                return response()->json([
                            'status' => '0',
                            'message' => 'You have not entered this event.',
                            'code' => 403023 //error code: No user permission
                ]);
            }

            $isPowerUsed = UsersUsedPower::where([
                    ['contest_id', $request->contest_id],
                    ['user_id', $request->user()->id]
                ])->first();

            if($isPowerUsed) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Please remove current Klash token.',
                            'code' => 403024 //error code: Provider limit reached
                ]);
            }

            $powerDetail = UsersPower::where([
                    ['item_id', $request->item_id],
                    ['user_id', $userId],
                    ['used', Config::get('constant.POWER_UNUSED')]
                ])->first();

            if($powerDetail === null) {
                return response()->json([
                            'status' => '0',
                            'message' => 'You don\'t have this item.',
                            'code' => 410000 //error code: Gone
                ]);
            }

            // In Seconds
            $remainingTime = Helpers::differenceInHIS($contest->contest_start_time, Carbon::now())['difference'];
            if(Helpers::eligibleToAccessRoster($remainingTime, $request->user()->id, $contest) === false) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Timeout.',
                            'code' => 504001 //error code: Timeout
                ]);
            }

            DB::beginTransaction();

            $powerDetail->fill(array_filter(['used' => Config::get('constant.POWER_USED')]));
            $powerDetail->save();

            $item = Item::find($powerDetail->item_id);

            UsersUsedPower::create([
                'user_id' => $userId,
                'contest_id' => $request->contest_id,
                'user_power_id' => $powerDetail->id,
                'item_id' => $powerDetail->item_id,
                'points' => ($item && $item->points) ? $item->points : 0.00,
                'remaining_pre_contest_substitution' => ($item && $item->pre_contest_substitution) ? $item->pre_contest_substitution : 0,
                'remaining_contest_substitution' => ($item && $item->contest_substitution) ? $item->contest_substitution : 0
            ]);

            // If contest is playing as a player wise (Based on player selection in roster)
            if($contest->is_teamwise == 0) {
                $roster = $contest->with(['player' => function ($query) use ($userId) {
                                $query->where('user_id', $userId);
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
            } else {
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

            // Contest is cancelled or completed
            if ($roster[0]->status == Config::get('constant.COMPLETED_CONTEST_STATUS') || $roster[0]->status == Config::get('constant.CANCELLED_CONTEST_STATUS') || $roster[0]->status == Config::get('constant.PENDING_CONTEST_STATUS')) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Event is no longer available.',
                            'code' => 410000 //error code: Gone
                ]);
            }

            // If contest is playing as a player wise (Based on player selection in roster)
            if($contest->is_teamwise == 0) {
                foreach ($roster[0]->player as $player) {
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
            } else {
                foreach ($roster[0]->team as $team) {
                    $team->team_image = ($team->team_image != NULL && $team->team_image != '') ? url($this->teamThumbImageUploadPath . $team->team_image) : '';
                }
            }

            $userUsedPower = (count($roster[0]->userUsedPower) > 0)?$roster[0]->userUsedPower[0]->pivot:[];

            $roster[0]->userUsedPower[0] = $userUsedPower;
            $contestUserData = ContestUser::where('contest_id', $request->contest_id)->where('user_id', $userId)->first();

            $roster[0]->userBuying = ($contestUserData && isset($contestUserData->is_paid) && $contestUserData->is_paid == 1) ? 1 : 0;

            DB::commit();
            return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => [
                            'roster' => $roster
                        ]
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Error while use power.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    public function buyPower(Request $request) {
        try {
            if(isset($request->gamecase_id) && !empty($request->gamecase_id)) {
                $gameCase = GameCase::find($request->gamecase_id);
                $gamecaseNumber = 1;
                $price = $gameCase->price;
                $response = $gameCase;
                if($response !== null) {
                    $response->is_gameCase = 1;
                }
            } else if(isset($request->gameCaseBundle_id) && !empty($request->gameCaseBundle_id)) {
                $gameCaseBundle = GameCaseBundle::find($request->gameCaseBundle_id);

                if($gameCaseBundle === null) {
                    return response()->json([
                                'status' => '0',
                                'message' => 'Data not found.',
                                'code' => '404'
                    ]);
                }
                $gameCase = GameCase::where('slug', $gameCaseBundle->gamecase_slug)->first();
                $gamecaseNumber = $gameCaseBundle->size;
                $price = $gameCaseBundle->price;
                $response = $gameCaseBundle;
                if($response !== null) {
                    $response->is_gameCase = 0;
                }
            } else {
                return response()->json([
                            'status' => '0',
                            'message' => 'Incorrect input parameter.',
                            'code' => '404'
                ]);
            }
            // Game case not found
            if($gameCase === null) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Data not found.',
                            'code' => '404'
                ]);
            }

            $user = User::getCurrentUser($request->user()->id);
            if($user->virtual_currency < $price) {
                return response()->json([
                            'status' => '0',
                            'message' => 'You don\'t have enough Klash Coins to purchase this boost pack.',
                            'code' => '400'
                ]);
            }

            // Get items for gamecase
            $gameCaseItems = GameCaseItems::where('gamecase_id', $gameCase->id)->get();
            if(count($gameCaseItems) == 0) {
                return response()->json([
                            'status' => '0',
                            'message' => 'No item found in this gamecase.',
                            'code' => '404'
                ]);
            }

            DB::beginTransaction();

            $items = [];
            for($i=0; $i < $gamecaseNumber; $i++) {

                foreach ($gameCaseItems as $_gameCaseItems) {
                    $data = [];
                    $priorities = [];
                    // If possibility is 100 or more
                    if($_gameCaseItems->possibility >= 100) {

                        $itemDetail = Item::find($_gameCaseItems->item_id);
                        if($itemDetail === null) {
                            DB::rollback();
                            return response()->json([
                                        'status' => '0',
                                        'message' => 'Something went wrong. Please contact admin.',
                                        'code' => 404
                            ]);
                        }
                        $data = [
                            'user_id' => $request->user()->id,
                            'item_id' => $_gameCaseItems->item_id,
                            'gamecase_id' => $request->gamecase_id
                        ];
                        $userPower = new UsersPower(array_filter($data));
                        $userPower->save();

                        if(empty($items)) {
                            $itemDetail->item_count = 1;
                            array_push($items, $itemDetail->toArray());
                        } else {
                            $itemExist = Helpers::in_array($itemDetail->id, $items);
                            if(is_bool($itemExist)) {
                                $itemDetail->item_count = 1;
                                array_push($items, $itemDetail->toArray());
                            } else {
                                $items[$itemExist]['item_count'] = $items[$itemExist]['item_count'] + 1;
                            }
                        }

                    } else {
                        $priorities[$_gameCaseItems->item_id] = $_gameCaseItems->possibility;
                        $priorities[$_gameCaseItems->alternate_item_id] = $_gameCaseItems->alternate_possibility;
                        // Generate random item based on priority
                        $item_id = $this->generateRandomItem($priorities);

                        if($item_id === false) {
                            DB::rollback();
                            return response()->json([
                                        'status' => '0',
                                        'message' => 'Something went wrong. Please try again.',
                                        'code' => 404
                            ]);
                        }

                        // Item not found
                        $itemDetail = Item::find($item_id);
                        if($itemDetail === null) {
                            DB::rollback();
                            return response()->json([
                                        'status' => '0',
                                        'message' => 'Something went wrong. Please contact admin.',
                                        'code' => 404
                            ]);
                        }

                        $data = [
                            'user_id' => $request->user()->id,
                            'item_id' => $item_id,
                            'gamecase_id' => $request->gamecase_id
                        ];
                        $userPower = new UsersPower(array_filter($data));
                        $userPower->save();
                        if(empty($items)) {
                            $itemDetail->item_count = 1;
                            array_push($items, $itemDetail->toArray());
                        } else {
                            $itemExist = Helpers::in_array($itemDetail->id, $items);
                            if(is_bool($itemExist)) {
                                $itemDetail->item_count = 1;
                                array_push($items, $itemDetail->toArray());
                            } else {
                                $items[$itemExist]['item_count'] = $items[$itemExist]['item_count'] + 1;
                            }
                        }
                    }
                }
            }

            if(!empty($items)) {
                foreach ($items as $key => $_items) {
                    $items[$key]['item_image'] = ($_items['item_image'] != NULL && $_items['item_image'] != '') ? url($this->itemThumbImageUploadPath .$_items['item_image']) : '';
                }

                $response->item = $items;

                // Deduct balance from user virtual currency balanace
                $points = $user->virtual_currency - $price;
                $user->fill(array_filter(['virtual_currency' => $points]));
                $user->save();

                //for virtual currency history
                $virtualData = [];
                $virtualData['user_id'] = $request->user()->id;
                $virtualData['virtual_currency'] = $price;
                if($response->is_gameCase == 1){
                    $virtualData['gamecase_id'] = $request->gamecase_id;
                }
                if($response->is_gameCase == 0){
                    $virtualData['gamecase_bundle_id'] = $request->gameCaseBundle_id;
                }
                $virtualData['status'] = 'debit';

                VirtualCurrencyHistory::create($virtualData);

                DB::commit();
                return response()->json([
                            'status' => '1',
                            'message' => 'Success',
                            'data' => [
                                'items' => $response
                            ]
                ]);
            } else {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Something went wrong. Please try again.',
                            'code' => 404
                ]);
            }
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Error while buying boost.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    public function generateRandomItem($priorities) {
        try {
            $numbers = array();
            foreach($priorities as $k=>$v){
                for($i=0; $i<$v; $i++)
                    $numbers[] = $k;
            }

            return $numbers[array_rand($numbers)];
        } catch (Exception $e) {
            return false;
        }
    }

    public function getUserPower(Request $request) {
        try {
            $item_id = 0;
            if(isset($request->contest_id)){
                $usedPower = UsersUsedPower::where('contest_id', $request->contest_id)->where('user_id',$request->user()->id)->first();
                $item_id = (isset($usedPower->item_id) ? $usedPower->item_id : 0);
            }
            // Un-used power (Boost) of user
            $power = $request->user()->item()
                ->select('items.*')
                ->selectSub('COUNT(*)', 'item_count')
                ->groupBy('items.id')
                ->wherePivot('used', Config::get('constant.POWER_UNUSED'))
                ->get()->each(function ($power) use($item_id) {
                    $power->item_image = ($power->item_image != NULL && $power->item_image != '') ? url($this->itemThumbImageUploadPath.$power->item_image) : '';
                    $power->is_used_for_contest = ($power->id == $item_id) ? 1 : 0;
                });

            return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => [
                            'boost' => $power
                        ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error while list boost.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }
    public function deleteUserPower(Request $request) {
        try {
                if(!isset($request->contest_id))
                {
                    return response()->json([
                            'status' => '0',
                            'message' => 'Invalid Request Parameter',
                            'code' => 404
                    ]);
                }
                $date = date('Y-m-d H:i:s');
                $item_id = 0;
                $contest = Contest::find($request->contest_id);
                if($contest === null)
                {
                    return response()->json([
                            'status' => '0',
                            'message' => 'No Event found',
                            'code' => 404
                    ]);
                }
                $contest_start_time = $contest->contest_start_time;
                $difference = Helpers::differenceInHIS($contest_start_time, $date);
                if ($difference['hours'] >= 1)
                {
                    $usedPower = UsersUsedPower::where('contest_id', $request->contest_id)->where('user_id',$request->user()->id)->first();
                    if($usedPower === null)
                    {
                        return response()->json([
                                'status' => '0',
                                'message' => 'You have not used power',
                                'code' => 404
                        ]);
                    }
                    $user_power = UsersPower::where('id', $usedPower->user_power_id)->first();
                    if($user_power === null)
                    {
                        return response()->json([
                                'status' => '0',
                                'message' => 'No user power found',
                                'code' => 404
                        ]);
                    }
                    DB::beginTransaction();
                    $user_power->used = 0;
                    $user_power->save();
                    $usedPower->forceDelete();
                    // DB::table('users_used_power')->where('id', $usedPower->id)->delete();

                }else{
                        return response()->json([
                                    'status' => '0',
                                    'message' => 'You can not Perform this action',
                                    'code' => 404
                            ]);
                }
            DB::commit();
            return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => []
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Error while list boost.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }
}
