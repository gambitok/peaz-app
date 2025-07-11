@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">Edit Tag</h1>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.tag.update', $tag['id']) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="link">Name:</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $tag['name'] }}">
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.tag.index') }}" class="btn btn-secondary">Back to tag</a>
                </form>
            </div>
        </div>
    </div>
@endsection
