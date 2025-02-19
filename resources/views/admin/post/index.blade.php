@extends('layouts.master')
@section('title') @lang('translation.Data_Tables') @endsection
@section('css')

<link href="{{ URL::asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.css"/>
@endsection

@section('content')
@include('components.breadcum')
<div class="row">
    <div class="col-12">
        <a href="{{ route('admin.post.create') }}" class="btn btn-info">Create Post</a>
    </div>
    <br>
</div>
<div class="row">
    <div class="col-12">
        {!! success_error_view_generator() !!}
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="listResults" class="table dt-responsive mb-4  nowrap w-100 mb-">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name</th>
                            <th>Profile</th>
                            <th>Added</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<!-- Required datatable js -->
<script src="{{asset('/assets/admin/vendors/general/validate/jquery.validate.min.js')}}"></script>
<script src="{{asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        oTable = $('#listResults').DataTable({
            "processing": true,
            "serverSide": true,
            columnDefs: [
                { className: 'text-center', targets: [1,2,3,4] },
            ],
            "order": [
                [0, "DESC"]
            ],
            "ajax": "{{route('admin.post.listing')}}",
            "columns": [{
                    "data": "DT_RowIndex",
                    name: 'DT_RowIndex',
                    searchable: true,
                    sortable: false
                },
                {
                    "data": "title",
                    searchable: true,
                    sortable: false
                },
                {
                    "data": "user_name",
                    searchable: true,
                    sortable: false
                },
                {
                    "data": "created_at",
                    searchable: true,
                    sortable: false
                },
                {
                    "data": "status",
                    searchable: false,
                    sortable: false
                },
                {
                    "data": "action",
                    searchable: false,
                    sortable: false
                }
            ]
        });
    });
</script>
@endsection
