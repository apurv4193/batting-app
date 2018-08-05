<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use App\GameCase;
use App\GameCaseBundle;
use Redirect;
use File;
use Image;
use Config;
use Response;

class GameCaseBunddleController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('IsAdmininstrator');
        $this->objGameCase = new GameCase();
        $this->objGameCaseBundle = new GameCaseBundle();
        $this->gameCaseBundleOriginalImageUploadPath = Config::get('constant.GAMECASEBUNDLE_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->gameCaseBundleThumbImageUploadPath = Config::get('constant.GAMECASEBUNDLE_THUMB_IMAGE_UPLOAD_PATH');
        $this->gameCaseBundleThumbImageHeight = Config::get('constant.GAMECASEBUNDLE_THUMB_IMAGE_HEIGHT');
        $this->gameCaseBundleThumbImageWidth = Config::get('constant.GAMECASEBUNDLE_THUMB_IMAGE_WIDTH');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function getGameCaseBundle() {
        return view('admin.gameCaseBundle-list');
    }

    public function addGameCaseBundle() {
        $gameCaseRecords = $this->objGameCase->get();
        $gameCaseId = [];
        return view('admin.add-GameCaseBundle', compact('gameCaseRecords', 'gameCaseId'));
    }

    /* save game case bundle in data */

    public function saveGameCaseBundle() {
        $rules = [
            'name' => ['required',Rule::unique('gamecase_bundle', 'name')->ignore(Input::get('id'))],
            'gamecase_image' => 'image|max:10240',
            'size' => 'required|integer|min:1|max:100',
            'price' => 'required|integer|min:1|digits_between:1,6',
            'description' => 'required'
        ];
        if(Input::get('id') == 0) {
            $rules['gamecase_slug'] = 'required';
        }
        
        $this->validate(request(), $rules);
        
        $data = Input::all();
        
        $hiddenProfile = Input::get('hidden_profile');
        $data['gamecase_image'] = $hiddenProfile;
        $file = Input::file('gamecase_image');
        if (Input::file()) {
            $file = Input::file('gamecase_image');
            if (!empty($file)) {
                $fileName = 'gameCaseBundle_' . time() . '.' . $file->getClientOriginalExtension();
                $pathOriginal = public_path($this->gameCaseBundleOriginalImageUploadPath . $fileName);
                $pathThumb = public_path($this->gameCaseBundleThumbImageUploadPath . $fileName);

                if (!file_exists(public_path($this->gameCaseBundleOriginalImageUploadPath)))
                    File::makeDirectory(public_path($this->gameCaseBundleOriginalImageUploadPath), 0777, true, true);
                if (!file_exists(public_path($this->gameCaseBundleThumbImageUploadPath)))
                    File::makeDirectory(public_path($this->gameCaseBundleThumbImageUploadPath), 0777, true, true);

                // created instance
                $img = Image::make($file->getRealPath());

                $img->save($pathOriginal);
                // resize the image to a height of $this->contestThumbImageHeight and constrain aspect ratio (auto width)
                if( $img->height() < 500 ) {
                    $img->resize(null, $img->height(), function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($pathThumb);
                }
                else {
                    $img->resize(null, $this->gameCaseBundleThumbImageHeight, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($pathThumb);
                }

                if ($hiddenProfile != '' && $hiddenProfile != "default.png") {
                    $imageOriginal = public_path($this->gameCaseBundleOriginalImageUploadPath . $hiddenProfile);
                    $imageThumb = public_path($this->gameCaseBundleThumbImageUploadPath . $hiddenProfile);
                    if (file_exists($imageOriginal) && $hiddenProfile != '') {
                        File::delete($imageOriginal);
                    }
                    if (file_exists($imageThumb) && $hiddenProfile != '') {
                        File::delete($imageThumb);
                    }
                }
                $data['gamecase_image'] = $fileName;
            }
        }
        try {
            $gameCase = array(
                'name' => e(Input::get('name')),
                'gamecase_slug' => e(Input::get('gamecase_slug')),
                'size' => e(Input::get('size')),
                'price' => e(Input::get('price')),
                'description' => e(Input::get('description')),
                'gamecase_image' => $data['gamecase_image'],
            );
            if (isset($data['id']) && $data['id'] > 0) {
                
                $bundleUpdate = $this->objGameCaseBundle->find($data['id']);
                $bundleUpdate->name = $data['name'];
                $bundleUpdate->size = $data['size'];
                $bundleUpdate->price = $data['price'];
                $bundleUpdate->description = $data['description'];
                $bundleUpdate->gamecase_image = $data['gamecase_image'];
                $bundleUpdate->save();
               /* if (!empty($data['gamecase_ids'])) {
                    foreach ($data['gamecase_ids'] as $k => $value) {
                        $bundleGameCase['game_case_bundle_id'] = $data['id'];
                        $bundleGameCase['game_case_id'] = $value;
                        $this->objBundleGameCase->create($bundleGameCase);
                    }
                }*/
                return Redirect::to("/admin/gamecase_bundle")->with('success', trans('adminmsg.gamecase_bundle_updated_uccess'));
            } else {
                if ($gameCase) {
                        $this->objGameCaseBundle->create($gameCase);
                    
                } else {
                    return back()->withInput()->with('error', trans('adminmsg.common_error_msg'));
                }
                return Redirect::to("/admin/gamecase_bundle/")->with('success', trans('adminmsg.gamecase_bundle_created_success'));
            }
        } catch (Exception $ex) {
            return Redirect::to("/admin/gamecase_bundle")->with('error', trans('adminmsg.common_error_msg'));
        }
    }

    public function editGameCaseBundle($id) {
        $gameCaseBundle = GameCaseBundle::find($id);
        //$bundleGameCase = BundleGameCase::GetBundleGameCase($id);
        $gameCaseImagePath = $this->gameCaseBundleThumbImageUploadPath;
        $gameCaseRecords = $this->objGameCase->get();
        if (!$gameCaseBundle) {
            return Redirect::to("/admin/gamecase_bundle/")->with('error', trans('adminmsg.gamecase_not_exist'));
        }
        return view('admin.add-GameCaseBundle', compact('gameCaseBundle', 'gameCaseRecords', 'gameCaseImagePath'));
    }

    public function listGameCaseAjax() {
        $records = array();
        //processing custom actions
        if (Input::get('customActionType') == 'groupAction') {
            $action = Input::get('customActionName');
            $idArray = Input::get('id');
            switch ($action) {
                case "delete":
                    foreach ($idArray as $_idArray) {
                        $gameCaseDelete = GameCaseBundle::find($_idArray);
                        $gameCaseDelete->forceDelete();
                        //$gameCaseBundle = BundleGameCase::DeleteRecord($_idArray);
                        //$gameCaseBundle->delete();
                    }
                    $records["customMessage"] = trans('adminmsg.delete_gamecase_bundle');
            }
        }
        $columns = array(
            0 => 'name',
            1 => 'gamecase_ids',
            2 => 'price'
        );
        $order = Input::get('order');
        $search = Input::get('search');
        $records["data"] = array();
        $iTotalRecords = GameCaseBundle::count();
        $iTotalFiltered = $iTotalRecords;
        $iDisplayLength = intval(Input::get('length')) <= 0 ? $iTotalRecords : intval(Input::get('length'));
        $iDisplayStart = intval(Input::get('start'));
        $sEcho = intval(Input::get('draw'));
        $records["data"] = GameCaseBundle::select('*');
        if (!empty($search['value'])) {
            $val = $search['value'];
            $records["data"]->where(function($query) use ($val) {
                $query->SearchName($val);
                $query->SearchBundlePrice($val);
            });
            // No of record after filtering
            $iTotalFiltered = $records["data"]->where(function($query) use ($val) {
                        $query->SearchName($val);
                        $query->SearchBundlePrice($val);
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
                $edit = route('gamecase_bundle.edit', $_records->id);
                $records["data"][$key]['gamecase_image'] = ($_records->gamecase_image != '' && File::exists(public_path($this->gameCaseBundleThumbImageUploadPath . $_records->gamecase_image)) ? '<img src="' . url($this->gameCaseBundleThumbImageUploadPath . $_records->gamecase_image) . '"   height="50" width="50">' : '<img src="' . asset('/images/default.png') . '" class="user-image" alt="Default Image" height="50" width="50">');
                $records["data"][$key]->action = "&emsp;<a href='{$edit}' title='Edit Bundle' ><span class='glyphicon glyphicon-edit'></span></a>
                                                    &emsp;<a href='javascript:;' data-id='" . $_records->id . "' class='btn-delete-game' title='Delete Game Case Bundle' ><span class='glyphicon glyphicon-trash'></span></a>";
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalFiltered;

        return Response::json($records);
    }

}
