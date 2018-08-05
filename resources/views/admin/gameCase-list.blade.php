@extends('layouts.admin-master')

@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.game_case_management')}}
        <small>{{trans('adminlabels.gamecase')}}</small>
    </h1>
</section>

<section class="content">
    <div class="row">        
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{trans('adminlabels.gamecase_list')}}</h3>
                </div>
                <div class="box-body">
                    <table id="listGameCase" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{trans('adminlabels.gamecase_name')}}</th>
                                <th>{{trans('adminlabels.gamecase_photo')}}</th>
                                <th>{{trans('adminlabels.gamecase_price')}}</th>
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
    var getGameCaseList = function(ajaxParams) {
        $("#listGameCase").DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax":{
                "url": "{{ url('admin/list-gamecase-ajax') }}",
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
                { "data": "photo", "orderable": false },
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
        getGameCaseList(ajaxParams);
    });
</script>
@endsection