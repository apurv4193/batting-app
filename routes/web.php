<?php

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */

//Route::get('/', function () {
//    return view('auth.login');
//});

Route::get('/', 'HomeController@checkLogin');

Auth::routes();

Route::any('admin/dashboard', 'Admin\DashboardController@index');

Route::any('password/reset-success', 'Auth\ResetPasswordController@resetSuccess');
/*
 * User Module Routes
 */
Route::get('/home', 'UsersController@getUser')->name('home');
Route::post('/admin/list-user-ajax', 'UsersController@listUserAjax');
Route::get('/admin/edit-user/{id}', array('as' => 'user.edit', 'uses' => 'UsersController@editUser'));
Route::post('/admin/save-user', 'UsersController@saveUser');
Route::get('/admin/users', 'UsersController@getUser');

/*
 * Game Module Routes
 */
Route::get('/admin/games', 'Admin\GameController@getGames');
Route::post('/admin/list-game-ajax', 'Admin\GameController@listGameAjax');
Route::get('/admin/add-game', 'Admin\GameController@addGame');
Route::get('/admin/edit-game/{id}', array('as' => 'game.edit', 'uses' => 'Admin\GameController@editGame'));
Route::post('/admin/save-game', 'Admin\GameController@saveGame');

/**
 * Item Module Routes
 */
Route::get('/admin/items', 'Admin\ItemController@getItem');
Route::post('/admin/list-item-ajax', 'Admin\ItemController@listItemAjax');
Route::get('/admin/edit-item/{id}', array('as' => 'item.edit', 'uses' => 'Admin\ItemController@editItem'));
Route::post('/admin/save-item', 'Admin\ItemController@saveItem');

/*
 * Game Case Module Routes
 */
Route::get('/admin/gamecase', 'Admin\GameCaseController@getGameCase');
Route::post('/admin/list-gamecase-ajax', 'Admin\GameCaseController@listGameCaseAjax');
Route::get('/admin/edit-game-case/{id}', array('as' => 'gamecase.edit', 'uses' => 'Admin\GameCaseController@editGameCase'));
Route::post('/admin/save-game-case', 'Admin\GameCaseController@saveGameCaseItem');
Route::get('/admin/getItems', 'Admin\GameCaseController@getItems');
/*
 * Game Case Bunddle Module Routes
 */

Route::get('/admin/gamecase_bundle', 'Admin\GameCaseBunddleController@getGameCaseBundle');
Route::post('/admin/list-gamecase_bundle-ajax', 'Admin\GameCaseBunddleController@listGameCaseAjax');
Route::get('/admin/add-gamecase_bundle', 'Admin\GameCaseBunddleController@addGameCaseBundle');
Route::get('/admin/edit-gamecase_bundle/{id}', array('as' => 'gamecase_bundle.edit', 'uses' => 'Admin\GameCaseBunddleController@editGameCaseBundle'));
Route::post('/admin/save-gamecase_bundle', 'Admin\GameCaseBunddleController@saveGameCaseBundle');


/*
 * Contest Module Routes
 */
Route::get('/admin/contests', 'Admin\ContestController@getContests');
Route::post('/admin/list-contest-ajax', 'Admin\ContestController@listContestAjax');
Route::post('/admin/list-contest-score-ajax', 'Admin\ContestController@listContestScoreAjax');
Route::get('/admin/add-contest', 'Admin\ContestController@addContest');
Route::get('/admin/edit-contest/{id}', array('as' => 'contest.edit', 'uses' => 'Admin\ContestController@editContest'));
Route::get('/admin/edit-contest-score/{id}', array('as' => 'contest_score.edit', 'uses' => 'Admin\ContestController@editContestScore'));
Route::post('/admin/save-contest', 'Admin\ContestController@saveContest');
Route::post('/admin/save-contest-score', 'Admin\ContestController@saveContestScore');
Route::get('/admin/contest_score', 'Admin\ContestController@getContestScore');
Route::get('/admin/send-request', 'Admin\ContestController@sendRequest');
Route::get('/admin/get-contest-images' , 'Admin\ContestController@getContestImages');
Route::post('/admin/perform-custom-action-ajax', 'Admin\ContestController@performCustomActionAjax');
/*
 * Ads Module Routes
 */
Route::get('/admin/ads', 'Admin\AdsController@getads');
Route::post('/admin/list-ads-ajax', 'Admin\AdsController@listAdsAjax');
Route::get('/admin/add-ads', 'Admin\AdsController@addAds');
Route::get('/admin/edit-ads/{id}', array('as' => 'ads.edit', 'uses' => 'Admin\AdsController@editAds'));
Route::post('/admin/save-ads', 'Admin\AdsController@saveAds');

/*
 * Klash Coin Pack
 */

