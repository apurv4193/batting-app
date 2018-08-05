<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\ContestType;
use Response;
use Redirect;

class ContestTypeController extends Controller {

    public function __construct() {
        $this->middleware('IsAdmininstrator');
        $this->objContestType = new ContestType();
    }

    public function getContest() {
        return view('admin.contestTypeList');
    }

    public function editContestType($id) {
        $contestType = $this->objContestType->find($id);
        if (!$contestType) {
            return Redirect::to("/admin/contest_type/")->with('error', trans('adminmsg.contest_type_not_exist'));
        }
        return view('admin.add-ContestType', compact('contestType'));
    }

    public function saveContestType() {
        $this->validate(request(), [
            'contest_cap_amount' => 'required|digits_between:1,6',
        ]);
        $contestData = Input::all();
        $contestType = $this->objContestType->find($contestData['id']);
        if (isset($contestData['id']) && $contestData['id'] > 0) {
            $contestType->contest_cap_amount = $contestData['contest_cap_amount'];
            $contestType->save();
            return Redirect::to("/admin/contest_type/")->with('success', trans('adminmsg.contest_type_updated_success'));
        }
    }

    public function listContestTypeAjax() {
        $records = array();
        $columns = array(
            0 => 'type',
            1 => 'contest_cap_amount'
        );
        $order = Input::get('order');
        $search = Input::get('search');
        $records["data"] = array();
        $iTotalRecords = ContestType::count();
        $iTotalFiltered = $iTotalRecords;
        $iDisplayLength = intval(Input::get('length')) <= 0 ? $iTotalRecords : intval(Input::get('length'));
        $iDisplayStart = intval(Input::get('start'));
        $sEcho = intval(Input::get('draw'));
        $records["data"] = ContestType::select('*');
        if (!empty($search['value'])) {
            $val = $search['value'];
            $records["data"]->where(function($query) use ($val) {
                $query->SearchType($val);
                $query->SearchCapAmount($val);
            });
            // No of record after filtering
            $iTotalFiltered = $records["data"]->where(function($query) use ($val) {
                        $query->SearchType($val);
                        $query->SearchCapAmount($val);
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
                $edit = route('Contest_Type.edit', $_records->id);
                $records["data"][$key]['action'] = "&emsp;<a href='{$edit}' title='Edit Contest Type' ><span class='glyphicon glyphicon-edit'></span></a>";
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalFiltered;

        return Response::json($records);
    }

}
