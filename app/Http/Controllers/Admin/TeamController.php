<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Team;
use App\TeamPlayers;
use App\Game;
use App\GamesPlayers;
use Redirect;
use File;
use Image;
use Config;
use Response;
use DB;
use Helpers;

class TeamController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('IsAdmininstrator');
        $this->objTeam = new Team();
        $this->objTeamPlayers = new TeamPlayers();
        $this->objGame = new Game();
        $this->objGamePlayers = new GamesPlayers();
        $this->teamOriginalImageUploadPath = Config::get('constant.TEAM_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->teamThumbImageUploadPath = Config::get('constant.TEAM_THUMB_IMAGE_UPLOAD_PATH');
        $this->teamThumbImageHeight = Config::get('constant.TEAM_THUMB_IMAGE_HEIGHT');
        $this->teamThumbImageWidth = Config::get('constant.TEAM_THUMB_IMAGE_WIDTH');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTeams() {
        return view('admin.team-list');
    }

    /*
     * 
     * Add Players 
     */
    public function addTeams() {
        $gameList = Helpers::getGames();
        $contestTypesList = Helpers::getContestTypes();
        return view('admin.add-Team', compact('gameList', 'contestTypesList'));
    }

    /*
     * 
     * Get Player By Game  
     */
    public function getPlayerByGame() {
        $gameId = Input::get('gameId');
        $playerList = Helpers::getPlayersByGame($gameId);
        return json_encode($playerList);
    }

    /*
     * 
     * Get Contest Cap Amount  
     */
    public function getCapAmount() {
        $contestId = Input::get('contestId');
        $capAmount = Helpers::getCapAmount($contestId);
        $contestMaxPlayer = Helpers::getContestTypePlayer($contestId);
        return json_encode([
            'capAmount' => $capAmount,
            'contestMaxPlayer' => $contestMaxPlayer
        ]);
    }

    /*
     * Ajax List 
     */
    public function listTeamsAjax() {
        $records = array();
        //processing custom actions
        if (Input::get('customActionType') == 'groupAction') {
            $action = Input::get('customActionName');
            $idArray = Input::get('id');
            switch ($action) {
                case "delete":
                    foreach ($idArray as $_idArray) {
                        try {
                            DB::beginTransaction();
                            $team = Team::find($_idArray);
                            if($team !== null) {
                                $team->status = 1;
                                $team->save();
                                $team->players()->detach();
                            } 
                            DB::commit();
                        } catch (Exception $ex) {
                            DB::rollback();
                        }
                    }
                    $records["customMessage"] = trans('adminmsg.delete_team');
            }
        }

        $columns = array(
            0 => 'name',
            1 => 'game_id',
            2 => 'contest_type_id',
            3 => 'win',
            4 => 'loss',
            5 => 'team_cap_amount'
        );

        $order = Input::get('order');
        $search = Input::get('search');
        $records["data"] = array();
        $iTotalRecords = Team::count();
        $iTotalFiltered = $iTotalRecords;
        $iDisplayLength = intval(Input::get('length')) <= 0 ? $iTotalRecords : intval(Input::get('length'));
        $iDisplayStart = intval(Input::get('start'));
        $sEcho = intval(Input::get('draw'));

        $records["data"] = Team::leftjoin('games', 'games.id', '=', 'teams.game_id')
                ->leftjoin('contest_type', 'contest_type.id', '=', 'teams.contest_type_id')
                ->where('teams.status', 0);

        if (!empty($search['value'])) {
            $val = $search['value'];
            $records["data"]->where(function($query) use ($val) {
                $query->SearchName($val);
                $query->SearchGameName($val);
                $query->SearchContestType($val);
                $query->SearchWin($val);
                $query->SearchLoss($val);
                $query->SearchTeamCapAmount($val);
            });
            // No of record after filtering
            $iTotalFiltered = $records["data"]->where(function($query) use ($val) {
                        $query->SearchName($val);
                        $query->SearchGameName($val);
                        $query->SearchContestType($val);
                        $query->SearchWin($val);
                        $query->SearchLoss($val);
                        $query->SearchTeamCapAmount($val);
                    })->count();
        }
        //order by
        foreach ($order as $o) {
            $records["data"] = $records["data"]->orderBy($columns[$o['column']], $o['dir']);
        }
        //limit
        $records["data"] = $records["data"]->take($iDisplayLength)->offset($iDisplayStart)->get([
                'teams.id',
                'teams.name',
                'games.name as game_name',
                'contest_type.type',
                'teams.win',
                'teams.loss',
                'teams.team_cap_amount',
                'teams.team_image'
            ]);
        if (!empty($records["data"]) && count($records["data"]) != 0) {
            
            foreach ($records["data"] as $key => $_records) {
                $edit = route('teams.edit', $_records->id);
                
                $records["data"][$key]['team_image'] = ($_records->team_image != '' && File::exists(public_path($this->teamThumbImageUploadPath . $_records->team_image)) ? '<img src="'.url($this->teamThumbImageUploadPath.$_records->team_image).'" alt="{{$_records->team_image}}"  height="50" width="50">' : '<img src="'.asset('/images/default.png').'" alt="Default Image" height="50" width="50">');
                $records["data"][$key]['action'] = "&emsp;<a href='{$edit}' title='Edit Team' ><span class='glyphicon glyphicon-edit'></span></a>
                                                    &emsp;<a href='javascript:;' data-id='" . $_records->id . "' class='btn-delete-team' title='Delete Team' ><span class='glyphicon glyphicon-trash'></span></a>&emsp;";
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalFiltered;

        return Response::json($records);
    }

    public function saveTeams(Request $request) {
        
        try {
            DB::beginTransaction();
            
            $id = (Input::get('id')) ? Input::get('id') : 0;            
            if($id > 0) {
                $this->validate(request(), [
                    'name' => 'required|max:255',
                    'game_id' => 'required',
                    'contest_type_id' => 'required',
                    'win' => 'required|integer',
                    'loss' => 'required|integer',
                    'player' => 'required',
                ]);
            } else {
                $this->validate(request(), [
                    'name' => 'required|max:255',
                    'game_id' => 'required',
                    'contest_type_id' => 'required',
                    'player' => 'required',
                ]);
            }
            
            $contestPlayer = Helpers::getContestTypePlayer(Input::get('contest_type_id')); // Get contest player count from contest type
            if($contestPlayer < count(Input::get('player'))) {
                DB::rollback();
                return Redirect::back()->withInput($request->all())->withErrors([
                    'You must have to select ' . $contestPlayer . ' player for this team.'
                ]);
            }
            
             // Get player cap amount sum of player for given game
            $playerCapAmountSum = Helpers::getCapAmountSumofPlayer($request->player, $request->game_id);
            if(Helpers::getCapAmount($request->contest_type_id) < $playerCapAmountSum) {
                DB::rollback();
                return Redirect::back()->withInput($request->all())->withErrors([
                    'Team exceeded max player cap amount.'
                ]);
            }

            $allTeam = Team::where('id',"!=",Input::get('id'))->get();
            foreach ($allTeam as $key => $value) {
                
                if($value->name == $request->name && $value->game_id == $request->game_id && $value->contest_type_id == $request->contest_type_id )
                {
                    DB::rollback();
                    return Redirect::back()->withInput($request->all())->withErrors([
                        'Team already exist.'
                    ]); 
                }
            }

            $team = $this->objTeam->find(Input::get('id'));
            
            $data = $request->only('name', 'game_id', 'contest_type_id');
            $data['team_cap_amount'] = $playerCapAmountSum;

            $hiddenTeamImage = Input::get('hidden_team_image');
            $data['team_image'] = $hiddenTeamImage;
            $file = Input::file('team_image');

            if (Input::file()) {
                $file = Input::file('team_image');
                if (!empty($file)) {
                    $fileName = 'team_' . str_random(20) . '.' . $file->getClientOriginalExtension();
                    $pathOriginal = public_path($this->teamOriginalImageUploadPath . $fileName);
                    $pathThumb = public_path($this->teamThumbImageUploadPath . $fileName);

                    if (!file_exists(public_path($this->teamOriginalImageUploadPath)))
                        File::makeDirectory(public_path($this->teamOriginalImageUploadPath), 0777, true, true);
                    if (!file_exists(public_path($this->teamThumbImageUploadPath)))
                        File::makeDirectory(public_path($this->teamThumbImageUploadPath), 0777, true, true);

                    // created instance
                    $img = Image::make($file->getRealPath());

                    $img->save($pathOriginal);

                    // resize the image to a height of $this->teamThumbImageHeight and constrain aspect ratio (auto width)
                    if( $img->height() < 500 ){
                        $img->resize(null, $img->height(), function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($pathThumb);
                    }
                    else{
                        $img->resize(null, $this->teamThumbImageHeight, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($pathThumb);
                    }

                    if ($hiddenTeamImage != '' && $hiddenTeamImage != 'default.png') {
                        $imageOriginal = public_path($this->teamOriginalImageUploadPath . $hiddenTeamImage);
                        $imageThumb = public_path($this->teamThumbImageUploadPath . $hiddenTeamImage);
                        if (file_exists($imageOriginal)) {
                            File::delete($imageOriginal);
                        }
                        if (file_exists($imageThumb)) {
                            File::delete($imageThumb);
                        }
                    }
                    $data['team_image'] = $fileName;
                }
            }
            
            if ($id > 0) {
                if($team === null || ($team && $team->status == Config::get('constant.DELETED_STATUS_FLAG'))) {
                    DB::rollback();
                    return Redirect::to("/admin/team/")->with('error', 'Team data not found!');
                }

                $data['win'] = $request->win;
                $data['loss'] = $request->loss;
                $team->update(array_filter($data));

                if($request->has('player')) {
                    $team->players()->detach();
                    foreach($request->player as $player) {
                        $playerCapAmount = Helpers::getPlayerCapAmount($player, Input::get('game_id'));
                        $team->players()->attach($player, ['team_player_cap_amount'=> $playerCapAmount ]);
                    }
                }
                DB::commit();
                return Redirect::to("/admin/team/")->with('success', trans('adminmsg.team_updated_success'));
            } else {
                $team = new Team(array_filter($data));
                $team->save();
                
                if($request->has('player')) {
                    foreach($request->player as $player) {
                        if(!$team->players()->find($player)) {
                            $playerCapAmount = Helpers::getPlayerCapAmount($player, Input::get('game_id'));
                            $team->players()->attach($player, ['team_player_cap_amount'=> $playerCapAmount ]);
                        }
                    }
                }
                DB::commit();
                return Redirect::to("/admin/team/")->with('success', trans('adminmsg.team_created_success'));
            }
        } catch (Exception $e) {
            DB::rollback();
            $msg = trans('adminlabels.common_error_msg');
            return Redirect::to("admin/team")->with('error', $msg);
        }
    }

    /*
     * Team edit 
     */

    public function editTeam($id) {
        $team = $this->objTeam->find($id);
        if (!$team || ($team && $team->status == Config::get('constant.DELETED_STATUS_FLAG'))) {
            return Redirect::to("/admin/team/")->with('error', trans('adminmsg.team_not_exist'));
        }
        $teamPlayer = TeamPlayers::where('team_id', $id)->pluck('player_id')->toArray();
        $gameList = Helpers::getGames();
        $contestTypesList = Helpers::getContestTypes();
        $playerList = Helpers::getPlayersByGame($team->game_id);
        $teamThumbImagePath = $this->teamThumbImageUploadPath;
        return view('admin.add-Team', compact('team', 'gameList', 'contestTypesList', 'playerList', 'teamPlayer', 'teamThumbImagePath'));
    }

}
