@extends('layouts.admin-master')

@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.Prize_management')}}
        <small>{{trans('adminlabels.Prize')}}</small>
        <div class="pull-right">
            <a href="{{ url('admin/add-prize') }}" class="btn btn-block btn-primary add-btn-primary pull-right">{{trans('adminlabels.add')}}</a>
        </div>       
    </h1>
</section>

<section class="content">
    <div class="row">        
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{trans('adminlabels.Prize_management_list')}}</h3>
                </div>
                <div class="box-body">
                    <table id="listPrize" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{trans('adminlabels.Prize_name')}}</th>
                                <th>{{trans('adminlabels.Prize_winner')}}</th>
                                <th>{{trans('adminlabels.game_list_action_label')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div>                
            </div>
        </div> 
    </div>
</section>
@endsection
@section('script')

<script>
    var getadsList = function (ajaxParams) {
        $("#listPrize").DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax": {
                "url": "{{ url('admin/list-prize-plan-ajax') }}",
                "dataType": "json",
                "type": "POST",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }} "
                },
                "data": function (data) {
                    if (ajaxParams) {
                        $.each(ajaxParams, function (key, value) {
                            data[key] = value;
                        });
                        ajaxParams = {};
                    }
                }
            },
            "columns": [
                {"data": "name"},
                {"data": "winner"},
                {"data": "action", "orderable": false}
            ],
            "initComplete": function (settings, json) {
                if (typeof (json.customMessage) != "undefined" && json.customMessage !== '') {
                    $('.customMessage').removeClass('hidden');
                    $('#customMessage').html(json.customMessage);
                }
            }
        });
    };
    $(document).ready(function () {
        var ajaxParams = {};
        getadsList(ajaxParams);
        // Remove user
        $(document).on('click', '.btn-delete-ads', function (e) {
            e.preventDefault();
            var userId = $(this).attr('data-id');
            var cmessage = 'Are you sure you want to Delete this Prize ?';
            var ctitle = 'Delete Ads';

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
                            getadsList(ajaxParams);
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