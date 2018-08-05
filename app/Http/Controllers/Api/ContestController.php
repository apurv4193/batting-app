<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Contest;
use App\ContestType;
use App\Game;
use App\PrizeDistributionPlan;
use App\ContestInvitedUser;
use App\ContestUser;
use App\Roster;
use App\UsersUsedPower;
use App\UsersPower;
use App\LeagueInvitedUser;
use Carbon\Carbon;
use App\User;
use App\UserDevice;
use App\ContestScoreImages;
use App\League;
use DB;
use Config;
use Input;
use Image;
use File;
use Validator;
use Helpers;

class ContestController extends Controller {

    private $contest;

    public function __construct(Contest $contest) {
        $this->contest = $contest;
        $this->contestOriginalImageUploadPath = Config::get('constant.CONTEST_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->contestThumbImageUploadPath = Config::get('constant.CONTEST_THUMB_IMAGE_UPLOAD_PATH');
        $this->contestThumbImageHeight = Config::get('constant.CONTEST_THUMB_IMAGE_HEIGHT');
        $this->contestThumbImageWidth = Config::get('constant.CONTEST_THUMB_IMAGE_WIDTH');

        $this->userThumbImageUploadPath = Config::get('constant.USER_THUMB_IMAGE_UPLOAD_PATH');

        $this->contestScoreOriginalImageUploadPath = Config::get('constant.CONTEST_SCORE_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->contestScoreThumbImageUploadPath = Config::get('constant.CONTEST_SCORE_THUMB_IMAGE_UPLOAD_PATH');
    }

    /**
     * Get data for create contest.
     *
     * To do: Get available balance
     * @return \App\Game Game list.
     * @return \App\PrizeDistributionPlan PrizeDistributionPlan list
     * @see \App\ContestType
     * @see \App\Game
     * @see \App\PrizeDistributionPlan
     * @Get("/")
     * @Transaction({
     *     @Request({}),
     *     @Response( {"status": "1","message": "Success","data": {"game": [{"id": 1,"name": "Cricket","created_at": "2017-10-12 07:40:00","updated_at": "2017-10-12 07:40:00","deleted_at": null}],"prizeDistributionPlan": [{"id": 1,"name": "All","winner": 0,"created_at": "2017-10-13 00:00:00","updated_at": "2017-10-13 00:00:00","deleted_at": null}]}} )
     *     @Response( {"status": "0",'message': 'Error.','code' => $e->getStatusCode()} )
     * })
     */
    public function createContest(Request $request) {
        try {
            $game = Game::notDeleted()->get();

            $prizeDistributionPlan = PrizeDistributionPlan::notDeleted()->get();
            $league = League::whereIn('status', ['upcoming', 'live'])->where('created_by', $request->user()->id)->get();

            return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => [
                            'game' => $game,
                            'prizeDistributionPlan' => $prizeDistributionPlan,
                            'league' => $league
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
     * Add contest.
     *
     * @param Request $request The current request
     * @return \App\Contest A new \App\Contest object
     * @throws Exception If there was an error
     * @see \App\Contest
     * @see \App\ContestInvitedUser
     * @Post("/")
     * @Parameters({
     *     @Parameter("contest_type_id", description="Contest type id", type="integer"),
     *     @Parameter("level_id", description="Contest level id", type="integer"),
     *     @Parameter("privacy", description="It is public or not", type="string"),
     *     @Parameter("game_id", description="Game id", type="integer"),
     *     @Parameter("contest_name", description="Contest name", type="string"),
     *     @Parameter("contest_fees", description="Contest fee to enter", type="integer"),
     *     @Parameter("prize_distribution_plan_id", description="Prize distribution plan id", type="integer"),
     *     @Parameter("contest_min_participants", description="Min number of participant to start contest.", type="integer")
     *     @Parameter("contest_max_participants", description="Max number of participant that can take part.", type="integer")
     *     @Parameter("contest_video_link", description="Twitch url of game.", type="string")
     *     @Parameter("contest_start_time", description="Start date time.", type="date")
     *     @Parameter("contest_end_time", description="Contest End date time.", type="date")
     *     @Parameter("banner", description="Contest banner image.", type="image")
     *     @Parameter("invited_user_list", description="Invited user's id if contest is private.", type="array")
     * })
     * @Transaction({
     *     @Request( {"contest_type_id": "1","level_id": "4","privacy": "public","game_id": "1","contest_name": "Contest #1","contest_fees": "100","prize_distribution_plan_id": "1","contest_min_participants": "1","contest_max_participants": "10","contest_video_link": "https://twitch.com/gali-cricket/","contest_start_time": "2017-12-17 04:54:34","contest_end_time": "2017-12-19 18:54:37","banner": Choose File } ),
     *     @Response( {"status": "1","message": "Contest created successfully.","data": {"contestDetail": {"contest_type_id": "4",level_id": "3","privacy": "public","game_id": "1","contest_name": "Contest Name","contest_fees": "100","prize_distribution_plan_id": "1","contest_min_participants": "1","contest_max_participants": "10","contest_video_link": "https://twitch.com/gali-cricket/","contest_start_time": "2017-12-17 04:54:34","contest_end_time": "2017-12-19 18:54:37","banner": "http://local.batting-app.com:8012/uploads/contest/thumb/7c7Jw7vzPhcIarKSTfL6.jpg","created_by": 2,"roster_cap_amount": 0,"updated_at": "2017-10-30 11:33:13","created_at": "2017-10-30 11:33:13","id": 44}}} ),
     *     @Response( {"status": "0",'message': 'Error creating contest.','code' => $e->getStatusCode()} )
     *     @Response( {"status": "0","message": "Please enter minimum 1 as minimum participant.","code": "400"} )
     *     @Response( {"status": "0",'message': 'You don\'t have enough balance to participate in contest.','code' => 400} )
     *     @Response( {"status": "0","message": "Something went wrong while invite user. Please try again.","code": "400"} )
     * })
     */
    public function saveContest(Request $request) {
        DB::beginTransaction();
        try {
            $rules = [
                'contest_type_id' => 'required|integer',
                'level_id' => 'required|integer',
                'privacy' => 'required',
                'game_id' => 'required|integer',
                'contest_fees' => 'required|digits_between:1,6',
                'contest_start_time' => 'required|date|date_format:Y-m-d H:i:s|after:' . Carbon::now()->addHours(3),
                'contest_end_time' => 'required|date|after:contest_start_time',
                'prize_distribution_plan_id' => 'required|integer',
                'contest_min_participants' => 'required|integer',
                'contest_max_participants' => 'required|integer|min:1|greater_than_or_equal_field:contest_min_participants',
                'banner' => 'required|image',
                'is_teamwise' => 'required|boolean'
            ];

            if (isset($request->league_id)) {
                $rules['league_id'] = 'required|integer';
            } else {
                $rules['contest_name'] = 'required|max:255|regex:/^(?![0-9]*$)[a-zA-Z0-9 ]+$/';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => $validator->messages()->all()[0],
                            'code' => '20100'
                ]);
            }

            $data = $request->all();
            $league = (isset($request->league_id)) ? League::find($request->league_id) : null;

            if (isset($request->league_id) && $league === null) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Invalid input parameter',
                            'code' => '400'
                ]);
            }
            if ($league !== null) {
                $leagueContest = Contest::where('league_id', $request->league_id)->count();
                $leagueContest +=1;
                $data['contest_name'] = 'Event ' . $leagueContest;

                if ($league->status == Config::get('constant.CANCELLED_CONTEST_STATUS') || $league->status == Config::get('constant.PENDING_CONTEST_STATUS') || $league->status == Config::get('constant.COMPLETED_CONTEST_STATUS') || $league->created_by != $request->user()->id) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'Invalid input parameter',
                                'code' => '400'
                    ]);
                }
            }

            if ($league !== null) {
                $data['game_id'] = $league->game_id;
                $data['contest_type_id'] = $league->contest_type_id;
                $data['level_id'] = $league->level_id;
                $data['league_min_participants'] = $league->league_min_participants;
                $data['privacy'] = 'private';

                $contestEndDate = date('Y-m-d', strtotime($request->contest_end_time));
                if ($contestEndDate > $league->league_end_date) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'Event end date equal or before league end date.',
                                'code' => '400'
                    ]);
                }
            }

