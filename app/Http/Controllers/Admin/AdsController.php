<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Ads;
use Redirect;
use File;
use Image;
use Config;
use Response;
use FFMpeg;

class AdsController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('IsAdmininstrator');
        $this->objAds = new Ads();
        $this->adsOriginalImageUploadPath = Config::get('constant.ADS_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->adsThumbImageUploadPath = Config::get('constant.ADS_THUMB_IMAGE_UPLOAD_PATH');
        $this->adsVideoUploadPath = Config::get('constant.ADS_VIDEO_UPLOAD_PATH');
        $this->adsThumbImageHeight = Config::get('constant.ADS_THUMB_IMAGE_HEIGHT');
        $this->adsThumbImageWidth = Config::get('constant.ADS_THUMB_IMAGE_WIDTH');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function getads() {
        return view('admin.ads-list');
    }

    /*
     * Add Content for Ads Module
     */

    public function addAds() {
        return view('admin.add-ads');
    }

    /*
     * Save Ads data 
     */
    public function saveAds() {

        if (isset($inputData['id']) && $inputData['id'] > 0) {
            $this->validate(request(), [
                'name' => 'required|regex:/^(?![0-9]*$)[a-zA-Z0-9 ]+$/',           
                'file' => 'required|mimes:webm,mkv,flv,vob,ogv,ogg,wmv,asf,amv,mp4,m4p,m4v,m4v,3gp,3g2,f4v,f4p,f4a,f4b,mpeg,mpg,m2v,mpv,mpe,png,jpeg,jpg,bmp|max:20480',
                'no_secs_display' => 'required|numeric',
            ]);
        } else {
            $this->validate(request(), [
                'name' => 'required|regex:/^(?![0-9]*$)[a-zA-Z0-9 ]+$/',           
                'no_secs_display' => 'required|numeric',
            ]);
        }

        $inputData = Input::all();
        $ads = $this->objAds->find($inputData['id']);
        
        $hiddenImage = Input::get('hidden_image');
        $hiddenVideoUrl = Input::get('hidden_video_url');
        
        $inputData['file'] = $hiddenImage;
        $inputData['video_url'] = $hiddenVideoUrl;
        if (Input::file()) {
            $file = Input::file('file');
            if (!empty($file)) {
                $imageType = array('image/jpeg', 'image/png', 'image/jpg', 'image/jpe');
                if (in_array($file->getMimeType(), $imageType)) {
                    $filename = 'ads_' . str_random(20) . '.' . $file->getClientOriginalExtension();
                    $pathOriginal = public_path($this->adsOriginalImageUploadPath . $filename);
                    $pathThumb = public_path($this->adsThumbImageUploadPath . $filename);
                    if (!file_exists(public_path($this->adsOriginalImageUploadPath)))
                        File::makeDirectory(public_path($this->adsOriginalImageUploadPath), 0777, true, true);
                    if (!file_exists(public_path($this->adsThumbImageUploadPath)))
                        File::makeDirectory(public_path($this->adsThumbImageUploadPath), 0777, true, true);

                    // created instance
                    $img = Image::make($file->getRealPath());

                    $img->save($pathOriginal);

                    // resize the image to a height of $this->adsThumbImageHeight and constrain aspect ratio (auto width)
                    $height = ($img->height() < 500) ? $img->height() : $this->adsThumbImageHeight;
                    
                    $img->resize(null, $height, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($pathThumb);
                    
                    if ($hiddenImage != '' && $hiddenImage != 'default.png') {
                        $imageOriginal = public_path($this->adsOriginalImageUploadPath . $hiddenImage);
                        $imageThumb = public_path($this->adsThumbImageUploadPath . $hiddenImage);
                        
                        // Unlink original file if exist
                        if (file_exists($imageOriginal)) {
                            File::delete($imageOriginal);
                        }
                        
                        // Unlink thumb file if exist
                        if (file_exists($imageThumb)) {
                            File::delete($imageThumb);
                        }
                        
                        $originalVideo = ($hiddenVideoUrl != '') ? public_path($this->adsVideoUploadPath . $hiddenVideoUrl) : '';
                        // Unlink video if exist
                        if ($originalVideo != '' && file_exists($originalVideo)) {
                            File::delete($originalVideo);
                        }
                        
                    }
                    $inputData['file'] = $filename;
                    $inputData['video_url'] = null;
                }
                
                // Upload video
                $videoType = array('video/mp4', 'video/mpeg', 'video/flv', 'video/mov', 'video/mkv', 'video/3gp');
                if (in_array($file->getMimeType(), $videoType)) {
                    
                    $ffmpeg = FFMpeg\FFMpeg::create();
                    
                    /**
                     * To work with FFMpeg in local
                     * Download ffpmeg and put into C:/Program Files
                     * Give path for FFMpeg binary and FFProbe binary
                     */
                       
//                    $ffmpeg = FFMpeg\FFMpeg::create([
//                                'ffmpeg.binaries' => 'C:/Program Files/ffmpeg-20160506-git-abb69a2-win64-static/bin/ffmpeg.exe', // the path to the FFMpeg binary
//                                'ffprobe.binaries' => 'C:/Program Files/ffmpeg-20160506-git-abb69a2-win64-static/bin/ffprobe.exe', // the path to the FFProbe binary
//                                'timeout' => 3600, // the timeout for the underlying process
//                                'ffmpeg.threads' => 12, // the number of threads that FFMpeg should use
//                    ]);
                    
                    $videoFileName = 'ads_' . str_random(20) . '.' . $file->getClientOriginalExtension();
                    $videoPathOriginal = public_path($this->adsVideoUploadPath . $videoFileName);
                    
                    if (!file_exists(public_path($this->adsVideoUploadPath)))
                        File::makeDirectory(public_path($this->adsVideoUploadPath), 0777, true, true);

                    Input::file('file')->move(public_path($this->adsVideoUploadPath), $videoFileName);

                    $video = $ffmpeg->open(public_path($this->adsVideoUploadPath . $videoFileName));
                    $videostream = $ffmpeg->getFFProbe()
                            ->streams($videoPathOriginal)
                            ->videos()
                            ->first()
                            ->get('duration');
                    
                    $duration = ($videostream > 4 ? 3 : 1);
                    
                    $thumbImageName = 'ads_' . str_random(20) . '.jpg';
                    $thumbOrigialPath = public_path($this->adsOriginalImageUploadPath);
                    $thumbThumbPath = public_path($this->adsThumbImageUploadPath);
                    
                    if (!file_exists($thumbOrigialPath))
                        File::makeDirectory($thumbOrigialPath , 0777, true, true);
                    if (!file_exists($thumbThumbPath))
                        File::makeDirectory($thumbThumbPath, 0777, true, true);
                    
                    $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($duration))
                            ->save($thumbOrigialPath . $thumbImageName);
                    
                    // created instance
                    $img = Image::make($thumbOrigialPath . $thumbImageName);

                    // resize the image to a height of $this->adsThumbImageHeight and constrain aspect ratio (auto width)
                    $height = ($img->height() < 500) ? $img->height() : $this->adsThumbImageHeight;
                    $img->resize(null, $height, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($thumbThumbPath . $thumbImageName);
                    
                    if ($hiddenImage != '' && $hiddenImage != 'default.png') {
                        
                        $videoOriginal = ($hiddenVideoUrl != '') ? public_path($this->adsVideoUploadPath . $hiddenVideoUrl) : '';
                        $videoThumbImage = public_path($this->adsThumbImageUploadPath . $hiddenImage);
                        $videoOriginalImage = public_path($this->adsOriginalImageUploadPath . $hiddenImage);
                        
                        // Unlink video if exists
                        if (!empty($videoOriginal) && file_exists($videoOriginal)) {
                            File::delete($videoOriginal);
                        }
                        
                        // Unlink thumb image if exists
                        if (file_exists($videoThumbImage)) {
                            File::delete($videoThumbImage);
                        }
                        
                        // Unlink image if exists
                        if (file_exists($videoOriginalImage)) {
                            File::delete($videoOriginalImage);
                        }
                    }
                    $inputData['file'] = $thumbImageName;
                    $inputData['video_url'] = $videoFileName;
                }
            }
        }
        
        if (isset($inputData['id']) && $inputData['id'] > 0) {
            $ads->name = $inputData['name'];
            $ads->file = $inputData['file'];
            $ads->video_url = $inputData['video_url'];
            $ads->no_secs_display = $inputData['no_secs_display'];
            $ads->save();
            return Redirect::to("/admin/ads/")->with('success', trans('adminmsg.ads_updated_success'));
        } else {
            $this->objAds->create($inputData);
            return Redirect::to("/admin/ads/")->with('success', trans('adminmsg.ads_created_success'));
        }
    }

    public function editAds($id) {
        $editAds = Ads::find($id);
        if (!$editAds) {
            return Redirect::to("/admin/ads/")->with('error', trans('adminmsg.ads_not_exist'));
        }
        $adsUploadImage = $this->adsThumbImageUploadPath;
        return view('admin.add-ads', compact('editAds', 'adsUploadImage'));
    }

    public function listAdsAjax() {
        $records = array();
        //processing custom actions
        if (Input::get('customActionType') == 'groupAction') {
            $action = Input::get('customActionName');
            $idArray = Input::get('id');
            switch ($action) {
                case "delete":
                    foreach ($idArray as $_idArray) {
                        $adsDelete = Ads::find($_idArray);
                        
                        $imageOriginal = ($adsDelete->file != '' && $adsDelete->file != null) ? public_path($this->adsOriginalImageUploadPath . $adsDelete->file) : '';
                        $imageThumb = ($adsDelete->file != '' && $adsDelete->file != null) ? public_path($this->adsThumbImageUploadPath . $adsDelete->file) : '';
                        $videoFile = ($adsDelete->video_url != '' && $adsDelete->video_url != null) ? public_path($this->adsThumbImageUploadPath . $adsDelete->video_url) : '';
                        
                        // Unlink original image if exists
                        if (!empty($imageOriginal) && file_exists($imageOriginal)) {
                            File::delete($imageOriginal);
                        }
                        
                        // Unlink thumb image if exists
                        if (!empty($imageThumb) && file_exists($imageThumb)) {
                            File::delete($imageThumb);
                        }

                        // Unlink original image if exists
                        if (!empty($videoFile) && file_exists($videoFile)) {
                            File::delete($videoFile);
                        }
                        
                        $adsDelete->delete();
                    }
                    $records["customMessage"] = trans('adminmsg.delete_ads');

                   case "default":
                    // print_r($idArray);die;
                    Ads::where('default_ad',1)->update(['default_ad'=>0]);
                    Ads::where('id',$idArray)->update(['default_ad'=>1]);

                    $records["customMessage"] = trans('adminmsg.default_ads'); 
            }
        }
        $columns = array(
            0 => 'name',
            1 => 'file',
            2 => 'no_secs_display'
        );
        $order = Input::get('order');
        $search = Input::get('search');
        $records["data"] = array();
        $iTotalRecords = Ads::count();
        $iTotalFiltered = $iTotalRecords;
        $iDisplayLength = intval(Input::get('length')) <= 0 ? $iTotalRecords : intval(Input::get('length'));
        $iDisplayStart = intval(Input::get('start'));
        $sEcho = intval(Input::get('draw'));
        $records["data"] = Ads::select('*');
        if (!empty($search['value'])) {
            $val = $search['value'];
            $records["data"]->where(function($query) use ($val) {
                $query->SearchName($val);
                $query->SearchMediaDuration($val);
            });
            // No of record after filtering
            $iTotalFiltered = $records["data"]->where(function($query) use ($val) {
                        $query->SearchName($val);
                        $query->SearchMediaDuration($val);
                    })->count();
        }
        //order by
        foreach ($order as $o) {
            $records["data"] = $records["data"]->orderBy($columns[$o['column']], $o['dir']);
        }
        //limit
        $records["data"] = $records["data"]->take($iDisplayLength)->offset($iDisplayStart)->get();
        if (!empty($records["data"])) {
            foreach ($records["data"] as $key => $_records) {
                $edit = route('ads.edit', $_records->id);
                $Image = [];
                if($_records->file != '' && File::exists(public_path($this->adsThumbImageUploadPath . $_records->file))){
                    $Image = '<img src="' . url($this->adsThumbImageUploadPath . $_records->file) . '"  height="50" width="50"/>';
                } else{
                    $Image = '<img src="' . url($this->adsVideoUploadPath . $_records->file) . '"  height="50" width="50"/>';
                }
                //$records["data"][$key]['file'] = ($_records->file != '' && File::exists(public_path($this->adsThumbImageUploadPath . $_records->file)) ? '<img src="' . url($this->adsThumbImageUploadPath . $_records->file) . '"  height="50" width="50"/>' : '<img src="' . asset('/uploads/user/thumb/default.png') . '" class="user-image" alt="Default Image" height="50" width="50"/>');
                $disable = ($_records->default_ad == 1)?'none':'relative';
                $records["data"][$key]['file'] = ($Image);
                $records["data"][$key]['no_secs_display'] = $_records->no_secs_display . ' ' . trans('adminlabels.ads_list_ads_seconds');
                $records["data"][$key]['action'] = "&emsp;<a href='{$edit}' title='Edit Ads' ><span class='glyphicon glyphicon-edit'></span></a>
                                                    &emsp;<a href='javascript:;' data-id='" . $_records->id . "' class='btn-delete-ads' title='Delete Ads' ><span class='glyphicon glyphicon-trash'></span></a>&emsp;<a href='javascript:;' data-id='" . $_records->id . "' class='btn-default-ads' title='Set Default' style='display:".$disable.";'><span class='glyphicon glyphicon-ok'></span></a>";
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalFiltered;

        return Response::json($records);
    }

}
