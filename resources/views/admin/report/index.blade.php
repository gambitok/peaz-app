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
      </div>
      {!! success_error_view_generator() !!}
    <div class="card">
        <div class="card-body"> 
        <div class="wd-sl-modalbtn">
              <!-- <a href="{{route('admin.interestedlist.create')}}">
                <button type="button" class="btn btn-primary waves-effect waves-light"> Add 
                                        </button>
                                    </a> -->
                                </div>
            <div class="table-responsive ">
                <table id="listResults" class="table dt-responsive mb-4  nowrap w-100 mb-">
                    <thead>
                        <tr>
                            
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
            "ajax": "{{route('admin.user_report.listing')}}",
            "drawCallback": function( settings ) {
                    $(".fancybox").fancybox();
                },
            "columns": [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        "title": "No"
                    },
                    {
                        "data": "user_id",
                        "title": "User"
                    },
                    {
                        "data": "post_id",
                        "title": "post"
                    },
                    {
                        "data": "report_id",
                        "title": "Report"
                    },
                    {
                        "data": "status",
                        "title": "Status"
                    }
                ],
        });
    });
</script>
@endsection
