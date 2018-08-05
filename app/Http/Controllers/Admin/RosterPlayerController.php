<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Roster;
use Redirect;
use Response;

class RosterPlayerController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() 
    {
        $this->middleware('IsAdmininstrator');
        $this->objRoster = new Roster();
        
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRosterPlayers($rosterId) 
    {
        $roster = $this->objRoster->find($rosterId);
        $rosterPlayers = $roster->players;
        $players = $this->objRoster->notAvailablePlayers($rosterId, $rosterPlayers);
        return view('admin.roster-player-list', compact('rosterId', 'players'));
    }

    public function listRosterPlayersAjax()
    {
        $records = array();
        //processing custom actions
        if (Input::get('customActionType') == 'groupAction') {

            $action = Input::get('customActionName');
            $rosterId = Input::get('rosterId');
            $playerId = Input::get('playerId');

            switch ($action) {
                case "delete":
                    //foreach ($idArray as $_idArray) {
                        $roster = $this->objRoster->find($rosterId);
                        $roster->players()->detach($playerId);
                    //}
                    $records["customMessage"] = trans('adminmsg.delete_roster_player');
            }
        }

        $columns = array(
            0 => 'name',
        );

        $order = Input::get('order');
        // $search = Input::get('search');

        $records["data"] = array();

        $rosterId = Input::get('rosterId');
        $roster = $this->objRoster->find($rosterId);
        $records['data'] = $roster->players;
        
        $iTotalRecords = count((array)$records["data"]);
        $iTotalFiltered = $iTotalRecords;
        $iDisplayLength = intval(Input::get('length')) <= 0 ? $iTotalRecords : intval(Input::get('length'));
        $iDisplayStart = intval(Input::get('start'));
        $sEcho = intval(Input::get('draw'));
        
        // if (!empty($search['value'])) {
        //     $val = $search['value'];
        //     $records["data"]->where(function($query) use ($val) {
        //         $query->SearchRoster($val);
        //         $query->SearchRosterCapAmount($val);
        //     });

        //     // No of record after filtering
        //     $iTotalFiltered = $records["data"]->where(function($query) use ($val) {
        //                 $query->SearchRoster($val);
        //                 $query->SearchRosterCapAmount($val);
        //             })->count();
        // }

        //order by
        foreach ($order as $o) {
            $records["data"] = $this->objRoster->find($rosterId)->players()->orderBy($columns[$o['column']], $o['dir'])->get();
        }

        //limit
        if (!empty($records["data"])) {
            foreach ($records["data"] as $key => $_records) {
                $records["data"][$key]['action'] = "&emsp;<a href='javascript:;' data-id='" . $_records->id . "' class='btn-delete-roster' title='Delete Roster' ><span class='glyphicon glyphicon-trash'></span></a>";
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalFiltered;
        
        return Response::json($records);
    }

    public function saveRosterPlayer()
    {
        $rosterId = Input::get('roster_id');
        $playerId = Input::get('player_id');
        $playerArray['roster_id'] = $rosterId;
        $playerArray['player_id'] = $playerId;
        $rosterPlayer = $this->objRoster->saveRosterPlayerData($playerArray);
        if(isset($rosterPlayer) && !empty($rosterPlayer)) {
            return Redirect::to("/admin/roster-players/".$rosterId)->with('success', trans('adminmsg.roster_player_added_success'));
        } else {
            return Redirect::to("/admin/roster-players/".$rosterId)->with('error', trans('adminmsg.common_error_msg'));
        }
    }

}
