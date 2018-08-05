@extends('layouts.admin-master')

@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.team_management')}}
        <small>{{trans('adminlabels.teams')}}</small>
        <div class="pull-right">
            <a href="{{ url('admin/add-team') }}" class="btn btn-block btn-primary add-btn-primary pull-right">{{trans('adminlabels.add')}}</a>
        </div>       
    </h1>
</section>

<section class="content">
    <div class="row">        
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{trans('adminlabels.teams_list')}}</h3>
                </div>
                <div class="box-body">
                    <table id="listTeams" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{trans('adminlabels.teams_name')}}</th>                                
                                <th>{{trans('adminlabels.game_name')}}</th>                                
                                <th>{{trans('adminlabels.contest_type')}}</th>                                
                                <th>{{trans('adminlabels.win')}}</th>
                                <th>{{trans('adminlabels.loss')}}</th>
                                <th>{{trans('adminlabels.cap_amount')}}</th>
                                <th>{{trans('adminlabels.image')}}</th>
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
    var getTeamList = function(ajaxParams) {
        $("#listTeams").DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax":{
                "url": "{{ url('admin/list-team-ajax') }}",
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
                { "data": "game_name"},                
                { "data": "type"},
                { "data": "win"},
                { "data": "loss"},
                { "data": "team_cap_amount"},
                { "data": "team_image"},
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
        getTeamList(ajaxParams);
        // Remove user
        $(document).on('click', '.btn-delete-team', function(e){
            e.preventDefault();
            var userId = $(this).attr('data-id');
            var cmessage = 'Are you sure you want to Delete this Team ?';
            var ctitle = 'Delete Team';

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
                            getTeamList(ajaxParams);
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