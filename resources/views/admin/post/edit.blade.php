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
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
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
                                        <option value="{{ $tag->id }}" {{ in_array($tag->id, $selectedTagIds ?? []) ? 'selected' : '' }}>{{ $tag->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="dietaries" class="col-form-label">Dietaries</label>
                            <div class="w-50">
                                <select name="dietaries[]" id="dietaries" class="select2" multiple>
                                    @foreach($dietaries as $dietary)
                                        <option value="{{ $dietary->id }}" {{ in_array($dietary->id, $selectedDietaryIds ?? []) ? 'selected' : '' }}>{{ $dietary->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="cuisines" class="col-form-label">Cuisines</label>
                            <div class="w-50">
                                <select name="cuisines[]" id="cuisines" class="form-control select2 w-50" multiple>
                                    @foreach($cuisines as $cuisine)
                                        <option value="{{ $cuisine->id }}" {{ in_array($cuisine->id, $selectedCuisineIds ?? []) ? 'selected' : '' }}>{{ $cuisine->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>

                        <h3 class="text-lg font-bold mt-6 mb-2">Ingredients</h3>
                        <div class="form-group">
                            <div id="ingredients-container">
                                @php $ingredientIndex = 0; @endphp
                                @foreach($postIngredients as $ingredient)
                                    <div class="d-flex align-items-center gap-2 mb-2 ingredient-item" data-index="{{ $ingredientIndex }}">
                                        <input type="hidden" name="ingredients[{{ $ingredientIndex }}][id]" value="{{ $ingredient->ingredient_id }}">
                                        <input type="hidden" name="ingredients[{{ $ingredientIndex }}][measurement]" value="{{ $ingredient->measurement }}">
                                        <span class="badge bg-secondary">
                                            {{ $ingredients->firstWhere('id', $ingredient->ingredient_id)->name ?? 'Unknown ingredient' }}
                                        </span>
                                        <span class="text-muted">{{ $ingredient->measurement }}</span>
                                        <button type="button" class="btn btn-sm btn-danger remove-ingredient">Remove</button>
                                    </div>
                                    @php $ingredientIndex++; @endphp
                                @endforeach
                            </div>

                            <div class="d-flex gap-2 align-items-center mt-3">
                                <select id="ingredient-selector" class="form-control select2" style="width: 300px;">
                                    <option value="" disabled selected>Select ingredient</option>
                                    @foreach($ingredients as $ingredient)
                                        <option value="{{ $ingredient->id }}" data-weight="{{ $ingredient->weight }}">{{ $ingredient->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" id="ingredient-measurement" class="form-control" placeholder="Measurement (e.g. 200g)">
                                <button type="button" id="add-ingredient" class="btn btn-primary btn-sm">Add</button>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="file" class="col-form-label">File</label>
                            <div class="w-50">
                                <input type="file" name="file" id="file" class="form-control d-none">
                                <button type="button" class="btn btn-primary w-100" id="file-btn">
                                    {{ $data->file ? 'Change file' : 'Add file' }}
                                </button>

                                <div id="file-preview-container" class="mt-3">
                                    @if($data->file)
                                        @php
                                            $urlPath = parse_url($data->file, PHP_URL_PATH);
                                        @endphp
                                        <div class="file-preview-wrapper">
                                            <div style="background: gray; margin: 5px 0">
                                                <a href="{{ $data->file }}" target="_blank" id="file-link">
                                                    @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $urlPath))
                                                        <img src="{{ $data->file }}" alt="File" class="img-thumbnail" style="max-width: 200px; display: block; margin: 0 auto">
                                                    @elseif(preg_match('/\.(mp4|webm|ogg|avi|mov|mkv|wmv|flv)$/i', $urlPath))
                                                        <video src="{{ $data->file }}" controls style="max-width: 100%; max-height: 300px; display: block; margin: 0 auto"></video>
                                                    @else
                                                        <span class="text-muted">Uploaded file</span>
                                                    @endif
                                                </a>
                                            </div>
                                            <button type="button" class="btn btn-danger mt-2 w-100" id="delete-file-btn" data-id="{{ $data->id }}">Delete file</button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.post.index') }}" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
                            <button type="button" class="btn btn-danger">Delete</button>
                        </div>
                    </form>

                    <hr>

                    <h3 class="text-lg font-bold mt-6 mb-2">Instructions</h3>

                    @for ($i = 0; $i < 4; $i++)
                        @php
                            $thumbnail = $data->thumbnails[$i] ?? null;
                        @endphp

                        <div class="thumbnail-upload mb-4 border p-4 rounded">
                            {{-- === Preview (image/video or empty) === --}}
                            <div class="mb-2" id="preview-section-{{ $i }}">
                                @if ($thumbnail)
                                    @if ($thumbnail->type === 'image')
                                        <img src="{{ $thumbnail->thumbnail }}" class="img-thumbnail" width="100">
                                    @else
                                        <video width="100" controls>
                                            <source src="{{ $thumbnail->thumbnail }}" type="video/mp4">
                                        </video>
                                    @endif
                                @endif
                            </div>

                            {{-- === Main Form (store or update) === --}}
                            <form
                                method="POST"
                                action="{{ $thumbnail ? route('admin.post_thumbnail.update', $thumbnail->id) : route('admin.post_thumbnail.store') }}"
                                enctype="multipart/form-data"
                            >
                                @csrf
                                @if($thumbnail)
                                    @method('PUT')
                                @endif

                                <input type="hidden" name="post_id" value="{{ $data->id }}">
                                <input type="file" name="file" class="form-control thumbnail-input" accept="image/*,video/*" data-index="{{ $i }}">

                                <div class="form-group mb-2 mt-2">
                                    <input type="text" name="title" value="{{ old("thumbnails.$i.title", $thumbnail->title ?? '') }}" class="form-control" placeholder="Thumbnail title">
                                </div>

                                <div class="form-group mb-2">
                                    <textarea name="description" class="form-control" rows="2" placeholder="Thumbnail description">{{ old("thumbnails.$i.description", $thumbnail->description ?? '') }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-{{ $thumbnail ? 'warning' : 'info' }} btn-sm">
                                    {{ $thumbnail ? 'Update' : 'Add' }}
                                </button>
                            </form>

                            {{-- === Separate Delete Form (only if existing thumbnail) === --}}
                            @if ($thumbnail)
                                <form method="POST" action="{{ route('admin.post_thumbnail.delete', $thumbnail->id) }}" class="inline-block mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            @endif
                        </div>
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
        $('#file-btn').on('click', function () {
            $('#file').click();
        });

        $('#delete-file-btn').on('click', function () {
            const postId = $(this).data('id');

            if (!confirm('Are you sure you want to delete the file?')) return;

            $.ajax({
                url: `/admin/post/${postId}/file`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function () {
                    $('#file-preview-container').html('<p class="text-muted">File has been deleted.</p>');
                    $('#file-btn').text('Add file');
                },
                error: function () {
                    alert('Error deleting file.');
                }
            });
        });

        $('#file').on('change', function () {
            const fileName = this.files[0]?.name;
            if (fileName) {
                $('#file-btn').text('File selected: ' + fileName);
                $('#file-preview-container').html('<p class="text-muted">File selected. Preview will be available after processing.</p>');
            }
        });

        $(document).ready(function() {
            $('#ingredient-selector').on('change', function () {
                const selectedOption = $(this).find('option:selected');
                const weight = selectedOption.data('weight');

                if (weight !== undefined && weight !== null) {
                    $('#ingredient-measurement').val(weight);
                } else {
                    $('#ingredient-measurement').val('');
                }
            });

            $('.select2').select2();

            $('.thumbnail-input').on('change', function (e) {
                const file = e.target.files[0];
                const index = $(this).data('index');
                const previewSection = $('#preview-section-' + index);

                if (file) {
                    const reader = new FileReader();

                    if (file.type.startsWith('image/')) {
                        reader.onload = function (e) {
                            previewSection.html('<img src="' + e.target.result + '" class="img-thumbnail" width="100">');
                        };
                        reader.readAsDataURL(file);
                    } else if (file.type.startsWith('video/')) {
                        reader.onload = function (e) {
                            previewSection.html('<video width="100" controls><source src="' + e.target.result + '" type="' + file.type + '"></video>');
                        };
                        reader.readAsDataURL(file);
                    }
                }
            });

            let ingredientIndex = $('#ingredients-container .ingredient-item').length;
            $('#add-ingredient').on('click', function () {
                const ingredientId = $('#ingredient-selector').val();
                const ingredientName = $('#ingredient-selector option:selected').text();
                const measurement = $('#ingredient-measurement').val().trim();

                if (!ingredientId || !measurement) {
                    alert('Please select an ingredient and enter measurement.');
                    return;
                }

                const ingredientHtml = `
                <div class="d-flex align-items-center gap-2 mb-2 ingredient-item" data-index="${ingredientIndex}">
                    <input type="hidden" name="ingredients[${ingredientIndex}][id]" value="${ingredientId}">
                    <input type="hidden" name="ingredients[${ingredientIndex}][measurement]" value="${measurement}">
                    <span class="badge bg-secondary">${ingredientName}</span>
                    <span class="text-muted">${measurement}</span>
                    <button type="button" class="btn btn-sm btn-danger remove-ingredient">Remove</button>
                </div>
            `;

                $('#ingredients-container').append(ingredientHtml);

                $('#ingredient-selector').val(null).trigger('change');
                $('#ingredient-measurement').val('');
                ingredientIndex++;
            });

            $('#ingredients-container').on('click', '.remove-ingredient', function () {
                $(this).closest('.ingredient-item').remove();
            });

        });

    </script>
@endsection
