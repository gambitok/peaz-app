@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">Create cuisine</h1>
        <form action="{{ route('admin.cuisine.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" class="form-control" id="name" name="name">
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('admin.cuisine.index') }}" class="btn btn-secondary">Back to cuisines</a>
        </form>
    </div>
@endsection
