<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use App\PrizeDistributionPlan;
use App\PrizeRatio;
use Response;
use Redirect;
use Carbon\Carbon;
use DB;
use Config;

class PrizeController extends Controller {
    /*
     * Prize Distribution Plan Access of Admin side
     */

    public function __construct() {
        $this->middleware('IsAdmininstrator');
        $this->objPrizeDistribution = new PrizeDistributionPlan();
        $this->objPrizeRatio = new PrizeRatio();
    }

    /*
     * Prize Distribution Plan Get Listing View
     */

    public function getPrize() {
        return view('admin.prize-Distribution-Plan');
    }

    /*
     * Prize Distribution Plan Add Record View
     */

    public function addPrize() {
        return view('admin.add-Prize-Distribution-Plan');
    }

    /*
     * Prize Distribution Plan Save Record
     */

    public function savePrize() {

        $this->validate(request(), [
            'name' => 'required|max:100|regex:/^(?![0-9]*$)[a-zA-Z0-9 ]+$/',
        ]);
        DB::beginTransaction();
        try {
        $data = Input::all();

        $allPrize = PrizeDistributionPlan::where('status', Config::get('constant.NOT_DELETED'))->get();
        foreach ($allPrize as $key => $value) {
            if(isset($data['name']) && isset($data['winner'])) {

                if( $value->name == $data['name'] && $value->winner == $data['winner'])  {
                    DB::rollback();
                    return Redirect::back()->withInput()->withErrors([
                        'Prize Distribution Plan already exist.'
                    ]);
                } 
            } 

        }

        if( isset($data['id']) && $data['id'] > 0 ){
           $allPrize = PrizeDistributionPlan::where('id','!=',$data['id'])->NotDeleted()->get(); 
           foreach ($allPrize as $key => $value) {
                if(isset($data['name'])) {

                    if( $value->name == $data['name'])  {
                        DB::rollback();
                        return Redirect::back()->withInput()->withErrors([
                            'Prize Distribution Plan already exist.'
                        ]);
                    } 
                } 

            }   
        }

        $prizeDistriPlan = $this->objPrizeDistribution->find($data['id']);
        $prizeDistriRatio = $this->objPrizeRatio->GetPrizeRatio($data['id']);
        if (isset($data['id']) && $data['id'] > 0) {
            if($prizeDistriPlan === null || ($prizeDistriPlan && $prizeDistriPlan->status == Config::get('constant.DELETED_STATUS_FLAG'))) {
                DB::rollback();
                return Redirect::to("/admin/prize_distribution/")->with('error', trans('adminmsg.prize_not_exist'));
            }
            
            $prizeDistriPlan->name = $data['name'];
            $prizeDistriPlan->save();
            if (array_sum($data['prize_winner']) === 100 && !empty($data['prize_winner'])) {
                foreach ($data['prize_winner'] as $k => $savePrizeRatio) {
                    $prizeDistriRatio[$k]->ratio = $savePrizeRatio;
                    $prizeDistriRatio[$k]->save();
                }
            } else {
                DB::rollback();
                return back()->withInput()->with('error', 'Sum of % must be 100.');
            }
            DB::commit();
            return Redirect::to("/admin/prize_distribution/")->with('success', trans('adminmsg.prize_updated_success'));
        } else {

            $current = Carbon::now();
            if (isset($data['check']) == 'on') {
                $prize_distribution_plan['name'] = $data['name'];
                $prize_distribution_plan['winner'] = $data['check'] = 0;
                $this->objPrizeDistribution->create($prize_distribution_plan);
                DB::commit();
                return Redirect::to("/admin/prize_distribution/")->with('success', trans('adminmsg.prize_created_success'));
            } else {

                $prize_distribution_plan['name'] = $data['name'];
                $prize_distribution_plan['winner'] = $data['winner'];
                $prize_distribution_plan['created_at'] = $current;
                $prize_distribution_plan['updated_at'] = $current;
                $lastId = $this->objPrizeDistribution->insertGetId($prize_distribution_plan);
                if ($data['winner'] > 1) {
                    $prizeArray = $data['prize_winner'];
                    if (!empty($prizeArray) || is_numeric($prizeArray)) {
                        $prize_distribution_plan['name'] = $data['name'];
                        $prize_distribution_plan['winner'] = $data['winner'];
                        $prize_distribution_plan['created_at'] = $current;
                        $prize_distribution_plan['updated_at'] = $current;
                        if (array_sum($prizeArray) == 100) {
                            foreach ($prizeArray as $key => $prizeRatio) {
                                $prize_ratio_plan_id['prize_distribution_plan_id'] = $lastId;
                                $prize_ratio_plan_id['ratio'] = $prizeRatio;
                                $this->objPrizeRatio->create($prize_ratio_plan_id);
                            }
                            DB::commit();
                            return Redirect::to("/admin/prize_distribution/")->with('success', trans('adminmsg.prize_created_success'));
                        } else {
                            DB::rollback();
                            return back()->withInput()->with('error', 'Sum of % must be 100.');
                        }
                    } else {
                        DB::rollback();
                        return back()->withInput()->with('error', 'Please Enter Value For Prize Winner');
                    }
                } else if ($data['winner'] == 1) {
                    $prize_ratio_plan_id['prize_distribution_plan_id'] = $lastId;
                    $prize_ratio_plan_id['ratio'] = 100;
                    $this->objPrizeRatio->create($prize_ratio_plan_id);
                    DB::commit();
                    return Redirect::to("/admin/prize_distribution/")->with('success', trans('adminmsg.prize_created_success'));
                } 
                DB::commit();
                return Redirect::to("/admin/prize_distribution/")->with('success', trans('adminmsg.prize_created_success'));
            }
        }
        } catch (Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Something went wrong.');
        }
    }

