@extends('layouts.admin-master')

@section('content')
    <section class="content-header">
        <h1>
            {{trans('adminlabels.game_management')}}
            <small>{{trans('adminlabels.create_game')}}</small>
        </h1>     
    </section>

    <section class="content">
        <div class="row">
            <!-- right column -->
            <div class="col-md-12">
                <!-- Horizontal Form -->
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo (isset($game) && !empty($game)) ? trans('adminlabels.edit') : trans('adminlabels.add') ?> {{trans('adminlabels.game_label')}}</h3>
                    </div>
                    <form class="form-horizontal" id="addGames" method="POST" action="{{ url('admin/save-game') }}">
                        {{ csrf_field() }}
                        <div class="box-body">
                            <?php $id = ((isset($game) && !empty($game)) ? $game->id : '0'); ?>
                            <input type="hidden" name="id" value="{{$id}}">
                            <?php if ($id == 0) {
                                $name = (old('name')) ? old('name') : ((!empty($game) && $game->name) ? $game->name : ''); ?>
                                <div class="form-group">
                                    <label for="name" class="col-sm-2 control-label">{{ trans('adminlabels.name') }}</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="name[0]" name="name[0]" placeholder="{{ trans('adminlabels.name') }}" value="{{$name}}">
                                    </div>
                                    <div class="col-md-1">                                                                                
                                        <a class="btn btn-success btn-condensed add-game-row" href="javascript:void(0)" data-toggle="tooltip" data-placement="top" title="{{trans('adminlabels.add_game_tooltip')}}">
                                            <i class="fa fa-plus" ></i>
                                        </a>
                                    </div>
                                    <div class="col-md-1">                                                                                
                                        <a  class="btn btn-danger btn-condensed delete-game-row" data-toggle="tooltip" data-placement="top" title="{{trans('adminlabels.delete_game_tooltip')}}">
                                            <i class="fa fa-times"  ></i></a>
                                        <input type="hidden" value="0" name="counter" id="counter">
                                    </div>
                                </div>
                                <div id="div_game_row"></div>
                            <?php } else {
                                $name = (old('name')) ? old('name') : ((!empty($game) && $game->name) ? $game->name : ''); ?>
                                <div class="form-group">
                                    <label for="name" class="col-md-2 control-label">{{ trans('adminlabels.name') }}</label>
                                    <div class="col-md-6 ">
                                        <input type="text" class="form-control"  name="name" id="name" placeholder="{{ trans('adminlabels.name') }}" value="{{$name}}"/>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="box-footer">
                            <div class="form-group">
                                <div class="col-md-1 col-md-offset-2">
                                    <button type="submit" class="btn btn-primary">{{ trans('adminlabels.submit') }}</button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{url('admin/games')}}" class="btn btn-primary">{{ trans('adminlabels.cancel') }}</a>
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

        var id = $("input[name=id]").val();
        if (id !== 0) {
            $("#addGames").validate({
                ignore: ":hidden:not(select)",
                rules: {
                    "name[0]": {
                        required: true,
                        maxlength: 100
                    }
                }
            });
        } else {
            $("#addGames").validate({
                ignore: ":hidden:not(select)",
                rules: {
                    name: {
                        required: true,
                        maxlength: 100
                    }
                }
            });
        }

        // Add game row
        $(document).on("click", ".add-game-row", function (e) {
            var counter = parseInt($("#counter").val()) + 1;
            var fieldHTML = "<div class='form-group'>" +
                    "<label for='title' class='col-md-2 control-label'>{{trans('adminlabels.name')}}</label>" +
                    "<div class='col-sm-6'>" +
                    "<input type='text' class='form-control' id='name[" + counter + "]' name='name[" + counter + "]' placeholder='{{trans('adminlabels.name')}}' value=''>" +
                    "</div>" +
                    "<input type='checkbox'  name='txt_check[]' />" +
                    "</div>";
            $("#div_game_row").append(fieldHTML);
            $('input[name="name[' + counter + ']"]').rules("add", {// <- apply rule to new field
                required: true,
                maxlength: 100
            });
            $("#counter").val(counter);
        });

        // Delete game row
        $(document).on("click", ".delete-game-row", function (e) {
            $('DIV.form-group').each(function (index, item) {
                jQuery(':checkbox', this).each(function () {
                    if ($(this).is(':checked')) {
                        $(item).remove();
                    }
                });
            });
        });
    });
</script>
@endsection