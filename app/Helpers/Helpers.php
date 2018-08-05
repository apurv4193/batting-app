<?php

namespace App\Helpers;

use Config;
use App\Game;
use App\Players;
use App\GamesPlayers;
use App\ContestType;
use App\ContestLevel;
use App\ContestScoreImages;
use App\PrizeDistributionPlan;
use App\UsersUsedPower;
use App\Item;

Class Helpers {

    public static function getGames() {
        return Game::where('status',0)->get();
    }

    public static function getPlayersByGame($gameId) {
        $players = GamesPlayers::join('players','games_players.player_id', '=', 'players.id')->where('games_players.game_id', $gameId)->get(['players.name','players.id','games_players.cap_amount']);
        return $players;
    }

    public static function getContestImages($contestId) {
        $imageList = ContestScoreImages::where('contest_id', $contestId)->get();
        return $imageList;
    }

    public static function getCapAmount($contestId) {
        $capAmount = ContestType::where('id', $contestId)->first();
        return ($capAmount !== null) ? $capAmount->contest_cap_amount : 0;
    }

    // Get Player cap amount for given game
    public static function getPlayerCapAmount($playerId, $gameId) {
        $playerCapAmount = GamesPlayers::where('player_id', $playerId)->where('game_id', $gameId)->first();
        return ($playerCapAmount !== null) ? $playerCapAmount->cap_amount : 0;
    }

    public static function getPlayersGames($id) {

        $games = GamesPlayers::select('game_id')->where('player_id',$id)->get();
        return Game::whereNotIn('id',$games->toArray())->where('status',0)->get();
    }

    public static function getPlayersGamesEdit($player_id,$id) {

        $games = GamesPlayers::select('game_id')->where('player_id',$player_id)->where('game_id','!=',$id)->get();
        return Game::whereNotIn('id',$games->toArray())->where('status',0)->get();
    }

    public static function getContestTypes() {
        return ContestType::all();
    }

    public static function getContestLevels() {
        return ContestLevel::all();
    }

    public static function getPrizeDistributionPlans() {
        return PrizeDistributionPlan::where('status',0)->get();
    }

    public static function getItems() {
        return Item::all();
    }

    public static function getItemsById($id){
        return Item::where('id', '!=', $id)->get();
    }
    
    /**
     * Get player cap amount sum of player for given game
     * @param [Array] $playerArray
     * @param [integer] $gameId
     * @return [decimal] cap amount sum of player for given game
     */
    public static function getCapAmountSumofPlayer($playerArray, $gameId) {
        return GamesPlayers::whereIn('player_id', $playerArray)->where('game_id', $gameId)->sum('cap_amount');
    }

    public static function get_shorten_url($longUrl) {
        $response = $longUrl;

        // Get API key from : http://code.google.com/apis/console/
        $apiKey = 'AIzaSyDKMWbyLwqowXwHHWiqqXhd5sl3yNBnIMw';

        $postData = array('longUrl' => $longUrl);
        $jsonData = json_encode($postData);

        $curlObj = curl_init();

        curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url?key=' . $apiKey);
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlObj, CURLOPT_HEADER, 0);
        curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
        curl_setopt($curlObj, CURLOPT_POST, 1);
        curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

        $result = curl_exec($curlObj);
        $json = json_decode($result);

        if (empty($json->error)) {
            $responseUrl = $json->id;
            $response = (preg_match("@^https?://@", $responseUrl) == true) ? preg_replace('/^https(?=:\/\/)/i', 'http', $responseUrl) : $responseUrl;
        }
        return $response;
    }

    public static function pushNotificationForiPhone($token,$message) {

        $msg = [
            'body' => $message['message'],
            'title' => '',
            'data' => $message,
            'icon' => 'myicon', 
            'sound' => 'mySound'
        ];
        $fields = [
            'to' => $token, // expecting a single ID
            'notification' => $msg
        ];
        $headers = [
            'Authorization: key = AIzaSyBfH49rD8MOrRkKSWz5F__mEORFy2B85Bs',
            'Content-Type: application/json'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        // echo $result;
        curl_close($ch);

    }

    public static function pushNotificationForAndroid($tokens, $title, $message) {
        $url = "https://fcm.googleapis.com/fcm/send";

        //Creating the notification array.
        $notification = array('title' => $title, 'body' => $message);

        //This array contains, the token and the notification. The 'to' attribute stores the token.
        $arrayToSend = array('registration_ids' => $tokens, 'notification' => $notification);

        $fields = array(
            'registration_ids' => $tokens,
            'body' => 'hey'
        );

        $headers = array(
            'Authorization:key = AIzaSyBfH49rD8MOrRkKSWz5F__mEORFy2B85Bs',
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrayToSend));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    public static function in_array_r($needle, $haystack, $strict = false) {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && self::in_array_r($needle, $item, $strict))) {
                return true;
            }
        }
        return false;
    }
    
    public static function in_array($needle, $haystack, $strict = false) {
        foreach ($haystack as $key => $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && self::in_array($needle, $item, $strict))) {
                return $key;
            }
        }
        return false;
    }

    /**
     * To get date difference of two dates in hours / minutes / seconds
     * @param [date] $toDate
     * @param [date] $fromDate
     * @return array Array of hours, minutes and seconds
     */
    public static function differenceInHIS($toDate, $fromDate) {
        $diff  = strtotime($toDate) - strtotime($fromDate);
        $hours = floor( $diff / (60 * 60) );
        $minutes = floor( ($diff - $hours * (60 * 60)) / 60 );
        $seconds = ($diff - ( ($hours * (60 * 60)) + ($minutes * 60) ) );
        
        return [
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $seconds,
            'difference' => $diff
        ];
    }
    
    /**
     * Get player from contest player
     * @param [integer] $contestTypeId
     * @return integer Max player number
     */
    public static function getContestTypePlayer($contestTypeId) {
        $contestType = ContestType::find($contestTypeId);
        $count = 0;
        if($contestType->type == '1V1') {
            $count = Config::get('constant.1V1_CONTEST_PLAYER');
        } else if($contestType->type == '2V2') {
            $count = Config::get('constant.2V2_CONTEST_PLAYER');
        } else if($contestType->type == '4V4') {
            $count = Config::get('constant.4V4_CONTEST_PLAYER');
        } else if($contestType->type == '6V6') {
            $count = Config::get('constant.6V6_CONTEST_PLAYER');
        }
        
        return $count;
    }
    
    /**
     * To check that participant is eligible to access roster or save or update roster
     * 
     * @param integer $remainingTime [Remaining time to start contest in seconds]
     * @param integer $userId [Participant id]
     * @param integer $contest [Contest id]
     * @return boolean [true if eligible]
     */
    public static function eligibleToAccessRoster($remainingTime, $userId, $contest) {
       
        if($remainingTime <= Config::get('constant.ROSTER_LOCK_TIME_IN_SECOND')) {
            $usedPower = UsersUsedPower::where('user_id', $userId)->where('contest_id', $contest->id)->first();
            
            if($usedPower === null) {
                return false;
            } else if($usedPower->remaining_contest_substitution > 0 && $remainingTime < 0) {
                $remainingContestSubstitution = $usedPower->remaining_contest_substitution - 1;
                $usedPower->fill(array_filter(['remaining_contest_substitution' => $remainingContestSubstitution]));
                $usedPower->save();
                return true;
            } else if($usedPower->remaining_pre_contest_substitution > 0 && $remainingTime >= 0 && $remainingTime <= Config::get('constant.ROSTER_LOCK_TIME_IN_SECOND')) {
                $remainingPreContestSubstitution = $usedPower->remaining_pre_contest_substitution - 1;
                $usedPower->fill(array_filter(['remaining_pre_contest_substitution' => $remainingPreContestSubstitution]));
                $usedPower->save();
                return true;
            } else {
                return false;
            }
        }
        return true;
    }
    
    /**
     * To add http if not exist in url
     * @param string $url
     * @return string
     */
    public static function addhttp($url) {
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }
        return $url;
    }
}
