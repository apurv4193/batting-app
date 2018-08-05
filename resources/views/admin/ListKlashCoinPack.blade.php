@extends('layouts.admin-master')

@section('content')
<section class="content-header">
    <h1>
        {{trans('adminlabels.kalsh_coin_pack')}}
        <small>{{trans('adminlabels.kalsh_coin_pack_label')}}</small>
        <div class="pull-right">
            <a href="{{ url('admin/add-klash-coin-pack') }}" class="btn btn-block btn-primary add-btn-primary pull-right">{{trans('adminlabels.add')}}</a>
        </div>
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{trans('adminlabels.kalsh_coin_pack_listing')}}</h3>
                </div>
                <div class="box-body">
                    <table id="klashCoinPack" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{trans('adminlabels.kalsh_coin_pack_name')}}</th>
                                <th>{{trans('adminlabels.number_of_klash_coins')}}</th>
                                <th>{{trans('adminlabels.cost_to_user')}}</th>
                                <th>{{trans('adminlabels.image')}}</th>
                                <th>{{trans('adminlabels.status')}}</th>
                                <th>{{trans('adminlabels.action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($klashCoinPacks as $key=>$value)
                            <tr>
                                <td>{{$value->name}}</td>
                                <td>{{$value->number_of_klash_coins}}</td>
                                <td>{{$value->cost_to_user}}</td>
                                <td>
                                    @if(!empty($value->image ) && $value->image != '' && file_exists(Config::get('constant.KLASH_COIN_PACK_ORIGINAL_IMAGE_UPLOAD_PATH').$value->image))

                                        <img style="cursor: pointer;" data-toggle='modal' data-target='#{{$value->id.substr(trim($value->image), 0, -10)}}' src="{{ url(Config::get('constant.KLASH_COIN_PACK_ORIGINAL_IMAGE_UPLOAD_PATH').$value->image) }}" width="50" height="50" class="img-circle"/>
                                        <div class='modal modal-centered fade image_modal' id='{{$value->id.substr(trim($value->image), 0, -10)}}' role='dialog' style='vertical-align: center;'>
                                            <div class='modal-dialog modal-dialog-centered'>
                                                <div class='modal-content' style="background-color:transparent;">
                                                    <div class='modal-body'>
                                                    <center>
                                                        <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                                        <img src="{{ url(Config::get('constant.KLASH_COIN_PACK_ORIGINAL_IMAGE_UPLOAD_PATH').$value->image) }}" style='width:100%; border-radius:5px;' title="{{$value->image}}" />
                                                    <center>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <img src="{{ url('images/default.png') }}" width="50" height="50" class="img-circle"/>
                                    @endif
                                </td>
                                <td>
                                    @if ($value->status == 'active')
                                        <span class="label label-success">Active</span>
                                    @else
                                        <span class="label label-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{url('admin/edit-klash-coin-pack')}}/{{$value->id}}">
                                        <span class='glyphicon glyphicon-edit' data-toggle="tooltip" data-original-title="Edit"></span>
                                    </a>&nbsp;&nbsp;
                                    <a href="{{url('admin/delete-klash-coin-pack')}}/{{$value->id}}" onClick="return confirm(&#39;{{trans('adminlabels.confirmdeletemsg')}}&#39;)">
                                        <span class='glyphicon glyphicon-remove' data-toggle="tooltip" data-original-title="Delete"></span>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
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

<script type="text/javascript">

    $(document).ready(function() {
        $('#klashCoinPack').DataTable({
           "aaSorting": []
        });
    });

</script>
@endsection
