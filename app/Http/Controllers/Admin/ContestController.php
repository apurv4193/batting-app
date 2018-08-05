<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Contest;
use App\ContestScoreImages;
use Redirect;
use Response;
use App\User;
use App\PrizeDistributionPlan;
use App\PrizeRatio;
use App\ContestType;
use App\ContestUser;
use Carbon\Carbon;
use App\UserDevice;
use App\Roster;
use App\TeamPlayers;
use App\Team;
use App\GamesPlayers;
use App\UsersUsedPower;
use App\UsersPower;
use Helpers;
use Config;
use Image;
use File;
use DB;
use Mail;
use Session;
use FFMpeg;

class ContestController extends Controller {

/**
* Create a new controller instance.
*
* @return void
*/
public function __construct() {
    $this->middleware('IsAdmininstrator');
    $this->objContest = new Contest();
    $this->contestOriginalImageUploadPath = Config::get('constant.CONTEST_ORIGINAL_IMAGE_UPLOAD_PATH');
    $this->contestThumbImageUploadPath = Config::get('constant.CONTEST_THUMB_IMAGE_UPLOAD_PATH');
    $this->contestVideoUploadPath = Config::get('constant.CONTEST_VIDEO_UPLOAD_PATH');
    $this->contestThumbImageHeight = Config::get('constant.CONTEST_THUMB_IMAGE_HEIGHT');
    $this->contestThumbImageWidth = Config::get('constant.CONTEST_THUMB_IMAGE_WIDTH');

//contest score
    $this->contestScoreOriginalImageUploadPath = Config::get('constant.CONTEST_SCORE_ORIGINAL_IMAGE_UPLOAD_PATH');
    $this->contestScoreThumbImageUploadPath = Config::get('constant.CONTEST_SCORE_THUMB_IMAGE_UPLOAD_PATH');
    $this->contestScoreThumbImageHeight = Config::get('constant.CONTEST_SCORE_THUMB_IMAGE_HEIGHT');
    $this->contestScoreThumbImageWidth = Config::get('constant.CONTEST_SCORE_THUMB_IMAGE_WIDTH');
//end

    $this->sponsorVideoUploadPath = Config::get('constant.CONTEST_SPONSOR_VIDEO_UPLOAD_PATH');

    $this->sponsorOriginalImageUploadPath = Config::get('constant.CONTEST_SPONSOR_ORIGINAL_IMAGE_UPLOAD_PATH');
    $this->sponsorThumbImageUploadPath = Config::get('constant.CONTEST_SPONSOR_THUMB_IMAGE_UPLOAD_PATH');
    $this->sponsorThumbImageHeight = Config::get('constant.CONTEST_SPONSOR_THUMB_IMAGE_HEIGHT');
    $this->sponsorThumbImageWidth = Config::get('constant.CONTEST_SPONSOR_THUMB_IMAGE_WIDTH');
}

public function getContests() {
    $or = $this->contestScoreOriginalImageUploadPath;
    return view('admin.contest-list', compact('or'));
}

public function listContestAjax() {

    $records = array();
//processing custom actions
    if (Input::get('customActionType') == 'groupAction') {

        $action = Input::get('customActionName');
        $idArray = Input::get('id');

        switch ($action) {
            case "cancel":
            DB::beginTransaction();
            try{
            foreach ($idArray as $_idArray) {
                $contest = Contest::find($_idArray);
                if($contest) {
                    $contest->fill(array_filter(['status' => Config::get('constant.CANCELLED_CONTEST_STATUS'),'cancel_by' => 'admin','cancellation_reason' => 'By admin']));
                    $contest->save();

                    $contestUsers = ContestUser::where('contest_id',$_idArray)->get();

                    foreach ($contestUsers as $k => $v) {
                        $user = User::where('id',$v->user_id)->first();
                        // add points back to user account
                        $points = $user->points + $contest->contest_fees;
                        $addUserPoints = User::where('id',$user->id)->update(['points'=>$points]);

                        $user_used_power = UsersUsedPower::where('contest_id',$_idArray)->where('user_id',$user->id)->first();
                        if( !is_null($user_used_power) ) {
                            UsersPower::where('id',$user_used_power->user_power_id)->update(['used'=>0]);
                            $user_used_power->forceDelete();
                        }

                        $user_device = UserDevice::where('user_id',$v->user_id)->get();
                        if(count($user_device) > 0) {
                            foreach ($user_device as $device) {
                                $user_detail = User::find($device->user_id);

                                if( $user_detail->notification_status == 1 ) {
                                    $data = array(
                                                'notification_status' => 2,
                                                'message' => ucfirst($contest->contest_name).' Event has been canceled due to lack of participants',
                                                'contest_id' => $contest->id,
                                                'notification_type' => 'ContestCanceled'
                                            );
                                    Helpers::pushNotificationForiPhone($device->device_token,$data);
                                }
                            }
                        }
                    }
                }
            }
            DB::commit();
        }catch(Exception $e){
                        DB::rollback();
                        Session::flash('errors', trans('adminmsg.common_error_msg'));
                    }
            $records["customMessage"] = trans('adminmsg.cancel_contest');
        }
    }

    $columns = array(
        0 => 'name',
        1 => 'type',
        2 => 'contest_name',
        3 => 'contest_fees',
        4 => 'contest_start_time',
        5 => 'contest_end_time',
        6 => 'privacy',
        7 => 'status'
    );

    $order = Input::get('order');
    $search = Input::get('search');

    $records["data"] = array();
    $iTotalRecords = Contest::count();
    $iTotalFiltered = $iTotalRecords;
    $iDisplayLength = intval(Input::get('length')) <= 0 ? $iTotalRecords : intval(Input::get('length'));
    $iDisplayStart = intval(Input::get('start'));
    $sEcho = intval(Input::get('draw'));

    $records["data"] = Contest::leftjoin('games', 'games.id', '=', 'contests.game_id')
    ->leftjoin('contest_type', 'contest_type.id', '=', 'contests.contest_type_id')
    ;

    if (!empty($search['value'])) {
        $val = $search['value'];
        $records["data"]->where(function($query) use ($val) {
            $query->SearchGameName($val);
            $query->SearchContestType($val);
            $query->SearchName($val);
            $query->SearchContestFees($val);
            $query->SearchStartTime($val);
            $query->SearchEndTime($val);
            $query->SearchPrivacy($val);
            $query->SearchStatus($val);
        });

// No of record after filtering
        $iTotalFiltered = $records["data"]->where(function($query) use ($val) {
            $query->SearchGameName($val);
            $query->SearchContestType($val);
            $query->SearchName($val);
            $query->SearchContestFees($val);
            $query->SearchStartTime($val);
            $query->SearchEndTime($val);
            $query->SearchPrivacy($val);
            $query->SearchStatus($val);
        })->count();
    }

//order by
    foreach ($order as $o) {
        $records["data"] = $records["data"]->orderBy($columns[$o['column']], $o['dir']);
    }

//limit
    $records["data"] = $records["data"]->take($iDisplayLength)->offset($iDisplayStart)->get([
        'games.name',
        'contest_type.type',
        'contests.contest_name',
        'contests.contest_fees',
        'contests.contest_start_time',
        'contests.contest_end_time',
        'contests.privacy',
        'contests.status',
        'contests.id'
    ]);

    if (!empty($records["data"])) {
        foreach ($records["data"] as $key => $_records) {
            $edit = route('contest.edit', $_records->id);

            $records["data"][$key]['action'] = '';
            if($_records->status == Config::get('constant.UPCOMING_CONTEST_STATUS')) {
                $records["data"][$key]['action'] = "&emsp;<a href='{$edit}' title='Edit Contest' ><span class='glyphicon glyphicon-edit'></span></a>
                &emsp;<a href='javascript:;' data-id='" . $_records->id . "' class='btn-cancel-contest' title='Cancel Contest' ><span class='glyphicon glyphicon-minus-sign'></span></a>";
            } else if($_records->status == Config::get('constant.LIVE_CONTEST_STATUS')) {
                $records["data"][$key]['action'] = "&emsp;<a href='javascript:;' data-id='" . $_records->id . "' class='btn-cancel-contest' title='Cancel Contest' ><span class='glyphicon glyphicon-minus-sign'></span></a>";
            }
        }
    }
    $records["draw"] = $sEcho;
    $records["recordsTotal"] = $iTotalRecords;
    $records["recordsFiltered"] = $iTotalFiltered;

    return Response::json($records);
}

public function addContest() {
    $games = Helpers::getGames();
    $contestTypes = Helpers::getContestTypes();
    $contestLevels = Helpers::getContestLevels();
    $prizeDistributionPlan = Helpers::getPrizeDistributionPlans();
    return view('admin.add-contest', compact('games', 'contestTypes', 'contestLevels', 'prizeDistributionPlan'));
}

public function saveContest(Request $request) {
    try {

        if(!$request->id || empty($request->id) || $request->id == 0 ) {
            $rule = [
                'game_id' => 'required|integer',
                'contest_name' => 'required|max:40|regex:/^(?![0-9]*$)[a-zA-Z0-9 ]+$/',
                'banner' => 'required|mimes:webm,mkv,flv,vob,ogv,ogg,wmv,asf,amv,mp4,m4p,m4v,m4v,3gp,3g2,f4v,f4p,f4a,f4b,mpeg,mpg,m2v,mpv,mpe,png,jpeg,jpg,bmp|max:20480',
                'level_id' => 'required|integer',
                'contest_type_id' => 'required|integer',
                'contest_fees' => 'required|digits_between:1,6',
                'contest_start_time' => 'required|date|date_format:Y-m-d H:i:s|after:'.Carbon::now()->addHours(3),
                'contest_end_time' => 'required|date|after:contest_start_time',
                'contest_min_participants' => 'required|integer',
                'contest_max_participants' => 'required|integer|greater_than_or_equal_field:contest_min_participants',
                'prize_distribution_plan_id' => 'required|integer',
                'sponsored_by' => 'max:255',
                'sponsored_prize' => 'nullable|max:255|regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
                'sponsored_video_link' => 'mimes:webm,mkv,flv,vob,ogv,ogg,wmv,asf,amv,mp4,m4p,m4v,m4v,3gp,3g2,f4v,f4p,f4a,f4b,mpeg,mpg,m2v,mpv,mpe|max:20480',
                'sponsored_image' => 'image|max:10240',
                'is_teamwise' => 'boolean'
            ];
        } else {
            $rule = [
                'contest_name' => 'required|max:40|regex:/^(?![0-9]*$)[a-zA-Z0-9 ]+$/',
                'contest_start_time' => 'required|date|date_format:Y-m-d H:i:s|after:'.Carbon::now()->addHours(3),
                'contest_end_time' => 'required|date|after:contest_start_time',
                'prize_distribution_plan_id' => 'required|integer',
                'contest_min_participants' => 'required|integer',
                'contest_max_participants' => 'required|integer|greater_than_or_equal_field:contest_min_participants',
                'banner' => 'mimes:webm,mkv,flv,vob,ogv,ogg,wmv,asf,amv,mp4,m4p,m4v,m4v,3gp,3g2,f4v,f4p,f4a,f4b,mpeg,mpg,m2v,mpv,mpe,png,jpeg,jpg,bmp|max:20480',
                'sponsored_by' => 'max:255',
                'sponsored_prize' => 'nullable|max:255|regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
                'sponsored_video_link' => 'mimes:webm,mkv,flv,vob,ogv,ogg,wmv,asf,amv,mp4,m4p,m4v,m4v,3gp,3g2,f4v,f4p,f4a,f4b,mpeg,mpg,m2v,mpv,mpe|max:20480',
                'sponsored_image' => 'image|max:10240',
                'is_teamwise' => 'boolean'
            ];
        }

        $this->validate(request(), $rule);

        $prizeDistributioPlan = PrizeDistributionPlan::find($request->prize_distribution_plan_id);

        if($prizeDistributioPlan === null) {
            return Redirect::back()->withInput($request->all())->withErrors([
                'Invalid input parameter.'
            ]);
        }
        if($prizeDistributioPlan->winner != '0' && $prizeDistributioPlan->winner > $request->contest_min_participants) {
            return Redirect::back()->withInput($request->all())->withErrors([
                'Please enter minimum '.$prizeDistributioPlan->winner.' as minimum participant.'
            ]);
        }

        $allContest = Contest::whereNotIn('status',['completed','pending'])->get();

        foreach ($allContest as $key => $value) {
            if( $value->contest_name == $request->contest_name && $value->contest_start_time == $request->contest_start_time && $value->contest_end_time == $request->contest_end_time && $value->game_id == $request->game_id )
            {
                return Redirect::back()->withInput($request->all())->withErrors([
                    'Contest already exist.'
                ]);
            }
        }
        $contestData = $request->all();
        if($contestData['id'] || !empty($contestData['id']) || $contestData['id'] > 0)
        {
            $allContest = Contest::whereNotIn('status',['completed','pending'])->where('id','!=',$contestData['id'])->get();

            foreach ($allContest as $key => $value) {
                if( $value->contest_name == $request->contest_name && $value->contest_start_time == $request->contest_start_time && $value->contest_end_time == $request->contest_end_time && $value->game_id == $request->game_id )
                {
                    return Redirect::back()->withInput($request->all())->withErrors([
                        'Contest already exist.'
                    ]);
                }
            }
        }

        // $contestData = $request->all();

        if(!$contestData['id'] || empty($contestData['id']) || $contestData['id'] == 0) {
            $contestData['created_by'] = User::getCurrentUser()->id;
            $contestData['updated_by'] = User::getCurrentUser()->id;
            $contestData['roster_cap_amount'] = ContestType::find($request->contest_type_id)->contest_cap_amount;
        } else {
            $contest = $this->objContest->find($contestData['id']);
            if($contest === null) {
                return Redirect::to("/admin/contests/")->with('error', 'Contest data not found!');
            }
            if($contest->status != Config::get('constant.UPCOMING_CONTEST_STATUS')) {
                return Redirect::to("/admin/contests/")->with('error', 'You can\'t update '.$contest->status.' contest!');
            }
            $contestData['updated_by'] = User::getCurrentUser()->id;
        }

        $hiddenBanner = $contestData['hidden_banner'];
        $hiddenVideo = $contestData['hidden_video'];
        $hiddenSponsorImage = $contestData['hidden_image'];

        $contestData['banner'] = $hiddenBanner;
        $contestData['sponsored_video_link'] = $hiddenVideo;
        $contestData['sponsored_image'] = $hiddenSponsorImage;

        $contestData['contest_video_link'] = (!empty($contestData['contest_video_link'])) ? Helpers::addhttp($contestData['contest_video_link']) : null;
        $contestData['sponsored_link'] = (!empty($contestData['sponsored_link'])) ? Helpers::addhttp($contestData['sponsored_link']) : null;

        if ($request->hasFile('banner')) {
            $banner = $request->file('banner');

            if (!empty($banner)) {
                $imageType = array('image/jpeg', 'image/png', 'image/jpg', 'image/jpe');
                if (in_array($banner->getMimeType(), $imageType)) {
                $fileName = 'banner_' . time() . '.' . $banner->getClientOriginalExtension();

                $originalPath = public_path($this->contestOriginalImageUploadPath . $fileName);
                $thumbPath = public_path($this->contestThumbImageUploadPath . $fileName);

                if (!file_exists(public_path($this->contestOriginalImageUploadPath))) File::makeDirectory(public_path($this->contestOriginalImageUploadPath), 0777, true, true);
                    if (!file_exists(public_path($this->contestThumbImageUploadPath))) File::makeDirectory(public_path($this->contestThumbImageUploadPath), 0777, true, true);

// created instance
                        $img = Image::make($banner->getRealPath());

                        $img->save($originalPath);
// resize the image to a height of $this->contestThumbImageHeight and constrain aspect ratio (auto width)
                        if( $img->height() > 500) {
                            $img->resize(null, $img->height(), function ($constraint) {
                                $constraint->aspectRatio();
                            })->save($thumbPath);
                        }
                        else {
                            $img->resize(null, $this->contestThumbImageHeight, function ($constraint) {
                                $constraint->aspectRatio();
                            })->save($thumbPath);
                        }

                        if ($hiddenBanner != '' && $hiddenBanner != "default.png") {
                            $originalImage = public_path($this->contestOriginalImageUploadPath . $hiddenBanner);
                            $thumbImage = public_path($this->contestThumbImageUploadPath . $hiddenBanner);
                            if (file_exists($originalImage)) {
                                File::delete($originalImage);
                            }
                            if (file_exists($thumbImage)) {
                                File::delete($thumbImage);
                            }
                        }
                        $contestData['banner'] = $fileName;
                        }
                        // Upload video
                        $videoType = array('video/mp4', 'video/mpeg', 'video/flv', 'video/mov', 'video/mkv', 'video/3gp');
                        if (in_array($banner->getMimeType(), $videoType)) {

                            $ffmpeg = FFMpeg\FFMpeg::create();

                            $videoFileName = 'banner_' . str_random(20) . '.' . $banner->getClientOriginalExtension();
                            $videoPathOriginal = public_path($this->contestVideoUploadPath . $videoFileName);

                            if (!file_exists(public_path($this->contestVideoUploadPath)))
                                File::makeDirectory(public_path($this->contestVideoUploadPath), 0777, true, true);

                            Input::file('banner')->move(public_path($this->contestVideoUploadPath), $videoFileName);

                            $video = $ffmpeg->open(public_path($this->contestVideoUploadPath . $videoFileName));
                            $videostream = $ffmpeg->getFFProbe()
                                    ->streams($videoPathOriginal)
                                    ->videos()
                                    ->first()
                                    ->get('duration');

                            $duration = ($videostream > 4 ? 3 : 1);

                            $thumbImageName = 'baaaaaaa_' . str_random(20) . '.jpg';
                            $thumbOrigialPath = public_path($this->contestOriginalImageUploadPath);
                            $thumbThumbPath = public_path($this->contestThumbImageUploadPath);

                            if (!file_exists($thumbOrigialPath))
                                File::makeDirectory($thumbOrigialPath , 0777, true, true);
                            if (!file_exists($thumbThumbPath))
                                File::makeDirectory($thumbThumbPath, 0777, true, true);

                            $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($duration))
                                    ->save($thumbOrigialPath . $thumbImageName);

                            // created instance
                            $img = Image::make($thumbOrigialPath . $thumbImageName);

                            // resize the image to a height of $this->adsThumbImageHeight and constrain aspect ratio (auto width)
                            $height = ($img->height() < 500) ? $img->height() : $this->contestThumbImageHeight;
                            $img->resize(null, $height, function ($constraint) {
                                $constraint->aspectRatio();
                            })->save($thumbThumbPath . $thumbImageName);

                            if ($hiddenBanner!= '' && $hiddenBanner != 'default.png') {

                                $videoOriginal = ($hiddenBanner != '') ? public_path($this->contestVideoUploadPath . $hiddenBanner) : '';
                                $videoThumbImage = public_path($this->contestThumbImageUploadPath . $hiddenBanner);
                                $videoOriginalImage = public_path($this->contestOriginalImageUploadPath . $hiddenBanner);

                                // Unlink video if exists
                                if (!empty($videoOriginal) && file_exists($videoOriginal)) {
                                    File::delete($videoOriginal);
                                }

                                // Unlink thumb image if exists
                                if (file_exists($videoThumbImage)) {
                                    File::delete($videoThumbImage);
                                }

                                // Unlink image if exists
                                if (file_exists($videoOriginalImage)) {
                                    File::delete($videoOriginalImage);
                                }
                            }
                            $contestData['banner'] = $videoFileName;
                            $contestData['video_thumb'] = $thumbImageName;
                        }
                    }
                }

                if ($request->hasFile('sponsored_video_link')) {
                    $sponsorVideo = $request->file('sponsored_video_link');

                    if (!empty($sponsorVideo)) {
                        $fileName = time() . '.' . $sponsorVideo->getClientOriginalExtension();

                        if (!file_exists(public_path($this->sponsorVideoUploadPath))) File::makeDirectory(public_path($this->sponsorVideoUploadPath), 0777, true, true);

                            $sponsorVideo->move(public_path($this->sponsorVideoUploadPath), $fileName);

                            if ($hiddenVideo != '') {
                                $oldVideoFile = public_path($this->sponsorVideoUploadPath . $hiddenVideo);
                                if (file_exists($oldVideoFile)) {
                                    File::delete($oldVideoFile);
                                }
                            }
                            $contestData['sponsored_video_link'] = $fileName;
                        }
                    }

                    if ($request->hasFile('sponsored_image')) {
                        $sponsored_image = $request->file('sponsored_image');

                        if (!empty($sponsored_image)) {
                            $fileName = 'sponsored_image_' . time() . '.' . $sponsored_image->getClientOriginalExtension();

                            $originalPath = public_path($this->sponsorOriginalImageUploadPath . $fileName);
                            $thumbPath = public_path($this->sponsorThumbImageUploadPath . $fileName);

                            if (!file_exists(public_path($this->sponsorOriginalImageUploadPath))) File::makeDirectory(public_path($this->sponsorOriginalImageUploadPath), 0777, true, true);
                                if (!file_exists(public_path($this->sponsorThumbImageUploadPath))) File::makeDirectory(public_path($this->sponsorThumbImageUploadPath), 0777, true, true);

// created instance
                                    $img = Image::make($sponsored_image->getRealPath());

                                    $img->save($originalPath);
// resize the image to a height of $this->contestThumbImageHeight and constrain aspect ratio (auto width)
                                    if ( $img->height() < 500 ) {
                                        $img->resize(null, $img->height(), function ($constraint) {
                                            $constraint->aspectRatio();
                                        })->save($thumbPath);
                                    }
                                    else {
                                        $img->resize(null, $this->sponsorThumbImageHeight, function ($constraint) {
                                            $constraint->aspectRatio();
                                        })->save($thumbPath);
                                    }

                                    if ($hiddenSponsorImage != '' && $hiddenSponsorImage != "default.png") {
                                        $originalImage = public_path($this->sponsorOriginalImageUploadPath . $hiddenSponsorImage);
                                        $thumbImage = public_path($this->sponsorThumbImageUploadPath . $hiddenSponsorImage);
                                        if (file_exists($originalImage)) {
                                            File::delete($originalImage);
                                        }
                                        if (file_exists($thumbImage)) {
                                            File::delete($thumbImage);
                                        }
                                    }
                                    $contestData['sponsored_image'] = $fileName;
                                }
                            }
                            $contestData['is_teamwise'] = (isset($contestData['is_teamwise']) && $contestData['is_teamwise'] != '')?$contestData['is_teamwise']:0;

// Create contest
                            if(!$contestData['id'] || empty($contestData['id']) || $contestData['id'] == 0) {
                                $contest = $this->objContest->create(array_only($contestData, ['game_id', 'contest_type_id', 'level_id', 'contest_name', 'contest_fees', 'roster_cap_amount', 'contest_start_time', 'contest_end_time', 'prize_distribution_plan_id', 'contest_min_participants', 'contest_max_participants', 'banner', 'video_thumb', 'contest_video_link', 'sponsored_by', 'sponsored_video_link', 'sponsored_link', 'sponsored_image', 'created_by', 'updated_by', 'is_teamwise', 'sponsored_prize']));
                                return Redirect::to("/admin/contests/")->with('success', trans('adminmsg.contest_created_success'));
                            } else {
// Update contest
                                $contest->fill(array_filter(array_only($contestData, ['contest_name', 'contest_start_time', 'contest_end_time', 'prize_distribution_plan_id', 'contest_min_participants', 'contest_max_participants', 'banner', 'video_thumb', 'contest_video_link', 'updated_by', 'sponsored_by', 'sponsored_video_link', 'sponsored_link', 'sponsored_image', 'sponsored_prize'])));
                                $contest->save();
                                return Redirect::to("/admin/contests/")->with('success', trans('adminmsg.contest_updated_success'));
                            }
                        } catch (Exception $e) {
                            return Redirect::to("/admin/contests/")->with('error', trans('adminmsg.common_error_msg'));
                        }
                    }

                    public function editContest($id) {
                        $contest = $this->objContest->find($id);
                        $games = Helpers::getGames();
                        $contestTypes = Helpers::getContestTypes();
                        $contestLevels = Helpers::getContestLevels();
                        $prizeDistributionPlan = Helpers::getPrizeDistributionPlans();
                        $contestThumbPath = $this->contestThumbImageUploadPath;

                        if (!$contest) {
                            return Redirect::to("/admin/contests/")->with('error', trans('adminmsg.contest_not_exist'));
                        }

                        return view('admin.add-contest', compact('contest', 'games', 'contestTypes', 'contestLevels', 'prizeDistributionPlan', 'contestThumbPath'));
                    }

                    public function editContestScore($id) {
                        $contest = $this->objContest->find($id);
                        if ($contest->status != 'pending') {
                            return Redirect::to("/admin/contest_score/")->with('error', trans('adminmsg.contest_not_exist'));
                        }
                        $contest_user = Contest::leftjoin('games', 'games.id', '=', 'contests.game_id')->leftjoin('contest_type', 'contest_type.id', '=', 'contests.contest_type_id')->where('contests.id',$contest->id)->select('games.name','contest_type.type','contests.contest_fees','contests.contest_name')->first();

                        $contest_user_data = Contest::leftjoin('rosters', 'rosters.contest_id', '=', 'contests.id')->leftjoin('players', 'rosters.player_id', '=', 'players.id')->leftjoin('games_players', 'rosters.player_id', '=', 'games_players.player_id')->where('contests.id',$contest->id)->where('rosters.contest_id',$contest->id)->distinct()->get(['contests.contest_name', 'rosters.score', 'rosters.player_id', 'players.name']);


                        if (!$contest_user) {
                            return Redirect::to("/admin/contest_score/")->with('error', trans('adminmsg.contest_data_not_exist'));
                        }

                        return view('admin.edit-contest-score', compact('contest_user', 'contest', 'contest_user_data'));
                    }

                    public function saveContestScore(Request $request) {
                        DB::beginTransaction();
                        try{
                        $data['player_id'] = $request->player_id;
                        $data['score'] = $request->score;
                        $data['contest_id'] = $request->contest_id;

                        $contest = Contest::find($request->contest_id);

                        foreach ($data['score'] as $key => $value)
                        {
                            Roster::where('player_id',$key)->where('contest_id',$data['contest_id'])->update(['score'=>$value]);

                        }

                        $score_data = Roster::where('contest_id',$data['contest_id'])->groupBy('rosters.user_id','rosters.contest_id')->orderBy('rosters.score','desc')->get([DB::raw("SUM(rosters.score) as score"),'rosters.contest_id','rosters.user_id']);

                        foreach ($score_data as $key => $value) {
                            ContestUser::where('contest_id',$value->contest_id)->where('user_id',$value->user_id)->update(['score'=>$value->score]);

                            $user_used_power = UsersUsedPower::where('contest_id',$value->contest_id)->where('user_id',$value->user_id)->first();

                            if( $user_used_power != '' && !is_null($user_used_power) ) {
                                $contest_score = ContestUser::where('contest_id',$value->contest_id)->where('user_id',$value->user_id)->first();

                                $newScore = $contest_score->score + $user_used_power->points;

                                ContestUser::where('contest_id',$value->contest_id)->where('user_id',$value->user_id)->update(['score'=>$newScore]);}
                        }
                        DB::commit();

                            Session::flash('success', trans('adminmsg.contest_score_updated_success'));
                            return Redirect::to('/admin/edit-contest-score/'.$request->contest_id);
                    }catch(Exception $e){
                        DB::rollback();
                        Session::flash('errors', trans('adminmsg.common_error_msg'));
                    }
                }
                    public function getContestScore()
                    {
                        return view('admin.contest-score-list');
                    }

                    public function listContestScoreAjax() {
                        $records = array();

                        $columns = array(
                            0 => 'contest_name'
//1 => 'score'
                        );

                        $order = Input::get('order');
                        $search = Input::get('search');

                        $records["data"] = array();
                        $iTotalRecords = Contest::where('status','pending')->count();
                        $iTotalFiltered = $iTotalRecords;
                        $iDisplayLength = intval(Input::get('length')) <= 0 ? $iTotalRecords : intval(Input::get('length'));
                        $iDisplayStart = intval(Input::get('start'));
                        $sEcho = intval(Input::get('draw'));

                        $records["data"] = ContestUser::leftjoin("contests", "contests.id", '=', "contest_user.contest_id")->where('contests.status',Config::get('constant.PENDING_CONTEST_STATUS'));

                        if (!empty($search['value'])) {
                            $val = $search['value'];
                            $records["data"]->where(function($query) use ($val) {
                                $query->SearchName($val);
                                $query->SearchStartTime($val);
                                $query->SearchEndTime($val);
                            });

// No of record after filtering
                            $iTotalFiltered = $records["data"]->where(function($query) use ($val) {
                                $query->SearchName($val);
                                $query->SearchStartTime($val);
                                $query->SearchEndTime($val);
                            })->count();
                        }

//order by
                        foreach ($order as $o) {
                            $records["data"] = $records["data"]->orderBy($columns[$o['column']], $o['dir']);
                        }

//limit
                        $records["data"] = $records["data"]->groupBy('contests.contest_name', 'contest_user.contest_id','contests.status','contests.contest_start_time','contests.contest_end_time','contests.contest_video_link','contests.created_by')->take($iDisplayLength)->offset($iDisplayStart)->get([
                            'contests.contest_name',
                            'contest_user.contest_id',
                            'contests.status',
                            'contests.contest_start_time',
                            'contests.contest_end_time',
                            'contests.contest_video_link',
                            'contests.created_by',
                            DB::raw("SUM(contest_user.score) as score_sum")
                        ]);

                        if (!empty($records["data"])) {
                            foreach ($records["data"] as $key => $_records) {
                                $edit = route('contest_score.edit', $_records->contest_id);

                                if(isset($_records->contest_video_link) && !empty($_records->contest_video_link)){
                                    $link = (!empty($_records->contest_video_link)) ? Helpers::addhttp($_records->contest_video_link) : null;
// $link = $_records->contest_video_link;
                                }else{
                                    $link = "javascript:void(0);";
                                }
                                $records["data"][$key]['action'] = '';

                                $records["data"][$key]['action'] = "&emsp;<a href='".$link."' title='View Video' target='_blank'><span class='glyphicon glyphicon-facetime-video'></span></a>&emsp;<a href='{$edit}' title='Edit Contest Score' ><span class='glyphicon glyphicon-edit'></span></a>";


                                $records["data"][$key]['score_sum'] = ($_records->score_sum == 0.00)?"Not Scored":"Scored";
                            }
                        }
                        $records["draw"] = $sEcho;
                        $records["recordsTotal"] = $iTotalRecords;
                        $records["recordsFiltered"] = $iTotalFiltered;
                        return Response::json($records);
                    }

