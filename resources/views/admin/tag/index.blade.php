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
        <h1 class="mb-4">Tags</h1>
        <a href="{{ route('admin.tag.create') }}" class="btn btn-primary mb-3"><i class="fa fa-plus"></i> Add Tag</a>

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
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($tags as $tag)
                @if(isset($tag['id']))
                    <tr>
                        <td>
                            {{ $tag['id'] }}
                        </td>
                        <td>
                            {{ $tag['name'] }}
                        </td>
                        <td>
                            <form action="{{ route('admin.tag.destroy', $tag['id']) }}" method="POST" style="display:inline;">
                                <a href="{{ route('admin.tag.show', $tag['id']) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i> Show</a>
                                <a href="{{ route('admin.tag.edit', $tag['id']) }}" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-stop-circle"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                @else
                    <tr>
                        <td colspan="4">Invalid tag data</td>
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
