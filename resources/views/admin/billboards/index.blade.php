@extends('layouts.master')

@section('styles')
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
                <th>Title</th>
                <th>Profile name</th>
                <th>Created at</th>
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
                            <form action="{{ route('admin.billboards.destroy', $billboard['id']) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
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
@endsection

@section('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
@endsection
