@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h1 class="mb-4">Edit Billboard</h1>

                        <!-- Display error messages -->
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.billboards.update', $billboard['id']) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="form-group mt-2">
                                <label class="control-label" for="link">Link:</label>
                                <input type="text" class="form-control" id="link" name="link" placeholder="Link" value="{{ old('link', $billboard['link']) }}">
                            </div>
                            <div class="form-group mt-2">
                                <label for="file" class="col-form-label">File</label>
                                <div>
                                    <input type="file" name="file" id="file" class="form-control">
                                    @if($billboard['file'])
                                        <img src="{{ Storage::disk('s3')->url($billboard['file']) }}" alt="image" width="140" height="140">
                                    @else
                                        <p>No file uploaded</p>
                                    @endif
                                </div>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="status" name="status" style="display: inline;" {{ old('status', $billboard['status']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">Status</label>
                            </div>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>

                        <form action="{{ route('admin.billboards.destroy', $billboard->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                        <a href="{{ route('admin.billboards.index') }}" class="btn btn-secondary">Back to Billboards</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
