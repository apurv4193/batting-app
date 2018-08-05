<?php

namespace App\Http\Controllers\Admin;

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

class KlashCoinPackController extends Controller
{
    public function __construct()
    {
        $this->middleware('IsAdmininstrator');
        $this->objKlashCoinPack = new KlashCoinPack();

        $this->klashCoinPackOriginalImageUploadPath = Config::get('constant.KLASH_COIN_PACK_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->klashCoinPackThumbImageUploadPath = Config::get('constant.KLASH_COIN_PACK_THUMB_IMAGE_UPLOAD_PATH');
        $this->klashCoinPackImageThumbImageHeight = Config::get('constant.KLASH_COIN_PACK_THUMBNAIL_IMAGE_HEIGHT');
        $this->klashCoinPackImageThumbImageWidth = Config::get('constant.KLASH_COIN_PACK_THUMBNAIL_IMAGE_WIDTH');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $klashCoinPacks = $this->objKlashCoinPack->getAll();
        return view('admin.ListKlashCoinPack', compact('klashCoinPacks'));
    }

    /**
     * add a newly.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function add()
    {
        return view('admin.EditKlashCoinPack');
    }

    /**
     * Show the form for creating a new resource or update edit resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        $requestData = [];
        if( !empty(Input::get('id')) && Input::get('id') != null && Input::get('id') != '')
        {
           $this->validate(request(), [
                'name' => 'required|max:100|regex:/^(?![0-9]*$)[a-zA-Z0-9 ]+$/',
                'number_of_klash_coins' => 'required|integer',
                'cost_to_user' => 'required|numeric',
                'image' => 'image|mimes:jpeg,jpg,bmp,png,gif|max:52400'
            ]);
        }
        else
        {
            $this->validate(request(), [
                'name' => 'required|max:100|regex:/^(?![0-9]*$)[a-zA-Z0-9 ]+$/',
                'number_of_klash_coins' => 'required|integer',
                'cost_to_user' => 'required|numeric',
                'image' => 'required|image|mimes:jpeg,jpg,bmp,png,gif|max:52400'
            ]);
        }
        $requestData['id'] = e(input::get('id'));
        $id = $requestData['id'];
        $requestData['name'] = e(input::get('name'));
        $requestData['number_of_klash_coins'] = e(input::get('number_of_klash_coins'));
        $requestData['cost_to_user'] = e(input::get('cost_to_user'));
        $requestData['status'] = e(input::get('status'));

        $hiddenImage = e(Input::get('hidden_image'));
        $requestData['image'] = $hiddenImage;

        if (Input::file())
        {
            $file = Input::file('image');
            if (isset($file) && !empty($file))
            {
                $fileName = 'klash_coins_pack_image_' . time() . '.' . $file->getClientOriginalExtension();
                $pathOriginal = public_path($this->klashCoinPackOriginalImageUploadPath . $fileName);
                $pathThumb = public_path($this->klashCoinPackThumbImageUploadPath . $fileName);
                
                if (!file_exists(public_path($this->klashCoinPackOriginalImageUploadPath)))
                    File::makeDirectory(public_path($this->klashCoinPackOriginalImageUploadPath), 0777, true, true);
                if (!file_exists(public_path($this->klashCoinPackThumbImageUploadPath)))
                    File::makeDirectory(public_path($this->klashCoinPackThumbImageUploadPath), 0777, true, true);
                
                Image::make($file->getRealPath())->save($pathOriginal);
                Image::make($file->getRealPath())->resize($this->klashCoinPackImageThumbImageWidth, $this->klashCoinPackImageThumbImageHeight)->save($pathThumb);

                //Deleting Local Files
                if (!empty($hiddenImage) && $hiddenImage != '')
                {
                    File::delete($this->klashCoinPackOriginalImageUploadPath.$hiddenCatLogo, $this->klashCoinPackThumbImageUploadPath.$hiddenCatLogo);
                }
                $requestData['image'] = $fileName;
            }
        }
        $response = $this->objKlashCoinPack->insertUpdate($requestData);
        if ($response)
        {
            return Redirect::to("admin/klash-coin-pack")->with('success', trans('adminlabels.kalsh_coin_pack_save_success_msg'));
        }
        else
        {
            return Redirect::to("admin/klash-coin-pack")->with('error', trans('adminlabels.common_error'));
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try
        {
            $data = KlashCoinPack::find($id);
            if($data)
            {
                return view('admin.EditKlashCoinPack', compact('data'));
            }
            else
            {
                return Redirect::to("admin/klash-coin-pack")->with('error', trans('adminlabels.recordnotexist'));
            }
        } catch (DecryptException $e) {
            return Redirect::to("admin/klash-coin-pack")->with('error', trans('adminlabels.common_error'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $kalshCoinPackData = KlashCoinPack::find($id);
        if($kalshCoinPackData)
        {
            $response = $kalshCoinPackData->delete();
            if ($response)
            {
                return Redirect::to("admin/klash-coin-pack")->with('success', trans('adminlabels.kalsh_coin_pack_delete_success_msg'));
            }
            else
            {
                return Redirect::to("admin/klash-coin-pack")->with('error', trans('adminlabels.common_error'));
            }
        }
        else
        {
            return Redirect::to("admin/klash-coin-pack")->with('error', trans('adminlabels.recordnotexist'));
        }
    }
}
