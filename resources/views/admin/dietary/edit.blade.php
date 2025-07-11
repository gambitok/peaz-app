@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">Edit Dietary</h1>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.dietary.update', $dietary['id']) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="link">Name:</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $dietary['name'] }}">
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.dietary.index') }}" class="btn btn-secondary">Back to dietary</a>
                </form>
            </div>
        </div>
    </div>
@endsection
