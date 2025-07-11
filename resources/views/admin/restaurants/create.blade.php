@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h1 class="mb-4">Create Restaurant</h1>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form action="{{ route('admin.restaurants.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="title">Title:</label>
                                <input type="text" class="form-control" id="title" name="title">
                            </div>
                            <div class="form-group mt-2">
                                <label for="link">Link:</label>
                                <input type="text" class="form-control" id="link" name="link">
                            </div>
                            <div class="form-group mb-3 mt-2">
                                <label for="file" class="form-label">Logo</label>
                                <input type="file" name="file" id="file" class="form-control">
                            </div>
                            <div class="form-check mb-3 mt-2">
                                <input type="checkbox" class="form-check-input" id="status" name="status">
                                <label class="form-check-label" for="status">Status</label>
                            </div>
                            <button type="submit" class="btn btn-primary">Create</button>
                            <a href="{{ route('admin.restaurants.index') }}" class="btn btn-secondary">Back to Restaurants</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
