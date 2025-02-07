@extends('layouts.master')

@section('styles')
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/admin/vendors/general/datatable/jquery.dataTables.min.css">
@endsection

@section('content')

    <style>
        .table thead th {
            border-bottom: 2px solid #dee2e6;
        }
        .table {
            background-color: #ffffff;
        }
    </style>
    <div class="container mt-5">
        <h1 class="mb-4">Billboards</h1>
        <a href="{{ route('admin.billboards.create') }}" class="btn btn-primary mb-3">Create New Billboard</a>
        <table id="billboards-table" class="table">
            <thead>
            <tr>
                <th>Title</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($billboards as $billboard)
                @if(isset($billboard['id']))
                    <tr>
                        <td>
                            <a href="{{ route('admin.billboards.show', $billboard['id']) }}">
                                {{ $billboard['title'] }}
                            </a>
                        </td>
                        <td>
                            <form action="{{ route('admin.billboards.destroy', $billboard['id']) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @else
                    <tr>
                        <td colspan="2">Invalid billboard data</td>
                    </tr>
                @endif
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="/assets/admin/vendors/general/datatable/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#billboards-table').DataTable();
        });
    </script>
@endsection
