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
                <div class="card-body" style="display: block; margin: 0 auto;">
                    <form action="{{ route('admin.ingredient.update', $data->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label class="col-form-label">ID</label>
                            <span>{{ $data->id }}</span>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label class="col-form-label">Created</label>
                            <span>{{ $data->created_at ? $data->created_at->format('d-m-Y') : 'N/A' }}</span>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="name" class="col-form-label">Name</label>
                            <input type="text" name="name" id="name" class="form-control w-50" value="{{ $data->name }}" required>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="type" class="col-form-label">Type</label>
                            <input type="text" name="type" id="type" class="form-control w-50" value="{{ $data->type }}" required>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="weight" class="col-form-label">Weight</label>
                            <input type="number" name="weight" id="weight" class="form-control w-50" value="{{ $data->weight }}" step="0.01" required>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>

                        <div class="d-flex justify-content-end mt-2">
                            <a href="{{ route('admin.ingredient.index') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </form>
                    <form action="{{ route('admin.ingredient.destroy', $data->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('/assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@endsection
