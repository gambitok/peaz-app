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
                    <form action="{{ route('admin.post.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="user_id" class="col-form-label">Profile Name</label>
                            <div class="w-50">
                                <select name="user_id" id="user_id" class="select2">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="title" class="col-form-label">Title</label>
                            <input type="text" name="title" id="title" class="form-control w-50" placeholder="Title" required>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="caption" class="col-form-label">Caption</label>
                            <textarea class="form-control w-50" id="caption" name="caption" rows="3" placeholder="Caption" required></textarea>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4 align-items-center">
                            <label class="col-form-label">Total Time</label>
                            <div class="d-flex gap-2 w-50">
                                <input type="number" name="hours" id="hours" class="form-control w-25" placeholder="hours" required>
                                <label for="hours">Hours</label>
                                <input type="number" name="minutes" id="minutes" class="form-control w-25" placeholder="minutes" required>
                                <label for="minutes">Minutes</label>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="serving_size" class="col-form-label">Serving size</label>
                            <input type="number" name="serving_size" id="serving_size" class="form-control w-50" placeholder="Serving size" required>
                        </div>

                        <hr>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="tags" class="col-form-label">Tags</label>
                            <div class="w-50">
                                <select name="tags[]" id="tags" class="select2" multiple>
                                    @foreach($tags as $tag)
                                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="dietaries" class="col-form-label">Dietaries</label>
                            <div class="w-50">
                                <select name="dietaries[]" id="dietaries" class="select2" multiple>
                                    @foreach($dietaries as $dietary)
                                        <option value="{{ $dietary->id }}">{{ $dietary->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-between mt-4 mb-4">
                            <label for="cuisines" class="col-form-label">Cuisines</label>
                            <div class="w-50">
                                <select name="cuisines[]" id="cuisines" class="form-control select2 w-50" multiple>
                                    @foreach($cuisines as $cuisine)
                                        <option value="{{ $cuisine->id }}">{{ $cuisine->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-between">
                            <label for="file" class="col-form-label">File</label>
                            <div class="w-50">
                                <input type="file" name="file" id="file" class="form-control">
                                <p>No file uploaded</p>
                            </div>
                        </div>

                        <hr>

                        <h3 class="text-lg font-bold mt-6 mb-2">Ingredients</h3>
                        <div class="form-group">
                            <div id="ingredients-container"></div>

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

                        <h3 class="text-lg font-bold mt-6 mb-2">Instructions</h3>

                        <div class="form-group">
                            <div id="thumbnail-container">
                                @for($i = 0; $i < 4; $i++)
                                    <div class="thumbnail-upload mb-4 border p-3 rounded">
                                        <div class="mb-2">
                                            <input type="file" name="thumbnails[]" class="form-control thumbnail-input" accept="image/*,video/*">
                                        </div>
                                        <div class="form-group mb-2">
                                            <input type="text" name="thumbnail_titles[]" class="form-control" placeholder="Thumbnail title">
                                        </div>
                                        <div class="form-group mb-2">
                                            <textarea name="thumbnail_descriptions[]" class="form-control" rows="2" placeholder="Thumbnail description"></textarea>
                                        </div>
                                        <div class="preview-container mb-2" id="preview-{{$i}}"></div>
                                        <div class="thumbnail-preview mb-2" id="preview-section-{{$i}}">
                                            <!-- Preview content will be injected here -->
                                        </div>
                                        <button type="button" class="btn btn-danger btn-sm remove-thumbnail" data-index="{{$i}}">Remove</button>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.post.index') }}" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
                        </div>

                    </form>
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

            $('#ingredient-selector').on('change', function () {
                const selectedOption = $(this).find('option:selected');
                const weight = selectedOption.data('weight');

                if (weight !== undefined && weight !== null) {
                    $('#ingredient-measurement').val(weight);
                } else {
                    $('#ingredient-measurement').val('');
                }
            });

            $('.select2').select2({
                placeholder: {
                    id: '-1',
                    text: 'Select an option'
                }
            });

            $('.thumbnail-input').on('change', function(e) {
                var file = e.target.files[0];
                var index = $(this).closest('.thumbnail-upload').find('.remove-thumbnail').data('index');
                var previewSection = $('#preview-section-' + index);
                var removeButton = $(this).siblings('.remove-thumbnail');

                if (file) {
                    var reader = new FileReader();

                    if (file.type.startsWith('image/')) {
                        reader.onload = function(e) {
                            previewSection.html('<img src="' + e.target.result + '" class="img-thumbnail" width="100">');
                            removeButton.show();
                        };
                        reader.readAsDataURL(file);
                    }
                    else if (file.type.startsWith('video/')) {
                        reader.onload = function(e) {
                            previewSection.html('<video width="100" controls><source src="' + e.target.result + '" type="' + file.type + '"></video>');
                            removeButton.show();
                        };
                        reader.readAsDataURL(file);
                    }
                }
            });

            $('.remove-thumbnail').on('click', function() {
                var index = $(this).data('index');
                var inputField = $('input[name="thumbnails[]"]:eq(' + index + ')');
                inputField.val('');
                $('#preview-section-' + index).html('');
                $(this).hide();
            });

            let ingredientIndex = 0;

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
