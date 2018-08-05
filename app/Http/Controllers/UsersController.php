<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\User;
use App\Contest;
use App\ContestUser;
use App\UsersUsedPower;
use App\UsersPower;
use App\UserDevice;
use Redirect;
use Illuminate\Validation\Rule;
use Image;
use Config;
use File;
use Response;
use DB;
use Helpers;

class UsersController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('IsAdmininstrator');
        $this->userOriginalImageUploadPath = Config::get('constant.USER_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->userThumbImageUploadPath = Config::get('constant.USER_THUMB_IMAGE_UPLOAD_PATH');
        $this->userThumbImageHeight = Config::get('constant.USER_THUMB_IMAGE_HEIGHT');
        $this->userThumbImageWidth = Config::get('constant.USER_THUMB_IMAGE_WIDTH');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function addUser() {
        $uploadUserThumbPath = $this->userThumbImageUploadPath;
        return view('admin.add-user', compact('uploadUserThumbPath'));
    }

    /* save user in data */

    public function saveUser() {
        
        $this->validate(request(), [
            'user_pic' => 'image|max:10240',
            'username' => ['required', 'max:50', 'regex:/^[a-zA-Z0-9-_\.\/]+$/', Rule::unique('users', 'username')->ignore(Input::get('id'))],
            'email' => ['required', 'email', 'max:40'],
            'phone' => 'required|max:15',
            'dob' => 'required|date|date_format:Y-m-d|before:tomorrow',
            'gender' => 'required'
        ]);
        
        $user = User::find(Input::get('id'));
        
        if(!$user) {
            return Redirect::to("/admin/users/")->with('error', trans('adminmsg.user_not_exist'));
        }
        
        $postData = Input::all();
        //user same email check
        $userData = User::where('status',0)->where('id','!=',Input::get('id'))->get();
        foreach ($userData as $key => $value) {
            
            if( $postData['email'] == $value->email ) {
                return Redirect::to("/admin/users/")->with('error', trans('adminmsg.email_exist'));
            }
        }

        $hiddenProfile = Input::get('hidden_profile');
        $postData['user_pic'] = $hiddenProfile;
        if (Input::file()) {
            $file = Input::file('user_pic');

            if (!empty($file)) {
                $fileName = str_random(20). '.' . $file->getClientOriginalExtension();
                $pathOriginal = public_path($this->userOriginalImageUploadPath . $fileName);
                $pathThumb = public_path($this->userThumbImageUploadPath . $fileName);
                
                if (!file_exists(public_path($this->userOriginalImageUploadPath))) File::makeDirectory(public_path($this->userOriginalImageUploadPath), 0777, true, true);
                if (!file_exists(public_path($this->userThumbImageUploadPath))) File::makeDirectory(public_path($this->userThumbImageUploadPath), 0777, true, true);

                Image::make($file->getRealPath())->save($pathOriginal);
                Image::make($file->getRealPath())->resize($this->userThumbImageWidth, $this->userThumbImageHeight)->save($pathThumb);

                if ($hiddenProfile != '' && $hiddenProfile != "default.png") {
                    $imageOriginal = public_path($this->userOriginalImageUploadPath . $hiddenProfile);
                    $imageThumb = public_path($this->userThumbImageUploadPath . $hiddenProfile);
                    if (file_exists($imageOriginal) && $hiddenProfile != '') {
                        File::delete($imageOriginal);
                    }
                    if (file_exists($imageThumb) && $hiddenProfile != '') {
                        File::delete($imageThumb);
                    }
                }
                $postData['user_pic'] = $fileName;
            }
        }

        if (isset($postData['id']) && $postData['id'] > 0) {
            $user->name = $postData['name'];
            $user->username = $postData['username'];
            $user->email = $postData['email'];
            $user->phone = $postData['phone'];
            $user->dob = $postData['dob'];
            $user->user_pic = $postData['user_pic'];
            $user->gender = $postData['gender'];
            $user->save();
            return Redirect::to("/admin/users/")->with('success', trans('adminmsg.user_updated_success'));
        } else {
            $postData['password'] = bcrypt($postData['password']);
            $user::create($postData);
            return Redirect::to("/admin/users/")->with('success', trans('adminmsg.user_created_success'));  
        }
    }

    public function getUser() {
        return view('admin.user-list');
    }

    /**
     * [editUser Edit user]
     * @param  [integer]    [user's id]
     */
    public function editUser($id) {
        $user = User::find($id);
        
        if(!$user) {
            return Redirect::to("/admin/users/")->with('error', trans('adminmsg.user_not_exist'));
        }
        
        if($user->is_admin == Config::get('constant.ADMIN_USER_FLAG')) {
            return Redirect::to("/admin/users/")->with('error', trans('adminmsg.not_authorized'));
        }
        $uploadUserThumbPath = $this->userThumbImageUploadPath;
        return view('admin.add-user', compact('user', 'uploadUserThumbPath'));
    }

    /**
     * [listUserAjax List Users]
     * @param  [type]       [description]
     * @return [json]       [list of users]
     */
    public function listUserAjax() {
        $records = array();
        //processing custom actions
        if (Input::get('customActionType') == 'groupAction') {

            $action = Input::get('customActionName');
            $idArray = Input::get('id');

            switch ($action) {
                case "delete":
                    DB::beginTransaction();
                    try {
                        foreach ($idArray as $_idArray) {
                            $user = User::find($_idArray);
                            if(!$user || $user->is_admin == Config::get('constant.ADMIN_USER_FLAG')) {
                                continue;
                            }
                            if($user->user_pic != '') {
                                $imageOriginal = public_path($this->userOriginalImageUploadPath . $user->user_pic);
                                $imageThumb = public_path($this->userThumbImageUploadPath . $user->user_pic);
                                if (file_exists($imageOriginal)) {
                                    File::delete($imageOriginal);
                                }
                                if (file_exists($imageThumb)) {
                                    File::delete($imageThumb);
                                }
                            }
                            $user->status = 1;
                            $user->save();

                            //contest data delete
                            $contestDelete = Contest::where('created_by', $_idArray)->where('status','upcoming')->get();

                            if( !is_null($contestDelete) ) {
                                
                                foreach ($contestDelete as $key => $value) {
                                    $value->status = "cancelled";
                                    $value->cancellation_reason = "Cancel by admin because user is deleted.";
                                    $value->cancel_by = "admin";
                                    $value->save();

                                    $contestUser = ContestUser::where('contest_id',$value->id)->get();
                                    foreach ($contestUser as $user) {

                                        $userDetails = User::where('id', $user->user_id)->first();
                                        // add points back to user account
                                        if($user->is_paid == 1) {

                                            $points = $userDetails->points + $value->contest_fees;
                                            $addUserPoints = User::where('id', $userDetails->id)->update(['points' => $points]);
                                            
                                        }

                                        $user_used_power = UsersUsedPower::where('contest_id', $value->id)->where('user_id', $userDetails->id)->first();

                                        if (!is_null($user_used_power)) {
                                            UsersPower::where('id', $user_used_power->user_power_id)->update(['used' => 0]);
                                            $user_used_power->forceDelete();
                                        }

                                        $user_device = UserDevice::where('user_id', $user->user_id)->get();
                                        if (count($user_device) > 0) {
                                            foreach ($user_device as $device) {
                                                $user_detail = User::find($device->user_id);

                                                if ($user_detail->notification_status == 1) {
                                                    $data = array(
                                                        'notification_status' => 2,
                                                        'message' => ucfirst($value->contest_name) . ' Event has been cancelled because user deleted by admin.',
                                                        'contest_id' => $value->id,
                                                        'notification_type' => 'ContestCanceled'
                                                    );
                                                    Helpers::pushNotificationForiPhone($device->device_token, $data);
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            DB::commit();
                            // $user->delete();
                        }
                        
                    }
                    catch(Exception $e){
                        DB::rollback();
                        Session::flash('errors', trans('adminmsg.common_error_msg'));
                    }
                    $records["customMessage"] = trans('adminmsg.delete_user');   
                }
            }
    
        
        $columns = array( 
            0 =>'name', 
            1 =>'email',
            2=> 'phone',
        );
        
        $order = Input::get('order');
        $search = Input::get('search');
        $records["data"] = array();
        
        //getting records for the users table
        $iTotalRecords = User::where('is_admin', Config::get('constant.NORMAL_USER_FLAG'))->where('status', 0)->count();
        $iTotalFiltered = $iTotalRecords;
        $iDisplayLength = intval(Input::get('length')) <= 0 ? $iTotalRecords : intval(Input::get('length'));
        $iDisplayStart = intval(Input::get('start'));
        $sEcho = intval(Input::get('draw'));
        
        $records["data"] = User::where('is_admin', Config::get('constant.NORMAL_USER_FLAG'))->where('status', 0);
        
        if (!empty($search['value'])) {
            $val = $search['value'];
            $records["data"]->where(function($query) use ($val) {
                $query->SearchName($val)
                    ->SearchEmail($val)
                    ->SearchPhone($val);
            });
            
            // No of record after filtering
            $iTotalFiltered = $records["data"]->where(function($query) use ($val) {
                                $query->SearchName($val)
                                    ->SearchEmail($val)
                                    ->SearchPhone($val);
            })->count();
        }
        
        //order by
        foreach ($order as $o) {
            $records["data"] = $records["data"]->orderBy($columns[$o['column']], $o['dir']);
        }

        //limit
        $records["data"] = $records["data"]->take($iDisplayLength)->offset($iDisplayStart)->get();

        if(!empty($records["data"])) {
            foreach ($records["data"] as $key => $_records) {
                $edit =  route('user.edit', $_records->id);

                $records["data"][$key]['user_pic'] = ($_records->user_pic != '' && File::exists(public_path($this->userThumbImageUploadPath . $_records->user_pic)) ? '<img src="'.url($this->userThumbImageUploadPath.$_records->user_pic).'" alt="{{$_records->user_pic}}"  height="50" width="50">' : '<img src="'.asset('/images/default.png').'" class="user-image" alt="Default Image" height="50" width="50">');
                $records["data"][$key]['action'] = "&emsp;<a href='{$edit}' title='Edit User' ><span class='glyphicon glyphicon-edit'></span></a>
                                                    &emsp;<a href='javascript:;' data-id='". $_records->id ."' class='btn-delete-user' title='Delete User' ><span class='glyphicon glyphicon-trash'></span></a>";
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalFiltered;
        
        return Response::json($records);
    }

}
