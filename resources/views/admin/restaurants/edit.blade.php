@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h1 class="mb-4">Edit Restaurant</h1>
                        <form action="{{ route('admin.restaurants.update', $restaurant['id']) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="file">File:</label>
                                <input type="text" class="form-control" id="file" name="file" value="{{ $restaurant['file'] }}">
                            </div>
                            <div class="form-group">
                                <label for="link">Link:</label>
                                <input type="text" class="form-control" id="link" name="link" value="{{ $restaurant['link'] }}">
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input"  id="status" name="status" @if($restaurant['status']) checked @endif>
                                <label class="form-check-label" for="status">Status</label>
                            </div>
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('admin.restaurants.index') }}" class="btn btn-secondary">Back to Restaurants</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
