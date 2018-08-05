@extends('layouts.admin-master')

@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.contest_management')}}
        <small>{{trans('adminlabels.contest')}}</small>
        <div class="pull-right">
            <a href="{{ url('admin/add-contest') }}" class="btn btn-block btn-primary add-btn-primary pull-right">{{trans('adminlabels.add')}}</a>
        </div>       
    </h1>
</section>

<section class="content">
    <div class="row">        
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{trans('adminlabels.contest_list')}}</h3>
                </div>
                <div class="box-body">
                    <table id="listContest" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{trans('adminlabels.contest_list_game_label')}}</th>
                                <th>{{trans('adminlabels.contest_list_contest_type_label')}}</th>
                                <th>{{trans('adminlabels.contest_list_contest_name_label')}}</th>
                                <th>{{trans('adminlabels.contest_list_fees_label')}}</th>
                                <th>{{trans('adminlabels.contest_list_start_time_label')}}</th>
                                <th>{{trans('adminlabels.contest_list_end_time_label')}}</th>
                                <th>{{trans('adminlabels.contest_list_privacy')}}</th>
                                <th>{{trans('adminlabels.contest_list_status')}}</th>
                                <th>{{trans('adminlabels.contest_list_action_label')}}</th>
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
    var getContestList = function(ajaxParams) {
        $("#listContest").DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax":{
                "url": "{{ url('admin/list-contest-ajax') }}",
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
                { "data": "type" },
                { "data": "contest_name" },
                { "data": "contest_fees" }, 
                { "data": "contest_start_time" }, 
                { "data": "contest_end_time" },
                { "data": "privacy" }, 
                { "data": "status" },
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
        getContestList(ajaxParams);
        // Remove user
        $(document).on('click', '.btn-cancel-contest', function(e){
            e.preventDefault();
            var contestId = $(this).attr('data-id');
            var cmessage = 'Are you sure you want to Cancel this Contest ?';
            var ctitle = 'Cancel Contest';

            ajaxParams.customActionType = 'groupAction';
            ajaxParams.customActionName = 'cancel';
            ajaxParams.id = [contestId];

            bootbox.dialog({
                onEscape: function () {},
                message: cmessage,
                title: ctitle,
                buttons: {
                    Yes: {
                        label: 'Yes',
                        className: 'btn green',
                        callback: function () {
                            getContestList(ajaxParams);
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