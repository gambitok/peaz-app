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
                        <span class="font-weight-bold mb-2 font-size-18 font-weight-bold">Filters:</span>
                        <div class="d-flex align-items-center mb-2">
                            <label for="verifiedFilter" class="mr-2">Show Pending:</label>
                            <label class="switch">
                                <input type="checkbox" class="toggle" id="verifiedFilter">
                                <span class="slider slider-secondary round"></span>
                            </label>
                        </div>
                        <div class="d-flex">
                            <div style="width: 200px; margin-right: 15px">
                                <select id="tagsFilter" class="form-control select2" multiple="multiple" data-placeholder="Select Tags">
                                    @foreach($tags as $tag)
                                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="width: 200px; margin-right: 15px">
                                <select id="dietariesFilter" class="form-control select2" multiple="multiple" data-placeholder="Select Dietaries">
                                    @foreach($dietaries as $dietary)
                                        <option value="{{ $dietary->id }}">{{ $dietary->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="width: 200px; margin-right: 15px">
                                <select id="cuisinesFilter" class="form-control select2" multiple="multiple" data-placeholder="Select Cuisines">
                                    @foreach($cuisines as $cuisine)
                                        <option value="{{ $cuisine->id }}">{{ $cuisine->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('admin.post.create') }}" class="btn btn-info">Create Post</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="listResults" class="table dt-responsive mb-4 nowrap w-100 mb-">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Profile name</th>
                            <th>Added</th>
                            <th>Pending</th>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('/assets/libs/select2/js/select2.full.min.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('.select2').select2();

            var oTable = $('#listResults').DataTable({
                "processing": true,
                "serverSide": true,
                columnDefs: [
                    { className: 'text-center', targets: [1,2,3,4,5] },
                ],
                "order": [
                    [0, "DESC"]
                ],
                "ajax": {
                    "url": "{{route('admin.post.listing')}}",
                    "data": function(d) {
                        d.verified = $('#verifiedFilter').is(':checked') ? 0 : '';
                        d.tags = $('#tagsFilter').val();
                        d.dietaries = $('#dietariesFilter').val();
                        d.cuisines = $('#cuisinesFilter').val();
                    }
                },
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
                        "data": "verified",
                        searchable: false,
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

            $('#verifiedFilter, #tagsFilter, #dietariesFilter, #cuisinesFilter').change(function() {
                oTable.draw();
            });
        });

        $(document).on('change', '.toggle-status', function () {
            let postId = $(this).data('id');

            $.ajax({
                url: "{{ route('admin.post.status') }}",
                type: "POST",
                data: {
                    id: postId,
                    _token: "{{ csrf_token() }}"
                }
            });
        });
        $(document).on('change', '.toggle-verified', function () {
            let postId = $(this).data('id');

            $.ajax({
                url: "{{ route('admin.post.verified') }}",
                type: "POST",
                data: {
                    id: postId,
                    _token: "{{ csrf_token() }}"
                }
            });
        });
    </script>

@endsection
