<?php

namespace App\Http\Controllers\Api;

use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\KlashCoinPack;
use DB;
use Redirect;
use Response;
use Helpers;
use Config;
use Image;
use File;
use Mail;
use Session;
use Validator;
use Crypt;
use \stdClass;
use Carbon\Carbon;
use JWTAuth;
use JWTAuthException;

class KlashCoinPackController extends Controller
{
    public function __construct()
    {
        $this->objKlashCoinPack = new KlashCoinPack();

        $this->klashCoinPackOriginalImageUploadPath = Config::get('constant.KLASH_COIN_PACK_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->klashCoinPackThumbImageUploadPath = Config::get('constant.KLASH_COIN_PACK_THUMB_IMAGE_UPLOAD_PATH');
        $this->klashCoinPackImageThumbImageHeight = Config::get('constant.KLASH_COIN_PACK_THUMBNAIL_IMAGE_HEIGHT');
        $this->klashCoinPackImageThumbImageWidth = Config::get('constant.KLASH_COIN_PACK_THUMBNAIL_IMAGE_WIDTH');
        
        $this->defaultImage = Config::get('constant.DEFAULT_IMAGE_PATH');
    }

    /**
     * Klash Coin Pack Listing.
     *
     * @param Request $request The current request
     * @return \App\KlashCoinPack Klash Coin Pack Listing for specified game.
     * @throws Exception If there was an error
     * @see \App\KlashCoinPack
     * @Post("/")
     * @Transaction({
     *     @Request( none ),
     *     @Response( {"status":1,"message":"Klash coins pack listing fetched successfully","data":[{"id":2,"name":"Coins Machine 1","number_of_klash_coins":130,"cost_to_user":"1250","image":"http:\/\/battingapp.localhost.com\/uploads\/klashCoinPack\/original\/klash_coins_pack_image_1529573819.png","status":"active","created_at":"2018-06-21 09:36:59","updated_at":"2018-06-21 09:36:59","deleted_at":null},{"id":1,"name":"Coin Machine","number_of_klash_coins":120,"cost_to_user":"1205","image":"http:\/\/battingapp.localhost.com\/uploads\/klashCoinPack\/original\/klash_coins_pack_image_1529573781.jpg","status":"active","created_at":"2018-06-21 09:36:21","updated_at":"2018-06-21 09:36:21","deleted_at":null}]} ),
     *     @Response( {"status":"0","message":"No Records Found","data":[]} )
     *     @Response( {"status": "0",'message': 'Error on klash coins pack listing.'} )
     * })
     */
    public function klashCoinPackListing(Request $request)
    {
        $outputArray = [];
        $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 0;
        try
        {
            $filters = [];
            $filters['status'] = Config::get('constant.ACTIVE');
            $paginate = (!empty($pageNo) && $pageNo > 0) ? true : false;
            $getKlashCoinsPack = $this->objKlashCoinPack->getAll($filters, $paginate);
            if($getKlashCoinsPack && !empty($getKlashCoinsPack) && $getKlashCoinsPack->count() > 0)
            {
                $outputArray['status'] = '1';
                $outputArray['message'] = trans('apimessages.klashcoinpack_listing_fetched_successfully');
                $outputArray['data'] = array();
                $statusCode = 200;
                
                foreach ($getKlashCoinsPack as $coinKey => $coinValue)
                {
                    $coinValue->image = (!empty($coinValue->image) && $coinValue->image != '' && file_exists($this->klashCoinPackOriginalImageUploadPath.$coinValue->image) ) ? url($this->klashCoinPackOriginalImageUploadPath.$coinValue->image) : url($this->defaultImage);
                }
                $outputArray['data'] = $getKlashCoinsPack;
            }
            else
            {
                $outputArray['status'] = '0';
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $statusCode = 200;
                $outputArray['data'] = array();
            } 
            return response()->json($outputArray, $statusCode);
        } 
        catch (Exception $e) 
        {
            $outputArray['status'] = '0';
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
    }

    /**
     * Klash Coin Pack Listing.
     *
     * @param Request $request The current request
     * @return \App\KlashCoinPack Klash Coin Pack Listing for specified game.
     * @throws Exception If there was an error
     * @see \App\KlashCoinPack
     * @Get
     * @Transaction({
     *     @Request(),
     *     @Response( {"status":"1","message":"apimessages. purchasing_klash_coins_pack_successfully"} ),
     *     @Response( {"status": "0",'message': 'No Records Found'} )
     *     @Response( {"status": "0",'message': 'User have not enough point'} )
     *     @Response( {"status": "0",'message': 'Error on klash coins pack listing.'} )
     * })
     */
    public function purchasingKlashCoinsPack(Request $request)
    {
        $outputArray = [];
        $userData = Auth::user();
        try
        {                        
            DB::beginTransaction();
            $rules = [
                'pack_id' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) 
            {
                DB::rollback();
                $outputArray['status'] = '0';
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);                
            }
            $klashCoinsPackDetails  = KlashCoinPack::find($request->pack_id);
            if($klashCoinsPackDetails && !empty($klashCoinsPackDetails) && $userData && !empty($userData) && $userData->points > 0 && $klashCoinsPackDetails->cost_to_user > 0)
            {
                if($userData->points > $klashCoinsPackDetails->cost_to_user)
                {
                    $userData->points = $userData->points - $klashCoinsPackDetails->cost_to_user;
                    $userData->virtual_currency = $userData->virtual_currency + $klashCoinsPackDetails->number_of_klash_coins;
                    $response = $userData->save();
                    if($response)
                    {
                        DB::commit();
                        $outputArray['status'] = '1';
                        $outputArray['message'] = trans('apimessages.purchasing_klash_coins_pack_successfully');
                        $statusCode = 200;
                        $outputArray['data']['userDetail'] = $userData;                        
                    }
                    else
                    {
                        $outputArray['status'] = '0';
                        $outputArray['message'] = trans('apimessages.default_error_msg');
                        $statusCode = 200;
                        $outputArray['data'] = new \stdClass();
                    }
                }
                else
                {
                    $outputArray['status'] = '0';
                    $outputArray['message'] = trans('apimessages.user_have_not_enough_point');
                    $statusCode = 200;
                    $outputArray['data'] = new \stdClass();
                }
            }
            else
            {
                $outputArray['status'] = '0';
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $statusCode = 200;
                $outputArray['data'] = new \stdClass();
            }            
        } 
        catch (Exception $e) 
        {
            $outputArray['status'] = '0';
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }

}
