@extends('layouts.master')

@section('css')
    <link href="{{ URL::asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.css"/>
@endsection

@section('content')
@include('components.breadcum')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body" style="font-size:14px;">
                    <div class="justify-content-between">
                        <span class="card-title">Edit Post</span>
                    </div>
                    <form action="{{ route('admin.post.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" name="title" id="title" class="form-control" value="{{ $data->title }}" required>
                        </div>
                        <div class="form-group">
                            <label for="file">File</label>
                            <input type="file" name="file" id="file" class="form-control">
                            @if($data->file)
                                <a href="{{ $data->file }}" target="_blank">Show File</a>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="thumbnail">Thumbnail</label>
                            <input type="file" name="thumbnail" id="thumbnail" class="form-control">
                            @if($data->thumbnail)
                                <a href="{{ $data->thumbnail }}" target="_blank">Show Thumbnail</a>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="hours">Hours</label>
                            <input type="number" name="hours" id="hours" class="form-control" value="{{ $data->hours }}">
                        </div>
                        <div class="form-group">
                            <label for="minutes">Minutes</label>
                            <input type="number" name="minutes" id="minutes" class="form-control" value="{{ $data->minutes }}">
                        </div>
                        <div class="form-group">
                            <label for="tags">Tags</label>
                            <input type="text" name="tags" id="tags" class="form-control" value="{{ $data->tags }}">
                            <small class="text-muted">Separate tags with commas (e.g., "Tag1, Tag2").</small>
                        </div>
                        <div class="form-group">
                            <label for="dietary">Dietaries</label>
                            <input type="text" name="dietaries" id="dietaries" class="form-control" value="{{ $data->dietaries }}">
                            <small class="text-muted">Separate dietary types with commas (e.g., "Vegan, Gluten-Free").</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('/assets/admin/vendors/general/validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.js"></script>
@endsection
