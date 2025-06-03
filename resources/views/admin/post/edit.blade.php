@extends('layouts.master')

@section('css')
    <link href="{{ URL::asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.css"/>
    <link href="{{ URL::asset('/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
@include('components.breadcum')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body" style="display: block; margin: 0 auto;}">
                    <form action="{{ route('admin.post.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="post_id" class="col-form-label">ID</label>
                            <span>{{ $data->id }}</span>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="post_id" class="col-form-label">Join Date</label>
                            <span>{{ $data->created_at ? $data->created_at->format('d-m-Y') : 'N/A' }}</span>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="post_id" class="col-form-label">Profile Name</label>
                            <span>{{ $data->user->name ?? 'No user' }}</span>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="title" class="col-form-label">Title</label>
                            <input type="text" name="title" id="title" class="form-control w-50" value="{{ $data->title }}" required>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="caption" class="col-form-label">Caption</label>
                            <textarea class="form-control w-50" id="caption" name="caption" rows="3">{{ $data->caption }}</textarea>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4 align-items-center">
                            <label class="col-form-label">Total Time</label>
                            <div class="d-flex gap-2 w-50">
                                <input type="number" name="hours" id="hours" class="form-control w-25" value="{{ $data->hours }}">
                                <label for="hours">Hours</label>
                                <input type="number" name="minutes" id="minutes" class="form-control w-25" value="{{ $data->minutes }}">
                                <label for="minutes">Minutes</label>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="serving_size" class="col-form-label">Serving size</label>
                            <input type="number" name="serving_size" id="serving_size" class="form-control w-50" value="{{ $data->serving_size }}">
                        </div>

                        <hr>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="tags" class="col-form-label">Tags</label>
                            <div class="w-50">
                                <select name="tags[]" id="tags" class="select2" multiple>
                                    @foreach($tags as $tag)
                                        <option value="{{ $tag->id }}" {{ in_array($tag->id, $data->tags) ? 'selected' : '' }}>{{ $tag->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="dietaries" class="col-form-label">Dietaries</label>
                            <div class="w-50">
                                <select name="dietaries[]" id="dietaries" class="select2" multiple>
                                    @foreach($dietaries as $dietary)
                                        <option value="{{ $dietary->id }}" {{ in_array($dietary->id, $data->dietaries) ? 'selected' : '' }}>{{ $dietary->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="cuisines" class="col-form-label">Cuisines</label>
                            <div class="w-50">
                                <select name="cuisines[]" id="cuisines" class="form-control select2 w-50" multiple>
                                    @foreach($cuisines as $cuisine)
                                        <option value="{{ $cuisine->id }}" {{ in_array($cuisine->id, $data->cuisines) ? 'selected' : '' }}>{{ $cuisine->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <button type="button" class="btn btn-danger">Delete</button>
                        </div>

                        <div class="d-flex justify-content-end mt-2">
                            <a href="{{ route('admin.post.index') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </form>

                    <hr>

                    <div class="form-group d-flex justify-content-between">
                        <label for="file" class="col-form-label">File</label>
                        <div class="w-50">
                            <input type="file" name="file" id="file" class="form-control upload-file" data-type="file" data-id="{{ $data->id }}" style="display:none;">
                            <button type="button" id="file-btn" class="btn btn-primary w-100">
                                {{ $data->file !== '' ? 'Change' : 'Add new' }}
                            </button>

                            <div id="file-preview-container" class="mt-2">
                                @if($data->file)
                                    <div class="d-flex align-items-center mt-2">
                                        <a href="{{ $data->file }}" target="_blank" id="file-link">
                                            @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $urlPath))
                                                <img src="{{ $data->file }}" alt="File" id="file-preview" style="max-width: 200px; max-height: 200px;">
                                            @elseif(preg_match('/\.(mp4|webm|ogg|avi|mov|mkv|wmv|flv)$/i', $urlPath))
                                                <video src="{{ $data->file }}" id="file-preview" style="max-width: 100%; max-height: 500px;" controls></video>
                                            @endif
                                        </a>
                                        <button class="btn btn-sm btn-danger ms-3 delete-file-btn" data-id="{{ $data->id }}" data-type="file">Delete</button>
                                    </div>
                                @else
                                    <p>No file uploaded</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h3 class="text-lg font-bold mt-6 mb-2">Instructions</h3>

                    @for ($i = 0; $i < 4; $i++)
                        @php
                            $thumbnail = $data->thumbnails[$i] ?? null;
                        @endphp

                        <div class="mb-6 border p-4 rounded-md shadow-sm">
                            <input type="hidden" name="thumbnails[{{ $i }}][id]" value="{{ $thumbnail->id ?? '' }}">

                            <div class="mb-4">
                                @if($thumbnail)
                                    <div class="relative">
                                        @if($thumbnail->type === 'image')
                                            <img src="{{ $thumbnail->thumbnail }}" alt="thumb" class="h-32 object-cover rounded mb-2">
                                        @else
                                            <video controls src="{{ $thumbnail->thumbnail }}" class="h-32 rounded mb-2"></video>
                                        @endif
                                    </div>
                                @endif

                                <input type="file" name="thumbnails[{{ $i }}][file]" class="form-control mt-2">
                            </div>

                            <div class="mb-2">
                                <label class="block text-sm font-semibold">Title</label>
                                <input type="text" name="thumbnails[{{ $i }}][title]" value="{{ old("thumbnails.$i.title", $thumbnail->title ?? '') }}" class="form-control">
                            </div>

                            <div class="mb-2">
                                <label class="block text-sm font-semibold">Description</label>
                                <textarea name="thumbnails[{{ $i }}][description]" class="form-control">{{ old("thumbnails.$i.description", $thumbnail->description ?? '') }}</textarea>
                            </div>

                            <div class="flex justify-between mt-4">
                                @if($thumbnail)
                                    <button type="submit" name="thumbnails[{{ $i }}][action]" value="delete" class="btn btn-danger btn-sm">Delete</button>
                                    <button type="submit" name="thumbnails[{{ $i }}][action]" value="update" class="btn btn-warning btn-sm">Update</button>
                                @else
                                    <button type="submit" name="thumbnails[{{ $i }}][action]" value="add" class="btn btn-info btn-sm">Add</button>
                                @endif
                            </div>
                        </div>
                        <br>
                    @endfor

                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('/assets/admin/vendors/general/validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('/assets/admin/vendors/general/datatable/jquery.dataTables.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox/dist/jquery.fancybox.min.js"></script>
    <script src="{{ asset('/assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@endsection
