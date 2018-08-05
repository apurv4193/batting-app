<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Item;
use File;
use Image;
use Redirect;
use Config;
use Response;

class ItemController extends Controller {
    private $items;

    public function __construct(Item $items) {
        $this->items = $items;
        $this->itemOriginalImageUploadPath = Config::get('constant.ITEM_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->itemThumbImageUploadPath = Config::get('constant.ITEM_THUMB_IMAGE_UPLOAD_PATH');
        $this->itemThumbImageHeight = Config::get('constant.ITEM_THUMB_IMAGE_HEIGHT');
        $this->itemThumbImageWidth = Config::get('constant.ITEM_THUMB_IMAGE_WIDTH');
    }
    
    /**
     * Item listing
     * @return view
     */
    public function getItem() {
        return view('admin.item-list');
    }

    /**
     * [listItemAjax List Items]
     * @param  [type]       [description]
     * @return [json]       [list of Items]
     */
    public function listItemAjax() {
        $records = array();
        
        $columns = array( 
            0 => 'name', 
            2 => 'points',
            3 => 'pre_contest_substitution',
            4 => 'contest_substitution'
        );
        
        $order = Input::get('order');
        $search = Input::get('search');
        $records["data"] = array();
        
        //getting records from the items table
        $iTotalRecords = Item::count();
        $iTotalFiltered = $iTotalRecords;
        $iDisplayLength = intval(Input::get('length')) <= 0 ? $iTotalRecords : intval(Input::get('length'));
        $iDisplayStart = intval(Input::get('start'));
        $sEcho = intval(Input::get('draw'));
        
        $records["data"] = Item::select('*');
        
        if (!empty($search['value'])) {
            $val = $search['value'];
            $records["data"]->where(function($query) use ($val) {
                $query->SearchName($val)
                    ->Points($val);
            });
            
            // No of record after filtering
            $iTotalFiltered = $records["data"]->where(function($query) use ($val) {
                                $query->SearchName($val)
                                    ->Points($val);
            })->count();
        }
        
        //order by
        foreach ($order as $o) {
            $records["data"] = $records["data"]->orderBy($columns[$o['column']], $o['dir']);
        }

        //limit
        $records["data"] = $records["data"]->take($iDisplayLength)->offset($iDisplayStart)->get();

        if(!empty($records["data"])) {
            foreach ($records["data"] as $key => $_records) {
                $edit =  route('item.edit', $_records->id);

                $records["data"][$key]['pre_contest_substitution'] = ($_records->pre_contest_substitution == '1' ? 'Yes' : 'No');
                $records["data"][$key]['contest_substitution'] = ($_records->contest_substitution == '1' ? 'Yes' : 'No');
                $records["data"][$key]['item_image'] = ($_records->item_image != '' && File::exists(public_path($this->itemThumbImageUploadPath . $_records->item_image)) ? '<img src="'.url($this->itemThumbImageUploadPath.$_records->item_image).'" alt="{{$_records->item_image}}"  height="50" width="50">' : '<img src="'.asset('/images/default.png').'" alt="Default Image" height="50" width="50">');
                $records["data"][$key]['action'] = "&emsp;<a href='{$edit}' title='Edit Item' ><span class='glyphicon glyphicon-edit'></span></a>";
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalFiltered;
        
        return Response::json($records);
    }
    
    /**
     * [editItem Edit item]
     * @param  [integer]    [item's id]
     */
    public function editItem($id) {
        $item = Item::find($id);
        
        if(!$item) {
            return Redirect::to("/admin/items/")->with('error', trans('adminmsg.item_not_exist'));
        }
        
        $itemThumbPath = $this->itemThumbImageUploadPath;
        return view('admin.add-item', compact('item', 'itemThumbPath'));
    }
    
    /* Save Items data */
    public function saveItem(Request $request) {
        try {
            $this->validate(request(), [
                'item_image' => 'image|max:10240'
            ]);

            $item = Item::find($request->id);

            if(!$item) {
                return Redirect::to("/admin/items/")->with('error', trans('adminmsg.item_not_exist'));
            }

            $itemData = $request->all();

            $hiddenItemImage = $request->hidden_item_image;
            $itemData['item_image'] = $hiddenItemImage;

            if ($request->hasFile('item_image')) {
                $itemImage = $request->file('item_image');

                if (!empty($itemImage)) {
                    $fileName = 'item_' . time() . '.' . $itemImage->getClientOriginalExtension();

                    $originalPath = public_path($this->itemOriginalImageUploadPath . $fileName);
                    $thumbPath = public_path($this->itemThumbImageUploadPath . $fileName);

                    if (!file_exists(public_path($this->itemOriginalImageUploadPath))) File::makeDirectory(public_path($this->itemOriginalImageUploadPath), 0777, true, true);
                    if (!file_exists(public_path($this->itemThumbImageUploadPath))) File::makeDirectory(public_path($this->itemThumbImageUploadPath), 0777, true, true);

                    // created instance
                    $img = Image::make($itemImage->getRealPath());

                    $img->save($originalPath);
                    // resize the image to a height of $this->contestThumbImageHeight and constrain aspect ratio (auto width)
                    if( $img->height() < 500 ){
                        $img->resize(null, $img->height(), function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($pathThumb);
                    }
                    else {
                        $img->resize(null, $this->itemThumbImageHeight, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($thumbPath);
                    }

                    if ($hiddenItemImage != '' && $hiddenItemImage != "default.png") {
                        $originalImage = public_path($this->itemOriginalImageUploadPath . $hiddenItemImage);
                        $thumbImage = public_path($this->itemThumbImageUploadPath . $hiddenItemImage);
                        if (file_exists($originalImage)) {
                            File::delete($originalImage);
                        }
                        if (file_exists($thumbImage)) {
                            File::delete($thumbImage);
                        }
                    }
                    $itemData['item_image'] = $fileName;
                }
            }

            if (isset($itemData['id']) && $itemData['id'] > 0) {
                // Update Item
                $item->fill(array_filter(array_only($itemData, ['item_image', 'points', 'description'])));
                $item->save();
                return Redirect::to("/admin/items/")->with('success', trans('adminmsg.item_updated_success'));
            }
        } catch (Exception $e) {
            return Redirect::to("/admin/items/")->with('error', trans('adminmsg.common_error_msg'));
        }
    }
    
}
