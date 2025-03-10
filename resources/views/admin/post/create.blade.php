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
                <div class="card-body" style="display: block; margin: 0 auto;}">
                    <form action="{{ route('admin.post.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="user_id" class="col-form-label">Profile Name</label>
                            <div class="w-50">
                                <select name="user_id" id="user_id" class="select2">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="title" class="col-form-label">Title</label>
                            <input type="text" name="title" id="title" class="form-control w-50" placeholder="Title" required>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="caption" class="col-form-label">Caption</label>
                            <textarea class="form-control w-50" id="caption" name="caption" rows="3" placeholder="Caption"></textarea>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4 align-items-center">
                            <label class="col-form-label">Total Time</label>
                            <div class="d-flex gap-2 w-50">
                                <input type="number" name="hours" id="hours" class="form-control w-25" placeholder="hours">
                                <label for="hours">Hours</label>
                                <input type="number" name="minutes" id="minutes" class="form-control w-25" placeholder="minutes">
                                <label for="minutes">Minutes</label>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="serving_size" class="col-form-label">Serving size</label>
                            <input type="number" name="serving_size" id="serving_size" class="form-control w-50" placeholder="Serving size">
                        </div>

                        <hr>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="tags" class="col-form-label">Tags</label>
                            <div class="w-50">
                                <select name="tags[]" id="tags" class="select2" multiple>
                                    @foreach($tags as $tag)
                                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="dietaries" class="col-form-label">Dietaries</label>
                            <div class="w-50">
                                <select name="dietaries[]" id="dietaries" class="select2" multiple>
                                    @foreach($dietaries as $dietary)
                                        <option value="{{ $dietary->id }}">{{ $dietary->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="cuisines" class="col-form-label">Cuisines</label>
                            <div class="w-50">
                                <select name="cuisines[]" id="cuisines" class="form-control select2 w-50" multiple>
                                    @foreach($cuisines as $cuisine)
                                        <option value="{{ $cuisine->id }}">{{ $cuisine->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-between">
                            <label for="file" class="col-form-label">File</label>
                            <div class="w-50">
                                <input type="file" name="file" id="file" class="form-control">
                                <p>No file uploaded</p>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-between">
                            <label for="thumbnail" class="col-form-label">Thumbnail</label>
                            <div class="w-50">
                                <input type="file" name="thumbnail" id="thumbnail" class="form-control">
                                <p>No thumbnail uploaded</p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">Create</button>
                        </div>

                        <div class="d-flex justify-content-end mt-2">
                            <a href="{{ route('admin.post.index') }}" class="btn btn-secondary">Back</a>
                        </div>
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
