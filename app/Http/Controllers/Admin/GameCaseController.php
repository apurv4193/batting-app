<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\GameCase;
use App\GameCaseBundle;
use App\GameCaseItems;
use Redirect;
use File;
use Image;
use Config;
use Response;
use Helpers;
use Validator;
use DB;

class GameCaseController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('IsAdmininstrator');
        $this->objGameCase = new GameCase();
        $this->objGameCaseItems = new GameCaseItems();
        $this->objGameCaseBundle = new GameCaseBundle();
        $this->gameCaseOriginalImageUploadPath = Config::get('constant.GAMECASE_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->gameCaseThumbImageUploadPath = Config::get('constant.GAMECASE_THUMB_IMAGE_UPLOAD_PATH');
        $this->gameCaseThumbImageHeight = Config::get('constant.GAMECASE_THUMB_IMAGE_HEIGHT');
        $this->gameCaseThumbImageWidth = Config::get('constant.GAMECASE_THUMB_IMAGE_WIDTH');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function getGameCase() {
        return view('admin.gameCase-list');
    }

    /* get items */
    public function getItems() {
        $itemId = Input::get('itemId');
        $items = Helpers::getItemsById($itemId);
        return $items;
    }

    /* save game in data */

    public function saveGameCaseItem() {
        
        $rules = [
            'photo' => 'image|max:10240',
            'price' => 'required|digits_between:1,6',
            'description' => 'required'
        ];

        if( Input::get('id') != '' && Input::get('alternate_item_id.*') == '' && Input::get('alternate_possibility.*') == '') {
            $rules['possibility.*'] = 'required|regex:/[0-9]?[0-9]?[0-9]?(\.[0-9][0-9]?)?$/';
            $rules['item_id.*'] = 'required';
        }
        
        $this->validate(request(), $rules);

        $postData = Input::all();

        foreach ($postData['possibility'] as $key => $value) {
            if( $postData['possibility'][$key] == 100 ){
                $this->validate(request(), [
                    'alternate_possibility.'.$key => 'regex:/[0-9]?[0-9]?[0-9]?(\.[0-9][0-9]?)?$/'
                ]);
            }
            else {
                $this->validate(request(), [
                    'alternate_possibility.'.$key => 'required|regex:/[0-9]?[0-9]?[0-9]?(\.[0-9][0-9]?)?$/',
                    'alternate_item_id.'.$key => 'required'
                ]);
            }
            
            if( $postData['alternate_item_id'][$key] == $postData['item_id'][$key]){
                return Redirect::back()->withInput(Input::all())->withErrors([
                    'Can not select two same items.'
                ]);
            }
        }
        
        $gameCaseInsert = $this->objGameCase->find($postData['id']);
        $hiddenProfile = Input::get('hidden_profile');        
        $postData['photo'] = $hiddenProfile;
        if (Input::file()) {
            $file = Input::file('photo');
            if (!empty($file)) {
                $fileName = 'gamecase_' . time() . '.' . $file->getClientOriginalExtension();
                $pathOriginal = public_path($this->gameCaseOriginalImageUploadPath . $fileName);
                $pathThumb = public_path($this->gameCaseThumbImageUploadPath . $fileName);

                if (!file_exists(public_path($this->gameCaseOriginalImageUploadPath)))
                    File::makeDirectory(public_path($this->gameCaseOriginalImageUploadPath), 0777, true, true);
                if (!file_exists(public_path($this->gameCaseThumbImageUploadPath)))
                    File::makeDirectory(public_path($this->gameCaseThumbImageUploadPath), 0777, true, true);

                // created instance
                $img = Image::make($file->getRealPath());

                $img->save($pathOriginal);
                // resize the image to a height of $this->contestThumbImageHeight and constrain aspect ratio (auto width)
                if( $img->height() < 500 ){
                    $img->resize(null, $img->height(), function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($pathThumb);
                }
                else {
                    $img->resize(null, $this->gameCaseThumbImageHeight, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($pathThumb);
                }

                if ($hiddenProfile != '' && $hiddenProfile != "default.png") {
                    $imageOriginal = public_path($this->gameCaseOriginalImageUploadPath . $hiddenProfile);
                    $imageThumb = public_path($this->gameCaseThumbImageUploadPath . $hiddenProfile);
                    if (file_exists($imageOriginal) && $hiddenProfile != '') {
                        File::delete($imageOriginal);
                    }
                    if (file_exists($imageThumb) && $hiddenProfile != '') {
                        File::delete($imageThumb);
                    }
                }
                $postData['photo'] = $fileName;
            }
        }
        if (isset($postData['id']) && $postData['id'] > 0) {
            $gameCaseInsert->name = $postData['name'];
            $gameCaseInsert->price = $postData['price'];
            $gameCaseInsert->photo = $postData['photo'];
            $gameCaseInsert->description = $postData['description'];
            $gameCaseInsert->save();

            //game case items insert

           
            DB::beginTransaction();
            try{

                $gameItem = GameCaseItems::where('gamecase_id',$postData['id'])->get(); 
             
                if( !empty($gameItem) && count($gameItem) > 0 ){
                    GameCaseItems::where('gamecase_id',$postData['id'])->forceDelete();
                }

                $i = 0;
                foreach ($postData['item_id'] as $key => $value) {
                    $gameItemData['possibility'] = $postData['possibility'][$key];
                    $gameItemData['item_id'] = $value;
                    $gameItemData['alternate_item_id'] = $postData['alternate_item_id'][$key];
                    $gameItemData['alternate_possibility'] = $postData['alternate_possibility'][$key];
                    $gameItemData['gamecase_id'] = $postData['id'];
                    GameCaseItems::create($gameItemData);
                    $i++;
                }
                DB::commit();
            }
            catch (Exception $e) {
                DB::rollback();
            }

            return Redirect::to("/admin/gamecase/")->with('success', trans('adminmsg.gamecase_updated_success'));
        } else {
            $this->objGameCase->create($postData);
            return Redirect::to("/admin/gamecase/")->with('success', trans('adminmsg.gamecase_created_success'));
        }
    }

    public function editGameCase($id) {
        $gameCase = GameCase::find($id);
        if (!$gameCase) {
            return Redirect::to("/admin/gamecase/")->with('error', trans('adminmsg.game_not_exist'));
        }
        //$gameCaseItems = $this->objGameCase->select('*')->get();
        $gameCaseUploadImage = $this->gameCaseThumbImageUploadPath;
        $items = Helpers::getItems();
        $gameCaseItems = $this->objGameCaseItems->where('gamecase_id',$id)->get();

        return view('admin.add-GameCase', compact('gameCase', 'gameCaseUploadImage', 'gameCaseItems', 'items'));
    }

    public function listGameCaseAjax() {
        $records = array();
        $columns = array(
            0 => 'name',
            1 => 'photo',
            2 => 'price'
        );
        $order = Input::get('order');
        $search = Input::get('search');
        $records["data"] = array();
        $iTotalRecords = GameCase::count();
        $iTotalFiltered = $iTotalRecords;
        $iDisplayLength = intval(Input::get('length')) <= 0 ? $iTotalRecords : intval(Input::get('length'));
        $iDisplayStart = intval(Input::get('start'));
        $sEcho = intval(Input::get('draw'));
        $records["data"] = GameCase::select('*');
        if (!empty($search['value'])) {
            $val = $search['value'];
            $records["data"]->where(function($query) use ($val) {
                $query->SearchName($val)
                    ->SearchPrice($val);
            });
            // No of record after filtering
            $iTotalFiltered = $records["data"]->where(function($query) use ($val) {
                        $query->SearchName($val)
                                ->SearchPrice($val);
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
                $edit = route('gamecase.edit', $_records->id);
                $records["data"][$key]['photo'] = ($_records->photo != '' && File::exists(public_path($this->gameCaseThumbImageUploadPath . $_records->photo)) ? '<img src="' . url($this->gameCaseThumbImageUploadPath . $_records->photo) . '" alt="{{$_records->photo}}"  height="50" width="50">' : '<img src="' . asset('/images/default.png') . '" alt="Default Image" height="50" width="50">');
                $records["data"][$key]['action'] = "&emsp;<a href='{$edit}' title='Edit Game Case' ><span class='glyphicon glyphicon-edit'></span></a>";
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalFiltered;

        return Response::json($records);
    }

}
