@extends('layouts.admin-master')

@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.game_case_management')}}
        <small><?php echo (isset($gameCase) && !empty($gameCase)) ? trans('adminlabels.edit') : trans('adminlabels.create') ?> {{ trans('adminlabels.gamecase') }}</small>        
    </h1>     
</section>

<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo (isset($gameCase) && !empty($gameCase)) ? trans('adminlabels.edit') : trans('adminlabels.create') ?> {{ trans('adminlabels.gamecase') }}</h3>
                </div>
                <form class="form-horizontal" id="addGameCase" enctype="multipart/form-data" method="POST" action="{{ url('admin/save-game-case') }}">
                    {{ csrf_field() }}                     
                    <div class="box-body">
                        <input type="hidden" name="id" value="<?php echo (isset($gameCase) && !empty($gameCase)) ? $gameCase->id : '0'; ?>">
                        <input type="hidden" name="hidden_profile" value="<?php echo (isset($gameCase) && !empty($gameCase)) ? $gameCase->photo : ''; ?>">                    
                        <?php $gameCaseName = (old('name') ? old('name') : (isset($gameCase) && !empty($gameCase) ? $gameCase->name : '')); ?>
                        <div class="form-group">
                            <label for="name" class="col-md-2 control-label">{{ trans('adminlabels.gamecase_name') }}</label>
                            <div class="col-md-6 ">
                                <input type="text" class="form-control"  name="name" id="gamecasename" placeholder="{{ trans('adminlabels.gamecase_name') }}" value="{{ $gameCaseName }}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="photo" class="col-md-2 control-label">{{ trans('adminlabels.gamecase_photo') }}</label>
                            <div class="col-md-6 ">
                                <input type="file" class="form-control" id="photo" name="photo">
                                <?php
                                if (isset($gameCase) && $gameCase->id != '0') {
                                    if (File::exists(public_path($gameCaseUploadImage . $gameCase->photo)) && $gameCase->photo != '') {
                                        ?>
                                        <img src="{{ url($gameCaseUploadImage.$gameCase->photo) }}"  height="70" width="70">
                                    <?php } else { ?>
                                        <img src="{{ asset('/images/default.png')}}" class="user-image" alt="Default Image" height="70" width="70">
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <?php $gameCasePrice = (old('price') ? old('price') : isset($gameCase) && !empty($gameCase) ? $gameCase->price : ''); ?>
                        <div class="form-group">
                            <label for="gamecaseprice" class="col-md-2 control-label">{{ trans('adminlabels.gamecase_price') }}</label>
                            <div class="col-md-6 ">
                                <input type="text" class="form-control"  name="price" id="gamecaseprice" placeholder="{{ trans('adminlabels.gamecase_price') }}" value="{{ $gameCasePrice }}"/>
                            </div>
                        </div>
                        <?php $gameCaseItem = (old('items')? old('items') : isset($gameCase) && !empty($gameCase) ? $gameCase->items : ''); ?>
                        <div class="form-group">
                            <label for="gamecaseitem" class="col-md-2 control-label">{{ trans('adminlabels.gamecase_item') }}</label>
                            <div class="col-md-6 ">
                                <input type="text" class="form-control"  name="items" id="gamecaseitem" disabled="disabled" placeholder="{{ trans('adminlabels.gamecase_item') }}" value="{{ $gameCaseItem }}"/>
                            </div>
                        </div>
                        <?php $description = (old('description') ? old('description') : (isset($gameCase) ? $gameCase->description : '')); ?>
                        <div class="form-group">
                            <label for="description" class="col-md-2 control-label"> {{ trans('adminlabels.players_description') }} </label>
                            <div class="col-md-6">
                                <textarea name="description" id="description" rows="4" cols="85">{{$description}}</textarea>
                            </div>
                        </div>
                        <?php for ($x = 0; $x < $gameCaseItem; $x++) {
                           ?>
                        <div class="form-group">
                            <label for="gamecaseitem" class="col-md-2 control-label">{{ trans('adminlabels.gamecase_item') }}</label>
                            <div class="col-md-2 ">
                                <select class="form-control" id="item_id_{{$x}}" name="item_id[]" data-placeholder="Select Items..." onchange="changed({{$x}})">
                                    <option value="">{{ trans('adminlabels.item_list') }}</option>
                                    <?php foreach ($items as $item) { ?>
                                        <option value="{{$item->id}}" <?php if($item->id == @$gameCaseItems[$x]['item_id']) echo "selected";?>>{{$item->name}}</option>
                                    <?php } ?>
                                </select>
                            </div>
                            <!-- <label for="name" class="col-sm-1 control-label">{{ trans('adminlabels.win') }}</label> -->
                            <div class="col-sm-1">
                                <input type="checkbox" id="is_checked" name="is_checked[{{$x}}]" <?php if(@$gameCaseItems[$x]['possibility'] == 100) echo "checked";?> value="1" onclick="clicked({{$x}})" />
                                <label for="squaredThree">100%</label>
                            </div> 
                            <?php $display = (@$gameCaseItems[$x]['possibility'] != 100)?'block':'none'; ?>
                            <any id="is_hide_{{$x}}" style="display: {{$display}};">
                            <label for="gamecaseitem" class="col-md-1 control-label">{{ trans('adminlabels.gamecase_possibility') }}</label>
                            <div class="col-md-1 is_hide_{{$x}}">
                                <input type="text" class="form-control"  name="possibility[]" id="possibility_{{$x}}" placeholder="{{ trans('adminlabels.gamecase_possibility') }}" value="{{ @$gameCaseItems[$x]['possibility'] }}" onkeyup="calc({{$x}})" onkeypress="checkValidation({{$x}})" />
                                <input id="old_possibility_{{$x}}" type="hidden">
                            </div>

                            <label for="gamecaseitem" class="col-md-1 control-label">{{ trans('adminlabels.gamecase_item') }}</label>
                            <div class="col-md-2">
                                <select class="form-control" id="alternate_item_id_{{$x}}" name="alternate_item_id[]" data-placeholder="Select Items...">
                                    <option value="">{{ trans('adminlabels.item_list') }}</option>
                                <?php if(@$gameCaseItems[$x]['alternate_item_id'] != '' && isset($gameCaseItems[$x]['alternate_item_id'])) { ?>
                                        <?php foreach ($items as $item) { ?>
                                            <option value="{{$item->id}}" <?php if($item->id == @$gameCaseItems[$x]['alternate_item_id']) echo "selected";?>>{{$item->name}}</option>
                                        <?php } 
                                    }?> 
                                </select>
                            </div>
                            <label for="gamecaseitem" class="col-md-1 control-label">{{ trans('adminlabels.gamecase_possibility') }}</label>
                            <div class="col-md-1">
                                <input type="text" class="form-control"  name="alternate_possibility[]" id="alternate_possibility_{{$x}}" placeholder="{{ trans('adminlabels.gamecase_possibility') }}" value="{{ @$gameCaseItems[$x]['alternate_possibility'] }}" readonly />
                            </div>
                            </any>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-md-1 col-md-offset-2">
                                <button type="submit" class="btn btn-primary">{{ trans('adminlabels.submit') }}</button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{url('admin/gamecase')}}" class="btn btn-primary">{{ trans('adminlabels.cancel') }}</a>
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
<script>
    jQuery(document).ready(function () {
        $('#gamecaseprice').on('xup', function () {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });
        var id = $("input[name=id]").val();
        if (id !== 0) {
            $("#addGameCase").validate({
                rules: {
                    name: {
                        required: true
                    },
                    price: {
                        required: true,
                        digits:true,
                        maxlength: 6
                    },
                    description: {
                        required: true,
                        maxlength: 240
                    },
                    "item_id[]": {
                        required: true
                    },
                    "possibility[]": {
                        required: true
                    }
                }
            });
        } else {
            $("#addGameCase").validate({
                rules: {
                    name: {
                        required: true
                    },
                    photo: {
                        required: true
                    },
                    price: {
                        required: true,
                        digits:true,
                        maxlength: 6
                    },
                    description: {
                        required: true,
                        maxlength: 240
                    },
                    "item_id[]": {
                        required: true
                    },
                    "possibility[]": {
                        required: true
                    }
                }
            });
        }
    });

    //checkbox changed.
    function clicked(i)
    {        
        if($("[name='is_checked["+i+"]']").prop('checked') == true) {
            $("#is_hide_"+i).css('display','none');
            $('#possibility_'+i).val(100);
            $('#alternate_possibility_'+i).val(0);
            $('#alternate_item_id_'+i).attr('readonly','readonly');
        }
        else {
            $("#is_hide_"+i).css('display','block');
            $('#possibility_'+i).val(0);
            $('#alternate_possibility_'+i).attr('readonly',false);
            $('#alternate_item_id_'+i).attr('readonly',false);
        }
    }

    //item list select change
    function changed(i)
    {
        
        var itemId = $("#item_id_"+i).val();
        $.ajax({
            type: "GET",
            url: "{{ url('admin/getItems') }}",
            data: {'itemId': itemId},
            success: function(data){
                $("#alternate_item_id_"+i).html('<option value="">{{ trans('adminlabels.item_list') }}</option>');

                $.each(data, function (key, val){
                    $('#alternate_item_id_'+i).append('<option value="'+data[key].id+'">'+data[key].name+'</option>');
                });
                
            }
        });
    }

    //
    function calc(i)
    {
        var possibility = $('#possibility_'+i).val();
        
        if (possibility <= 100 && possibility >= 0 && possibility != '') {
          var newPossibility = (100 - possibility);
          $('#alternate_possibility_'+i).val(newPossibility);
          $('#alternate_possibility_'+i).attr('readonly','readonly');
        } else if(possibility == ''){
          $('#alternate_possibility_'+i).val('');
        } else {
          $('#possibility_'+i).val($('#old_possibility_'+i).val());
        }
    }

    function checkValidation(i)
    {
        var possibility = $('#possibility_'+i).val();
        if (possibility <= 100 && possibility >= 0) {
            $('#old_possibility_'+i).val(possibility);
        }
    }
    
</script>
@endsection