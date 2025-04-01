@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 col-sm-12">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-body p-4">
                        <h1 class="mb-4 text-center">Edit Billboard</h1>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.billboards.update', $billboard['id']) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="link" class="form-label">Link</label>
                                <input type="text" class="form-control" id="link" name="link" placeholder="Enter link" value="{{ old('link', $billboard['link']) }}">
                            </div>

                            @foreach(['file' => 'File', 'logo_file' => 'Logo File', 'horizontal_file' => 'Horizontal File', 'video_file' => 'Video File'] as $field => $label)
                                <div class="mb-3">
                                    <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                                    <input type="file" name="{{ $field }}" id="{{ $field }}" class="form-control">
                                    @if($billboard[$field])
                                        <div class="mt-2">
                                            @if($field == 'video_file')
                                                <video width="100%" height="240" controls>
                                                    <source src="{{ $billboard[$field] }}" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            @else
                                                <img src="{{ $billboard[$field] }}" alt="image" class="img-thumbnail" width="140" height="140">
                                            @endif
                                        </div>
                                    @else
                                        <p class="text-muted small">No {{ strtolower($label) }} uploaded</p>
                                    @endif
                                </div>
                            @endforeach

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="status" name="status" {{ old('status', $billboard['status']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">Active</label>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a href="{{ route('admin.billboards.index') }}" class="btn btn-secondary">Back</a>
                            </div>
                        </form>

                        <form action="{{ route('admin.billboards.destroy', $billboard->id) }}" method="POST" class="mt-3" onsubmit="return confirm('Are you sure you want to delete this?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
