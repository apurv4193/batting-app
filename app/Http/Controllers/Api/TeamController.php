<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\Team;
use App\ContestType;
use App\GamesPlayers;
use Config;

class TeamController extends Controller {

    public function __construct() {
        $this->teamOriginalImageUploadPath = Config::get('constant.TEAM_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->teamThumbImageUploadPath = Config::get('constant.TEAM_THUMB_IMAGE_UPLOAD_PATH');
        $this->teamThumbImageHeight = Config::get('constant.TEAM_THUMB_IMAGE_HEIGHT');
        $this->teamThumbImageWidth = Config::get('constant.TEAM_THUMB_IMAGE_WIDTH');
        
        $this->playerThumbImageUploadPath = Config::get('constant.PLAYERS_THUMB_IMAGE_UPLOAD_PATH');
    }

    /** 
     * Get team list.
     *
     * @return \App\Team Team list.
     * @see \App\Team
     * @Post("/")
     * @Parameters({
     *     @Parameter("contest_type_id", description="Contest type id", type="integer"),
     * })
     * @Transaction({
     *     @Request( {"contest_type_id":1} ),
     *     @Response( {"status": "1","message": "Success","data": {"teamListing": [{"id": 2,"name": "Vandit Kotadiya - App","game_id": 1,"contest_type_id": 2,"team_image": "http://local.batting-app.com:8012/uploads/team/thumb/team_q6nUii1sEoPIAPWI8X9b.jpg","win": 1,"loss": 0,"team_cap_amount": "17.00","created_at": "2018-01-11 14:29:49","updated_at": "2018-01-12 06:28:20","deleted_at": null}]}} )
     *     @Response( {"status": "0",'message': 'Error.','code' => $e->getStatusCode()} )
     * })
     */
    public function teamListing(Request $request) {
        try {
            
            $validator = Validator::make($request->all(), [
                'contest_type_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                            'status' => '0',
                            'message' => $validator->messages()->all()[0],
                            'code' => '20100'
                ]);
            }
            
            // Invalid contest type value
            if(!ContestType::where('id', $request->contest_type_id)->exists()) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Invalid input parameter.',
                            'code' => 40400
                ]);
            }

            $search = ($request->search ? $request->search : '');
            $sort = ($request->sort) ? (is_array($request->sort) ? $request->sort : Config::get('constant.TEAM_LISTING_DEFAULT_SORT')) : Config::get('constant.TEAM_LISTING_DEFAULT_SORT');
            
            $team = Team::TeamWithContestType($request->contest_type_id)->NotDeleted()->Search($search)->get()->each(function ($team) {
                $team->team_image = ($team->team_image != NULL && $team->team_image != '') ? url($this->teamThumbImageUploadPath.$team->team_image) : '';
            });

            return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => [
                            'teamListing' => $team
                        ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }
    
    /**
     * Get Team detail.
     *
     * @param Request $request The current request
     * @return \App\Team Team \App\Team object for given id
     * @return \App\Player Player \App\Player
     * @throws Exception If there was an error
     * @see \App\Team
     * @see \App\Player
     * @see \App\TeamPlayer
     * @Get("/{id}")
     * @Transaction({
     *     @Request(),
     *     @Response( {"status": "1","message": "Success","data": {"teamsPlayerDetail": [{"id": 3,"name": "Hardik Pandya","description": "About Hardik Pandya","profile_image": "http://local.batting-app.com:8012/uploads/players/thumb/players_1510205227.jpg","created_at": "2017-11-09 10:57:07","updated_at": "2017-11-17 12:22:04","deleted_at": null,"cap_amount": "8.00","win": 97,"loss": 3,"pivot": {"team_id": 2,"player_id": 3,"team_player_cap_amount": "8.00"}}]}} ),
     *     @Response( {"status": "0",'message': 'Data not found.','code' => 404} )
     *     @Response( {"status": "0",'message': 'Error getting player detail.','code' => $e->getStatusCode()} )
     * })
     */
    public function teamDetail(Request $request, $id) {
        try {
            
            $team = Team::find($id);
            
            if ($team === null) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Data not found.',
                            'code' => 40400
                ]);
            }
            
            // Team players detail
            $teamPlayers = $team->players()->wherePivot('team_id', $id)->get()->each(function ($teamPlayers) use($team) {
                
                $teamPlayers->profile_image = ($teamPlayers->profile_image != NULL && $teamPlayers->profile_image != '') ? url($this->playerThumbImageUploadPath.$teamPlayers->profile_image) : '';
                
                $playerDetail = GamesPlayers::where('game_id', $team->game_id)->where('player_id', $teamPlayers->id)->first();
                $teamPlayers->cap_amount = ($playerDetail !== null) ? $playerDetail->cap_amount : 0;
                $teamPlayers->win = ($playerDetail !== null) ? $playerDetail->win : 0;
                $teamPlayers->loss = ($playerDetail !== null) ? $playerDetail->loss : 0;
            });
            
            return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => [
                            'teamsPlayerDetail' => $teamPlayers
                        ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }
}
