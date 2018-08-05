<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Ads;
use Config;
use DB;

class AdsController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {

        $this->adsOriginalImageUploadPath = Config::get('constant.ADS_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->adsThumbImageUploadPath = Config::get('constant.ADS_THUMB_IMAGE_UPLOAD_PATH');
        $this->adsVideoUploadPath = Config::get('constant.ADS_VIDEO_UPLOAD_PATH');
        $this->adsThumbImageHeight = Config::get('constant.ADS_THUMB_IMAGE_HEIGHT');
        $this->adsThumbImageWidth = Config::get('constant.ADS_THUMB_IMAGE_WIDTH');
    }

    /**
     * All Ads Listing 
     *
     * @param Request $request The current request
     * @return \App\Ads Ads Listing.
     * @throws Exception If there was an error
     * @see \App\Ads
     * @Get("/")
     * @Transaction({
     *     @Request( {} ),
     *     @Response( {"status": "1","message": "Success","data": {"ads": {"id": 3,"name": "Second Advertisement","file": "http://local.batting-app.com:8012/uploads/Adsfiles/thumb/ads_F6GYeg0A0KpWf6wPSEV9.png","video_url": "","no_secs_display": 6,"created_at": "2018-01-16 10:06:04","updated_at": "2018-01-16 10:06:04","deleted_at": null}}} ),
     *     @Response( {"status": "0",'message': 'Error listing ads list.','code' => $e->getStatusCode()} )
     * })
     */
    public function getAllAds(Request $request) {
        try {
            if($request->first_time && $request->first_time == 1)
            {
                $ads = Ads::where('default_ad',1)->first();
                if(!is_null($ads))
                {
                    $ads->file = ($ads->file != NULL && $ads->file != '') ? url($this->adsThumbImageUploadPath . $ads->file) : '';
                    $ads->video_url = ($ads->video_url != NULL && $ads->video_url != '') ? url($this->adsVideoUploadPath . $ads->video_url) : '';
                }
                return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => [
                            'ads' => $ads
                        ]
                ]);

            }
            DB::beginTransaction();
            // Already displayed ads
            $ad = $request->user()->ads()->pluck('id')->toArray();
            
            $adsIds = Ads::orderBy('id', 'ASC')->pluck('id');
            
            $adsId = null;
            foreach ($adsIds as $_adsIds) {
                if(!in_array($_adsIds, $ad)) {
                    $adsId = $_adsIds;
                    $request->user()->ads()->attach(['ads_id' => $_adsIds]);
                    break;
                }
            }
            
            if($adsId === null && count($adsIds) > 0) {
                $request->user()->ads()->detach();
                $adsId = $adsIds[0];
                $request->user()->ads()->attach(['ads_id' => $adsId]);
            }
            
            $ads = [];
            if($adsId !== null) {
                $ads = Ads::find($adsId);
                $ads->file = ($ads->file != NULL && $ads->file != '') ? url($this->adsThumbImageUploadPath . $ads->file) : '';
                $ads->video_url = ($ads->video_url != NULL && $ads->video_url != '') ? url($this->adsVideoUploadPath . $ads->video_url) : '';
            }
            
            DB::commit();
            return response()->json([
                        'status' => '1',
                        'message' => 'Success',
                        'data' => [
                            'ads' => $ads
                        ]
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                        'status' => '0',
                        'message' => 'Error listing ads list.',
                        'code' => $e->getStatusCode()
            ]);
        }
    }
}
