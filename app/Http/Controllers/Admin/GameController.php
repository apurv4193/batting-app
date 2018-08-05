<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use App\Game;
use Redirect;
use Response;
use Config;

class GameController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('IsAdmininstrator');
        $this->objGame = new Game();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function addGame() {
        return view('admin.add-Game');
    }

    /* save game in data */

    public function saveGame() {

        if (null !== Input::get('id') && Input::get('id') != '0') {
            $this->validate(request(), [
                'name' => ['required',Rule::unique('games', 'name')->ignore(Input::get('id'))]
            ]);
        }

        $data = Input::all();

        try {
            if (is_numeric($data['id']) && $data['id'] == 0) {

                foreach ($data['name'] as $_name) {

                    if (empty($_name))
                        continue;

                    $game = new Game();
                    $game->name = trim($_name);
                    $game->save();
                }
            } else {
                $game = $this->objGame->find($data['id']);
                $game->name = trim($data['name']);
                $game->save();
            }
            if ($data['id'] == 0) {
                return Redirect::to('admin/games')->with('success', trans('adminmsg.game_created_success'));
            } else {
                return Redirect::to('admin/games')->with('success', trans('adminmsg.game_updated_success'));
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $error_message = $e->getMessage();
            if (strpos($error_message, '1062 Duplicate entry') !== false) {

                if (strpos($error_message, 'games_name_unique') !== false) {
                    $msg = "Game name already exists.";
                }
            }
            $msg = trans($msg);
            return Redirect::to("admin/games")->with('error', $msg);
        } catch (ModelNotFoundException $e) {
            $msg = trans('adminmsg.game_not_exist');
            return Redirect::to("admin/games")->with('error', $msg);
        } catch (Exception $e) {
            $msg = trans('adminmsg.common_error_msg');
            return Redirect::to("admin/games")->with('error', $msg);
        }
    }

    public function getGames() {
        return view('admin.game-list');
    }

    public function editGame($id) {
        $game = Game::find($id);

        if (!$game || ($game && $game->status == Config::get('constant.DELETED'))) {
            return Redirect::to("/admin/games/")->with('error', trans('adminmsg.game_not_exist'));
        }

        return view('admin.add-Game', compact('game'));
    }

    public function listGameAjax() {
        $records = array();
        //processing custom actions
        if (Input::get('customActionType') == 'groupAction') {

            $action = Input::get('customActionName');
            $idArray = Input::get('id');

            switch ($action) {
                case "delete":
                    foreach ($idArray as $_idArray) {
                        $game = Game::find($_idArray);
                        $game->status = 1;
                        $game->save();
                    }
                    $records["customMessage"] = trans('adminmsg.delete_game');
            }
        }

        $columns = array(
            0 => 'name'
        );

        $order = Input::get('order');
        $search = Input::get('search');

        $records["data"] = array();
        $iTotalRecords = Game::where('status', Config::get('constant.ACTIVE_STATUS_FLAG'))->count();
        $iTotalFiltered = $iTotalRecords;
        $iDisplayLength = intval(Input::get('length')) <= 0 ? $iTotalRecords : intval(Input::get('length'));
        $iDisplayStart = intval(Input::get('start'));
        $sEcho = intval(Input::get('draw'));

        $records["data"] = Game::select('*')->where('status', Config::get('constant.ACTIVE_STATUS_FLAG'));

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
        if (!empty($records["data"])) {
            foreach ($records["data"] as $key => $_records) {
                $edit = route('game.edit', $_records->id);

                $records["data"][$key]['action'] = "&emsp;<a href='{$edit}' title='Edit Game' ><span class='glyphicon glyphicon-edit'></span></a>
                                                    &emsp;<a href='javascript:;' data-id='" . $_records->id . "' class='btn-delete-game' title='Delete Game' ><span class='glyphicon glyphicon-trash'></span></a>";
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalFiltered;

        return Response::json($records);
    }

}
