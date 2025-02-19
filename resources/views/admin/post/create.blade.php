@extends('layouts.master')
@section('css')
    <link href="{{ URL::asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.css"/>
    <link href="{{ URL::asset('/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
@include('components.breadcum')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>{{ $title }}</h1>
                <form action="{{ route('admin.post.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="caption">Caption:</label>
                        <textarea class="form-control" id="caption" name="caption" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="file">File</label>
                        <input type="file" class="form-control" id="file" name="file">
                    </div>
                    <div class="form-group">
                        <label for="thumbnail">Thumbnail</label>
                        <input type="file" class="form-control" id="thumbnail" name="thumbnail">
                    </div>
                    <div class="form-group">
                        <label for="minutes">Serving size</label>
                        <input type="number" name="serving_size" id="serving_size" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="hours">Hours</label>
                        <input type="number" class="form-control" id="hours" name="hours" required>
                    </div>
                    <div class="form-group">
                        <label for="minutes">Minutes</label>
                        <input type="number" class="form-control" id="minutes" name="minutes" required>
                    </div>
                    <div class="form-group">
                        <label for="tags">Tags</label>
                        <select multiple class="form-control select2" id="tags" name="tags[]">
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="dietaries">Dietaries</label>
                        <select multiple class="form-control select2" id="dietaries" name="dietaries[]">
                            @foreach($dietaries as $dietary)
                                <option value="{{ $dietary->id }}">{{ $dietary->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cuisines">Cuisines</label>
                        <select multiple class="form-control select2" id="cuisines" name="cuisines[]">
                            @foreach($cuisines as $cuisine)
                                <option value="{{ $cuisine->id }}">{{ $cuisine->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Post</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('/assets/admin/vendors/general/validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.js"></script>
    <script src="{{ asset('/assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@endsection
