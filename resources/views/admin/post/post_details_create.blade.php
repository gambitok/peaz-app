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
                <div class="card-body">
                    <form action="{{ route('admin.post.post_details_store', $post_id) }}" method="POST">
                        @csrf

                        <div class="form-group mt-4 mb-4">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="" placeholder="Name">
                        </div>
                        <div class="form-group mt-4 mb-4">
                            <label for="type">Type</label>
                            <input type="text" class="form-control" id="type" name="type" value="" placeholder="Type">
                        </div>
                        <div class="form-group mt-4 mb-4">
                            <label for="measurement">Measurement</label>
                            <input type="text" class="form-control" id="measurement" name="measurement" value="" placeholder="Measurement">
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
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
