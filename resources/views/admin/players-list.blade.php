@extends('layouts.admin-master')

@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.players_management')}}
        <small>{{trans('adminlabels.players')}}</small>
        <div class="pull-right">
            <a href="{{ url('admin/add-players') }}" class="btn btn-block btn-primary add-btn-primary pull-right">{{trans('adminlabels.add')}}</a>
        </div>       
    </h1>
</section>

<section class="content">
    <div class="row">        
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{trans('adminlabels.players_list')}}</h3>
                </div>
                <div class="box-body">
                    <table id="listPlayers" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{trans('adminlabels.players_name')}}</th>                                
                                <th>{{trans('adminlabels.players_image')}}</th>                                
                                <!-- <th>{{trans('adminlabels.players_game')}}</th>                                
                                <th>{{trans('adminlabels.players_cap_amount')}}</th>                                
                                <th>{{trans('adminlabels.players_win')}}</th>                                
                                <th>{{trans('adminlabels.players_loss')}}</th> -->                                
                                <th>{{trans('adminlabels.players_list_action_label')}}</th>
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
    var getPlayersList = function(ajaxParams) {
        $("#listPlayers").DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax":{
                "url": "{{ url('admin/list-players-ajax') }}",
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
                { "data": "profile_image", "orderable": false },                
                /*{ "data": "game_id"},                
                { "data": "cap_amount"},                
                { "data": "win"},                
                { "data": "loss"},*/                
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
        getPlayersList(ajaxParams);
        // Remove user
        $(document).on('click', '.btn-delete-players', function(e){
            e.preventDefault();
            var userId = $(this).attr('data-id');
            var cmessage = 'Are you sure you want to Delete this Players Name ?';
            var ctitle = 'Delete Players';

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
                            getPlayersList(ajaxParams);
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