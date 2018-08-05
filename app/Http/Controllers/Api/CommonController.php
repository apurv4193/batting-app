<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Contest;
use App\GameCaseBundle;
use App\GameCase;
use App\Game;
use Config;
use DB;

class CommonController extends Controller {

    public function __construct() {
        $this->gameCaseThumbImageUploadPath = Config::get('constant.GAMECASE_THUMB_IMAGE_UPLOAD_PATH');
        $this->contestThumbImageUploadPath = Config::get('constant.CONTEST_THUMB_IMAGE_UPLOAD_PATH');
        $this->sponsorVideoUploadPath = Config::get('constant.CONTEST_SPONSOR_VIDEO_UPLOAD_PATH');
        $this->sponsorOriginalImageUploadPath = Config::get('constant.CONTEST_SPONSOR_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->sponsorThumbImageUploadPath = Config::get('constant.CONTEST_SPONSOR_THUMB_IMAGE_UPLOAD_PATH');
        $this->gameCaseBundleThumbImageUploadPath = Config::get('constant.GAMECASEBUNDLE_THUMB_IMAGE_UPLOAD_PATH');
    }

    /**
     * Register a new user.
     *
     * @param Request $request The current request
     * @throws Exception If there was an error
     * @see \App\User
     * @see \App\Contest;
     * @see \App\GameCaseBundle;
     * @see \App\GameCase;
     * @see \App\Game;
     * @Post("/")
     * @Transaction({
     *     @Request({}),
     *     @Response( {"status": "1","message": "Success","data": {"contest": [{"banner": "","sponsored_video_link": ""}],"game": [{"id": 1,"name": "Cricket","created_at": "2017-10-17 11:56:43","updated_at": "2017-10-17 11:56:43","deleted_at": null,"contets_count": 1}],"gameCases": []}} ),
     *     @Response( {"status": "0",'message': 'Error.','code' => $e->getStatusCode()} )
     * })
     */
    public function homeScreen(Request $request)
    {
        try
        {
            // Upcoming and public contest
            $contest = Contest::upcomingContest()->publicContest()->get([
                'contests.banner',
                'contests.sponsored_video_link',
                'contests.id',
                'contests.sponsored_link',
                'contests.sponsored_image'
            ])->each(function ($contest) {
                $contest->banner = ($contest->banner != NULL && $contest->banner != '') ? url($this->contestThumbImageUploadPath.$contest->banner) : '';
                $contest->sponsored_video_link = ($contest->sponsored_video_link != NULL && $contest->sponsored_video_link != '') ? url($this->sponsorVideoUploadPath.$contest->sponsored_video_link) : '';
                $contest->sponsored_link = ($contest->sponsored_link != NULL && $contest->sponsored_link != '') ? $contest->sponsored_link : '';
                $contest->sponsored_image = ($contest->sponsored_image != NULL && $contest->sponsored_image != '') ? url($this->sponsorOriginalImageUploadPath.$contest->sponsored_image) : '';
            });

            $userID = $request->user()->id;
            // Get upcoming public contest count of game with game detail
            $game = Game::with(['contests' => function($query) {
                            $query->where([
                                ['status', Config::get('constant.UPCOMING_CONTEST_STATUS')],
                                ['contest_start_time', '>', date('Y-m-d H:i:s')],
                                ['privacy', Config::get('constant.PUBLIC_CONTEST')]
                            ]);
                        }
                    ])
                    ->notDeleted()->get()->each(function ($game) use($userID) {
                        $participatedCount = Contest::join('contest_user', 'contest_user.contest_id', '=', 'contests.id')->where([
                                ['contests.status', Config::get('constant.UPCOMING_CONTEST_STATUS')],
                                ['contests.contest_start_time', '>', date('Y-m-d H:i:s')],
                                ['contests.privacy', Config::get('constant.PUBLIC_CONTEST')],
                                ['contests.game_id', $game->id],
                                ['contest_user.user_id', $userID]
                            ])->count();
                        $game->contets_count = count($game->contests) - $participatedCount;

                        $beginnerCount = DB::select(' select (select count(*) as aggregate from `contests` where `status` = "'.Config::get('constant.UPCOMING_CONTEST_STATUS').'" and `contest_start_time` > "'.date("Y-m-d H:i:s").'" and `privacy` = "'.Config::get("constant.PUBLIC_CONTEST").'" and `game_id` = '.$game->id.' and `level_id` = 1) - (select count(*) as aggregate from `contests` inner join `contest_user` on `contest_user`.`contest_id` = `contests`.`id` where `contests`.`status` = "'.Config::get('constant.UPCOMING_CONTEST_STATUS').'" and `contests`.`contest_start_time` > "'.date("Y-m-d H:i:s").'" and `contests`.`privacy` = "'.Config::get("constant.PUBLIC_CONTEST").'" and `contests`.`game_id` = '.$game->id.' and `contest_user`.`user_id` = '.$userID.' and `contests`.`level_id` = 1) as beginnerCount');

                        $game->beginner_count = $beginnerCount[0]->beginnerCount;

                        $intermediateCount = DB::select(' select (select count(*) as aggregate from `contests` where `status` = "'.Config::get('constant.UPCOMING_CONTEST_STATUS').'" and `contest_start_time` > "'.date("Y-m-d H:i:s").'" and `privacy` = "'.Config::get("constant.PUBLIC_CONTEST").'" and `game_id` = '.$game->id.' and `level_id` = 2) - (select count(*) as aggregate from `contests` inner join `contest_user` on `contest_user`.`contest_id` = `contests`.`id` where `contests`.`status` = "'.Config::get('constant.UPCOMING_CONTEST_STATUS').'" and `contests`.`contest_start_time` > "'.date("Y-m-d H:i:s").'" and `contests`.`privacy` = "'.Config::get("constant.PUBLIC_CONTEST").'" and `contests`.`game_id` = '.$game->id.' and `contest_user`.`user_id` = '.$userID.' and `contests`.`level_id` = 2) as intermediateCount');

                        $game->intermediate_count = $intermediateCount[0]->intermediateCount;

                        $advanceCount = DB::select(' select (select count(*) as aggregate from `contests` where `status` = "'.Config::get('constant.UPCOMING_CONTEST_STATUS').'" and `contest_start_time` > "'.date("Y-m-d H:i:s").'" and `privacy` = "'.Config::get("constant.PUBLIC_CONTEST").'" and `game_id` = '.$game->id.' and `level_id` = 3) - (select count(*) as aggregate from `contests` inner join `contest_user` on `contest_user`.`contest_id` = `contests`.`id` where `contests`.`status` = "'.Config::get('constant.UPCOMING_CONTEST_STATUS').'" and `contests`.`contest_start_time` > "'.date("Y-m-d H:i:s").'" and `contests`.`privacy` = "'.Config::get("constant.PUBLIC_CONTEST").'" and `contests`.`game_id` = '.$game->id.' and `contest_user`.`user_id` = '.$userID.' and `contests`.`level_id` = 3) as advanceCount');

                        $game->advance_count = $advanceCount[0]->advanceCount;

                        $extremeCount = DB::select(' select (select count(*) as aggregate from `contests` where `status` = "'.Config::get('constant.UPCOMING_CONTEST_STATUS').'" and `contest_start_time` > "'.date("Y-m-d H:i:s").'" and `privacy` = "'.Config::get("constant.PUBLIC_CONTEST").'" and `game_id` = '.$game->id.' and `level_id` = 4) - (select count(*) as aggregate from `contests` inner join `contest_user` on `contest_user`.`contest_id` = `contests`.`id` where `contests`.`status` = "'.Config::get('constant.UPCOMING_CONTEST_STATUS').'" and `contests`.`contest_start_time` > "'.date("Y-m-d H:i:s").'" and `contests`.`privacy` = "'.Config::get("constant.PUBLIC_CONTEST").'" and `contests`.`game_id` = '.$game->id.' and `contest_user`.`user_id` = '.$userID.' and `contests`.`level_id` = 4) as extremeCount');

                        $game->extreme_count = $extremeCount[0]->extremeCount;

                        unset($game->contests);
                    });

            return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => [
                            'contest' => $contest,
                            'game' => $game,
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
     * get Game Cases and case bundle
     *
     * @param Request $request The current request
     * @throws Exception If there was an error
     * @see \App\User
     * @see \App\Contest;
     * @see \App\GameCaseBundle;
     * @see \App\GameCase;
     * @see \App\Game;
     * @Post("/")
     * @Transaction({
     *     @Request({}),
     *     @Response( ),
     *     @Response( {"status": "0",'message': 'Error.','code' => $e->getStatusCode()} )
     * })
     */
    public function getGameCasesAndCaseBundle(Request $request)
    {
        try
        {
            $userID = $request->user()->id;
            $gameCases = GameCase::all()->each(function ($gameCases) {
                $gameCases->photo = ($gameCases->photo != NULL && $gameCases->photo != '') ? url($this->gameCaseThumbImageUploadPath.$gameCases->photo) : '';
                $gameCases->price = (string)$gameCases->price;
            });

            $gameCaseBundle = GameCaseBundle::all()->each(function ($gameCaseBundle) {
                $gameCaseBundle->gamecase_image = ($gameCaseBundle->gamecase_image != NULL && $gameCaseBundle->gamecase_image != '') ? url($this->gameCaseBundleThumbImageUploadPath.$gameCaseBundle->gamecase_image) : '';
                $gameCaseBundle->price = (string)$gameCaseBundle->price;
            });
            return response()->json([
                'status' => '1',
                'message' => 'Success',
                'data' => [
                    'gameCases' => $gameCases,
                    'gameCaseBundle' => $gameCaseBundle
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
