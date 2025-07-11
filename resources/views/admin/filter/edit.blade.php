@extends('layouts.master')
@section('css')
    <link href="{{ URL::asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.css"/>
    <link href="{{ URL::asset('/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">Edit section</h1>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.filter.update', $filter['id']) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="link">Name:</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $filter['name'] }}">
                    </div>
                    <div class="form-group">
                        <label for="tag_ids" class="col-form-label">Tags</label>
                        <select name="tag_ids[]" id="tag_ids" class="select2" multiple>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}"
                                    {{ $filter->tags->pluck('id')->contains($tag->id) ? 'selected' : '' }}>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="dietary_ids" class="col-form-label">Dietaries</label>
                        <select name="dietary_ids[]" id="dietary_ids" class="select2" multiple>
                            @foreach($dietaries as $dietary)
                                <option value="{{ $dietary->id }}"
                                    {{ $filter->dietaries->pluck('id')->contains($dietary->id) ? 'selected' : '' }}>
                                    {{ $dietary->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cuisine_ids" class="col-form-label">Cuisines</label>
                        <select name="cuisine_ids[]" id="cuisine_ids" class="select2" multiple>
                            @foreach($cuisines as $cuisine)
                                <option value="{{ $cuisine->id }}"
                                    {{ $filter->cuisines->pluck('id')->contains($cuisine->id) ? 'selected' : '' }}>
                                    {{ $cuisine->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary">Update</button>
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
