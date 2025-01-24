@extends('layouts.master')

@section('css')
    <link href="{{ URL::asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.css"/>
    <link href="{{ URL::asset('/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
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
                            <select name="tags[]" id="tags" class="form-control select2" multiple>
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}" {{ in_array($tag->id, $data->tags) ? 'selected' : '' }}>{{ $tag->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select multiple tags.</small>
                        </div>

                        <div class="form-group">
                            <label for="dietaries">Dietaries</label>
                            <select name="dietaries[]" id="dietaries" class="form-control select2" multiple>
                                @foreach($dietaries as $dietary)
                                    <option value="{{ $dietary->id }}" {{ in_array($dietary->id, $data->dietaries) ? 'selected' : '' }}>{{ $dietary->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select multiple dietaries.</small>
                        </div>

                        <div class="form-group">
                            <label for="cuisines">Cuisines</label>
                            <select name="cuisines[]" id="cuisines" class="form-control select2" multiple>
                                @foreach($cuisines as $cuisine)
                                    <option value="{{ $cuisine->id }}" {{ in_array($cuisine->id, $data->cuisines) ? 'selected' : '' }}>{{ $cuisine->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select multiple cuisines.</small>
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
    <script src="{{ asset('/assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@endsection
