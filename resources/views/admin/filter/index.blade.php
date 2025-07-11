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
                    <a href="{{ route('admin.filter.create') }}" class="btn btn-primary mb-3"><i class="fa fa-plus"></i> Add inspiration section</a>

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

                    <table id="tags-table" class="table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Tags</th>
                            <th>Dietaries</th>
                            <th>Cuisines</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($filters as $filter)
                            @if(isset($filter['id']))
                                <tr>
                                    <td>
                                        {{ $filter['id'] }}
                                    </td>
                                    <td>
                                        {{ $filter['name'] }}
                                    </td>
                                    <td>
                                        {{ $filter['tags']->pluck('name')->implode(', ') }}
                                    </td>
                                    <td>
                                        {{ $filter['dietaries']->pluck('name')->implode(', ') }}
                                    </td>
                                    <td>
                                        {{ $filter['cuisines']->pluck('name')->implode(', ') }}
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.filter.destroy', $filter['id']) }}" method="POST" style="display:inline;">
                                            <a href="{{ route('admin.filter.show', $filter['id']) }}" class="btn btn-sm btn-clean btn-icon btn-icon-md btnView"><i class="fa fa-eye"></i></a>
                                            <a href="{{ route('admin.filter.edit', $filter['id']) }}" class="btn btn-sm btn-clean btn-icon btn-icon-md btnEdit"><i class="fa fa-edit"></i></a>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-clean btn-icon btn-icon-md"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="4">Invalid data</td>
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
