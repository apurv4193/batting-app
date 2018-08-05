@extends('layouts.admin-master')

@section('content')
    <section class="content-header">
        <h1>
            {{trans('adminlabels.kalsh_coin_pack')}}
            <small>{{trans('adminlabels.kalsh_coin_pack_label')}}</small>
        </h1>
    </section>

    <section class="content">

        <div class="row">

            <!-- right column -->

            <div class="col-md-12">

                <!-- Horizontal Form -->

                <div class="box box-info">

                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo (isset($data) && !empty($data)) ? trans('adminlabels.edit') : trans('adminlabels.add') ?> {{trans('adminlabels.kalsh_coin_pack_label')}}</h3>
                    </div>

                    <form class="form-horizontal" id="klashCoinPack" method="POST" action="{{ url('admin/save-klash-coin-pack') }}" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <div class="box-body">

                            <input type="hidden" name="id" value="<?php echo (isset($data) && !empty($data) && isset($data->id) && $data->id > 0) ? $data->id : '0' ?>">
                            <input type="hidden" name="hidden_image" value="<?php echo (isset($data) && !empty($data) && !empty($data->image) ) ? $data->image : '' ?>">

                            <div class="form-group">
                                <?php
                                    if (old('name'))
                                        $name = old('name');
                                    elseif (isset($data) && !empty ($data->name))
                                        $name = $data->name;
                                    else
                                        $name = '';
                                ?>
                                <label for="name" class="col-sm-2 control-label">{{trans('adminlabels.kalsh_coin_pack_name')}}<span class="star_red">*</span></label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" placeholder="{{ trans('adminlabels.kalsh_coin_pack_name') }}" id="name" name="name" value="{{$name}}">
                                </div>
                            </div>

                            <div class="form-group">
                                <?php
                                    if (old('number_of_klash_coins'))
                                        $number_of_klash_coins = old('number_of_klash_coins');
                                    elseif (isset($data) && !empty ($data->number_of_klash_coins))
                                        $number_of_klash_coins = $data->number_of_klash_coins;
                                    else
                                        $number_of_klash_coins = '';
                                ?>
                                <label for="number_of_klash_coins" class="col-sm-2 control-label">{{trans('adminlabels.number_of_klash_coins')}}<span class="star_red">*</span></label>
                                <div class="col-sm-6">
                                    <input type="number" class="form-control" placeholder="{{ trans('adminlabels.number_of_klash_coins') }}" id="number_of_klash_coins" name="number_of_klash_coins" value="{{$number_of_klash_coins}}">
                                </div>
                            </div>

                            <div class="form-group">
                                <?php
                                    if (old('cost_to_user'))
                                        $cost_to_user = old('cost_to_user');
                                    elseif (isset($data) && !empty ($data->cost_to_user))
                                        $cost_to_user = $data->cost_to_user;
                                    else
                                        $cost_to_user = '';
                                ?>
                                <label for="cost_to_user" class="col-sm-2 control-label">{{trans('adminlabels.cost_to_user')}}<span class="star_red">*</span></label>
                                <div class="col-sm-6">
                                    <input type="number" class="form-control" placeholder="{{ trans('adminlabels.cost_to_user') }}" id="cost_to_user" name="cost_to_user" value="{{$cost_to_user}}">
                                </div>
                            </div>

                            <div class="form-group">
                            <?php
                                if (old('image'))
                                    $image = old('image');
                                elseif (isset ($data) && !empty ($data->image))
                                    $image = $data->image;
                                else
                                    $image = '';
                            ?>
                                <label for="image" class="col-sm-2 control-label">{{trans('adminlabels.image')}}<span class="star_red">*</span></label>
                                <div class="col-sm-5">
                                    <input type="file" class="form-control" id="image" name="image">
                                </div>
                                <div class="col-sm-3">
                                    @if(!empty($image) && $image != '')
                                        @if(file_exists(Config::get('constant.KLASH_COIN_PACK_ORIGINAL_IMAGE_UPLOAD_PATH').$image))
                                            <img src="{{ url(Config::get('constant.KLASH_COIN_PACK_ORIGINAL_IMAGE_UPLOAD_PATH').$image) }}" class="report-image img-thumbnail" alt="{{$image}}" height="{{Config::get('constant.KLASH_COIN_PACK_THUMBNAIL_IMAGE_HEIGHT')}}" width="{{Config::get('constant.KLASH_COIN_PACK_THUMBNAIL_IMAGE_WIDTH')}}" />
                                        @else
                                            <img src="{{ url('images/default.png') }}" width="100" height="100"/>
                                        @endif
                                    @endif
                                </div>

                            </div>

                            <div class="form-group">
                                <?php
                                if (old('status'))
                                    $status = old('status');
                                elseif (isset($data))
                                    $status = $data->status;
                                else
                                    $status = '';
                                ?>
                                <label for="status" class="col-sm-2 control-label">{{trans('adminlabels.status')}}</label>
                                <div class="col-sm-8">
                                    <select name="status" data="" class="form-control">
                                        <option value="active" {{($status == 'active') ? 'selected' : ''}}>Active</option>
                                        <option value="inactive" {{($status == 'inactive') ? 'selected' : ''}}>Inactive</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="box-footer">
                            <div class="form-group">
                                <div class="col-md-1 col-md-offset-2">
                                    <button type="submit" class="btn btn-primary">{{ trans('adminlabels.submit') }}</button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{url('admin/klash-coin-pack')}}" class="btn btn-primary">{{ trans('adminlabels.cancel') }}</a>
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
    jQuery(document).ready(function ()
    {
        var id = <?php echo (isset($data) && !empty($data)) ? $data->id : '0'; ?>;
        if (id !== 0)
        {
            $("#addPlayers").validate({
                rules: {
                    name: {
                        required: true,
                        maxlength: 100
                    },
                    number_of_klash_coins: {
                        required: true,
                        number: true
                    },
                    cost_to_user: {
                        required: true,
                        number: true,
                        maxlength: 100
                    },
                    status: {
                        required: true
                    }
                }
            });
        }
        else
        {
            $("#addPlayers").validate({
                rules: {
                    name: {
                        required: true,
                        maxlength: 100
                    },
                    number_of_klash_coins: {
                        required: true,
                        number: true
                    },
                    cost_to_user: {
                        required: true,
                        number: true,
                        maxlength: 100
                    },
                    image: {
                        required: true
                    },
                    status: {
                        required: true
                    }
                }
            });
        }

    });
</script>
@endsection
