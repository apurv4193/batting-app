<?php

/*
  |--------------------------------------------------------------------------
  | API Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register API routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | is assigned the "api" middleware group. Enjoy building your API!
  |
 */
// password reset link api request routes...
Route::post('password/email', 'Api\UsersController@forgotPassword');

//
Route::post('register', 'Api\UsersController@register');
Route::post('auth/token', 'Api\AuthController@getToken');

Route::group(['middleware' => 'jwt.auth'], function () {
    // Home screen
    Route::post('home', 'Api\CommonController@homeScreen');
    Route::post('getGameCasesAndCaseBundle', 'Api\CommonController@getGameCasesAndCaseBundle');
    // User Detail
    Route::get('user-detail', 'Api\UsersController@userDetail');
    Route::post('edit-profile', 'Api\UsersController@editProfile');
    Route::post('edit-notification-status', 'Api\UsersController@editNotificationStatus');

    // Klash Coin Pack
    Route::post('klash-coin-pack-listing', 'Api\KlashCoinPackController@klashCoinPackListing');
    Route::post('purchasing-klash-coins-pack', 'Api\KlashCoinPackController@purchasingKlashCoinsPack');

    // Contest management
    Route::get('create-contest', 'Api\ContestController@createContest');
    Route::post('save-contest', 'Api\ContestController@saveContest');
    Route::post('delete-contest', 'Api\ContestController@deleteContest');
    Route::post('contest-listing', 'Api\ContestController@contestListing');
    Route::post('user-contest-listing', 'Api\ContestController@userContestListing');
    Route::get('get-contest/{id}', 'Api\ContestController@getContest');
    Route::post('update-contest/{id}', 'Api\ContestController@updateContest');
    Route::post('accept-invitation', 'Api\ContestController@acceptInvitation');
    Route::post('delete-invitation', 'Api\ContestController@deleteInvitation');
    Route::post('participate-in-contest', 'Api\ContestController@participateInContest');
    Route::post('contest-history', 'Api\ContestController@contestHistory');
    Route::post('contest-result', 'Api\ContestController@contestResult');
    Route::post('contest-score-images', 'Api\ContestController@contestScoreImages');
    Route::get('get-contest-score-images/{id}', 'Api\ContestController@getContestScoreImage');
    Route::post('update-contest-score-images', 'Api\ContestController@updateContestScoreImage');
    // Friend management
    Route::post('find-friend', 'Api\FriendController@findFriend');
    Route::post('friend-list', 'Api\FriendController@friendList');
    Route::post('add-friend', 'Api\FriendController@addFriend');
    Route::post('delete-friend', 'Api\FriendController@deleteFriend');
    Route::post('accept-friend-request', 'Api\FriendController@acceptFriendRequest');
    // Roster management
    Route::post('roster', 'Api\RosterController@roster');
    Route::post('save-roster', 'Api\RosterController@saveRoster');
    Route::post('update-roster', 'Api\RosterController@updateRoster');
    Route::get('roster-listing/{contestId}', 'Api\RosterController@rosterListing');
    // Player management
    Route::post('player', 'Api\PlayerController@playerListing');
    Route::get('player-detail/{id}', 'Api\PlayerController@playerDetail');
    // Game Case Management
    Route::post('use-power', 'Api\GameCaseController@usePower');
    Route::post('buy-power', 'Api\GameCaseController@buyPower');
    Route::any('get-user-power', 'Api\GameCaseController@getUserPower');
    Route::post('delete-power', 'Api\GameCaseController@deleteUserPower');
    // Chat API
    Route::post('user-chat', 'Api\UsersController@userChat');
    Route::post('create-group', 'Api\UsersController@createChatGroup');
    // Payment API
    Route::post('add-balance', 'Api\PaymentController@addBalance');
    Route::post('payout', 'Api\PaymentController@payToUser');
    Route::post('payment-history', 'Api\PaymentController@paymentHistory');
    Route::get('generate-client-token', 'Api\PaymentController@generateBrainTreeClientToken');
    Route::post('add-virtual-currency', 'Api\PaymentController@addVirtualCurrency');
    Route::get('get-virtual-currency-history', 'Api\PaymentController@getVirtualCurrencyHistory');
    //Logout API
    Route::post('logout', 'Api\UsersController@logout');
    //Ads API
    Route::post('get-all-ads', 'Api\AdsController@getAllAds');
    //Team API
    Route::post('team', 'Api\TeamController@teamListing');
    Route::post('team-detail/{id}', 'Api\TeamController@teamDetail');
    //League API
    Route::post('save-league', 'Api\LeagueController@saveLeague');
    Route::post('update-league/{id}', 'Api\LeagueController@updateLeague');
    Route::post('league-listing', 'Api\LeagueController@leagueListing');
    Route::get('get-league/{id}', 'Api\LeagueController@getLeague');
});
