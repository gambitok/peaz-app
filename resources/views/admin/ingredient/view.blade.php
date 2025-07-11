@extends('layouts.master')

@section('css')
    <!-- DataTables -->
    <link href="{{ URL::asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.css"/>
@endsection

@section('content')

    @include('components.breadcum')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body" style="font-size:14px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="card-title font-weight-bold">Ingredient Details</span>
                        <a href="{{ route('admin.ingredient.edit', $data->id) }}" class="btn btn-primary btn-sm">Edit Ingredient</a>
                    </div>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex" style="padding: 15px 0;">
                            <span class="font-weight-bold" style="flex: 0 0 50%;">Name:</span>
                            <span style="flex: 1; text-align: right">{{ $data->name }}</span>
                        </li>
                        <li class="list-group-item d-flex" style="padding: 15px 0;">
                            <span class="font-weight-bold" style="flex: 0 0 50%;">Type:</span>
                            <span style="flex: 1; text-align: right">{{ $data->type }}</span>
                        </li>
                        <li class="list-group-item d-flex" style="padding: 15px 0;">
                            <span class="font-weight-bold" style="flex: 0 0 50%;">Weight:</span>
                            <span style="flex: 1; text-align: right">{{ $data->weight }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            {{-- Place for any additional related data if needed, or instructions/relations --}}
            {{-- Example: --}}
            {{-- <div class="card">
                <div class="card-body">
                    <div class="card-title">
                        Related Info
                    </div>
                    <div>
                        <table id="relatedInfo" class="table dt-responsive mb-4 nowrap w-100">
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
@endsection

@section('script')
    <script src="{{asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.js"></script>
    <script>
        // No extra JS needed for the ingredient details view
    </script>
@endsection
