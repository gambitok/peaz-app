@extends('layouts.master')
@section('title') @lang('translation.Data_Tables') @endsection
@section('css')

<!-- DataTables -->
<link href="{{ URL::asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.css"/>
@endsection
@section('content')

@include('components.breadcum')

<div class="row">
    <div class="col-12">
        {!! success_error_view_generator() !!}
    </div>
    <div class="card">
        <div class="card-body">
            <div>
                <a href="{{ route('admin.user.create') }}" class="btn btn-primary mb-3"><i class="fa fa-plus"></i> Add Member</a>
            </div>
            <div class="table-responsive">
                <table id="listResults" class="table dt-responsive mb-4  nowrap w-100 mb-">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Membership level</th>
                            <th>Profile name</th>
                            <th>Verified</th>
                            <th>Join date</th>
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
            "order": [
                [0, "DESC"]
            ],
            "ajax": "{{route('admin.user.listing')}}",
            "columns": [
                {
                    "data": "id",
                    searchable: true,
                    sortable: false
                },
                {
                    "data": "membership_level",
                    searchable: false,
                    sortable: true
                },
                {
                    "data": "username",
                    searchable: true,
                    sortable: true
                },
                {
                    "data": "verified",
                    searchable: false,
                    sortable: false
                },
                {
                    "data": "created_at",
                    searchable: true,
                    sortable: true
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

<script>
    $(document).on('change', '.toggle-user-verified', function () {
        let postId = $(this).data('id');

        $.ajax({
            url: "{{ route('admin.user.verified') }}",
            type: "POST",
            data: {
                id: postId,
                _token: "{{ csrf_token() }}"
            }
        });
    });

    $(document).on('change', '.status-dropdown', function () {
        let postId = $(this).data('id');
        let status = $(this).val();

        $.ajax({
            url: "{{ route('admin.user.status') }}",
            type: "POST",
            data: {
                id: postId,
                status: status,
                _token: "{{ csrf_token() }}"
            }
        });
    });

    $(document).on('change', '.membership-dropdown', function () {
        let postId = $(this).data('id');
        let membership_level = $(this).val();

        $.ajax({
            url: "{{ route('admin.user.membership') }}",
            type: "POST",
            data: {
                id: postId,
                membership_level: membership_level,
                _token: "{{ csrf_token() }}"
            }
        });
    });

</script>
@endsection
