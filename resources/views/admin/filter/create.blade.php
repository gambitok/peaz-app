@extends('layouts.master')
@section('css')
    <link href="{{ URL::asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.css"/>
    <link href="{{ URL::asset('/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">Create section</h1>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.filter.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" class="form-control" id="name" name="name">
                    </div>
                    <div class="form-group">
                        <label for="tag_ids" class="col-form-label">Tags</label>
                        <select name="tag_ids[]" id="tag_ids" class="select2" multiple>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary">Create</button>
                    <a href="{{ route('admin.filter.index') }}" class="btn btn-secondary">Back to list</a>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('/assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {

            $('.select2').select2({
                placeholder: {
                    id: '-1',
                    text: 'Select an option'
                }
            });
        });
    </script>
@endsection
