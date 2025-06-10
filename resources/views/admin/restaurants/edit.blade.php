@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h1 class="mb-4">Edit Restaurant</h1>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form action="{{ route('admin.restaurants.update', $restaurant['id']) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="title">Title:</label>
                                <input type="text" class="form-control" id="title" name="title" value="{{ $restaurant['title'] }}">
                            </div>
                            <div class="form-group">
                                <label for="link">Link:</label>
                                <input type="text" class="form-control" id="link" name="link" value="{{ $restaurant['link'] }}">
                            </div>
                            <div class="form-group">
                                <label for="file" class="form-label">Logo</label>
                                <input type="file" name="file" id="file" class="form-control">
                                @if($restaurant['file'])
                                    <div class="mt-2">
{{--                                        <img src="{{ Storage::disk('s3')->url($restaurant->file) }}" alt="image" class="img-thumbnail" width="140" height="140">--}}
                                        <img src="{{ $restaurant['file'] }}" alt="image" class="img-thumbnail" width="140" height="140">
                                    </div>
                                @else
                                    <p class="text-muted small">No logo uploaded</p>
                                @endif
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
