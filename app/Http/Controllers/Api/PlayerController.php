<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Game;
use App\GamesPlayers;
use App\Players;
use Config;

class PlayerController extends Controller {

    public function __construct() {
        $this->playersThumbImageUploadPath = Config::get('constant.PLAYERS_THUMB_IMAGE_UPLOAD_PATH');
    }

    /**
     * Player Listing for specified game.
     *
     * @param Request $request The current request
     * @return \App\Players Player Listing for specified game.
     * @throws Exception If there was an error
     * @see \App\Players
     * @Post("/")
     * @Transaction({
     *     @Request( {"game_id":"1","search":"","sort":{"column":"name","sort":"ASC"}} ),
     *     @Response( {"status": "1","message": "Success","data": {"players": [{"id": 1,"name": "Viral Kohli","game_id": 1,"description": "Description","profile_image": "http://local.batting-app.com:8012/uploads/players/thumb/players_1509527352.jpg","cap_amount": "1000.00","win": 100,"loss": 0,"created_at": "2017-11-01 09:09:12","updated_at": "2017-11-01 09:09:12","deleted_at": null}]}} ),
     *     @Response( {"status": "0",'message': 'Data not found','code' => 404} )
     *     @Response( {"status": "0",'message': 'Error listing player list.','code' => $e->getStatusCode()} )
     * })
     */
    public function playerListing(Request $request) {
        try {
            
            $game = Game::find($request->game_id);
            $search = ($request->search ? $request->search : '');
            $sort = ($request->sort && !empty($request->sort) ? $request->sort : Config::get('constant.PLAYER_DEFAULT_SORT'));
            
            if ($game === null || ($game && $game->status == Config::get('constant.DELETED'))) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Data not found.',
                            'code' => 404
                ]);
            }
            $players = GamesPlayers::leftjoin('players', 'games_players.player_id', '=', 'players.id')->search($search)->sort($sort)->where('game_id',$request->game_id)->where('players.status', Config::get('constant.ACTIVE_STATUS_FLAG'))->get()->each(function ($player) {
                $player->profile_image = ($player->profile_image != NULL && $player->profile_image != '') ? url($this->playersThumbImageUploadPath.$player->profile_image) : '';
                $player->cap_amount = (float) $player->cap_amount;
            });
            
            return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => [
                            'players' => $players
                        ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error listing player list.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }
    
    /**
     * Get Player detail.
     *
     * @param Request $request The current request
     * @return \App\Players Players \App\Players object for given id
     * @throws Exception If there was an error
     * @see \App\Players
     * @Get("/{id}")
     * @Transaction({
     *     @Request(),
     *     @Response( {"status": "1","message": "Success","data": {"playerDetail": {"id": 1,"name": "Viral Kohli","game_id": 1,"description": "Description","profile_image": "http://local.batting-app.com:8012/uploads/players/thumb/players_1509527352.jpg","cap_amount": "1000.00","win": 100,"loss": 0,"created_at": "2017-11-01 09:09:12","updated_at": "2017-11-01 09:09:12","deleted_at": null}}} ),
     *     @Response( {"status": "0",'message': 'Data not found.','code' => 404} )
     *     @Response( {"status": "0",'message': 'Error getting player detail.','code' => $e->getStatusCode()} )
     * })
     */
    public function playerDetail(Request $request, $id) {
        try {
            $player = Players::where('id',$id)->where('status',Config::get('constant.ACTIVE_STATUS_FLAG'))->first();

            if($player === null) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Data not found.',
                            'code' => 404
                ]);
            }
            $player->profile_image = ($player->profile_image != NULL && $player->profile_image != '') ? url($this->playersThumbImageUploadPath.$player->profile_image) : '';
            $games = GamesPlayers::leftjoin('games', 'games.id', '=', 'games_players.game_id')->where('player_id',$id)->get();

            if( count($games) > 0 ) {
                foreach ($games as $game) {
                    $game->cap_amount = (float) $game->cap_amount;
                    $game->win = (float) $game->win;
                    $game->loss = (float) $game->loss;
                }
            }
            
            
            return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => [
                            'playerDetail' => $player,
                            'playerGameDetail' => $games
                        ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error getting player detail.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

}
