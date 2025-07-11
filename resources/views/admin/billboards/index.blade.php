@extends('layouts.master')

@section('styles')
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
@endsection

@section('content')
    @include('components.breadcum')

    <style>
        .table thead th {
            border-bottom: 2px solid #dee2e6;
        }
        .table {
            background-color: #ffffff;
        }
    </style>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('admin.billboards.create') }}" class="btn btn-primary mb-3"><i class="fa fa-plus"></i> Add Billboard ADS</a>

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                            @if($errors->has('api_error'))
                                <pre>{{ print_r($errors->get('api_error'), true) }}</pre>
                            @endif
                        </div>
                    @endif

                    <table id="billboards-table" class="table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Membership level</th>
                            <th>Profile name</th>
                            <th>Join Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($billboards as $billboard)
                            @if(isset($billboard['id']))
                                <tr>
                                    <td>
                                        {{ $billboard['id'] }}
                                    </td>
                                    <td>
                                        {{ $billboard['user']['membership_level'] }}
                                    </td>
                                    <td>
                                        <span>
                                            {{ $billboard['user']['name'] ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span>
                                            {{ \Carbon\Carbon::parse($billboard['created_at'])->format('d.m.Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $billboard['status'] }}
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.billboards.destroy', $billboard['id']) }}" method="POST" style="display:inline;">
                                            <a href="{{ route('admin.billboards.show', $billboard['id']) }}" class="btn btn-sm btn-clean btn-icon btn-icon-md btnView"><i class="fa fa-eye"></i></a>
                                            <a href="{{ route('admin.billboards.edit', $billboard['id']) }}" class="btn btn-sm btn-clean btn-icon btn-icon-md btnEdit"><i class="fa fa-edit"></i></a>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-clean btn-icon btn-icon-md"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="4">Invalid billboard data</td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
@endsection
