@extends('layouts.master')
@section('title') @lang('translation.Data_Tables') @endsection
@section('css')
    <link href="{{ URL::asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.css"/>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ URL::asset('/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    @include('components.breadcum')

    <div class="row">
        <div class="col-12">
            {!! success_error_view_generator() !!}
        </div>
        <div class="card">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column w-75">
                    </div>
                    <div>
                        <a href="{{ route('admin.ingredient.create') }}" class="btn btn-info"><i class="fa fa-plus"></i> Create Ingredient</a>
                    </div>
                </div>
                <br>

                <div class="table-responsive">
                    <table id="listResults" class="table dt-responsive mb-4 nowrap w-100 mb-">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Weight</th>
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
    <script src="{{ asset('/assets/libs/select2/js/select2.full.min.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            var oTable = $('#listResults').DataTable({
                "processing": true,
                "serverSide": true,
                columnDefs: [
                    { className: 'text-center', targets: [1,2,3] },
                ],
                "order": [
                    [0, "DESC"]
                ],
                "ajax": {
                    "url": "{{route('admin.ingredient.listing')}}",
                    "data": function(d) {
                        d.name = $('#nameFilter').val();
                        d.type = $('#typeFilter').val();
                    }
                },
                "columns": [
                    {
                        "data": "DT_RowIndex",
                        name: 'DT_RowIndex',
                        searchable: true,
                        sortable: false
                    },
                    {
                        "data": "name",
                        searchable: true,
                        sortable: false
                    },
                    {
                        "data": "type",
                        searchable: true,
                        sortable: false
                    },
                    {
                        "data": "weight",
                        searchable: true,
                        sortable: false
                    },
                    {
                        "data": "action",
                        searchable: false,
                        sortable: false
                    }
                ]
            });

            $('#nameFilter, #typeFilter').on('input', function() {
                oTable.draw();
            });
        });
    </script>
@endsection