    public function editPrize($id) {
        $editPrizeDistributionPlan = PrizeDistributionPlan::find($id);
        $editPrizeDistributionRatio = $this->objPrizeRatio->GetPrizeRatio($id);
        // echo '<pre>';
        // print_r($editPrizeDistributionRatio);die;
        if (!$editPrizeDistributionPlan || ($editPrizeDistributionPlan && $editPrizeDistributionPlan->status == Config::get('constant.DELETED'))) {
            return Redirect::to("/admin/prize_distribution/")->with('error', trans('adminmsg.prize_not_exist'));
        }
        return view('admin.add-Prize-Distribution-Plan', compact('editPrizeDistributionPlan', 'editPrizeDistributionRatio'));
    }

    public function listPrizeAjax() {
        $records = array();
        //processing custom actions
        if (Input::get('customActionType') == 'groupAction') {
            $action = Input::get('customActionName');
            $idArray = Input::get('id');
            switch ($action) {
                case "delete":
                    foreach ($idArray as $_idArray) {
                        $PrizePlanDelete = PrizeDistributionPlan::find($_idArray);
                        if($PrizePlanDelete !== null) {
                            $PrizePlanDelete->status = 1;
                            $PrizePlanDelete->save();
                        }
                        $PrizeRatioDelete = $this->objPrizeRatio->DeleteRatio($_idArray);
                        foreach ($PrizeRatioDelete as $d => $deleteRatio) {
                            $PrizeRatioDelete[$d]->delete();
                        }
                    }
                    $records["customMessage"] = trans('adminmsg.delete_prize');
            }
        }
        $columns = array(
            0 => 'name',
            1 => 'winner'
        );
        $order = Input::get('order');
        $search = Input::get('search');
        $records["data"] = array();
        $iTotalRecords = PrizeDistributionPlan::where('status', 0)->count();
        $iTotalFiltered = $iTotalRecords;
        $iDisplayLength = intval(Input::get('length')) <= 0 ? $iTotalRecords : intval(Input::get('length'));
        $iDisplayStart = intval(Input::get('start'));
        $sEcho = intval(Input::get('draw'));
        $records["data"] = PrizeDistributionPlan::select('*')->where('status', 0);
        if (!empty($search['value'])) {
            $val = $search['value'];
            $records["data"]->where(function($query) use ($val) {
                $query->SearchName($val);
                $query->SearchWinner($val);
            });
            // No of record after filtering
            $iTotalFiltered = $records["data"]->where(function($query) use ($val) {
                        $query->SearchName($val);
                        $query->SearchWinner($val);
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
                $edit = route('prize.edit', $_records->id);
                $records["data"][$key]['action'] = "&emsp;<a href='{$edit}' title='Edit Prize' ><span class='glyphicon glyphicon-edit'></span></a>
                                                    &emsp;<a href='javascript:;' data-id='" . $_records->id . "' class='btn-delete-ads' title='Delete Prize' ><span class='glyphicon glyphicon-trash'></span></a>";
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalFiltered;

        return Response::json($records);
    }

}
