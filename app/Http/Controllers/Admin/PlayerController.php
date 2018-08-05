<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Players;
use App\Game;
use App\GamesPlayers;
use Helpers;
use Redirect;
use File;
use Image;
use Config;
use Response;

class PlayerController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('IsAdmininstrator');
        $this->objPlayers = new Players();
        $this->objGame = new Game();
        $this->objGamePlayers = new GamesPlayers();
        $this->playersOriginalImageUploadPath = Config::get('constant.PLAYERS_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->playersThumbImageUploadPath = Config::get('constant.PLAYERS_THUMB_IMAGE_UPLOAD_PATH');
        $this->playersThumbImageHeight = Config::get('constant.PLAYERS_THUMB_IMAGE_HEIGHT');
        $this->playersThumbImageWidth = Config::get('constant.PLAYERS_THUMB_IMAGE_WIDTH');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPlayers() {
        return view('admin.players-list');
    }

    public function savePlayers() {

        if( !empty(Input::get('id')) && Input::get('id') != null && Input::get('id') != ''){
           $this->validate(request(), [
                'name' => 'required|max:100|regex:/^(?![0-9]*$)[a-zA-Z0-9 ]+$/',
                'description' => 'required',
                'profile_image' => 'image|max:10240',
              /*  'cap_amount' => 'numeric',
                'win' => 'integer',
                'loss' => 'integer',*/
            ]); 
        }
        else {
            $this->validate(request(), [
                'name' => 'required|max:100|regex:/^(?![0-9]*$)[a-zA-Z0-9 ]+$/',
                'game_id' => 'required',
                'description' => 'required',
                'profile_image' => 'image|max:10240',
              /*  'cap_amount' => 'numeric',
                'win' => 'integer',
                'loss' => 'integer',*/
            ]);
        }
       
        $data = Input::all();
        $existingPlayer = Players::where('name',$data['name'])->first();
        if( !is_null($existingPlayer) )
        {
            //print_r("expression");die;
            foreach ($data['game_id'] as $key => $value) {
                if( GamesPlayers::where('player_id',$existingPlayer->id)->where('game_id',$value)->first() ) {
                    return Redirect::back()->with('error', trans('adminmsg.player_game_exist'));
                }
            }
        }
        $Players = $this->objPlayers->find($data['id']);
        $hiddenProfile = Input::get('hidden_profile');
        $data['profile_image'] = $hiddenProfile;
        $file = Input::file('profile_image');
        
        if (Input::file()) {
            $file = Input::file('profile_image');
            if (!empty($file)) {
                $fileName = 'players_' . time() . '.' . $file->getClientOriginalExtension();
                $pathOriginal = public_path($this->playersOriginalImageUploadPath . $fileName);
                $pathThumb = public_path($this->playersThumbImageUploadPath . $fileName);

                if (!file_exists(public_path($this->playersOriginalImageUploadPath)))
                    File::makeDirectory(public_path($this->playersOriginalImageUploadPath), 0777, true, true);
                if (!file_exists(public_path($this->playersThumbImageUploadPath)))
                    File::makeDirectory(public_path($this->playersThumbImageUploadPath), 0777, true, true);

                // created instance
                $img = Image::make($file->getRealPath());

                $img->save($pathOriginal);
    
                // resize the image to a height of $this->contestThumbImageHeight and constrain aspect ratio (auto width)
                if( $img->height() < 500 ){
                    $img->resize(null, $img->height(), function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($pathThumb);
                }
                else{
                    $img->resize(null, $this->playersThumbImageHeight, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($pathThumb);
                }

                if ($hiddenProfile != '' && $hiddenProfile != "default.png") {
                    $imageOriginal = public_path($this->playersOriginalImageUploadPath . $hiddenProfile);
                    $imageThumb = public_path($this->playersThumbImageUploadPath . $hiddenProfile);
                    if (file_exists($imageOriginal) && $hiddenProfile != '') {
                        File::delete($imageOriginal);
                    }
                    if (file_exists($imageThumb) && $hiddenProfile != '') {
                        File::delete($imageThumb);
                    }
                }
                $data['profile_image'] = $fileName;
            }
        }
        if (isset($data['id']) && $data['id'] > 0) {
            if($Players === null || ($Players && $Players->status == Config::get('constant.DELETED_STATUS_FLAG'))) {
                return Redirect::to("/admin/players/")->with('error', 'Player data not found!');
            }
            
            $Players->name = $data['name'];
            //$Players->game_id = $data['game_id'];
            $Players->description = $data['description'];
            $Players->profile_image = $data['profile_image'];
            /*$Players->cap_amount = $data['cap_amount'];
            $Players->win = $data['win'];
            $Players->loss = $data['loss'];*/
            $Players->save();
            return Redirect::to("/admin/players/")->with('success', trans('adminmsg.players_updated_success'));
        } else {
            $create = $this->objPlayers->create($data);
    
            foreach ($data['game_id'] as $key => $value) {
                $data['player_id'] = $create->id;
                $data['game_id'] = $value;
                $this->objGamePlayers->create($data);
            }
            return Redirect::to("/admin/players/")->with('success', trans('adminmsg.players_created_success'));
        }
    }

    public function savePlayersGames() {
        $this->validate(request(), [
            'game_id' => 'required',
            'cap_amount' => 'numeric|min:0|max:999999',
            'win' => 'integer|min:0|max:999999',
            'loss' => 'integer|min:0|max:999999'
        ]);
        $data = Input::all();
        $Games = $this->objGamePlayers->find($data['id']);
        
        if (isset($data['id']) && $data['id'] > 0) {
            if($Games === null || ($Games && $Games->status == Config::get('constant.DELETED_STATUS_FLAG'))) {
                return Redirect::to("/admin/view-games/".$data['player_id'])->with('error', 'Player game data not found!');
            }
        
            $Games->player_id = $data['player_id'];
            $Games->game_id = $data['game_id'];
            $Games->cap_amount = $data['cap_amount'];
            $Games->win = $data['win'];
            $Games->loss = $data['loss'];
          
            $Games->save();
            return Redirect::to("/admin/view-games/".$data['player_id'])->with('success', trans('adminmsg.player_game_updated_success'));
        } else {

            $gameData['player_id'] = $data['player_id'];
            $gameData['game_id'] = $data['game_id'];
            $gameData['cap_amount'] = $data['cap_amount'];
            $gameData['loss'] = $data['loss'];
            $gameData['win'] = $data['win'];

            $this->objGamePlayers->create($gameData);

            return Redirect::to("/admin/view-games/".$data['player_id'])->with('success', trans('adminmsg.player_game_created_success'));
        }
    }
    /*
     * Players edit 
     */

    public function editPlayers($id) {
        $Playesr = $this->objPlayers->find($id);
        if (!$Playesr || ($Playesr && $Playesr->status == Config::get('constant.DELETED'))) {
            return Redirect::to("/admin/players/")->with('error', trans('adminmsg.players_not_exist'));
        }
        $gameList = Helpers::getGames();
        $playersImage = $this->playersThumbImageUploadPath;
        return view('admin.add-Players', compact('Playesr','gameList','playersImage'));
    }

    /*
     * Players game edit 
     */

    public function editPlayersGames($id) {
        $Games = $this->objGamePlayers->find($id);
        
        if (!$Games) {
            return Redirect::to("/admin/players/")->with('error', trans('adminmsg.game_not_exist'));
        }
        $gameList = Helpers::getPlayersGamesEdit($Games->player_id,$Games->game_id);

        return view('admin.add-Players-Games', compact('Games','gameList' , 'id'));
    }
    /*
     * Games list 
     */

    public function getGames($id) {
        return view('admin.view-games',compact('id'));
    }

    /*
     * 
     * Add Players 
     */

    public function addPlayers() {
        $gameList = Helpers::getGames();
        return view('admin.add-Players', compact('gameList'));
    }

    /* Add player's games */
    public function addPlayersGames($playerId) {
        $gameList = Helpers::getPlayersGames($playerId);
        return view('admin.add-Players-Games', compact('gameList','playerId'));
    }
    /*
     * Ajax List 
     */

    public function listPlayersAjax() {
        $records = array();
        //processing custom actions
        if (Input::get('customActionType') == 'groupAction') {
            $action = Input::get('customActionName');
            $idArray = Input::get('id');
            switch ($action) {
                case "delete":
                    foreach ($idArray as $_idArray) {
                        $playersDelete = Players::find($_idArray);
                        if($playersDelete !== null) {
                            $playersDelete->status = 1;
                            $playersDelete->save();
                        }
                    }
                    $records["customMessage"] = trans('adminmsg.delete_players');
            }
        }
        $columns = array(
            0 => 'name',            
            1 => 'profile_image',
          /*  3 => 'cap_amount',
            4 => 'win',
            5 => 'loss',*/
        );
        $order = Input::get('order');
        $search = Input::get('search');
        $records["data"] = array();
        $iTotalRecords = Players::where('status', 0)->count();
        $iTotalFiltered = $iTotalRecords;
        $iDisplayLength = intval(Input::get('length')) <= 0 ? $iTotalRecords : intval(Input::get('length'));
        $iDisplayStart = intval(Input::get('start'));
        $sEcho = intval(Input::get('draw'));
        $records["data"] = Players::select('*')->where('status', 0);
        if (!empty($search['value'])) {
            $val = $search['value'];
            $records["data"]->where(function($query) use ($val) {
                $query->SearchName($val);
            });
            // No of record after filtering
            $iTotalFiltered = $records["data"]->where(function($query) use ($val) {
                        $query->SearchName($val);
                    })->count();
        }
        //order by
        foreach ($order as $o) {
            $records["data"] = $records["data"]->orderBy($columns[$o['column']], $o['dir']);
        }
        //limit
        $records["data"] = $records["data"]->take($iDisplayLength)->offset($iDisplayStart)->get();        
        if (!empty($records["data"]) && count($records["data"]) != 0) {            
            foreach ($records["data"] as $key => $_records) {  
                $edit = route('players.edit', $_records->id);
                $view_games = route('games.view', $_records->id);
                $game_Id = $this->objGame->find($_records->game_id);
                //$records["data"][$key]['game_id'] = $game_Id['name'];
                $records["data"][$key]['profile_image'] = ($_records->profile_image != '' && File::exists(public_path($this->playersThumbImageUploadPath . $_records->profile_image)) ? '<img src="' . url($this->playersThumbImageUploadPath . $_records->profile_image) . '"   height="50" width="50">' : '<img src="' . asset('/images/default.png') . '" class="user-image" alt="Default Image" height="50" width="50">');
                $records["data"][$key]['action'] = "&emsp;<a href='{$edit}' title='Edit Players' ><span class='glyphicon glyphicon-edit'></span></a>
                                                    &emsp;<a href='javascript:;' data-id='" . $_records->id . "' class='btn-delete-players' title='Delete Players' ><span class='glyphicon glyphicon-trash'></span></a>&emsp;<a href='{$view_games}' title='View Players' ><span class='glyphicon glyphicon-eye-open'></span></a>";
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalFiltered;

        return Response::json($records);
    }


    public function listGamesAjax() {
    
        $records = array();
        //processing custom actions
        if (Input::get('customActionType') == 'groupAction') {
            $action = Input::get('customActionName');
            $idArray = Input::get('id');
            switch ($action) {
                case "delete":
                    foreach ($idArray as $_idArray) {
                        $playersDelete = GamesPlayers::where('player_id',$_idArray)->get();
                        $playersDelete->delete();
                    }
                    $records["customMessage"] = trans('adminmsg.delete_game');
            }
        }
        $columns = array(
            0 => 'games.name',
            1 => 'cap_amount',
            2 => 'win',
            3 => 'loss',
        );
        $order = Input::get('order');
        $search = Input::get('search');
        $records["data"] = array();
        $iTotalRecords = GamesPlayers::where('player_id',Input::get('player_id'))->count();
        $iTotalFiltered = $iTotalRecords;
        $iDisplayLength = intval(Input::get('length')) <= 0 ? $iTotalRecords : intval(Input::get('length'));
        $iDisplayStart = intval(Input::get('start'));
        $sEcho = intval(Input::get('draw'));
        $records["data"] = GamesPlayers::leftjoin('games', 'games.id', '=', 'games_players.game_id')
                ->leftjoin('players', 'players.id', '=', 'games_players.player_id')->where('games_players.player_id', Input::get('player_id'));

        
        if (!empty($search['value'])) {
            $val = $search['value'];
            $records["data"]->where(function($query) use ($val) {
                $query->SearchName($val);
                //$query->SearchCapAmount($val);
            });

            // No of record after filtering
            $iTotalFiltered = $records["data"]->where(function($query) use ($val) {
                        $query->SearchName($val);
                        //$query->SearchCapAmount($val);
                    })->count();
        }
        
        //order by
        foreach ($order as $o) {
            $records["data"] = $records["data"]->orderBy('games.name', $o['dir']);
        }
        //limit
        $records["data"] = $records["data"]->take($iDisplayLength)->offset($iDisplayStart);  

        $records['data'] = $records["data"]->get(['games.name as game_name','players.name as player_name','game_id','player_id','cap_amount','loss','win','games_players.id as gameId']);

        if (!empty($records["data"]) && count($records["data"]) != 0) {            
            foreach ($records["data"] as $key => $_records) {  
                $edit = route('players_game.edit', $_records->gameId);
                
                $records["data"][$key]['action'] = "&emsp;<a href='{$edit}' title='Edit Games' ><span class='glyphicon glyphicon-edit'></span></a>";
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalFiltered;

        return Response::json($records);
    }

}
