@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">Create Dietary</h1>
        <form action="{{ route('admin.dietary.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" class="form-control" id="name" name="name">
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('admin.dietary.index') }}" class="btn btn-secondary">Back to dietary</a>
        </form>
    </div>
@endsection
