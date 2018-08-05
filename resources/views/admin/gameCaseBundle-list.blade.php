@extends('layouts.admin-master')

@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.game_case_bundle_management')}}
        <small>{{trans('adminlabels.game_case_bundle')}}</small>
        <div class="pull-right">
            <a href="{{ url('admin/add-gamecase_bundle') }}" class="btn btn-block btn-primary add-btn-primary pull-right">{{trans('adminlabels.add')}}</a>
        </div>       
    </h1>
</section>

<section class="content">
    <div class="row">        
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{trans('adminlabels.game_case_bundle_list')}}</h3>
                </div>
                <div class="box-body">
                    <table id="listGameCaseBundle" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{trans('adminlabels.game_case_bundle_list_name')}}</th>
                                <th>{{trans('adminlabels.game_case_bundle_Image')}}</th>
                                <th>{{trans('adminlabels.game_case_bundle_list_price')}}</th>
                                <th>{{trans('adminlabels.game_list_action_label')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>

        </div>
        <!--/.col (right) -->
    </div>
    <!-- /.row -->
</section>
@endsection
@section('script')

<script>
    var getGameList = function(ajaxParams) {
        $("#listGameCaseBundle").DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax":{
                "url": "{{ url('admin/list-gamecase_bundle-ajax') }}",
                "dataType": "json",
                "type": "POST",
                headers: { 
                    'X-CSRF-TOKEN': "{{ csrf_token() }} "
                },
                "data" : function(data) {
                    if (ajaxParams) {
                        $.each(ajaxParams, function(key, value) {
                            data[key] = value;
                        });
                        ajaxParams = {};
                    }
                }
            },
            "columns": [
                { "data": "name" },
                { "data": "gamecase_image" },
                { "data": "price" },
                { "data": "action", "orderable": false }
            ], 
            "initComplete": function(settings, json) {
                if(typeof(json.customMessage) != "undefined" && json.customMessage !== '') {
                    $('.customMessage').removeClass('hidden');
                    $('#customMessage').html(json.customMessage);
                }
            }
        });
    };
    $(document).ready(function () {
        var ajaxParams = {};
        getGameList(ajaxParams);
        // Remove user
        $(document).on('click', '.btn-delete-game', function(e){
            e.preventDefault();
            var userId = $(this).attr('data-id');
            var cmessage = 'Are you sure you want to Delete this Game Case Bundle ?';
            var ctitle = 'Delete Game Case Bundle';

            ajaxParams.customActionType = 'groupAction';
            ajaxParams.customActionName = 'delete';
            ajaxParams.id = [userId];

            bootbox.dialog({
                onEscape: function () {},
                message: cmessage,
                title: ctitle,
                buttons: {
                    Yes: {
                        label: 'Yes',
                        className: 'btn green',
                        callback: function () {
                            getGameList(ajaxParams);
                        }
                    },
                    No: {
                        label: 'No',
                        className: 'btn btn-default'
                    }
                }
            });
        });
    });
</script>
@endsection