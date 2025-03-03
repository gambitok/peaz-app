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
        <h1 class="mb-4">Restaurants</h1>
        <a href="{{ route('admin.restaurants.create') }}" class="btn btn-primary mb-3">Create New Restaurant</a>

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

        <table id="restaurants-table" class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>Profile name</th>
                <th>Created at</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($restaurants as $restaurant)
                @if(isset($restaurant['id']))
                    <tr>
                        <td>
                            {{ $restaurant['id'] }}
                        </td>
                        <td>
                            <span>
                                {{ $restaurant['user']['name'] ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <span>
                                {{ \Carbon\Carbon::parse($restaurant['created_at'])->format('d.m.Y') }}
                            </span>
                        </td>
                        <td>
                            <form action="{{ route('admin.restaurants.destroy', $restaurant['id']) }}" method="POST" style="display:inline;">
                                <a href="{{ route('admin.restaurants.show', $restaurant['id']) }}" class="btn btn-sm btn-clean btn-icon btn-icon-md btnView"><i class="fa fa-eye"></i></a>
                                <a href="{{ route('admin.restaurants.edit', $restaurant['id']) }}" class="btn btn-sm btn-clean btn-icon btn-icon-md btnEdit"><i class="fa fa-edit"></i></a>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-clean btn-icon btn-icon-md"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @else
                    <tr>
                        <td colspan="4">Invalid restaurant data</td>
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
