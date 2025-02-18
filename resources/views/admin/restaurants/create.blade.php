@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">Create Restaurant</h1>
        <form action="{{ route('admin.restaurants.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="file">File:</label>
                <input type="text" class="form-control" id="file" name="file">
            </div>
            <div class="form-group">
                <label for="link">Link:</label>
                <input type="text" class="form-control" id="link" name="link">
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" id="status" name="status">
                <label for="status">Status</label>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('admin.restaurants.index') }}" class="btn btn-secondary">Back to Restaurants</a>
        </form>
    </div>
@endsection
