<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\UserDevice;
use Illuminate\Validation\Rule;
use JWTAuth;
use JWTAuthException;
use DB;
use Validator;
use Config;
use Input;
use Image;
use File;
use Mail;
use Carbon\Carbon;

class UsersController extends Controller {

    private $user;

    public function __construct(User $user) {
        $this->user = $user;
        $this->userThumbImageUploadPath = Config::get('constant.USER_THUMB_IMAGE_UPLOAD_PATH');
        $this->userOriginalImageUploadPath = Config::get('constant.USER_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->userThumbImageHeight = Config::get('constant.USER_THUMB_IMAGE_HEIGHT');
        $this->userThumbImageWidth = Config::get('constant.USER_THUMB_IMAGE_WIDTH');
    }

    /**
     * Register a new user.
     *
     * @param Request $request The current request
     * @return \App\User A new \App\User object
     * @throws Exception If there was an error
     * @see \App\User
     * @Post("/")
     * @Parameters({
     *     @Parameter("username", description="The username of the user", type="string"),
     *     @Parameter("email", description="A valid email address", type="string"),
     *     @Parameter("phone", description="A phone number", type="string"),
     *     @Parameter("device_token", description="Device token", type="string"),
     *     @Parameter("dob", description="Date of birth of user", type="date"),
     *     @Parameter("gender", description="User gender", type="integer"),
     *     @Parameter("zipcode", description="Valid US zipcode", type="string"),
     *     @Parameter("password", description="A valid password for the user. Minimum 8 characters.", type="string")
     *     @Parameter("social_type", description="0:App, 1"Gmail, 2:Facebook", type="integer")
     *     @Parameter("social_id", description="Social Id", type="string")
     * })
     * @Transaction({
     *     @Request( {"username": "vandit.kotadiya","email": "vandit.inexture@gmail.com","device_token":"ajjh","phone": "+11234567890","dob": "1993-06-19","gender": "1","zipcode": "90210","password": "12345678"} ),
     *     @Response( {"status": "1","message": "User created successfully.","data": {"userDetail": {"username": "vandit.kotadiya","email": "vandit.kotadiya@inexture.in","phone": "+11234567890","dob": "1993-06-19","gender": "1","zipcode": "90210","latitude": 34.1030032,"longitude": -118.4104684,"city": "Beverly Hills","state": "California","country": "United States","updated_at": "2017-10-31 10:22:20","created_at": "2017-10-31 10:22:20","id": 6},"loginToken": {"token": "ASDFGHe678"}}} ),
     *     @Response( {"status": "0",'message': 'Error registering user.','code' => $e->getStatusCode()} )
     *     @Response( {"status": "0",'message': 'Invalid credential.','code' => 422} )
     *     @Response( {"status": "0",'message': 'Failed to create token.','code' => 500} )
     * })
     */
    public function register(Request $request) {

        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                        'username' => ['required', 'max:50', 'regex:/^[a-zA-Z0-9-_\.\/]+$/', Rule::unique('users', 'username')->ignore($request->id)],
                        'email' => ['required', 'email', 'max:40'],
                        'phone' => 'required',
                        // 'device_token' => 'required',
                        'dob' => 'required|date|date_format:Y-m-d|before:'. Carbon::now()->subYears(Config::get('constant.MINIMUM_AGE')),
                        'gender' => 'required',
                        'zipcode' => ['required', 'regex:/^\d{5}(?:[-\s]\d{4})?$/'], // US Zip code validation regex
                        'password' => 'required|min:8|max:20'
            ]);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => $validator->messages()->all()[0],
                            'code' => '20100'
                ]);
            }

            //user same email check
            $userData = User::where('status',0)->where('id','!=',$request->id)->get();
            foreach ($userData as $key => $value) {
                
                if( $request->email == $value->email ) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'User with same email already exist.',
                                'code' => 400
                    ]);
                }
            }

            $locationDetail = $this->getLocationFromZipcode($request->zipcode);

            if ($locationDetail['status'] == '0' || ($locationDetail['status'] == '1' && empty($locationDetail['data']['results']))) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Error while getting location. Please enter valid zipcode.',
                            'code' => (isset($locationDetail['code']) ? $locationDetail['code'] : 404)
                ]);
            }

            if (isset($request->social_id) && $request->social_id != '' && isset($request->social_type) && $request->social_type != '') {
                $data = $request->only('username', 'email', 'phone', 'dob', 'gender', 'zipcode', 'password', 'social_id', 'social_type');
            } else {
                $data = $request->only('username', 'email', 'phone', 'dob', 'gender', 'zipcode', 'password');
            }

            // Users location detail
            $data['latitude'] = $locationDetail['data']['results'][0]['geometry']['location']['lat'];
            $data['longitude'] = $locationDetail['data']['results'][0]['geometry']['location']['lng'];
            $data['city'] = $locationDetail['data']['results'][0]['address_components'][1]['long_name'];
            $data['state'] = $locationDetail['data']['results'][0]['address_components'][3]['long_name'];
            $data['country'] = $locationDetail['data']['results'][0]['address_components'][4]['long_name'];

            $user = $this->user->create($data);

            // Added device token
            $request->device_token = ($request->device_token) ? $request->device_token : '';
            $device['device_token'] = $request->device_token;
            $device['user_id'] = $user->id;
            UserDevice::create($device);
            
            // Generate authorization token
            $credentials = $request->only('email', 'password');

            $token = null;
            try {
                // Get token with email and password
                if (!$token = JWTAuth::attempt($credentials)) {

                    $credentials = [
                        'username' => $request->email,
                        'password' => $request->password
                    ];

                    // Get token with username and password
                    if (!$token = JWTAuth::attempt($credentials)) {
                        DB::rollback();
                        return response()->json([
                                    'status' => '0',
                                    'message' => 'Invalid credential.',
                                    'code' => 422
                        ]);
                    }
                }
            } catch (JWTAuthException $e) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Failed to create token.',
                            'code' => 500
                ]);
            }
            DB::commit();
            //create account in applozic

            $postData = array('userId' => $request->user()->id, 'email' => $request->user()->email, 'password' => '12345678', 'applicationId' => 'inexture3bb6f81e1e4a5c9f68934f', 'deviceType' => 4);
            
            $jsonData = json_encode($postData);

            $curlObj = curl_init();

            curl_setopt($curlObj, CURLOPT_URL, 'https://apps.applozic.com/rest/ws/register/client');
            curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curlObj, CURLOPT_HEADER, 0);
            curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json', 'Apz-AppId:inexture3bb6f81e1e4a5c9f68934f', 'Apz-Token:BASIC eWFzaGsuaW5leHR1cmVAZ21haWwuY29tOmluZXh0dXJlMTIzPw=='));
            curl_setopt($curlObj, CURLOPT_POST, 1);
            curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

            $result = curl_exec($curlObj);
            $json = json_decode($result);

            $request->user()->user_pic = ($request->user()->user_pic != NULL && $request->user()->user_pic != '') ? url($this->userThumbImageUploadPath . $request->user()->user_pic) : '';
            return response()->json([
                        'status' => '1',
                        'message' => 'User created successfully.',
                        'data' => [
                            'userDetail' => $request->user(),
                            'loginToken' => compact('token')
                        ]
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Error registering user.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Get user detail.
     *
     * @return \App\User User detail.
     * @see \App\User
     * @Get("/")
     * @Transaction({
     *     @Request({}),
     *     @Response( {status": "1","message": "Success","data": {"userDetail": {"id": 2,"name": null,"username": "vandit.kotadiya","email": "vandit.kotadiya@inexture.in","phone": "+11234567890","dob": "1993-06-19","longitude": -118.410468,"latitude": 34.103003,"zipcode": "90210","city": "Beverly Hills","state": "California","country": "United States","user_pic": null,"gender": 1,"points": 99800,"roster_app_amount": null,"funds": null,"is_admin": 0,"created_at": "2017-10-17 11:54:50","updated_at": "2017-11-15 08:55:23","deleted_at": null}}} )
     *     @Response( {"status": "0",'message': 'Error getting user detail.','code' => $e->getStatusCode()} )
     * })
     */
    public function userDetail(Request $request) {
        try {
            $request->user()->user_pic = ($request->user()->user_pic != NULL && $request->user()->user_pic != '') ? url($this->userThumbImageUploadPath . $request->user()->user_pic) : '';
            return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => [
                            'userDetail' => $request->user()
                        ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error getting user detail.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Get location from zipcode (Google API)
     * @param [string] $zipcode
     * @return [array] Location detail or exception
     */
    public function getLocationFromZipcode($zipcode) {

        try {
            $url = "http://maps.googleapis.com/maps/api/geocode/json?address=" . $zipcode . "&sensor=false";
            $details = file_get_contents($url);
            $result = json_decode($details, true);

            return [
                'status' => '1',
                'data' => $result
            ];
        } catch (Exception $e) {
            return [
                'status' => '0',
                'code' => $e->getStatusCode()
            ];
        }
    }

    /**
     * Update an existing user.
     *
     * @param Request $request The current request
     * @return \App\User A new \App\User object
     * @throws Exception If there was an error
     * @see \App\User
     * @Post("/")
     * @Parameters({
     *     @Parameter("username", description="The new username of the user", type="string"),
     *     @Parameter("email", description="A new valid email address", type="string"),
     *     @Parameter("phone", description="A new phone number", type="string"),
     *     @Parameter("dob", description="New Date of birth of user", type="date"),
     *     @Parameter("zipcode", description="Valid US zipcode", type="string")
     *     @Parameter("user_pic", description="User picture", type="file")
     * })
     * @Transaction({
     *     @Request( {"username": "vandit.kotadiya","email": "vandit.inexture@gmail.com","phone": "+11234567890","dob": "1993-06-19","zipcode": "90210"} ),
     *     @Response( {"status": "1","message": "User updated successfully.","data": {"userDetail": {"username": "vandit.kotadiya","email": "vandit.kotadiya@inexture.in","phone": "+11234567890","dob": "1993-06-19","gender": "1","zipcode": "90210","latitude": 34.1030032,"longitude": -118.4104684,"city": "Beverly Hills","state": "California","country": "United States","updated_at": "2017-10-31 10:22:20","created_at": "2017-10-31 10:22:20","id": 6},"loginToken": {"token": "ASDFGHe678"}}} ),
     *     @Response( {"status": "0",'message': 'Error updating user.','code' => $e->getStatusCode()} )
     *     @Response( {"status": "0",'message': 'Invalid credential.','code' => 422} )
     *     @Response( {"status": "0",'message': 'Failed to create token.','code' => 500} )
     * })
     */
    public function editProfile(Request $request) {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                        'username' => ['required', 'max:50', 'regex:/^[a-zA-Z0-9-_\.\/]+$/', Rule::unique('users', 'username')->ignore($request->user()->id)],
                        'phone' => 'required',
                        'gender' => 'required',
                        'dob' => 'required|date|date_format:Y-m-d|before:'. Carbon::now()->subYears(Config::get('constant.MINIMUM_AGE')),
                        'user_pic' => 'image',
                        'zipcode' => ['required', 'regex:/^\d{5}(?:[-\s]\d{4})?$/'] // US Zip code validation regex
            ]);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => $validator->messages()->all()[0],
                            'code' => '20100'
                ]);
            }

            $locationDetail = $this->getLocationFromZipcode($request->zipcode);

            if ($locationDetail['status'] == '0' || ($locationDetail['status'] == '1' && empty($locationDetail['data']['results']))) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Error while getting location. Please try again.',
                            'code' => (isset($locationDetail['code']) ? $locationDetail['code'] : 404)
                ]);
            }

            $data = $request->only('username', 'phone', 'dob', 'gender', 'zipcode');
            $user = $request->user();

            if (Input::file()) {
                $file = Input::file('user_pic');

                if (!empty($file)) {
                    $fileName = str_random(20) . '.' . $file->getClientOriginalExtension();
                    $pathOriginal = public_path($this->userOriginalImageUploadPath . $fileName);
                    $pathThumb = public_path($this->userThumbImageUploadPath . $fileName);

                    if (!file_exists(public_path($this->userOriginalImageUploadPath)))
                        File::makeDirectory(public_path($this->userOriginalImageUploadPath), 0777, true, true);
                    if (!file_exists(public_path($this->userThumbImageUploadPath)))
                        File::makeDirectory(public_path($this->userThumbImageUploadPath), 0777, true, true);

                    // created instance
                    $img = Image::make($file->getRealPath());

                    $img->save($pathOriginal);
                    // resize the image to a height of $this->contestThumbImageHeight and constrain aspect ratio (auto width)
                    if ($img->height() < 500) {
                        $img->resize(null, $img->height(), function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($pathThumb);
                    } else {
                        $img->resize(null, $this->userThumbImageHeight, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($pathThumb);
                    }

                    if (!($user->user_pic == '' || $user->user_pic == 'default.png')) {
                        $imageOriginal = public_path($this->userOriginalImageUploadPath . $user->user_pic);
                        $imageThumb = public_path($this->userThumbImageUploadPath . $user->user_pic);
                        if (file_exists($imageOriginal)) {
                            File::delete($imageOriginal);
                        }
                        if (file_exists($imageThumb)) {
                            File::delete($imageThumb);
                        }
                    }
                    $data['user_pic'] = $fileName;
                }
            }

            // Users location detail
            $data['latitude'] = $locationDetail['data']['results'][0]['geometry']['location']['lat'];
            $data['longitude'] = $locationDetail['data']['results'][0]['geometry']['location']['lng'];
            $data['city'] = $locationDetail['data']['results'][0]['address_components'][1]['long_name'];
            $data['state'] = $locationDetail['data']['results'][0]['address_components'][3]['long_name'];
            $data['country'] = $locationDetail['data']['results'][0]['address_components'][4]['long_name'];

            $user->fill(array_filter($data));
            $user->save();

            DB::commit();

            $user->user_pic = ($user->user_pic != NULL && $user->user_pic != '') ? url($this->userThumbImageUploadPath . $user->user_pic) : '';

            $user->gender = (int) $user->gender;

            return response()->json([
                        'status' => '1',
                        'message' => 'User updated successfully.',
                        'data' => [
                            'userDetail' => $user
                        ]
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Error updating user.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /* Forgot password */

    public function forgotPassword(Request $request) {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                        'forgot_email' => ['required', 'email', 'max:255'],
            ]);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => $validator->messages()->all()[0],
                            'code' => '20100'
                ]);
            }
            // Get user detail from database
            $user = User::where('email', $request->forgot_email)->where('status',0)->first();

            // User not exist
            if (is_null($user)) {
                return response()->json([
                            'status' => '0',
                            'message' => 'User does not exist!',
                            'code' => '20100'
                ]);
            }
            DB::table('password_resets')->where('email', $request->forgot_email)->delete();

            //create a new token to be sent to the user. 
            DB::table('password_resets')->insert([
                'email' => $request->forgot_email,
                'token' => str_random(80),
                'created_at' => Carbon::now()
            ]);

            $tokenData = DB::table('password_resets')->where('email', $request->forgot_email)->first();

            $token = $tokenData->token;
            $email = $request->forgot_email;

            // Send Password reset mail
            $data = [
                'url' => url('password/reset/' . $token),
                'username' => ($user->name == null && $user->name == '')?$user->username:$user->name
            ];

            Mail::send('emails.ResetPassword', $data, function($message) use($email) {
                $message->to($email)->subject('Your Password Reset Link');
            });

            DB::commit();
            return response()->json([
                        'status' => '1',
                        'message' => 'Mail sent in your account.',
                        'data' => [
                        ]
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'error',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Update an existing user's notification status.
     *
     * @param Request $request The current request
     * @return \App\User A new \App\User object
     * @throws Exception If there was an error
     * @see \App\User
     * @Post("/")
     * @Parameters({
     *     @Parameter("notification_status", description="The new notification status of the user", type="boolean"),
     * })
     * @Transaction({
     *     @Request( {"notification_status":0} ),
     *     @Response( {"status": "1","message": "Success","data": {"id": 2,"name": "dhruvit","username":"dhruvit","email": "dhruvit.inexture@gmail.com","phone": "1234567890","dob": "1992-11-26","longitude": -118.410468,"latitude": 34.103003,"zipcode": "90210","city": "Beverly Hills","state": "California","country": "United States","user_pic": "bJ9OCOU1WvTDRG1nlxTV.png","gender": 1,"points": 3070,"roster_app_amount": null,"funds": null,"is_admin": 0,"social_type": "0","social_id": null,"notification_status": 0,"created_at": "2017-10-31 15:31:14","updated_at": "2018-01-04 12:43:39","deleted_at": null}} ),
     *     @Response( {"status": "0",'message': 'error','code' => $e->getStatusCode()} )
     * })
     */
    public function editNotificationStatus(Request $request) {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                        'notification_status' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => $validator->messages()->all()[0],
                            'code' => '20100'
                ]);
            }

            // Update notification status
            $user = $request->user();
            $user->notification_status = $request->notification_status;
            $user->save();
            DB::commit();

            return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => [
                            'userDetail' => $request->user()
                        ]
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'error',
                        'code' => $e->getStatusCode()
            ]);
        }
    }
    /**
     * Logout user delete device token.
     *
     * @param Request $request The current request
     * @return \App\User A new \App\User object
     * @throws Exception If there was an error
     * @see \App\User
     * @Post("/")
     * @Parameters({
     *     @Parameter("device_token", description="The device token which you wants to delete.", type="string"),
     * })
     * @Transaction({
     *     @Request( {"device_token":"token"} ),
     *     @Response( {"status": "1","message": "Success","data": []} ),
     *     @Response( {"status": "0",'message': 'error','code' => $e->getStatusCode()} )
     * })
     */
    public function logout(Request $request) {
        try {
            DB::beginTransaction();
            
            $request->device_token = ($request->device_token) ? $request->device_token : '';
            
            // Delete device token of logged in user
            $user = $request->user();
            UserDevice::where('user_id',$user->id)->where('device_token', $request->device_token)->forceDelete();
            DB::commit();

            return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => []
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'error',
                        'code' => $e->getStatusCode()
            ]);
        }
    }
    /* Chat API Applozic */

    public function userChat(Request $request) {
        $postData = array('userId' => $request->userId, 'email' => $request->email);
        $jsonData = json_encode($postData);

        $curlObj = curl_init();

        curl_setopt($curlObj, CURLOPT_URL, 'https://apps.applozic.com/rest/ws/user/v2/create');
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlObj, CURLOPT_HEADER, 0);
        curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json', 'Apz-AppId:23857dd793c2f3f9d5e8882d584dd28d2', 'Apz-Token:BASIC a3Jpc2huYS5pbmV4dHVyZUBnbWFpbC5jb206aW5leHR1cmUxMjM/'));
        curl_setopt($curlObj, CURLOPT_POST, 1);
        curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

        $result = curl_exec($curlObj);
        $json = json_decode($result);

        return $result;
    }

    /* Chat user group create API */

    public function createChatGroup(Request $request) {
        $postData = array('groupName' => $request->groupName, 'groupMemberList' => $request->groupMemberList, 'clientGroupId' => $request->clientGroupId, 'admin' => $request->admin);
        $jsonData = json_encode($postData);

        $curlObj = curl_init();

        curl_setopt($curlObj, CURLOPT_URL, 'https://apps.applozic.com/rest/ws/group/v2/create');
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlObj, CURLOPT_HEADER, 0);
        curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json', 'Apz-AppId:23857dd793c2f3f9d5e8882d584dd28d2', 'Apz-Token:BASIC a3Jpc2huYS5pbmV4dHVyZUBnbWFpbC5jb206aW5leHR1cmUxMjM/'));
        curl_setopt($curlObj, CURLOPT_POST, 1);
        curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

        $result = curl_exec($curlObj);
        $json = json_decode($result);

        return $result;
    }

}
