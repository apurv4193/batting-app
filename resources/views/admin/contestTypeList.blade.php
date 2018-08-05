@extends('layouts.admin-master')

@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.contest_type')}}
        <small>{{trans('adminlabels.contest_type_list')}}</small>
    </h1>
</section>

<section class="content">
    <div class="row">        
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{trans('adminlabels.contest_type_list')}}</h3>
                </div>
                <div class="box-body">
                    <table id="listContestType" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{trans('adminlabels.contest_type')}}</th>
                                <th>{{trans('adminlabels.contest_cap_amount')}}</th>
                                <th>{{trans('adminlabels.contest_type_list_action_label')}}</th>
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
        $("#listContestType").DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax":{
                "url": "{{ url('admin/list-contest-type-ajax') }}",
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
                { "data": "type" },                
                { "data": "contest_cap_amount"},  
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
    });
</script>
@endsection