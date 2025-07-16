@extends('layouts.master')

@section('styles')
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
@endsection

@section('content')
{{--    @include('components.breadcum')--}}

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
                    <a href="{{ route('admin.pages.create') }}" class="btn btn-primary mb-3">
                        <i class="fa fa-plus"></i> Add Page
                    </a>

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

                    <table id="pages-table" class="table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Slug</th>
                            <th>Title</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($pages as $page)
                            <tr>
                                <td>{{ $page->id }}</td>
                                <td>{{ $page->slug }}</td>
                                <td>{{ $page->title }}</td>
                                <td>
                                    <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" style="display:inline;">
                                        <a href="{{ route('admin.pages.edit', $page->id) }}" class="btn btn-sm btn-clean btn-icon btn-icon-md btnEdit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-clean btn-icon btn-icon-md">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        @if($pages->isEmpty())
                            <tr><td colspan="4">No pages found.</td></tr>
                        @endif
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
