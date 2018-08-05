@extends('layouts.admin-master')
@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.game_case_bundle_management')}}
        <small><?php echo (isset($gameCaseBundle) && !empty($gameCaseBundle)) ? trans('adminlabels.edit') : trans('adminlabels.create') ?> {{ trans('adminlabels.game_case_bundle') }}</small>        
    </h1>     
</section>

<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo (isset($gameCaseBundle) && !empty($gameCaseBundle)) ? trans('adminlabels.edit') : trans('adminlabels.create') ?> {{ trans('adminlabels.game_case_bundle') }}</h3>
                </div>
                <form class="form-horizontal" id="addGameCaseBundle" enctype="multipart/form-data"  method="POST" action="{{ url('admin/save-gamecase_bundle') }}">
                    {{ csrf_field() }} 
                    <?php $id = (isset($gameCaseBundle) && !empty($gameCaseBundle)) ? $gameCaseBundle->id : '0'; ?>
                    <div class="box-body">
                        <input type="hidden" name="id" value="{{ $id }}">
                        <input type="hidden" name="hidden_profile" value="<?php echo (isset($gameCaseBundle) && !empty($gameCaseBundle)) ? $gameCaseBundle->gamecase_image : ''; ?>">                    
                        <?php $gameCaseBundleName = (old('name') ? old('name') : (isset($gameCaseBundle) ? $gameCaseBundle->name : '')); ?>
                        <div class="form-group">
                            <label for="name" class="col-md-2 control-label">{{ trans('adminlabels.game_case_bundle_list_name') }}</label>
                            <div class="col-md-6 ">
                                <input type="text" class="form-control"  name="name" id="name" placeholder="{{ trans('adminlabels.game_case_bundle_list_name') }}" value="{{ $gameCaseBundleName }}"/>                                
                            </div>
                        </div> 
                        
                        <!-- Game Case -->
                        <?php $gamecaseSlug = (old('gamecase_slug') ? old('gamecase_slug') : (isset($gameCaseBundle) ? $gameCaseBundle->gamecase_slug : '')); ?>
                        <div class="form-group">
                            <label for="gamecase_slug" class="col-sm-2 control-label">{{ trans('adminlabels.bundle_for') }}</label>
                            <div class="col-sm-6">
                                <select class="form-control" id="gamecase_slug" name="gamecase_slug" @if($id == 0 || empty($id)) @else disabled="disabled"  @endif>
                                    <option value="">{{ trans('adminlabels.select_item') }}</option>
                                    <?php foreach ($gameCaseRecords as $_gameCaseRecord) { ?>
                                        <option value="{{$_gameCaseRecord->slug}}" <?php if ($gamecaseSlug == $_gameCaseRecord->slug) echo 'selected'; ?> >{{$_gameCaseRecord->name}}</option>
                                        
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="gamecase_image" class="col-md-2 control-label">{{ trans('adminlabels.game_case_bundle_Image')}}</label>
                            <div class="col-md-6">
                                <input type="file" class="form-control" id="gamecase_image" name="gamecase_image">
                                <?php
                                if (isset($gameCaseBundle) && $gameCaseBundle->id != '0') {
                                    if (File::exists(public_path($gameCaseImagePath . $gameCaseBundle->gamecase_image)) && $gameCaseBundle->gamecase_image != '') {
                                        ?>
                                        <img src="{{ url($gameCaseImagePath.$gameCaseBundle->gamecase_image) }}"  height="70" width="70">
                                    <?php } else { ?>
                                        <img src="{{ asset('/images/default.png')}}" alt="Default Image" height="70" width="70">
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <?php $gameCaseBundleSize = (old('size') ? old('size') : (isset($gameCaseBundle) ? $gameCaseBundle->size : 0)); ?>
                        <div class="form-group">
                            <label for="size" class="col-md-2 control-label">{{ trans('adminlabels.game_case_bundle_size') }}</label>
                            <div class="col-md-6 ">
                                <input type="text" class="form-control" name="size" id="size" placeholder="{{ trans('adminlabels.game_case_bundle_size') }}" value="{{ $gameCaseBundleSize }}"/>
                            </div>
                        </div>
                        <?php $gameCaseBundlePrice = (old('price') ? old('price') : (isset($gameCaseBundle) ? $gameCaseBundle->price : '')); ?>
                        <div class="form-group">
                            <label for="price" class="col-md-2 control-label">{{ trans('adminlabels.game_case_bundle_list_price') }}</label>
                            <div class="col-md-6 ">
                                <input type="text" class="form-control"  name="price" id="price" placeholder="{{ trans('adminlabels.game_case_bundle_list_price') }}" value="{{ $gameCaseBundlePrice }}"/>                              
                            </div>
                        </div>
                        <?php $description = (old('description') ? old('description') : (isset($gameCaseBundle) ? $gameCaseBundle->description : '')); ?>
                        <div class="form-group">
                            <label for="description" class="col-md-2 control-label"> {{ trans('adminlabels.players_description') }} </label>
                            <div class="col-md-6">
                                <textarea name="description" id="description" rows="4" cols="85">{{$description}}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-md-1 col-md-offset-2">
                                <button type="submit" class="btn btn-primary">{{ trans('adminlabels.submit') }}</button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{url('admin/gamecase_bundle')}}" class="btn btn-primary">{{ trans('adminlabels.cancel') }}</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- /.box -->
        </div>
        <!--/.col (right) -->
    </div>
    <!-- /.row -->
</section>
<!-- /.content -->
<!-- /.content-wrapper -->
@endsection
@section('script')
<script type="text/javascript">
    var gameCaseRecord = <?php echo json_encode($gameCaseRecords) ?>;
    var gameCaseId = <?php echo json_encode(isset($bundleGameCase) && !empty($bundleGameCase) ? $bundleGameCase : '') ?>;
    jQuery(document).ready(function () {
        $('#price').on('keyup', function () {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });
        var id = $("input[name=id]").val();
        if (id !== 0) {
            $("#addGameCaseBundle").validate({
                ignore: ":hidden:not(select)",
                rules: {
                    name: {
                        required: true
                    },
                    gamecase_slug: {
                        required: true
                    },
                    size: {
                        required: true,
                        digits:true,
                        max: 100
                    },
                    price: {
                        required: true,
                        digits:true,
                        maxlength: 6
                    },
                    description: {
                        required: true,
                        maxlength: 240
                    }
                }
            });
        } else {
            $("#addGameCaseBundle").validate({
                ignore: ":hidden:not(select)",
                rules: {
                    name: {
                        required: true
                    },
                    gamecase_slug: {
                        required: true
                    },
                    size: {
                        required: true,
                        digits:true,
                        max: 100
                    },
                    price: {
                        required: true,
                        digits:true,
                        maxlength: 6
                    },
                    description: {
                        required: true,
                        maxlength: 240
                    }
                }
            });
        }
        // for (var j = 1; j < gameCaseId.length; j++) {
        //     $('select[name="gamecase_ids[' + j + ']"]').rules("add", {// <- apply rule to new field
        //         required: true
        //     });
        // }

        // Add game row
        // $(document).on("click", ".add-item-row", function (e) {
        //     var counter = parseInt($("#counter").val()) + 1;
        //     var fieldHTML = "<div class='form-group'>" +
        //             "<label for='title' class='col-md-2 control-label'>{{trans('adminlabels.game_cases')}}</label>" +
        //             "<div class='col-sm-6'>" +
        //             "<select class='form-control' id='gamecase_ids[" + counter + "]' name='gamecase_ids[" + counter + "]'>" +
        //             "<option value=''>{{ trans('adminlabels.select_item') }}</option>" +
        //             "</select>" +
        //             "</div>" +
        //             "<input type='checkbox'  name='txt_check[]' />" +
        //             "</div>";
        //     $("#div_item_row").append(fieldHTML);
        //     for (var i = 0; i < gameCaseRecord.length; i++) {
        //         $('select[name="gamecase_ids[' + counter + ']"]').append($("<option></option>").val(gameCaseRecord[i].id).html(gameCaseRecord[i].name));
        //     }
        //     $('select[name="gamecase_ids[' + counter + ']"]').rules("add", {// <- apply rule to new field
        //         required: true
        //     });
        //     $("#counter").val(counter);
        // });

        // Delete game row
        // $(document).on("click", ".delete-item-row", function (e) {
        //     $('DIV.form-group').each(function (index, item) {
        //         jQuery(':checkbox', this).each(function () {
        //             if ($(this).is(':checked')) {
        //                 $(item).remove();
        //             }
        //         });
        //     });
        // });
    });

</script>
@endsection