            $allContest = Contest::whereNotIn('status', ['completed', 'pending'])->get();

            foreach ($allContest as $value) {
                if ($value->contest_name == $request->contest_name && $value->contest_start_time == $request->contest_start_time && $value->contest_end_time == $request->contest_end_time && $value->game_id == $request->game_id) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'Contest already exists',
                                'code' => '400'
                    ]);
                }
            }

            $data['contest_video_link'] = (!empty($data['contest_video_link'])) ? Helpers::addhttp($data['contest_video_link']) : null;
            $data['created_by'] = $request->user()->id;
            $data['participated'] = 1;
            $data['roster_cap_amount'] = ContestType::find($data['contest_type_id'])->contest_cap_amount;

            $prizeDistributioPlan = PrizeDistributionPlan::find($request->prize_distribution_plan_id);
            if($prizeDistributioPlan == null || ($prizeDistributioPlan && $prizeDistributioPlan->status == Config::get('constant.DELETED'))){
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Invalid input parameter',
                            'code' => '400'
                ]);
            }

            if ($prizeDistributioPlan->winner == '0') {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Something went wrong. Please contact to admin.',
                            'code' => '400'
                ]);
            }

            if ($prizeDistributioPlan->winner > $data['contest_min_participants']) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Please enter minimum ' . $prizeDistributioPlan->winner . ' as minimum participant.',
                            'code' => '400'
                ]);
            }

            // Save contest banner
            if (Input::file()) {
                $file = Input::file('banner');

                if (!empty($file)) {
                    $fileName = str_random(20) . '.' . $file->getClientOriginalExtension();
                    $pathOriginal = public_path($this->contestOriginalImageUploadPath . $fileName);
                    $pathThumb = public_path($this->contestThumbImageUploadPath . $fileName);

                    if (!file_exists(public_path($this->contestOriginalImageUploadPath)))
                        File::makeDirectory(public_path($this->contestOriginalImageUploadPath), 0777, true, true);
                    if (!file_exists(public_path($this->contestThumbImageUploadPath)))
                        File::makeDirectory(public_path($this->contestThumbImageUploadPath), 0777, true, true);

                    // created instance
                    $img = Image::make($file->getRealPath());

                    $img->save($pathOriginal);
                    // resize the image to a height of $this->contestThumbImageHeight and constrain aspect ratio (auto width)
                    if ($img->height() < 500) {
                        $img->resize(null, $img->height(), function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($pathThumb);
                    } else {
                        $img->resize(null, $this->contestThumbImageHeight, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($pathThumb);
                    }

                    $data['banner'] = $fileName;
                }
            }
            $contest = $this->contest->create($data);

            $newInvitedUser = [];
            if ($data['privacy'] == 'private') {
                if ($league !== null) {
                    $leagueInvitedUser = LeagueInvitedUser::where('league_id', $league->id)->pluck('user_id')->toArray();
                    $request->invited_user_list = $leagueInvitedUser;
                } else {
                    $request->invited_user_list = (!empty($request->invited_user_list)) ? explode(',', $request->invited_user_list) : [];
                }

                if ($prizeDistributioPlan->winner > count($request->invited_user_list)) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'You must have to invite atleast ' . $prizeDistributioPlan->winner . ' friend(s).',
                                'code' => '400'
                    ]);
                }

                foreach ($request->invited_user_list as $invitedUser) {
                    $invitedUserData = User::find($invitedUser);
                    if(!ContestInvitedUser::where('contest_id',$contest->id)->where('user_id', $invitedUser)->exists() && $invitedUserData !== null && $invitedUserData->status == Config::get('constant.NOT_DELETED')) {
                        ContestInvitedUser::create([
                            'contest_id' => $contest->id,
                            'user_id' => $invitedUser,
                            'status' => 'pending'
                        ]);
                        array_push($newInvitedUser, $invitedUser);
                    } else {
                        DB::rollback();
                        return response()->json([
                                    'status' => '0',
                                    'message' => 'Something went wrong while inviting user.',
                                    'code' => 400
                        ]);
                    }
                }

                // Notofication to newly invited user for contest
                $user_device = (!empty($newInvitedUser)) ? UserDevice::whereIn('user_id', $newInvitedUser)->get() : [];
                if (count($user_device) > 0) {
                    foreach ($user_device as $device) {

                        $user_detail = User::find($device->user_id);
                        if ($user_detail->notification_status == 1) {

                            $data = array(
                                'notification_status' => 2,
                                'message' => 'You are invited to join ' . ucfirst($contest->contest_name),
                                'contest_id' => $contest->id,
                                'notification_type' => 'ContestInvitation'
                            );
                            Helpers::pushNotificationForiPhone($device->device_token, $data);
                        }
                    }
                }
            }

            if (User::getCurrentUser($request->user()->id)->points == null || User::getCurrentUser($request->user()->id)->points < $contest->contest_fees) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You don\'t have enough balance to participate in event.',
                            'code' => 400
                ]);
            }

            // Add user as a participant
            ContestUser::create([
                'contest_id' => $contest->id,
                'user_id' => $request->user()->id,
                'is_paid' => 1
            ]);

            // Deduct balance from user wallet amount
            $user = User::getCurrentUser($request->user()->id);
            $points = $user->points - $contest->contest_fees;
            $user->fill(array_filter(['points' => $points]));
            $user->save();

            // Increase prize of contest
            $new_fees = ($contest->contest_fees * 10) / 100;
            $total_fees = $contest->contest_fees - $new_fees;
            $contest->prize = $contest->prize + $total_fees;
            $contest->save();
            
            DB::commit();

            $contest->banner = ($contest->banner != NULL && $contest->banner != '') ? url($this->contestThumbImageUploadPath . $contest->banner) : '';

            return response()->json([
                        'status' => '1',
                        'message' => 'Event created successfully.',
                        'data' => [
                            'contestDetail' => $contest
                        ]
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Error creating event.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Contest List to participate. (From filtering)
     *
     * To Do: Get only public and shared private contest
     * @param Request $request The current request
     * @return \App\Contest Contest listing for specified game, level and type
     * @throws Exception If there was an error
     * @see \App\Contest
     * @Post("/")
     * @Parameters({
     *     @Parameter("game", description="Game id so that filtering that games contest", type="integer"),
     *     @Parameter("contestLevel", description="Contest level id so that filtering that level contest", type="integer"),
     *     @Parameter("contestType", description="Contest type id so that filtering that type contest", type="string"),
     *     @Parameter("searchString", description="To search in contest", type="string"),
     *     @Parameter("sort", description="Sort contest based on parameter", type="array"),
     * })
     * @Transaction({
     *     @Request( {"game": "1","contestLevel": "4","contestType": "1","searchString": "","sort": {"column": "contest_name","order": "DESC"}} ),
     *     @Response( {"status": "1","message": "Contest listing.","data": {"contestDetail": [{"id": 8,"game_id": 1,"contest_type_id": 1,"level_id": 4,"contest_name": "Contest #2","contest_fees": 100,"contest_start_time": "2017-12-17 04:54:34","contest_end_time": "2017-12-19 18:54:37","privacy": "public","url": null,"prize_distribution_plan_id": 1,"contest_max_participants": "10","participated": null,"contest_video_link": "https://twitch.com/gali-cricket/","prize": 0,"created_by": 2,"created_at": "2017-10-18 12:22:15","updated_at": "2017-10-18 12:22:15","deleted_at": null}]}} ),
     *     @Response( {"status": "0",'message': 'Error listing contest.','code' => $e->getStatusCode()} )
     * })
     */
    public function contestListing(Request $request) {
        try {

            $currentUserId = $request->user()->id;
            $search = ($request->searchString ? $request->searchString : '');
            $sort = ($request->sort && !empty($request->sort) ? (is_array($request->sort) ? $request->sort : Config::get('constant.CONTEST_LISTING_DEFAULT_SORT') ) : Config::get('constant.CONTEST_LISTING_DEFAULT_SORT'));

            // Intialized array of participated contest of user
            $usersContestIds = [];
            $i = 0;
            $contest = Contest::with('userInContest')
                    ->filterGame($request->game)
                    ->filterLevel($request->contestLevel)
                    ->searchContest($search)
                    ->sort($sort)
                    ->upcomingContest()
                    ->publicContest()
                    ->get();

            foreach ($contest as $key => $_contest) {
                $_contest->banner = ($_contest->banner != NULL && $_contest->banner != '') ? url($this->contestThumbImageUploadPath . $_contest->banner) : '';
                foreach ($_contest->userInContest as $_userInContest) {
                    // If user already participated then unset that contest data
                    if ($_userInContest->id == $request->user()->id) {
                        unset($contest[$key]);
                        continue;
                    }
                }
                if (isset($contest[$key])) {
                    unset($contest[$key]->userInContest);
                }
            }

            return response()->json([
                        'status' => '1',
                        'message' => 'Contest listing.',
                        'data' => [
                            'contestDetail' => array_values($contest->toArray())
                        ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error listing event.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Users Contest Listing and listing in which user participated and invitation of contest from friend.
     *
     * @param Request $request The current request
     * @return \App\Contest Currently authorized user's created contest, participated contest and contest invitation
     * @throws Exception If there was an error
     * @see \App\Contest
     * @Post("/")
     * @Transaction({
     *     @Request( {} ),
     *     @Response( {"status": "1","message": "User's Contest listing.","data": {"myContest": [{"id": 8,"game_id": 1,"contest_type_id": 1,"level_id": 4,"contest_name": "Contest #2","contest_fees": 100,"contest_start_time": "2017-12-17 04:54:34","contest_end_time": "2017-12-19 18:54:37","privacy": "public","url": null,"prize_distribution_plan_id": 1,"contest_max_participants": "10","participated": 0,"contest_video_link": "https://twitch.com/gali-cricket/","prize": 0,"created_by": 2,"created_at": "2017-10-18 12:22:15","updated_at": "2017-10-18 12:22:15","deleted_at": null,"startIn": "1241:54:20"}],"participatedContest": [{"id": 2,"game_id": 1,"contest_type_id": 1,"level_id": 4,"contest_name": "Contest #1","contest_fees": 1000,"contest_start_time": "2017-10-26 04:54:34","contest_end_time": "2017-12-27 18:54:37","privacy": "friend-only","url": null,"prize_distribution_plan_id": 1,"contest_max_participants": "15","participated": 0,"contest_video_link": "https://twitch.com/gali-cricket/","prize": 0,"created_by": 1,"created_at": "2017-10-17 12:34:53","updated_at": "2017-10-17 12:34:53","deleted_at": null,"startIn": "00:00:00","pivot": {"user_id": 2,"contest_id": 2,"rank": null}}],"contestInvitation": [{"id": 37,"game_id": 1,"contest_type_id": 4,"level_id": 3,"contest_name": "Contest #2","contest_fees": 100,"roster_cap_amount": 10,"contest_start_time": "2017-12-17 04:54:34","contest_end_time": "2017-12-19 18:54:37","privacy": "private","prize_distribution_plan_id": 1,"contest_max_participants": "10","participated": 0,"contest_video_link": "https://twitch.com/gali-cricket/","prize": 0,"created_by": 2,"status": "upcoming","created_at": "2017-10-27 10:39:04","updated_at": "2017-10-27 10:39:04","deleted_at": null,"pivot": {"user_id": 2,"contest_id": 37,"invitation_status": "pending"}}}} ),
     *     @Response( {"status": "0",'message': 'Error listing user\'s contest detail.','code' => $e->getStatusCode()} )
     * })
     */
    public function userContestListing(Request $request) {
        try {

            $myContest = $request->user()->contest()->exceptPastContest()->orderBy('contest_start_time', 'desc')->get()->each(function ($myContest) {
                $difference = Helpers::differenceInHIS($myContest->contest_start_time, date('Y-m-d H:i:s')); // Get date difference in Hours / minutes and seconds
                $myContest->startIn = ($difference['hours'] < 0) ? '00:00:00' : $difference['hours'] . ':' . sprintf("%02d", $difference['minutes']) . ':' . sprintf("%02d", $difference['seconds']);
                $myContest->banner = ($myContest->banner != NULL && $myContest->banner != '') ? url($this->contestThumbImageUploadPath . $myContest->banner) : '';
            });
            $participatedContest = $request->user()->participatedInContest()->exceptPastContest()->orderBy('contest_start_time', 'desc')->get()->each(function ($participatedContest) {
                $difference = Helpers::differenceInHIS($participatedContest->contest_start_time, date('Y-m-d H:i:s')); // Get date difference in Hours / minutes and seconds
                $participatedContest->startIn = ($difference['hours'] < 0) ? '00:00:00' : $difference['hours'] . ':' . sprintf("%02d", $difference['minutes']) . ':' . sprintf("%02d", $difference['seconds']);
                $participatedContest->banner = ($participatedContest->banner != NULL && $participatedContest->banner != '') ? url($this->contestThumbImageUploadPath . $participatedContest->banner) : '';
            });

            $contestInvitation = $request->user()->contestListOfInvitation()->upcomingContest()->wherePivot('invitation_status', Config::get('constant.PENDING_INVITATION_STATUS'))->orderBy('contest_start_time', 'desc')->get()->each(function ($contestInvitation) {
                $contestInvitation->banner = ($contestInvitation->banner != NULL && $contestInvitation->banner != '') ? url($this->contestThumbImageUploadPath . $contestInvitation->banner) : '';
                $difference = Helpers::differenceInHIS($contestInvitation->contest_start_time, date('Y-m-d H:i:s')); // Get date difference in Hours / minutes and seconds
                $contestInvitation->startIn = ($difference['hours'] < 0) ? '00:00:00' : $difference['hours'] . ':' . sprintf("%02d", $difference['minutes']) . ':' . sprintf("%02d", $difference['seconds']);
            });

            return response()->json([
                        'status' => '1',
                        'message' => 'User\'s Event listing detail.',
                        'data' => [
                            'myContest' => $myContest,
                            'participatedContest' => $participatedContest,
                            'contestInvitation' => $contestInvitation
                        ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error listing user\'s event detail.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Get contest detail which is created by user to update.
     *
     * @param Request $request The current request
     * @return \App\Contest Users \App\Contest object for given contest id
     * @throws Exception If there was an error
     * @see \App\Contest
     * @Get("/{id}")
     * @Transaction({
     *     @Request(),
     *     @Response( {"status": "1","message": "Contest detail.","data": {"contest": {"id": 44,"game_id": 1,"contest_type_id": 4,"level_id": 3,"contest_name": "Contest Name","contest_fees": 100,"roster_cap_amount": 0,"contest_start_time": "2017-12-17 04:54:34","contest_end_time": "2017-12-19 18:54:37","privacy": "public","prize_distribution_plan_id": 1,"contest_min_participants": 1,"contest_max_participants": "10","participated": 0,"banner": "7c7Jw7vzPhcIarKSTfL6.jpg","contest_video_link": "https://twitch.com/gali-cricket/","prize": 0,"created_by": 2,"status": "upcoming","created_at": "2017-10-30 11:33:13","updated_at": "2017-10-30 11:33:13","deleted_at": null}}} ),
     *     @Response( {"status": "0",'message': 'Contest not found.','code' => 404} )
     *     @Response( {"status": "0",'message': 'You can\'t perform this action.','code' => 400} )
     *     @Response( {"status": "0",'message': 'Error getting contest detail.','code' => $e->getStatusCode()} )
     * })
     */
    public function getContest(Request $request, $id) {
        try {
            $user = $request->user();
            $contest = $user->contest()->where('id', $id)->first();

            if ($contest === null) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Event not found.',
                            'code' => 404
                ]);
            }

            if (Helpers::differenceInHIS($contest->contest_start_time, Carbon::now())['difference'] <= Config::get('constant.CONTEST_LOCK_TIME_IN_SECOND')) {
                return response()->json([
                            'status' => '0',
                            'message' => 'You can\'t perform this action.',
                            'code' => 400
                ]);
            }
            $contest->banner = ($contest->banner != NULL && $contest->banner != '') ? url($this->contestThumbImageUploadPath . $contest->banner) : '';
            return response()->json([
                        'status' => '1',
                        'message' => 'Event detail.',
                        'data' => [
                            'contest' => $contest
                        ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error getting event detail.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Update a cotest of the currently authorized user.
     *
     * @param int $id The id of the contest.
     * @return \App\Contest The currently authorized users selected contest, with the new updated values
     * @throws Exception If there was an error
     * @see \App\Contest
     * @Post("/{id}")
     * @Parameters({
     *     @Parameter("privacy", description="It is public or not", type="string"),
     *     @Parameter("contest_name", description="Contest name", type="string"),
     *     @Parameter("prize_distribution_plan_id", description="Prize distribution plan id", type="integer"),
     *     @Parameter("contest_min_participants", description="Min number of participant to start contest.", type="integer")
     *     @Parameter("contest_max_participants", description="Max number of participant that can take part.", type="integer")
     *     @Parameter("contest_video_link", description="Twitch url of game.", type="string")
     *     @Parameter("contest_start_time", description="Start date time.", type="date")
     *     @Parameter("contest_end_time", description="Contest End date time.", type="date")
     *     @Parameter("banner", description="Contest banner image.", type="image")
     *     @Parameter("invited_user_list", description="Invited user's id if contest is private.", type="array")
     * })
     * @Transaction({
     *     @Request( {"privacy": "private","contest_name": "Contest #1","prize_distribution_plan_id": "1","contest_min_participants": "1","contest_max_participants": "10","contest_video_link": "https://twitch.com/gali-cricket/","contest_start_time": "2017-12-17 04:54:34","contest_end_time": "2017-12-19 18:54:37","banner": Choose File,"invited_user_list": "3" } ),
     *     @Response( {"status": "1","message": "Contest updated successfully.","data": {"contest": {"id": 44,"game_id": 1,"contest_type_id": 4,"level_id": 3,"contest_name": "Contest Name (Updated)","contest_fees": 100,"roster_cap_amount": 0,"contest_start_time": "2017-11-24 04:54:34","contest_end_time": "2017-12-19 18:54:37","privacy": "private","prize_distribution_plan_id": "1","contest_min_participants": "100","contest_max_participants": "1000","participated": 0,"banner": "http://local.batting-app.com:8012/uploads/contest/thumb/d7Osxihv0ROeqt7XMFbA.jpg","contest_video_link": "https://www.twitch.com/cricket","prize": 0,"created_by": 2,"status": "upcoming","created_at": "2017-10-30 11:33:13","updated_at": "2017-10-31 09:35:38","deleted_at": null}}} ),
     *     @Response( {"status": "0",'message': 'Contest not found.','code' => 404} )
     *     @Response( {"status": "0",'message': 'You can\'t perform this action.','code' => 400} )
     *     @Response( {"status": "0",'message': 'You can\'t update contest as a private.','code' => 400} )
     *     @Response( {"status": "0",'message': 'Error updating contest.','code' => $e->getStatusCode()} )
     *     @Response( {"status": "0","message": "Please enter minimum 1 as minimum participant.","code": "400"} )
     *     @Response( {"status": "0","message": "Something went wrong while invite user. Please try again.","code": "400"} )
     * })
     */
    public function updateContest(Request $request, $id) {
        DB::beginTransaction();
        try {

            $rules = [
                'privacy' => 'required',
                'contest_start_time' => 'required|date|date_format:Y-m-d H:i:s|after:' . Carbon::now()->addHours(3),
                'contest_end_time' => 'required|date|after:contest_start_time',
                'prize_distribution_plan_id' => 'required|integer',
                'contest_min_participants' => 'required|integer',
                'contest_max_participants' => 'required|integer|min:1|greater_than_or_equal_field:contest_min_participants',
                'banner' => 'image'
            ];

            if (isset($request->league_id)) {
                $rules['league_id'] = 'required|integer';
            } else {
                $rules['contest_name'] = 'required|max:255|regex:/^(?![0-9]*$)[a-zA-Z0-9 ]+$/';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => $validator->messages()->all()[0],
                            'code' => '20100'
                ]);
            }

            $allContest = Contest::whereNotIn('status', ['completed', 'pending'])->where('id', '<>', $id)->get();

            foreach ($allContest as $value) {
                if ($value->contest_name == $request->contest_name && $value->contest_start_time == $request->contest_start_time && $value->contest_end_time == $request->contest_end_time && $value->game_id == $request->game_id) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'Event already exists',
                                'code' => '400'
                    ]);
                }
            }

            $user = $request->user();
            $contest = $user->contest()->where('id', '=', $id)->first();

            if ($contest === null) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Event not found.',
                            'code' => 404
                ]);
            }

            // Only enable to update if contest has remain more than 3 hours to start
            if (Helpers::differenceInHIS($contest->contest_start_time, Carbon::now())['difference'] <= Config::get('constant.CONTEST_LOCK_TIME_IN_SECOND')) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You can\'t perform this action.',
                            'code' => 400
                ]);
            }

            // User can\'t update contest as a public from private
            if ($contest->privacy == 'public' && $request->privacy == 'private') {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You can\'t update event as a private.',
                            'code' => 400
                ]);
            }

            // Minimun contest has greater than or equal value to max winner
            $prizeDistributioPlan = PrizeDistributionPlan::where('id', $request->prize_distribution_plan_id)->where('status', Config::get('constant.ACTIVE_STATUS_FLAG'))->first();

            if($prizeDistributioPlan == null || ($prizeDistributioPlan && $prizeDistributioPlan->status == Config::get('constant.DELETED'))){
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Invalid input parameter',
                            'code' => '400'
                ]);
            }

            if (!$prizeDistributioPlan || ($prizeDistributioPlan->winner != '0' && $prizeDistributioPlan->winner > $request->contest_min_participants)) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Please enter minimum ' . $prizeDistributioPlan->winner . ' as minimum participant.',
                            'code' => 400
                ]);
            }

            $data = $request->only('privacy', 'contest_name', 'prize_distribution_plan_id', 'contest_min_participants', 'contest_max_participants', 'contest_video_link', 'contest_start_time', 'contest_end_time');

            $league = (isset($request->league_id)) ? League::find($request->league_id) : null;
            if ($league !== null) {

                if ($league->id != $contest->league_id) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'Event is not associated with given league.',
                                'code' => '400'
                    ]);
                }
                $data['league_min_participants'] = $league->league_min_participants;

                $contestEndDate = date('Y-m-d', strtotime($request->contest_end_time));
                if ($contestEndDate > $league->league_end_date) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'Event end date equal or before league end date.',
                                'code' => '400'
                    ]);
                }
                $data = $request->only('privacy', 'prize_distribution_plan_id', 'contest_min_participants', 'contest_max_participants', 'contest_video_link', 'contest_start_time', 'contest_end_time');
            }

            // Save contest banner
            if (Input::file()) {
                $file = Input::file('banner');

                if (!empty($file)) {
                    $fileName = str_random(20) . '.' . $file->getClientOriginalExtension();
                    $pathOriginal = public_path($this->contestOriginalImageUploadPath . $fileName);
                    $pathThumb = public_path($this->contestThumbImageUploadPath . $fileName);

                    if (!file_exists(public_path($this->contestOriginalImageUploadPath)))
                        File::makeDirectory(public_path($this->contestOriginalImageUploadPath), 0777, true, true);
                    if (!file_exists(public_path($this->contestThumbImageUploadPath)))
                        File::makeDirectory(public_path($this->contestThumbImageUploadPath), 0777, true, true);

                    // created instance
                    $img = Image::make($file->getRealPath());

                    $img->save($pathOriginal);
                    // resize the image to a height of $this->contestThumbImageHeight and constrain aspect ratio (auto width)
                    if ($img->height() < 500) {
                        $img->resize(null, $img->height(), function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($pathThumb);
                    } else {
                        $img->resize(null, $this->contestThumbImageHeight, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($pathThumb);
                    }

                    if (!($contest->banner == '' || $contest->banner == 'default.png')) {
                        $imageOriginal = public_path($this->contestOriginalImageUploadPath . $contest->banner);
                        $imageThumb = public_path($this->contestThumbImageUploadPath . $contest->banner);
                        if (file_exists($imageOriginal)) {
                            File::delete($imageOriginal);
                        }
                        if (file_exists($imageThumb)) {
                            File::delete($imageThumb);
                        }
                    }
                    $data['banner'] = $fileName;
                }
            }
            $data['contest_video_link'] = (!empty($data['contest_video_link'])) ? Helpers::addhttp($data['contest_video_link']) : null;
            $contest->fill(array_filter($data));
            $contest->save();

            $contest_users = ContestUser::where('contest_id', $id)->pluck('user_id')->toArray();

            if ($league === null) {
                // Notofication to newly invited user for contest
                $newInvitedUser = [];
                if ($request->privacy == 'private') {
                    $request->invited_user_list = (!empty($request->invited_user_list)) ? explode(',', $request->invited_user_list) : [];
                    foreach ($request->invited_user_list as $invitedUser) {
                        $invitedUserData = User::find($invitedUser);
                        if(!ContestInvitedUser::where('contest_id',$contest->id)->where('user_id',$invitedUser)->exists() && $invitedUserData !== null && $invitedUserData->status == Config::get('constant.NOT_DELETED')) {
                            ContestInvitedUser::create([
                                'contest_id' => $contest->id,
                                'user_id' => $invitedUser,
                                'status' => 'pending'
                            ]);
                            array_push($newInvitedUser, $invitedUser);
                        } else {
                            DB::rollback();
                            return response()->json([
                                        'status' => '0',
                                        'message' => 'Something went wrong while inviting user.',
                                        'code' => 400
                            ]);
                        }
                    }
                }
                $user_device = (!empty($newInvitedUser)) ? UserDevice::whereIn('user_id', $newInvitedUser)->get() : [];
                if (count($user_device) > 0) {
                    foreach ($user_device as $device) {
                        $user_details = User::find($device->user_id);

                        if ($user_details->notification_status == 1) {
                            $data = array(
                                'notification_status' => 2,
                                'message' => 'You are invited to join ' . ucfirst($contest->contest_name),
                                'contest_id' => $contest->id,
                                'notification_type' => 'ContestInvitation'
                            );
                            Helpers::pushNotificationForiPhone($device->device_token, $data);
                        }
                    }
                }
            }
            if (count($contest_users) > 0) {
                $user_device = UserDevice::whereIn('user_id', $contest_users)->get();

                if (count($user_device) > 0) {
                    foreach ($user_device as $device) {
                        $user_details = User::find($device->user_id);

                        if ($user_details->notification_status == 1) {
                            $data = array(
                                'notification_status' => 2,
                                'message' => 'Event updated',
                                'contest_id' => $id,
                                'notification_type' => 'ContestUpdated'
                            );
                            Helpers::pushNotificationForiPhone($device->device_token, $data);
                        }
                    }
                }
            }
            DB::commit();

            $contest->banner = ($contest->banner != NULL && $contest->banner != '') ? url($this->contestThumbImageUploadPath . $contest->banner) : '';

            return response()->json([
                        'status' => '1',
                        'message' => 'Event updated successfully.',
                        'data' => [
                            'contestDetail' => $contest
                        ]
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Error updating event detail.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Accept Contest invitation.
     *
     * @param Request $request The current request.
     * @return
     * @throws Exception If there was an error
     * @see \App\Contest
     * @see \App\ContestInvitedUser
     * @see \App\ContestUser
     * @see \App\User
     * @Post("/")
     * @Parameters({
     *     @Parameter("contest_id", description="Contest id of invitation", type="integer"),
     * })
     * @Transaction({
     *     @Request( {"contest_id": "44"} ),
     *     @Response( {"status": "1","message": "Success.","data": []} ),
     *     @Response( {"status": "0",'message': 'You don\'t have invitation for this contest.','code' => 404} )
     *     @Response( {"status": "0",'message': 'You have already accepted invitation.','code' => 400} )
     *     @Response( {"status": "0",'message': 'You are already participant in this contest.','code' => 400} )
     *     @Response( {"status": "0",'message': 'Timeout.','code' => 423} )
     *     @Response( {"status": "0",'message': 'Contest exceeded max participant.','code' => 400} )
     *     @Response( {"status": "0","message": "You don\'t have enough balance to participate in contest.","code": "400"} )
     *     @Response( {"status": "0","message": "Error.","code": "$e->getStatusCode()"} )
     * })
     */
    public function acceptInvitation(Request $request) {
        DB::beginTransaction();
        try {
            $contestInvitation = ContestInvitedUser::where('contest_id', $request->contest_id)->where('user_id', $request->user()->id)->first();

            // Invitation not found for current user for given contest
            if ($contestInvitation === null) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You don\'t have invitation for this event.',
                            'code' => 404
                ]);
            }

            // Already accepted invitation
            if ($contestInvitation->invitation_status == Config::get('constant.ACCEPTED_INVITATION_STATUS')) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You have already accepted invitation.',
                            'code' => 400
                ]);
            }

            if ($contestInvitation->invitation_status == Config::get('constant.PENDING_INVITATION_STATUS')) {
                $contest = Contest::find($request->contest_id);

                $isParticipant = $contest->userInContest()->existUserInContest($request->user()->id)->count();
                // Already participant
                if ($isParticipant != 0) {

                    // Updated invitation status of user
                    $contestInvitation->fill(array_filter(['invitation_status' => 'accepted']));
                    $contestInvitation->save();
                    DB::commit();
                    return response()->json([
                                'status' => '0',
                                'message' => 'You are already participant in this event.',
                                'code' => 400
                    ]);
                }

                // If contest has only 1 hour or less time remaining to start
                if (Helpers::differenceInHIS($contest->contest_start_time, Carbon::now())['difference'] <= Config::get('constant.ROSTER_LOCK_TIME_IN_SECOND')) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'Timeout.',
                                'code' => 423
                    ]);
                }

                // Contest is cancelled or live or completed
                if ($contest->status != Config::get('constant.UPCOMING_CONTEST_STATUS')) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'Event is no more available to participate.',
                                'code' => 404
                    ]);
                }

                if ($contest->contest_max_participants <= $contest->participated) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'Event exceeded max participant.',
                                'code' => 400
                    ]);
                }

                // Increased participated user's count in  contest
                $participated = $contest->participated + 1;

                $contest->fill(array_filter(['participated' => $participated]));
                $contest->save();

                // Updated invitation status of user
                $contestInvitation->fill(array_filter(['invitation_status' => 'accepted']));
                $contestInvitation->save();

                // Inserted as contest participatant
                ContestUser::create([
                    'contest_id' => $contest->id,
                    'user_id' => $request->user()->id
                ]);

                DB::commit();
                return response()->json([
                            'status' => '1',
                            'message' => 'Success.',
                            'data' => []
                ]);
            }
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Error.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Delete Contest invitation.
     *
     * @param Request $request The current request.
     * @return
     * @throws Exception If there was an error
     * @see \App\Contest
     * @see \App\ContestInvitedUser
     * @Post("/")
     * @Parameters({
     *     @Parameter("contest_id", description="Contest id of invitation", type="integer"),
     * })
     * @Transaction({
     *     @Request( {"contest_id": "44"} ),
     *     @Response( {"status": "1","message": "Invitation deleted successfully.","data": []} ),
     *     @Response( {"status": "0",'message': 'You don\'t have pending invitation for this contest.','code' => 404} )
     *     @Response( {"status": "0","message": "Error.","code": "$e->getStatusCode()"} )
     * })
     */
    public function deleteInvitation(Request $request) {
        try {
            $contestInvitation = ContestInvitedUser::where('contest_id', $request->contest_id)->where('user_id', $request->user()->id)->pendingInvitation()->first();

            // Invitation not found for current user for given contest
            if ($contestInvitation === null) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You don\'t have pending invitation for this event.',
                            'code' => 404
                ]);
            }

            // Updated invitation status of user and delete
            $contestInvitation->fill(array_filter(['invitation_status' => 'deleted']));
            $contestInvitation->save();
            $contestInvitation->delete();

            return response()->json([
                        'status' => '1',
                        'message' => 'Invitation deleted successfully.',
                        'data' => []
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
     * Participate in contest.
     *
     * @param Request $request The current request.
     * @return
     * @throws Exception If there was an error
     * @see \App\Contest
     * @see \App\ContestUser
     * @Post("/")
     * @Parameters({
     *     @Parameter("contest_id", description="Contest id to participate", type="integer"),
     * })
     * @Transaction({
     *     @Request( {"contest_id": "44"} ),
     *     @Response( {"status": "1","message": "success.","data": []} ),
     *     @Response( {"status": "0",'message': 'Contest is no more available to participate.','code' => 404} )
     *     @Response( {"status": "0",'message': 'You are already participant in this contest.','code' => 404} )
     *     @Response( {"status": "0",'message': 'You can participate in private contest by invitation only.','code' => 400} )
     *     @Response( {"status": "0",'message': 'Timeout.','code' => 423} )
     *     @Response( {"status": "0",'message': 'Contest exceeded max participant.','code' => 400} )
     *     @Response( {"status": "0",'message': 'You don\'t have enough balance to participate in contest.','code' => 404} )
     *     @Response( {"status": "0","message": "Error.","code": "$e->getStatusCode()"} )
     * })
     */
    public function participateInContest(Request $request) {
        DB::beginTransaction();
        try {
            $contest = Contest::find($request->contest_id);

            if ($contest === null) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Event data not found.',
                            'code' => 404
                ]);
            }

            // Contest is cancelled or live or completed
            if ($contest->status != Config::get('constant.UPCOMING_CONTEST_STATUS')) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Event is no more available to participate.',
                            'code' => 404
                ]);
            }

            if ($contest->privacy == Config::get('constant.PRIVATE_CONTEST')) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You can participate in private event by invitation only.',
                            'code' => 400
                ]);
            }

            if (Helpers::differenceInHIS($contest->contest_start_time, Carbon::now())['difference'] <= Config::get('constant.CONTEST_LOCK_TIME_IN_SECOND')) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Timeout.',
                            'code' => 423
                ]);
            }

            if (User::getCurrentUser($request->user()->id)->points == null || User::getCurrentUser($request->user()->id)->points < $contest->contest_fees) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You don\'t have enough balance to participate in event.',
                            'code' => 400
                ]);
            }

            $isParticipant = $contest->userInContest()->existUserInContest($request->user()->id)->count();

            // Not participant
            $participated = $contest->participated;
            if ($isParticipant > 0)
            {
                $contestUser = ContestUser::where('contest_id', $contest->id)->where('user_id', $request->user()->id)->first();
                if($contestUser && $contestUser->is_paid == 1)
                {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'You have already paid for this event.',
                                'code' => 400
                    ]);
                }
            } else {
                if ($contest->contest_max_participants <= $contest->participated) {
                    DB::rollback();
                    return response()->json([
                                'status' => '0',
                                'message' => 'Event exceeded max participant.',
                                'code' => 400
                    ]);
                }
                // Inserted as contest participatant
                $contest->userInContest()->attach(['user_id' => $request->user()->id]);

                // Increased participated user's count in  contest
                $participated = $participated + 1;
            }

            // Increase prize of contest
            $prize = $contest->prize + $contest->contest_fees;

            $contest->fill(array_filter(['participated' => $participated, 'prize' => $prize]));
            $contest->save();

            // Deduct balance from user wallet amount
            $user = User::getCurrentUser($request->user()->id);
            $points = $user->points - $contest->contest_fees;
            $user->fill(array_filter(['points' => $points]));
            $user->save();

            ContestUser::where('contest_id', $contest->id)->where('user_id', $request->user()->id)->update(['is_paid' => 1]);

            DB::commit();

            return response()->json([
                        'status' => '1',
                        'message' => 'Success.',
                        'data' => []
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Error.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * User's contest history.
     *
     * @param Request $request The current request.
     * @return
     * @throws Exception If there was an error
     * @see \App\Contest
     * @Post("/")
     * @Parameters({
     * })
     * @Transaction({
     *     @Request(),
     *     @Response( {"status": "1","message": "User's Contest History.","data": {"participatedContest": [{"id": 1,"game_id": 1,"contest_type_id": 1,"level_id": 1,"contest_name": "Public Contest #1","contest_fees": 100,"roster_cap_amount": 9,"contest_start_time": "2017-11-17 10:40:50","contest_end_time": "2017-11-17 10:50:00","privacy": "public","prize_distribution_plan_id": 1,"contest_min_participants": 1,"contest_max_participants": 10,"participated": 1,"banner": "","contest_video_link": "www.twitch.com/public-contest","prize": 100,"created_by": 2,"updated_by": null,"status": "completed","created_at": "2017-11-17 07:20:43","updated_at": "2017-11-17 07:20:43","deleted_at": null,"pivot": {"user_id": 2,"contest_id": 1,"rank": null}}]}} ),
     *     @Response( {"status": "0","message": "Error in getting contest history.","code": "$e->getStatusCode()"} )
     * })
     */
    public function contestHistory(Request $request) {
        try {
            $participatedContest = $request->user()->participatedInContest()->orderByRaw(DB::raw("FIELD(status, 'pending', 'cancelled', 'completed', 'live')"))->history()->get()->each(function ($participatedContest) {
                $participatedContest->banner = ($participatedContest->banner != NULL && $participatedContest->banner != '') ? url($this->contestThumbImageUploadPath . $participatedContest->banner) : '';
            });

            return response()->json([
                        'status' => '1',
                        'message' => 'User\'s Event History.',
                        'data' => [
                            'participatedContest' => $participatedContest
                        ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error in getting event history.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Contest Result.
     *
     * @param Request $request The current request.
     * @return
     * @throws Exception If there was an error
     * @see \App\User
     * @see \App\Contest
     * @see \App\ContestUser
     * @Post("/")
     * @Parameters({
     *     @Parameter("contest_id", description="Contest id to get result", type="integer"),
     * })
     * @Transaction({
     *     @Request( {"contest_id": "1"} ),
     *     @Response( {"status": "1","message": "Contest result.","data": {"contestResult": [{"id": 2,"name": null,"username": "vandit.kotadiya","email": "vandit.kotadiya@inexture.in","phone": "+11234567890","dob": "1993-06-19","longitude": -118.410468,"latitude": 34.103003,"zipcode": "90210","city": "Beverly Hills","state": "California","country": "United States","user_pic": "","gender": 1,"points": 99700,"roster_app_amount": null,"funds": null,"is_admin": 0,"created_at": "2017-10-17 11:54:50","updated_at": "2017-11-17 07:20:44","deleted_at": null,"pivot": {"contest_id": 1,"user_id": 2,"points_win": 97.5,"score": 1456,"rank": 1}}]}} ),
     *     @Response( {"status": "0","message": "Contest data not found.","code": "404000"} )
     *     @Response( {"status": "0","message": "You have not entered this event.","code": "403005"} )
     *     @Response( {"status": "0","message": "Contest is not completed yet.","code": "403026"} )
     *     @Response( {"status": "0","message": "Contest result declare soon.","code": "100001"} )
     *     @Response( {"status": "0","message": "Contest result will declare at 1980-01-01 00:00:00.","code": "100001"} )
     *     @Response( {"status": "0","message": "Error in getting contest history.","code": "$e->getStatusCode()"} )
     * })
     */
    public function contestResult(Request $request) {
        try {
            $contest = Contest::find($request->contest_id);
            if ($contest === null) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Event data not found.',
                            'code' => 404000 //error code: Not found
                ]);
            }

            $isParticipant = $contest->userInContest()->existUserInContest($request->user()->id)->count();
            // Already participant
            if ($isParticipant == 0) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You have not entered this event.',
                            'code' => 403005 //error code: Unauthorized user
                ]);
            }

            // Contest is cancelled or live or completed
            if ($contest->status != Config::get('constant.COMPLETED_CONTEST_STATUS')) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Event is not completed yet.',
                            'code' => 403026 //error code: Unauthorized access error
                ]);
            }

            if ($contest->result_declare_status == Config::get('constant.CONTEST_RESULT_NOT_DECLARED')) {
                if ($contest->result_declare_date == null) {
                    return response()->json([
                                'status' => '0',
                                'message' => 'Event result declare soon.',
                                'code' => 100001 //error code: Data pending
                    ]);
                } else {
                    return response()->json([
                                'status' => '0',
                                'message' => 'Event result will declare at ' . $contest->result_declare_date . '.',
                                'code' => 100001 //error code: Data pending
                    ]);
                }
            }
            $contestResult = [];
            if (!is_null($contest->league_id)) {
                $leagueContest = Contest::where('league_id', $contest->league_id)->pluck('id')->toArray();

                $contestResult = $contest->userInContest()->orderBy('pivot_is_win', 'desc')->orderBy('rank', 'asc')->get()->each(function ($contestResult) use($leagueContest, $contest) {
                    $sum = 0;
                    $userId = $contestResult->id;

                    $sum = ContestUser::whereIn('contest_id', $leagueContest)->where('user_id', $userId)->sum('score');

                    $contestResult->user_pic = ($contestResult->user_pic != NULL && $contestResult->user_pic != '') ? url($this->userThumbImageUploadPath . $contestResult->user_pic) : '';
                    $contestResult->pivot->points_win = (float) $contestResult->pivot->points_win;
                    $contestResult->pivot->score = (float) $contestResult->pivot->score;
                    $contestResult->pivot->league_score = (float) $sum;
                    $contestResult->league_id = (float) $contest->league_id;
                });
            } else {
                $contestResult = $contest->userInContest()->orderBy('pivot_is_win', 'desc')->orderBy('rank', 'asc')->get()->each(function ($contestResult) {
                    $contestResult->user_pic = ($contestResult->user_pic != NULL && $contestResult->user_pic != '') ? url($this->userThumbImageUploadPath . $contestResult->user_pic) : '';
                    $contestResult->pivot->points_win = (float) $contestResult->pivot->points_win;
                    $contestResult->pivot->score = (float) $contestResult->pivot->score;
                    $contestResult->league_id = (float) 0;
                });
            }
            return response()->json([
                        'status' => '1',
                        'message' => 'Event result.',
                        'data' => [
                            'contestResult' => $contestResult
                        ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error in getting event result.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /* contest delete

     * @param Request $request The current request.
     * @return
     * @throws Exception If there was an error
     * @see \App\User
     * @see \App\Contest
     * @see \App\ContestUser
     * @see \App\UserUsedPower
     * @see \App\ContestInvitedUser
     * @Post("/")
     * @Parameters({
     *     @Parameter("contest_id", description="Contest id to get result", type="integer"),
     * })
     * @Transaction({
     *     @Request( {"contest_id": "1"} ),
     *     @Response( {"status": "1","message": "Contest deleted successfully.","data": [] } ),
     *     @Response( {"status": "0","message": "Contest data not found.","code": "404"} )
     *     @Response( {"status": "0","message": "You don\'t have rights to delete this contest.","code": "400"} )
     *     @Response( {"status": "0","message": "You can\'t perform this action.","code": "400"} )
     *     @Response( {"status": "0","message": "Error in getting contest history.","code": "$e->getStatusCode()"} )
     * })
     */

    public function deleteContest(Request $request) {
        $contestDetail = Contest::find($request->contest_id);

        if ($contestDetail === null) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Event not found.',
                        'code' => 404
            ]);
        }

        if ($contestDetail->created_by != $request->user()->id) {
            return response()->json([
                        'status' => '0',
                        'message' => 'You don\'t have rights to delete this event.',
                        'code' => 400
            ]);
        }
        // $difference['hours'] <= 3

        $difference = Helpers::differenceInHIS($contestDetail->contest_start_time, Carbon::now());

        if ($difference['hours'] <= 3) {
            return response()->json([
                        'status' => '0',
                        'message' => 'You can\'t perform this action.',
                        'code' => 400
            ]);
        }
        //Add balance back to user wallet
        DB::beginTransaction();
        try {
            $contestUser = ContestUser::where('contest_id', $request->contest_id)->get();

            foreach ($contestUser as $key => $value) {
                if ($value->is_paid == 1) {
                    $user = User::find($value->user_id);
                    $points = $user->ponits + $contestDetail->contest_fees;

                    User::where('id', $value->user_id)->update(['points' => $points]);
                }
            }

            ContestUser::where('contest_id', $request->contest_id)->forceDelete();
            ContestInvitedUser::where('contest_id', $request->contest_id)->forceDelete();
            Roster::where('contest_id', $request->contest_id)->forceDelete();

            $user_power = UsersUsedPower::where('contest_id', $request->contest_id)->get();
            if (count($user_power) > 0) {
                foreach ($user_power as $key => $value) {
                    UsersPower::where('id', $value->user_power_id)->update(['used' => 0]);
                    $value->forceDelete();
                }
            }

            //UsersUsedPower::where('contest_id',$request->contest_id)->forceDelete();

            Contest::where('id', $request->contest_id)->forceDelete();

            DB::commit();
            return response()->json([
                        'status' => '1',
                        'message' => 'Event Deleted successfully',
                        'data' => []
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Error in deleting event.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * To upload contest score images
     *
     * @param Request $request The current request.
     * @return
     * @throws Exception If there was an error
     * @see \App\ContestScoreImage
     * @see \App\Contest
     * @Post("/")
     * @Parameters({
     *     @Parameter("contest_id", description="Contest id to get result", type="integer"),
     * })
     * @Transaction({
     *     @Request( {"contest_id": "1","image[0]":file,"image[1]":file} ),
     *     @Response( {"status": "1","message": "Images uploaded successfully.","data": [] ),
     *     @Response( {"status": "0","message": "Contest data not found.","code": "404"} )
     *     @Response( {"status": "0","message": "You can\'t perform this action.","code": "400"} )
     *     @Response( {"status": "0","message": "You have uploaded maximum images for this contest.","code": "400"} )
     *     @Response( {"status": "0","message": "You can only upload '. $canUploadImageCount .' image(s) for this contest.","code": "400"} )
     *     @Response( {"status": "0","message": "Error while featching images.","code": "$e->getStatusCode()"} )
     * })
     */
    public function contestScoreImages(Request $request) {
        // file_put_contents(public_path('saveContestImage.txt'), print_r($request->all(), true));
        try {
            $validator = Validator::make($request->all(), [
                        'contest_id' => 'required|integer',
                        'image' => 'required',
                        'image.*' => 'image|mimes:jpeg,png,jpg|max:5120'
            ]);

            if ($validator->fails()) {
                $errorMessage = $validator->messages()->all()[0];
                if ((strpos($validator->messages()->all()[0], "image.")) !== FALSE) {
                    $errorMessage = 'The images must be a type jpeg, png or jpg.';
                }
                return response()->json([
                            'status' => '0',
                            'message' => $errorMessage,
                            'code' => '20100'
                ]);
            }

            DB::beginTransaction();
            $contest = $request->user()->contest()->where('id', $request->contest_id)->first();

            if ($contest === null) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Event data not found.',
                            'code' => 404
                ]);
            }

            if ($contest->status != Config::get('constant.PENDING_CONTEST_STATUS')) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You can\'t perform this action.',
                            'code' => 400
                ]);
            }

            $contestImagesCount = $contest->contestScoreImages()->where('status', 0)->count();

            $canUploadImageCount = Config::get('constant.MAX_CONTEST_SCORE_IMAGE') - $contestImagesCount;
            $canUploadImageCount = ($canUploadImageCount <= 0 ? 0 : $canUploadImageCount);
            if ($canUploadImageCount == 0) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You have uploaded maximum images for this event.',
                            'code' => 400
                ]);
            }

            if (count($request->image) > $canUploadImageCount) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You can only upload ' . $canUploadImageCount . ' image(s) for this event.',
                            'code' => 400
                ]);
            }

            foreach ($request->image as $image) {
                $fileName = 'contest_score_' . str_random(20) . '.' . $image->getClientOriginalExtension();

                $pathOriginal = public_path($this->contestScoreOriginalImageUploadPath . $fileName);
                $pathThumb = public_path($this->contestScoreThumbImageUploadPath . $fileName);

                if (!file_exists(public_path($this->contestScoreOriginalImageUploadPath)))
                    File::makeDirectory(public_path($this->contestScoreOriginalImageUploadPath), 0777, true, true);
                if (!file_exists(public_path($this->contestScoreThumbImageUploadPath)))
                    File::makeDirectory(public_path($this->contestScoreThumbImageUploadPath), 0777, true, true);

                // created instance
                $img = Image::make($image->getRealPath());

                $img->save($pathOriginal);
                // resize the image to a height of $this->contestThumbImageHeight and constrain aspect ratio (auto width)
                if ($img->height() < 500) {
                    $img->resize(null, $img->height(), function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($pathThumb);
                } else {
                    $img->resize(null, $this->contestThumbImageHeight, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($pathThumb);
                }

                $data = [
                    'contest_id' => $request->contest_id,
                    'contest_image' => $fileName
                ];
                $contestScoreImage = new ContestScoreImages(array_filter($data));
                $contestScoreImage->save();
            }
            DB::commit();
            return response()->json([
                        'status' => '1',
                        'message' => 'Images uploaded successfully',
                        'data' => []
            ]);
        } catch (Exception $e) {
            DB::rollback();
            //
            $contest = Contest::find($request->contest_id);

            $user_device = UserDevice::where('user_id', $contest->created_by)->get();

            if (count($user_device) > 0) {
                foreach ($user_device as $device) {

                    $user_detail = User::find($contest->created_by);

                    if ($user_detail->notification_status == 1) {
                        $data = array(
                            'notification_status' => 2,
                            'message' => 'Please upload image for Event ' . ucfirst($contest->contest_name) . ' to rate and score event',
                            'contest_id' => $contest->id,
                            'notification_type' => 'UploadScoringImage'
                        );
                        Helpers::pushNotificationForiPhone($device->device_token, $data);
                    }
                }
            }
            //
            return response()->json([
                        'status' => '0',
                        'message' => 'Error while uploading images.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Get contest score image.
     *
     * @param Request $request The current request
     * @return \App\ContestScoreImage Users \App\ContestScoreImage object for given contest id
     * @throws Exception If there was an error
     * @see \App\ContestScoreImage
     * @Get("/{id}")
     * @Transaction({
     *     @Request(),
     *     @Response( {"status": "1","message": "Success","data": {"contestScoreImages": [{"id": 1,"contest_id": 170,"contest_image": "http://local.batting-app.com:8012/uploads/contest_score/thumb/contest_score_gTeVUFhGOHpeYHQOHuQu.jpg","is_rejected": 0,"created_at": "2018-01-16 14:24:27","updated_at": "2018-01-16 14:24:27"}]}} ),
     *     @Response( {"status": "0",'message': 'Contest data not found.','code' => 404} )
     *     @Response( {"status": "0",'message': 'Error while featching image list.','code' => $e->getStatusCode()} )
     * })
     */
    public function getContestScoreImage(Request $request, $id) {
        try {
            $contest = $request->user()->contest()->where('id', $id)->first();

            if ($contest === null) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Event data not found.',
                            'code' => 404
                ]);
            }

            // Get contest score images
            $contestScoreImages = $contest->contestScoreImages()->get()->each(function ($contestScoreImages) {
                $contestScoreImages->contest_image = ($contestScoreImages->contest_image != NULL && $contestScoreImages->contest_image != '') ? url($this->contestScoreThumbImageUploadPath . $contestScoreImages->contest_image) : '';
                $contestScoreImages->status = intval($contestScoreImages->status);
            });

            return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => [
                            'contestScoreImages' => $contestScoreImages
                        ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                        'status' => '0',
                        'message' => 'Error while featching image list.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * To update contest score images
     *
     * @param Request $request The current request.
     * @return
     * @throws Exception If there was an error
     * @see \App\ContestScoreImage
     * @see \App\Contest
     * @Post("/")
     * @Parameters({
     *     @Parameter("contest_id", description="Contest id to get result", type="integer"),
     * })
     * @Transaction({
     *     @Request( {"contest_id": "1","image[id(image)]":file,"image[id(image)]":file} ),
     *     @Response( {"status": "1","message": "Images uploaded successfully.","data": [] ),
     *     @Response( {"status": "0","message": "Contest data not found.","code": "404"} )
     *     @Response( {"status": "0","message": "You can\'t perform this action.","code": "400"} )
     *     @Response( {"status": "0","message": "You have uploaded maximum images for this contest.","code": "400"} )
     *     @Response( {"status": "0","message": "You can only upload '. $canUploadImageCount .' image(s) for this contest.","code": "400"} )
     *     @Response( {"status": "0","message": "Error while featching images.","code": "$e->getStatusCode()"} )
     * })
     */
    public function updateContestScoreImage(Request $request) {

        try {
            $validator = Validator::make($request->all(), [
                        'contest_id' => 'required|integer',
                        'image' => 'required',
                        'image.*' => 'image|mimes:jpeg,png,jpg|max:5120'
            ]);

            if ($validator->fails()) {
                $errorMessage = $validator->messages()->all()[0];
                if ((strpos($validator->messages()->all()[0], "image.")) !== FALSE) {
                    $errorMessage = 'The images must be a type jpeg, png or jpg.';
                }
                return response()->json([
                            'status' => '0',
                            'message' => $errorMessage,
                            'code' => '20100'
                ]);
            }

            DB::beginTransaction();
            $contest = $request->user()->contest()->where('id', $request->contest_id)->first();

            if ($contest === null) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'Event data not found.',
                            'code' => 404
                ]);
            }

            if ($contest->status != Config::get('constant.PENDING_CONTEST_STATUS')) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You can\'t perform this action.',
                            'code' => 400
                ]);
            }

            $contestImagesCount = $contest->contestScoreImages()->where('status', 0)->count();
            $canUploadImageCount = Config::get('constant.MAX_CONTEST_SCORE_IMAGE') - $contestImagesCount;
            $canUploadImageCount = ($canUploadImageCount <= 0 ? 0 : $canUploadImageCount);
            if ($canUploadImageCount == 0) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You have uploaded maximum images for this event.',
                            'code' => 400
                ]);
            }

            if (count($request->image) > $canUploadImageCount) {
                DB::rollback();
                return response()->json([
                            'status' => '0',
                            'message' => 'You can only upload ' . $canUploadImageCount . ' image(s) for this contest.',
                            'code' => 400
                ]);
            }

            foreach ($request->image as $key => $image) {
                $fileName = 'contest_score_' . str_random(20) . '.' . $image->getClientOriginalExtension();

                $pathOriginal = public_path($this->contestScoreOriginalImageUploadPath . $fileName);
                $pathThumb = public_path($this->contestScoreThumbImageUploadPath . $fileName);

                if (!file_exists(public_path($this->contestScoreOriginalImageUploadPath)))
                    File::makeDirectory(public_path($this->contestScoreOriginalImageUploadPath), 0777, true, true);
                if (!file_exists(public_path($this->contestScoreThumbImageUploadPath)))
                    File::makeDirectory(public_path($this->contestScoreThumbImageUploadPath), 0777, true, true);

                // created instance
                $img = Image::make($image->getRealPath());

                $img->save($pathOriginal);
                // resize the image to a height of $this->contestThumbImageHeight and constrain aspect ratio (auto width)
                if ($img->height() < 500) {
                    $img->resize(null, $img->height(), function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($pathThumb);
                } else {
                    $img->resize(null, $this->contestThumbImageHeight, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($pathThumb);
                }

                $data = [
                    'contest_id' => $request->contest_id,
                    'contest_image' => $fileName,
                    'id' => $key,
                    'status' => '0'
                ];

                $contestImage = ContestScoreImages::where('id', $data['id'])->where('contest_id', $data['contest_id'])->first();
                if ($contestImage != null && $contestImage != '') {
                    ContestScoreImages::where('id', $data['id'])->where('status', '2')->update(["contest_image" => $fileName, "status" => '0']);
                } else {
                    unset($data['id']);
                    ContestScoreImages::create($data);
                }
            }
            DB::commit();
            return response()->json([
                        'status' => '1',
                        'message' => 'Images uploaded successfully',
                        'data' => []
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Error while featching images.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }

}
