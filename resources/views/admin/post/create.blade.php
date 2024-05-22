@extends('layouts.master')

@section('content')
@section('title')
@lang('translation.Form_Layouts')
@endsection @section('content')
@include('components.breadcum')

<div class="row">
    <div class="col-12">
    </div>
    <div class="card">
        <div class="card-body">
            @if(isset($data) && !empty($data))
            <form class="" name="main_form" id="main_form" method="post" action="{{route('admin.post.post_details_update',$data->id)}}">
                @method('PATCH')
                @else
                <!-- <form class="" name="main_form" id="main_form" method="post" action="{{route('admin.post.post_details_update.store')}}" > -->
                    @endif
                    {!! get_error_html($errors) !!}
                    @csrf
                    <div class="mb-3 row">
                        <label for="example-text-input" class="col-md-2 col-form-label"><span class="text-danger">*</span>Name</label>
                        <div class="col-md-10">
                            <input type="text" name="name" id="name" class="form-control" value="{{($data->name) ?? ''}}">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="example-text-input" class="col-md-2 col-form-label">{{__('Type')}}</label>
                        <div class="col-md-10">
                        <input type="text" name="type" id="type" class="form-control" value="{{($data->type) ?? ''}}">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="example-text-input" class="col-md-2 col-form-label">{{__('Measurement')}}</label>
                        <div class="col-md-10">
                        <input type="text" name="measurement" id="measurement" class="form-control" value="{{($data->measurement) ?? ''}}">
                        </div>
                    </div>
                    <div class="kt-portlet__foot">
                        <div class=" ">
                            <div class="row">
                                <div class="wd-sl-modalbtn">
                                    <button type="submit" class="btn btn-success waves-effect waves-light" id="save_changes">Submit</button>
                                    <a href="{{route('admin.post.show',$data->post_id)}}" id="close"><button type="button" class="btn btn-outline-secondary waves-effect">Cancel</button></a>

                                </div>
                            </div>
                        </div>
                    </div>
                </form>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    $(function() {

        // tinymce.init({
        //         selector: 'textarea',
        //         height: 300,
        //         // theme: 'modern',
        //     });

        $("#main_form").validate({
            rules: {
                name: {
                    required: true,
                    maxlength: 30,
                },
              
            },
            messages: {
                name: {
                    required: "Please Enter Title",
                    maxlength: 'The title may not be greater than 30 characters.',
                },
               
            },
            submitHandler: function(form) {
                addOverlay();
                form.submit();
            }
        });

    });
</script>
@endsection