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
            <div class="card p-3 m-0">
                <div class="card-body" style="font-size:14px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="card-title font-weight-bold">Post Details</span>
                        <a href="{{ route('admin.post.edit', $data->id) }}" class="btn btn-primary btn-sm">Edit Post</a>
                    </div>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex" style="padding: 15px 0;">
                            <span class="font-weight-bold" style="flex: 0 0 50%;">Title:</span>
                            <span style="flex: 1; text-align: right">{{$data->title}}</span>
                        </li>
                        <li class="list-group-item d-flex" style="padding: 15px 0;">
                            <span class="font-weight-bold" style="flex: 0 0 50%;">Time:</span>
                            <span style="flex: 1; text-align: right">{{$data->hours}}h {{$data->minutes}}min</span>
                        </li>

                        @if($data->file)
                            <li class="list-group-item d-flex" style="padding: 15px 0;">
                                <span class="font-weight-bold" style="flex: 0 0 50%;">File:</span>
                                <div class="text-right" style="flex: 1;">
                                    @if(preg_match('/\.(jpg|jpeg|png|gif)$/i',  $data->file))
                                        <img src="{{ $data->file }}" alt="File" class="img-fluid" style="width: 100%; height: auto; object-fit: contain;">
                                    @elseif(preg_match('/\.(mp4|webm|ogg)$/i', $data->file))
                                        <video src="{{ $data->file }}" class="img-fluid" style="width: 100%; height: auto; object-fit: contain;" controls></video>
                                    @endif
                                </div>
                            </li>
                        @endif

                        @if($data->thumbnail)
                            <li class="list-group-item d-flex" style="padding: 15px 0;">
                                <span class="font-weight-bold" style="flex: 0 0 50%;">Thumbnail:</span>
                                <div class="text-right" style="flex: 1;">
                                    @if(preg_match('/\.(jpg|jpeg|png|gif)$/i', $data->thumbnail))
                                        <img src="{{ $data->thumbnail }}" alt="Thumbnail" class="img-fluid" style="width: 100%; height: auto; object-fit: contain;">
                                    @elseif(preg_match('/\.(mp4|webm|ogg)$/i', $data->thumbnail))
                                        <video src="{{ $data->thumbnail }}" class="img-fluid" style="width: 100%; height: auto; object-fit: contain;" controls></video>
                                    @endif
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

        </div>

        <div class="card">
            <div class="card-body" style="font-size:14px;">
            <div class="card-title">
                Tags
                </div>
                <table  class="table dt-responsive mb-4 nowrap w-100">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if(count($data->tags) > 0)
                        @foreach($data->tags as $key => $value)
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
                <table  class="table dt-responsive mb-4 nowrap w-100">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if(count($data->dietaries) > 0)
                        @foreach($data->dietaries as $key => $value)
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
                    Cuisines
                </div>
                <table  class="table dt-responsive mb-4 nowrap w-100">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(count($data->cuisines) > 0)
                        @foreach($data->cuisines as $key => $value)
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Ingredient</span>
                        <a href="{{ route('admin.post.post_details_create', $data->id) }}" class="btn btn-primary btn-sm">Add Ingredient</a>
                    </div>
                </div>
                <div>
                    <table id="listResults" class="table dt-responsive mb-4 nowrap w-100">
                        <thead></thead>
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
                    <table id="instruction" class="table dt-responsive mb-4 nowrap w-100">
                        <thead></thead>
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

        Table = $('#cuisines').DataTable();
    });

</script>
@endsection
