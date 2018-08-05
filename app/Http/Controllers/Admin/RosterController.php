<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Roster;
use Redirect;
use Response;
use App\Contest;

class RosterController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() 
    {
        $this->middleware('IsAdmininstrator');
        $this->objRoster = new Roster();
        $this->objContest = new Contest();
    }

    public function getRosters() 
    {
        return view('admin.roster-list');
    }

    public function listRosterAjax() 
    {
        $records = array();
        //processing custom actions
        if (Input::get('customActionType') == 'groupAction') {

            $action = Input::get('customActionName');
            $idArray = Input::get('id');

            switch ($action) {
                case "delete":
                    foreach ($idArray as $_idArray) {
                        $contest = Roster::find($_idArray);
                        $contest->delete();
                    }
                    $records["customMessage"] = trans('adminmsg.delete_roster');
            }
        }

        $columns = array(
            0 => 'contest_name',
            1 => 'roster',
            2 => 'roster_cap_amount',
        );

        $order = Input::get('order');
        $search = Input::get('search');

        $records["data"] = array();
        $iTotalRecords = Roster::count();
        $iTotalFiltered = $iTotalRecords;
        $iDisplayLength = intval(Input::get('length')) <= 0 ? $iTotalRecords : intval(Input::get('length'));
        $iDisplayStart = intval(Input::get('start'));
        $sEcho = intval(Input::get('draw'));

        $records["data"] = Roster::with('contest');
        $records["data"] = Roster::leftjoin('contests', 'contests.id', '=', 'rosters.contest_id');

        if (!empty($search['value'])) {
            $val = $search['value'];
            $records["data"]->where(function($query) use ($val) {
                $query->SearchContestName($val);
                $query->SearchRoster($val);
                $query->SearchRosterCapAmount($val);
            });

            // No of record after filtering
            $iTotalFiltered = $records["data"]->where(function($query) use ($val) {
                        $query->SearchContestName($val);
                        $query->SearchRoster($val);
                        $query->SearchRosterCapAmount($val);
                    })->count();
        }

        //order by
        foreach ($order as $o) {
            $records["data"] = $records["data"]->orderBy($columns[$o['column']], $o['dir']);
        }

        //limit
        $records["data"] = $records["data"]->take($iDisplayLength)->offset($iDisplayStart)->get([
            'contests.contest_name',
            'rosters.roster',
            'rosters.roster_cap_amount',
            'rosters.id'
        ]);
        if (!empty($records["data"])) {
            foreach ($records["data"] as $key => $_records) {
                $edit = route('roster.edit', $_records->id);
                $addPlayer = route('roster.addPlayer', $_records->id);
                $records["data"][$key]['action'] = "&emsp;<a href='{$addPlayer}' title='Players'>Player</a>
                                                    &emsp;<a href='{$edit}' title='Edit Roster' ><span class='glyphicon glyphicon-edit'></span></a>
                                                    &emsp;<a href='javascript:;' data-id='" . $_records->id . "' class='btn-delete-roster' title='Delete Roster' ><span class='glyphicon glyphicon-trash'></span></a>";
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalFiltered;
        return Response::json($records);
    }

    public function addRoster() 
    {
        $contests = $this->objContest->select('*')->get();
        return view('admin.add-roster', compact('contests'));
    }

    public function saveRoster() 
    {
        $this->validate(request(), [
            'roster' => 'required',
            'roster_cap_amount' => 'required',
        ]);
        $postData = Input::all();
        $roster = $this->objRoster->find($postData['id']);
        if (isset($postData['id']) && $postData['id'] > 0) {
            $roster->roster = $postData['roster'];
            $roster->roster_cap_amount = $postData['roster_cap_amount'];
            $roster->save();
            return Redirect::to("/admin/rosters/")->with('success', trans('adminmsg.roster_updated_success'));
        } else {
            $this->objRoster->create($postData);
            return Redirect::to("/admin/rosters/")->with('success', trans('adminmsg.roster_created_success'));
        }
    }

    public function editRoster($id) 
    {
        $roster = $this->objRoster->find($id);
        $contests = $this->objContest->select('*')->get();

        if (!$roster) {
            return Redirect::to("/admin/rosters/")->with('error', trans('adminmsg.roster_not_exist'));
        }

        return view('admin.add-roster', compact('roster', 'contests'));
    }

}
