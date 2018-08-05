<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Friend;
use App\League;
use App\LeagueInvitedUser;
use Config;
use Helpers;
use DB;
use App\UserDevice;

class FriendController extends Controller {

    public function __construct() {
        $this->userThumbImageUploadPath = Config::get('constant.USER_THUMB_IMAGE_UPLOAD_PATH');
    }

    /**
     * Find a friend from normal user.
     *
     * @param Request $request The current request
     * @throws Exception If there was an error
     * @see \App\User
     * @Post("/")
     * @Parameters({
     *     @Parameter("searchFriendString", description="Serach string", type="string"),
     * })
     * @Transaction({
     *     @Request( {"searchFriendString": "vandit"} ),
     *     @Response( {"status": "1","message": "Success","data": {"searchFriendList": [{"id": 3,"name": null,"username": "vandit.kotadiya01","email": "vandit.kotadiya01@inexture.in","phone": "+11234567890","dob": "1993-06-19","longitude": -118.410468,"latitude": 34.103003,"zipcode": "90210","city": "Beverly Hills","state": "California","country": "United States","user_pic": "","gender": 1,"points": null,"roster_app_amount": null,"funds": null,"is_admin": 0,"created_at": "2017-10-18 12:52:42","updated_at": "2017-10-18 12:52:43","deleted_at": null,"friend_request_status": [{"id": 1,"receiver_id": 2,"requester_id": 3,"status": "deleted","created_at": null,"updated_at": null,"deleted_at": null}]}]}} ),
     *     @Response( {"status": "0",'message': 'Error.','code' => $e->getStatusCode()} )
     * })
     */
    public function findFriend(Request $request) {

        try {
            $searchFriendString = ($request->searchFriendString) ? $request->searchFriendString : '';
            $currentUserId = $request->user()->id;

            // User list except blocked by current user / admin list
            $searchFriendList = User::with(['friendRequestStatus' => function($query) use($currentUserId) {
                    $query->where('receiver_id', $currentUserId)
                        ->whereNotIn('status', [Config::get('constant.REQUEST_BLOCKED_STATUS'), Config::get('constant.REQUEST_BLOCKED_BY_STATUS'), Config::get('constant.REQUEST_DELETED_STATUS'), Config::get('constant.REQUEST_DELETED_BY_STATUS')])
                        ->orderBy('id', 'DESC');
                                                }
                    ])
                    ->where('status', Config::get('constant.ACTIVE_STATUS_FLAG'))
                    ->normalUser()
                    ->notCurrentUser($currentUserId)
                    ->searchUserName($searchFriendString)
                    ->get()
                    ->each(function ($searchFriendList) {
                        $searchFriendList->user_pic = ($searchFriendList->user_pic != NULL && $searchFriendList->user_pic != '') ? url($this->userThumbImageUploadPath.$searchFriendList->user_pic) : '';
                    });

            return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => [
                            'searchFriendList' => $searchFriendList
                        ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Friend List of user. (All Friends and friend request list)
     * Also used while user invite friend on private contest. (Parameter vary)
     * @param Request $request The current request
     * @throws Exception If there was an error
     * @see \App\User
     * @see \App\Friend
     * @see \App\Contest
     * @see \App\ContestInvitedUser
     * @Post("/")
     * @Parameters({
     *     @Parameter("searchString", description="Serach string", type="string"),
     *     @Parameter("sortOrder", description="Filed that need to sort", type="array"),
     *     @Parameter("action", description="Required parameter while getting friend list on update contest", type="string"),
     *     @Parameter("contest_id", description="Required parameter while getting friend list on update contest", type="integer"),
     * })
     * @Transaction({
     *     @Request( {"searchString": "","sortOrder": {"column": "name","order": "DESC"},"action":"update_contest","contest_id":"44"} )
     *     @Response( {"status": "1","message": "Friend List","data": {"friendList": [{"id": 3,"name": null,"username": "vandit.kotadiya01","email": "vandit.kotadiya01@inexture.in","phone": "+11234567890","dob": "1993-06-19","longitude": -118.410468,"latitude": 34.103003,"zipcode": "90210","city": "Beverly Hills",state": "California","country": "United States","user_pic": "","gender": 1,"points": null,"roster_app_amount": null,"funds": null,"is_admin": 0,"created_at": "2017-10-18 12:52:42","updated_at": "2017-10-18 12:52:43","deleted_at": null,"pivot": {"receiver_id": 2,"requester_id": 3,"status": "accepted"}}],"friendRequestList": []}} ),
     *     @Response( {"status": "0",'message': 'Error while retrieving friend list','code' => $e->getStatusCode()} )
     *     @Response( {"status": "0",'message': 'This contest is not associated with current user.','code' => 404} )
     * })
     */
    public function friendList(Request $request) {
        try {
            $searchString = ($request->searchString) ? $request->searchString : '';
            $sort = ($request->sortOrder && !empty($request->sortOrder) ? $request->sortOrder : Config::get('constant.FRIEND_LISTING_DEFAULT_SORT'));

            // Required parameter while getting friend list on update contest
            //if($request->action == 'update_contest'){
            //$action = ($request->action && $request->action == 'update_contest') ? $request->action : '';
            $action = ($request->action) ? $request->action : '';
            $contestId = ($request->contest_id && $request->contest_id != '') ? $request->contest_id : 0;
            //}
            // Required parameter while getting friend list on update league
            //if($request->action == 'update_league'){
            //$action = ($request->action && $request->action == 'update_league') ? $request->action : '';
            $leagueId = ($request->league_id && $request->league_id != '') ? $request->league_id : 0;
            // }
            $friendList = $request->user()->friend()->normalUser()->searchUser($searchString)->sort($sort)->wherePivot('status', 'accepted')->where('users.status', Config::get('constant.ACTIVE_STATUS_FLAG'))->get();

            $friendRequestList = $request->user()->friend()->normalUser()->searchUser($searchString)->sort($sort)->where('users.status', Config::get('constant.ACTIVE_STATUS_FLAG'))->wherePivot('status', '<>', Config::get('constant.REQUEST_ACCEPTED_STATUS'))->wherePivot('status', '<>', Config::get('constant.REQUEST_DELETED_STATUS'))->wherePivot('status', '<>', Config::get('constant.REQUEST_DELETED_BY_STATUS'))->wherePivot('status', '<>', Config::get('constant.REQUEST_BLOCKED_STATUS'))->wherePivot('status', '<>', Config::get('constant.REQUEST_BLOCKED_BY_STATUS'))->get()->each(function ($friendRequestList) {
                $friendRequestList->user_pic = ($friendRequestList->user_pic != NULL && $friendRequestList->user_pic != '') ? url($this->userThumbImageUploadPath.$friendRequestList->user_pic) : '';
            });

            // Required parameter while getting friend list on update contest
            $invitedUserForContest = [];
            if($action == 'update_contest') {
                $contest = $request->user()->contest()->where('id', $contestId)->first();

                if($contest === null) {
                    return response()->json([
                                'status' => '0',
                                'message' => 'This event is not associated with current user.',
                                'code' => 404
                    ]);
                }

                // Get invited user's list for given contest id
                $invitedUserForContest = $contest->contestInvitedUser()->get([
                    'user_id'
                ])->toArray();
            }

            $invitedUserForLeague = [];
            if($action == 'update_league') {
                $league = $request->user()->league()->where('id', $leagueId)->first();

                if($league === null) {
                    return response()->json([
                                'status' => '0',
                                'message' => 'This league is not associated with current user.',
                                'code' => 404
                    ]);
                }

                // Get invited user's list for given contest id
                $invitedUserForLeague = $league->leagueInvitedUser()->get([
                    'user_id'
                ])->toArray();
            }
            if($action == 'update_contest') {
                foreach ($friendList as $key => $_friendList) {
                    if(Helpers::in_array_r($_friendList->id, $invitedUserForContest)) {
                        unset($friendList[$key]);
                    } else {
                        $_friendList->user_pic = ($_friendList->user_pic != NULL && $_friendList->user_pic != '') ? url($this->userThumbImageUploadPath.$_friendList->user_pic) : '';
                    }
                }
            }
            if($action == 'update_league') {
                foreach ($friendList as $key => $_friendList) {
                    if(Helpers::in_array_r($_friendList->id, $invitedUserForLeague)) {
                        unset($friendList[$key]);
                    } else {
                        $_friendList->user_pic = ($_friendList->user_pic != NULL && $_friendList->user_pic != '') ? url($this->userThumbImageUploadPath.$_friendList->user_pic) : '';
                    }
                }
            }
            return response()->json([
                        'status' => '1',
                        'message' => 'Friend List',
                        'data' => [
                            'friendList' => array_values($friendList->toArray()),
                            'friendRequestList' => $friendRequestList
                        ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error while retrieving friend list.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Add friend (Send friend request).
     *
     * @param Request $request The current request
     * @throws Exception If there was an error
     * @see \App\Friend
     * @Post("/")
     * @Parameters({
     *     @Parameter("receiver_id", description="User id of friend", type="integer"),
     * })
     * @Transaction({
     *     @Request( {"receiver_id":"5"} )
     *     @Response( {"status": "1","message": "Request sent sucessfully.","data": []} ),
     *     @Response( {"status": "0",'message': 'No such user found to send request.','code' => 404} )
     *     @Response( {"status": "0",'message': 'You can\'t send request to this user.','code' => 400} )
     *     @Response( {"status": "0",'message': 'You can\'t send request to own.','code' => 400} )
     *     @Response( {"status": "0",'message': 'Error in send friend request. Please try again.','code' => 400} )
     *     @Response( {"status": "0",'message': 'Something went wrong. Please try again.','code' => 400} )
     * })
     */
    public function addFriend(Request $request) {
        DB::beginTransaction();
        try {
            $receiverDetail = User::where('id', $request->receiver_id)->where('status', Config::get('constant.ACTIVE_STATUS_FLAG'))->first();

            // receiver data not found
            if($receiverDetail === null || ($receiverDetail && $receiverDetail->status == Config::get('constant.DELETED'))) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'No such user found to send request.',
                            'code' => 404
                ]);
            }

            // Can't send request to admin
            if($receiverDetail->is_admin == Config::get('constant.ADMIN_USER_FLAG')) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You can\'t send request to this user.',
                            'code' => 400
                ]);
            }

            // Can't send request to own
            if($receiverDetail->id == $request->user()->id) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You can\'t send request to own.',
                            'code' => 400
                ]);
            }

            // Get previous friend request history
            $requestDetail = Friend::where('receiver_id', $request->receiver_id)->where('requester_id', $request->user()->id)->notDeletedStatus()->first();

            if($requestDetail === null) {
                // Add in friend (Friend request received from current user)
                $requestReceived = [
                    'receiver_id' => $request->receiver_id,
                    'requester_id' => $request->user()->id,
                    'status' => Config::get('constant.REQUEST_PENDING_STATUS')
                ];
                Friend::create($requestReceived);

                $user_device = UserDevice::where('user_id',$request->receiver_id)->get();
                if(count($user_device) > 0) {
                    foreach ($user_device as $device) {
                        $user_details = User::find($device->user_id);

                        if( $user_details->notification_status == 1 ) {

                            $data = array(
                                        'notification_status' => 2,
                                        'message' => $request->user()->username.' sent you friend request.',
                                        'friend_id' => $request->user()->id,
                                        'notification_type' => 'FriendRequestReceived'
                                    );
                            Helpers::pushNotificationForiPhone($device->device_token,$data);
                        }
                    }
                }
                // Add in friend (Friend request sent by current user)
                $sentRequest = [
                    'receiver_id' => $request->user()->id,
                    'requester_id' => $request->receiver_id,
                    'status' => Config::get('constant.REQUESTED_STATUS')
                ];
                Friend::create($sentRequest);
                DB::commit();
                return response()->json([
                            'status' => '1',
                            'message' => 'Request sent sucessfully.',
                            'data' => [
                            ]
                ]);
            } else {
                // Request already sended but not accepted or blocked or pending request
                return response()->json([
                            'status' => '0',
                            'message' => 'Something went wrong. Please try again.',
                            'code' => 400
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error in send friend request. Please try again.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Delete friend (Delete friend from friend list or delete request or delete sent request).
     *
     * @param Request $request The current request
     * @throws Exception If there was an error
     * @see \App\Friend
     * @Post("/")
     * @Parameters({
     *     @Parameter("friend_id", description="User id of friend", type="integer"),
     * })
     * @Transaction({
     *     @Request( {"friend_id":"5"} )
     *     @Response( {"status": "1","message": "Removed successfully.","data": []} ),
     *     @Response( {"status": "0",'message': 'No such friend found.','code' => 404} )
     *     @Response( {"status": "0",'message': 'Something went wrong. Please try again.','code' => 400} )
     *     @Response( {"status": "0",'message': 'Error in delete friend request. Please try again','code' => $e->getStatusCode()} )
     * })
     */
    public function deleteFriend(Request $request) {
        DB::beginTransaction();
        try {
            $friendDetail = User::find($request->friend_id);

            // Friend data not found
            if($friendDetail === null) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'No such friend found.',
                            'code' => 404
                ]);
            }

            // Get friend detail
            $requestDetail = Friend::where('receiver_id', $request->friend_id)->where('requester_id', $request->user()->id)->first();
            if( $requestDetail && ($requestDetail->status == Config::get('constant.REQUEST_PENDING_STATUS') || $requestDetail->status == Config::get('constant.REQUESTED_STATUS') || $requestDetail->status == Config::get('constant.REQUEST_ACCEPTED_STATUS')) ) {
                $requestDetail->status = Config::get('constant.REQUEST_DELETED_STATUS');
                $requestDetail->save();
                $requestDetail->delete();

                $requesterDetail = Friend::where('requester_id', $request->friend_id)->where('receiver_id', $request->user()->id)->first();
                $requesterDetail->status = Config::get('constant.REQUEST_DELETED_BY_STATUS');
                $requesterDetail->save();
                $requesterDetail->delete();
            } else {
                DB::rollback();
                // Not in friend list or blocked
                return response()->json([
                            'status' => '0',
                            'message' => 'Something went wrong. Please try again.',
                            'code' => 400
                ]);
            }
            DB::commit();
            return response()->json([
                        'status' => '1',
                        'message' => 'Removed successfully.',
                        'data' => [
                        ]
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Error in delete friend request. Please try again.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Accept friend request(Accept friend request from arrived request).
     *
     * @param Request $request The current request
     * @throws Exception If there was an error
     * @see \App\Friend
     * @Post("/")
     * @Parameters({
     *     @Parameter("friend_id", description="User id of friend", type="integer"),
     * })
     * @Transaction({
     *     @Request( {"friend_id":"5"} )
     *     @Response( {"status": "1","message": "Accepted successfully.","data": []} ),
     *     @Response( {"status": "0",'message': 'No such friend found.','code' => 404} )
     *     @Response( {"status": "0",'message': 'Something went wrong. Please try again.','code' => 400} )
     *     @Response( {"status": "0",'message': 'Error in accepting friend request. Please try again.','code' => $e->getStatusCode()} )
     * })
     */
    public function acceptFriendRequest(Request $request) {
        DB::beginTransaction();
        try {
            $friendDetail = User::find($request->friend_id);

            // Friend data not found
            if($friendDetail === null && $friendDetail->status == Config::get('constant.DELETED')) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'No such friend found.',
                            'code' => 404
                ]);
            }

            // Get friend detail
            $requestDetail = Friend::where('requester_id', $request->friend_id)->where('receiver_id', $request->user()->id)->first();
            if( $requestDetail && $requestDetail->status == Config::get('constant.REQUEST_PENDING_STATUS') ) {
                $requestDetail->status = Config::get('constant.REQUEST_ACCEPTED_STATUS');
                $requestDetail->save();

                $user_device = UserDevice::where('user_id',$request->friend_id)->get();
                if(count($user_device) > 0) {
                    foreach ($user_device as $device) {
                        $user_details = User::find($device->user_id);

                        if( $user_details->notification_status == 1 ) {
                            $data = array(
                                        'notification_status' => 2,
                                        'message' => $request->user()->username.' accepted your friend request.',
                                        'friend_id' => $request->user()->id,
                                        'notification_type' => 'FriendRequestAccepted'
                                    );
                            Helpers::pushNotificationForiPhone($device->device_token,$data);
                        }
                    }
                }

                $requesterDetail = Friend::where('receiver_id', $request->friend_id)->where('requester_id', $request->user()->id)->first();
                $requesterDetail->status = Config::get('constant.REQUEST_ACCEPTED_STATUS');
                $requesterDetail->save();
            } else {
                DB::rollback();
                // Not in friend list or blocked
                return response()->json([
                            'status' => '0',
                            'message' => 'Something went wrong. Please try again.',
                            'code' => 400
                ]);
            }

            $user_device = UserDevice::where('user_id',$request->receiver_id)->get();
                if(count($user_device) > 0) {
                    foreach ($user_device as $device) {
                        $user_details = User::find($device->user_id);

                        if( $user_details->notification_status == 1 ) {
                            $data = array(
                                        'notification_status' => 2,
                                        'message' => $request->user()->username.' sent you friend request.',
                                        'friend_id' => $request->user()->id,
                                        'notification_type' => 'FriendRequestReceived'
                                    );
                            Helpers::pushNotificationForiPhone($device->device_token,$data);
                        }
                    }
            }

            DB::commit();
            return response()->json([
                        'status' => '1',
                        'message' => 'Accepted successfully.',
                        'data' => [
                        ]
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Error in accepting friend request. Please try again.',
                        'code' => $e->getStatusCode()
            ]);
        }

    }
}
