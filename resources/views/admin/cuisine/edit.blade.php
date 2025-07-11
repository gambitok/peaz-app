@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">Edit cuisine</h1>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.cuisine.update', $cuisine['id']) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="link">Name:</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $cuisine['name'] }}">
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.cuisine.index') }}" class="btn btn-secondary">Back to Сuisines</a>
                </form>
            </div>
        </div>
    </div>
@endsection