// Get contest images

                    public function getContestImages() {
                        $contestId = Input::get('contestId');
                        $imageList['data'] = Helpers::getContestImages($contestId);
                        $imageList['originalPath'] = url($this->contestScoreOriginalImageUploadPath);
                        $imageList['thumbPath'] = url($this->contestScoreThumbImageUploadPath);
                        $imageList['height'] = $this->contestScoreThumbImageHeight;
                        $imageList['width'] = $this->contestScoreThumbImageWidth;
                        return json_encode($imageList);
                    }

                    public function performCustomActionAjax() {
                        $records = array();

//processing custom actions
                        if (Input::get('ajaxParams')['customActionType'] == 'groupAction') {

                            $action = Input::get('ajaxParams')['customActionName'];
                            $idArray = Input::get('ajaxParams')['id'];
                            $requestData = (isset(Input::get('ajaxParams')['requestData']))?Input::get('ajaxParams')['requestData']:'';
                            $contest_id = (isset(Input::get('ajaxParams')['contest_id']))?Input::get('ajaxParams')['contest_id']:'';
                            switch ($action) {
                                case "cancel":

                                 DB::beginTransaction();
                                  try{
                                    foreach ($idArray as $_idArray) {
                                        $contest = Contest::find($_idArray);
                                        if($contest) {
                                            $contest->fill(array_filter(['status' => Config::get('constant.CANCELLED_CONTEST_STATUS')]));
                                            $contest->save();
                                            Session::flash('success', trans('adminmsg.cancel_contest'));
                                            $records["customMessage"] = "cancel";
                                            // trans('adminmsg.cancel_contest');
                                            DB::commit();
                                        }
                                    }
                                }catch(Exception $e){
                                        DB::rollback();
                                        Session::flash('errors', trans('adminmsg.common_error_msg') );
                                        $records["customMessage"] = "errors";

                                    }
                                    break;


                                case "complete":
                                    DB::beginTransaction();
                                    try{
                                        $contest = Contest::find($idArray);
                                        if($contest) {
                                            $score = ContestUser::where('contest_id',$idArray)->orderBy('score','desc')->groupBy('score')->get(['score']);

                                            $count = 1;
                                            foreach ($score as $key => $value) {
                                                ContestUser::where('score',$value->score)->where('contest_id',$idArray)->update(['rank'=>$count]);
                                                $count++;
                                            }

                                            $prize = PrizeDistributionPlan::where('prize_distribution_plan.id',$contest->prize_distribution_plan_id)->first();

                                            $rank = ContestUser::leftjoin('users','users.id','=','contest_user.user_id')
                                                ->where('contest_user.contest_id',$idArray)
                                                ->orderBy('contest_user.rank','asc')
                                                ->orderBy('users.username','asc')
                                                //->groupBy('contest_user.rank')
                                                //->limit($prize->winner)
                                                ->get(['contest_user.user_id','contest_user.rank','contest_user.team_id']);

                                            $rank_id = array_slice(array_intersect_key($rank->toArray(), array_unique(array_column($rank->toArray(), 'rank'))),0,$prize->winner);

                                            if( count($rank_id) != $prize->winner ){

                                                $rank_id = array_slice($rank->toArray(), 0,$prize->winner);
                                            }

                                            foreach ($rank_id as $i => $v) {
                                                unset($rank_id[$i]['rank']);
                                                unset($rank_id[$i]['team_id']);
                                            }

                                            if( $contest->is_teamwise == 1 ){

                                                $team_win = ContestUser::where('contest_id',$idArray)->whereIn('user_id',$rank_id)->get(['team_id']);

                                                $team_loss = ContestUser::where('contest_id',$idArray)->whereNotIn('user_id',$rank_id)->get(['team_id']);

                                                foreach ($team_win as $key => $value) {
                                                    Team::where('id',$value->team_id)->where('game_id',$contest->game_id)->increment('win',1);
                                                }

                                                foreach ($team_loss as $key => $value) {
                                                    Team::where('id',$value->team_id)->where('game_id',$contest->game_id)->increment('loss',1);
                                                }
                                            }

                                            $players_win = Roster::where('contest_id',$idArray)->distinct()->whereIn('user_id',$rank_id)->get(['player_id']);

                                            $players_loss = Roster::where('contest_id',$idArray)->distinct()->whereNotIn('user_id',$rank_id)->get(['player_id']);


                                            //increment in player win
                                            foreach ($players_win as $key => $value) {

                                                GamesPlayers::where('game_id',$contest->game_id)->where('player_id',$value->player_id)->increment('win',1);
                                            }

                                            //increment in player loss
                                            foreach ($players_loss as $key => $value) {

                                                GamesPlayers::where('game_id',$contest->game_id)->where('player_id',$value->player_id)->increment('loss',1);
                                            }

                                            //prize distribution
                                            $prize_distribution = PrizeRatio::where('prize_distribution_plan_id',$contest->prize_distribution_plan_id)->get();

                                            $total_prize = ($contest->sponsored_prize == '')?$contest->prize:$contest->sponsored_prize;


                                            //$new_prize = ($total_prize * 2)/100;
                                            //$real_prize = $total_prize - $new_prize;

                                            $j = 0;
                                            foreach ($rank_id as $key => $value) {
                                                $points_win = ($total_prize * $prize_distribution[$j]['ratio'])/100;

                                                $user_data = User::where('id',$value)->first();

                                                if( !is_null($user_data) ) {
                                                    $points = $user_data->points + floor(($total_prize * $prize_distribution[$j]['ratio'])/100);
                                                }

                                                User::where('id',$value)->update(['points'=>$points]);

                                                ContestUser::where('contest_id',$contest->id)->where('user_id',$value)->update(['is_win'=>1,'points_win'=>$points_win]);
                                                $j++;

                                            }

                                            $contest_score_images = ContestScoreImages::where('contest_id',$contest->id)->where('status',0)->get();

                                            foreach ($contest_score_images as $key => $value) {
                                                ContestScoreImages::where('id',$value->id)->update('status',1);
                                            }

                                            $contest->fill(array_filter(['status' => Config::get('constant.COMPLETED_CONTEST_STATUS'),'result_declare_status'=>1,'result_declare_date'=>Carbon::now()]));
                                            $contest->image_approved = 1;
                                            $contest->image_uploaded = 1;
                                            $contest->save();
                                            Session::flash('success', trans('adminmsg.complete_contest'));
                                            $records["customMessage"] = "complete";
                                             DB::commit();
                                            // trans('adminmsg.complete_contest');
                                        }
                                    }catch(Exception $e){
                                        DB::rollback();
                                        Session::flash('errors', trans('adminmsg.common_error_msg'));
                                        $records["customMessage"] = "errors";
                                    }
                                    break;

                                case "sendEmail":
                                    DB::beginTransaction();
                                    try{
                                        $contest = Contest::find($idArray);

                                        $userData = User::find($contest->created_by);

                                        if(!is_null($userData)) {
                                            $email = $userData->email;

                                            $data = [
                                                'message' => $requestData,
                                                'username' => $userData->username
                                            ];
                                            Mail::send('emails.imageReuploadMail', $data, function($message) use($email) {
                                                $message->to($email)->subject('Image Re-upload');
                                            });
                                            Session::flash('success',trans('adminmsg.reupload_request') );
                                            $records["customMessage"] = "sendEmail";
                                            DB::commit();
                                        }
                                    }catch(Exception $e){
                                        DB::rollback();
                                        Session::flash('errors', trans('adminmsg.common_error_msg'));
                                        $records["customMessage"] = "errors";
                                    }
                                    break;

                                case "delete":
                                    DB::beginTransaction();
                                    try{
                                        foreach ($idArray as $_idArray)
                                        {
                                            $imageData = ContestScoreImages::where('id', $_idArray)->update(['status' => '2']);

                                        }
                                        if($imageData == 1) {

                                            $contest = Contest::find($contest_id);

                                            $userData = User::find($contest->created_by);

                                            if(!is_null($userData)) {
                                                $email = $userData->email;

                                                $data = [
                                                    'url' => $requestData,
                                                    'username' => $userData->username
                                                ];
                                                Mail::send('emails.imageReuploadMail', $data, function($message) use($email) {
                                                    $message->to($email)->subject('Image Re-upload');
                                                });

                                            }
                                            $user_device = UserDevice::where('user_id',$contest->created_by)->get();

                                            if(count($user_device) > 0) {
                                                foreach ($user_device as $device) {

                                                $user_detail = User::find($contest->created_by);

                                                if( $user_detail->notification_status == 1 ) {
                                                    $data = array(
                                                                'notification_status' => 2,
                                                                'message' => $requestData,
                                                                'contest_id' => $contest->id,
                                                                'notification_type' => 'ScoringImageRejected'
                                                            );
                                                    Helpers::pushNotificationForiPhone($device->device_token,$data);
                                                    }
                                                }
                                            }
                                            DB::commit();
                                            $records["customMessage"] = "delete";
                                        }
                                    }
                                    catch(Exception $e){
                                        DB::rollback();
                                        $records["customMessage"] = "errors";
                                    }

                                    // trans('adminmsg.image_rejected');

                                break;
                            }
                        }
                        return Response::json($records);
                    }
                }
