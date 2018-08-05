<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuth;
use JWTAuthException;
use Config;
use App\User;
use App\UserDevice;
use Validator;
use DB;

class AuthController extends Controller {

    public function __construct() {
        $this->userThumbImageUploadPath = Config::get('constant.USER_THUMB_IMAGE_UPLOAD_PATH');
    }

    /**
     * Get user token The current request
     * 
     * @param Request $request
     * @return array An array with a single item, keyed with 'token', that contains an authorization token to be used with other API methods
     * @throws JWTAuthException If there was a JWT authentication error
     * @see \App\User
     * @Post("/")
     * @Parameters({
     *      @Parameter("email", description="email or username of user", type="string"),
     *      @Parameter("password", description="user password.", type="string"),
     * })
     * @Transaction({
     *      @Request( {"email": "test@test.com","password": "12345678"}),
     *      @Response( {"status": "1","message": "Token created successfully.","data": {"userDetail": {"id": 2,"name": null,"username": "vandit.kotadiya","email": "vandit.kotadiya@inexture.in","phone": "+11234567890","dob": "1993-06-19","longitude": -118.410468,"latitude": 34.103003,"zipcode": "90210","city": "Beverly Hills","state": "California","country": "United States","user_pic": null,"gender": 1,"points": null,"roster_app_amount": null,"funds": null,"is_admin": 0,"created_at": "2017-10-17 11:54:50","updated_at": "2017-10-17 11:54:50","deleted_at": null},"loginToken": {"token": "ASDFG5766"}}} )
     *      @Response( {"status": "0",'message': 'Invalid credential.','code' => 422} )
     *      @Response( {"status": "0",'message': 'Failed to create token.','code' => 500} )
     *      @Response( {"status": "0",'message': 'Unauthorized access.','code' => 401} )
     * })
     */
    public function getToken(Request $request) {

        $validator = Validator::make($request->all(), [
                    'email' => 'required'
        ]);

        if ($validator->fails()) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => $validator->messages()->all()[0],
                        'code' => '20100'
            ]);
        }

        $request->device_token = (isset($request->device_token) && $request->device_token != null) ? $request->device_token : '';
        $userData = User::where('email', $request->email)->orWhere('username', $request->email)->first();

        $token = null;
        // Social Login
        if (!is_null($userData) && $request->social_id != null && $request->social_id != '') {
            try {
                // Get token with email and password
                if (!$token = JWTAuth::fromUser($userData)) {

                    return response()->json([
                                'status' => '0',
                                'message' => 'User does not exists',
                                'code' => 422
                    ]);
                }
            } catch (JWTAuthException $e) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Failed to create token.',
                            'code' => 500
                ]);
            }
            $userData->user_pic = ($userData->user_pic != NULL && $userData->user_pic != '') ? url($this->userThumbImageUploadPath . $userData->user_pic) : '';
            $userData->social_id = ($userData->social_id != NULL && $userData->social_id != '') ? $userData->social_id : '';
            
            // Restrict admin login
            if ($userData->is_admin == Config::get('constant.ADMIN_USER_FLAG')) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Unauthorized access.',
                            'code' => 401
                ]);
            }

            //user id deleted or not.
            if ($userData->status != Config::get('constant.ACTIVE_STATUS_FLAG')) {
                return response()->json([
                            'status' => '0',
                            'message' => 'User not available.',
                            'code' => 401
                ]);
            }

            // Add device token if not exist
            $this->addDeviceToken($userData, $request->device_token);

            return response()->json([
                        'status' => '1',
                        'message' => 'Login with social Account.',
                        'data' => [
                            'userDetail' => $userData,
                            'loginToken' => compact('token')
                        ]
            ]);
        } else {
            try {
                $credentials = [
                    'email' => $request->email,
                    'password' => $request->password
                ];
                // Get token with email and password
                if (!$token = JWTAuth::attempt($credentials)) {
                    // Get token with username and password
                    $credentials = [
                        'username' => $request->email,
                        'password' => $request->password
                    ];
                    if (!$token = JWTAuth::attempt($credentials)) {
                        return response()->json([
                                    'status' => '0',
                                    'message' => 'User does not exists.',
                                    'code' => 422
                        ]);
                    }
                }
            } catch (JWTAuthException $e) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Failed to create token.',
                            'code' => 500
                ]);
            }

            // Restrict admin login
            if ($request->user()->is_admin == Config::get('constant.ADMIN_USER_FLAG')) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Unauthorized access.',
                            'code' => 401
                ]);
            }
            //user id deleted or not.
            if ($request->user()->status != Config::get('constant.ACTIVE_STATUS_FLAG')) {
                return response()->json([
                            'status' => '0',
                            'message' => 'User not available.',
                            'code' => 401
                ]);
            }

            $request->user()->user_pic = ($request->user()->user_pic != NULL && $request->user()->user_pic != '') ? url($this->userThumbImageUploadPath . $request->user()->user_pic) : '';
            $request->user()->social_id = ($request->user()->social_id != NULL && $request->user()->social_id != '') ? $request->user()->social_id : '';

            // Add device token if not exist
            $this->addDeviceToken($request->user(), $request->device_token);

            return response()->json([
                        'status' => '1',
                        'message' => 'Token created successfully.',
                        'data' => [
                            'userDetail' => $request->user(),
                            'loginToken' => compact('token')
                        ]
            ]);
        }
    }

    /**
     * To add device token of login user if not exist
     * @param [object] $user
     * @param [string] $deviceToken
     * @return boolean
     */
    public function addDeviceToken($user, $deviceToken) {
        try {
            $userDeviceToken = UserDevice::where('user_id', $user->id)->pluck('device_token');
            $userDeviceToken = $userDeviceToken->toArray();

            if (count($userDeviceToken) > 0) {
                if(!in_array($deviceToken, $userDeviceToken)) {
                    $deviceData['user_id'] = $user->id;
                    $deviceData['device_token'] = $deviceToken;
                    UserDevice::create($deviceData);
                }
            } else {
                $deviceData['user_id'] = $user->id;
                $deviceData['device_token'] = $deviceToken;
                UserDevice::create($deviceData);
            }
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }
}
