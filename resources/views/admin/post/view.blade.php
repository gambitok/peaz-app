@extends('layouts.master')

@section('css')
<!-- DataTables -->
<link href="{{ URL::asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.css"/>
@endsection

@section('content')

@include('components.breadcum')
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body" style="font-size:14px;">
                <div class="justify-content-between">
                    <span class="card-title">Post Details</span>
                    <a href="{{ route('admin.post.edit', $data->id) }}">Edit Post</a>
                </div>
                 <!-- <div class="card-title">
                    <div class="kt-widget__media text-center w-100">
                        {!! get_fancy_box_html(get_asset($data->getRawOriginal('file'))) !!}
                    </div>
                </div>  -->
                <!-- <div class="d-flex justify-content-between">
                    <span class="font-weight-bold">Name:</span>
                    <a href="#">{{$data->title}}</a>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="font-weight-bold">Email:</span>
                    <a href="mailto:{{$data->email}}">{{$data->email}}</a>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="font-weight-bold">Status:</span>
                    <span class="kt-widget__data">{!! user_status($data->status,$data->deleted_at) !!}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="font-weight-bold">Mobile:</span>
                    <a href="mailto:{{$data->email}}">@if(!empty($data->mobile)) {{$data->mobile}}  @else -- @endif </a>
                </div> -->
                <div class="d-flex justify-content-between">
                    <span class="font-weight-bold">Title:</span>
                    <div>{{$data->title}}</div>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="font-weight-bold">File:</span>
                    <a href="{{$data->file}}"  target="_blank">Show File</a>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="font-weight-bold">Thumbnail:</span>
                    <a href="{{$data->thumbnail}}"  target="_blank">Show File</a>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="font-weight-bold">Time:</span>
                    <div>{{$data->hours}}h {{$data->minutes}}min</div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="font-size:14px;">
            <div class="card-title">
                Tags
                </div>
                <table  class="table dt-responsive mb-4  nowrap w-100 mb-">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($data->tags)

                            @foreach(explode(",",$data->tags) as $key => $value)
                            <tr>
                            <td>{{$key+1}}</td>
                            <td>{{$value}}</td>
                            </tr>
                            @endforeach

                        @endif
                    </tbody>
                </table>
            </div>

        </div>
        <div class="card">
            <div class="card-body" style="font-size:14px;">
            <div class="card-title">
                Dietaries
                </div>
                <table  class="table dt-responsive mb-4  nowrap w-100 mb-">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>

                    @if($data->dietaries)

                            @foreach(explode(",",$data->dietaries) as $key => $value)
                            <tr>
                            <td>{{$key+1}}</td>
                            <td>{{$value}}</td>
                            </tr>
                            @endforeach

                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-body">
                <div class="card-title">
                   Ingredient
                </div>
                <div>
                <table id="listResults" class="table dt-responsive mb-4  nowrap w-100 mb-">
                    <thead>
                        <tr>

                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="card-title">
                Instruction
                </div>
                <div>

                <table id="instruction" class="table dt-responsive mb-4  nowrap w-100 mb-">
                    <thead>
                        <tr>

                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{asset('/assets/admin/vendors/general/validate/jquery.validate.min.js')}}"></script>
<script src="{{asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.js"></script>

<script type="text/javascript">
    $(document).ready(function () {
        Table = $('#listResults').DataTable({
            "processing": true,
            "serverSide": true,
            "order": [
                [0, "DESC"]
            ],
            "ajax":
            {
                'url':"{{route('admin.post.post_details')}}",
                'data': {
                    id:'{{ $data->id}}',
                },

             },
            "columns": [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        "title": "No"
                    },
                    {
                        "data": "name",
                        "title": "Name"
                    },
                    {
                        "data": "measurement",
                        "title": "Measurement"
                    },
                    {
                        "data": "type",
                        "title": "Type"
                    },
                    {
                        "data": "action",
                        "title": "Action"
                    },

                ],
        });

        Table = $('#instruction').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax":
            {
                'url':"{{route('admin.post.instruction')}}",
                'data': {
                    id:'{{ $data->id}}',
                },
             },
            "columns": [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        "title": "No"
                    },
                    {
                        "data": "title",
                        "title": "Title"
                    },
                    {
                        "data": "type",
                        "title": "Type"
                    },
                    {
                        "data": "file",
                        "title": "File"
                    },
                    {
                        "data": "thumbnail",
                        "title": "Thumbnail"
                    },
                    {
                        "data": "description",
                        "title": "Description"
                    },
                ],
        });

        Table = $('#taglist').DataTable();

        Table = $('#dietaries').DataTable();
    });

</script>
@endsection