Route::get('/admin/klash-coin-pack', 'Admin\KlashCoinPackController@index');
Route::get('/admin/add-klash-coin-pack', 'Admin\KlashCoinPackController@add');
Route::get('/admin/edit-klash-coin-pack/{id}', 'Admin\KlashCoinPackController@edit');
Route::post('/admin/save-klash-coin-pack', 'Admin\KlashCoinPackController@save');
Route::get('/admin/delete-klash-coin-pack/{id}', 'Admin\KlashCoinPackController@destroy');

/*
 * Roster Module Routes
 */
Route::get('/admin/rosters', 'Admin\RosterController@getRosters');
Route::post('/admin/list-roster-ajax', 'Admin\RosterController@listRosterAjax');
Route::get('/admin/add-roster', 'Admin\RosterController@addRoster');
Route::get('/admin/edit-roster/{id}', array('as' => 'roster.edit', 'uses' => 'Admin\RosterController@editRoster'));
Route::post('/admin/save-roster', 'Admin\RosterController@saveRoster');

/*
 * Players Module Routes
 */
Route::get('/admin/players' , 'Admin\PlayerController@getPlayers');
Route::post('/admin/list-players-ajax', 'Admin\PlayerController@listPlayersAjax');
Route::post('/admin/list-games-ajax', 'Admin\PlayerController@listGamesAjax');
Route::get('/admin/add-players', 'Admin\PlayerController@addPlayers');
Route::get('/admin/edit-players/{id}', array('as' => 'players.edit', 'uses' => 'Admin\PlayerController@editPlayers'));
Route::post('/admin/save-players', 'Admin\PlayerController@savePlayers');

Route::get('/admin/add-players-games/{id}', 'Admin\PlayerController@addPlayersGames');
Route::get('/admin/edit-players-games/{id}', array('as' => 'players_game.edit', 'uses' => 'Admin\PlayerController@editPlayersGames'));
Route::post('/admin/save-players-games', 'Admin\PlayerController@savePlayersGames');
Route::get('/admin/view-games/{id}', array('as' => 'games.view', 'uses' => 'Admin\PlayerController@getGames'));

/*
 * Team Module Routes
 */
Route::get('/admin/team' , 'Admin\TeamController@getTeams');
Route::post('/admin/list-team-ajax', 'Admin\TeamController@listTeamsAjax');
// Route::post('/admin/list-games-ajax', 'Admin\TeamController@listGamesAjax');
Route::get('/admin/add-team', 'Admin\TeamController@addTeams');
Route::get('/admin/edit-team/{id}', array('as' => 'teams.edit', 'uses' => 'Admin\TeamController@editTeam'));
Route::post('/admin/save-team', 'Admin\TeamController@saveTeams');

Route::get('/admin/add-team-games/{id}', 'Admin\TeamController@addTeamGames');
Route::get('/admin/edit-team-games/{id}', array('as' => 'team_game.edit', 'uses' => 'Admin\TeamController@editTeamGames'));
Route::post('/admin/save-team-games', 'Admin\TeamController@saveTeamGames');


Route::get('/admin/getplayerbygame' , 'Admin\TeamController@getPlayerByGame');
Route::get('/admin/getcapamount' , 'Admin\TeamController@getCapAmount');
/*
 * Roster-Player Routes
 */
Route::get('/admin/roster-players/{id}', array('as' => 'roster.addPlayer', 'uses' => 'Admin\RosterPlayerController@getRosterPlayers'));
Route::post('/admin/list-roster-player-ajax', 'Admin\RosterPlayerController@listRosterPlayersAjax');
Route::post('/admin/save-roster-player', 'Admin\RosterPlayerController@saveRosterPlayer');

/*
 * Prize distribution Plans Routes
 */
Route::get('/admin/prize_distribution' , 'Admin\PrizeController@getPrize');
Route::post('/admin/list-prize-plan-ajax', 'Admin\PrizeController@listPrizeAjax');
Route::get('/admin/add-prize', 'Admin\PrizeController@addPrize');
Route::get('/admin/edit-prize/{id}', array('as' => 'prize.edit', 'uses' => 'Admin\PrizeController@editPrize'));
Route::post('/admin/save-prize','Admin\PrizeController@savePrize');

/*
 * Contest Type Module
 */
Route::get('/admin/contest_type','Admin\ContestTypeController@getContest');
Route::post('/admin/list-contest-type-ajax', 'Admin\ContestTypeController@listContestTypeAjax');
Route::get('/admin/edit-contest-type/{id}', array('as' => 'Contest_Type.edit', 'uses' => 'Admin\ContestTypeController@editContestType'));
Route::post('/admin/save-contest-type','Admin\ContestTypeController@saveContestType');

