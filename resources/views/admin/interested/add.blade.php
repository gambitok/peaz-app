@extends('layouts.master')
@section('title')
    @lang('translation.Data_Tables')
@endsection
@section('content')
    @include('components.breadcum')
<style>
    select {
        -webkit-appearance: listbox !important;
    }
</style>
    <div class="row">
        <div class="col-12">
        </div>
        <div class="card">
            <div class="card-body">
                <form class="" name="main_form" id="main_form" method="post"
                      action="{{route('admin.interestedlist.store')}}" enctype="multipart/form-data">
                    {!! get_error_html($errors) !!}
                    @csrf
                    <div class="mb-3 row ">
                        <label for="example-text-input" class="col-md-2 col-form-label"><span
                                class="text-danger">*</span>{{__('Selecte Option')}}</label>
                        <div class="col-md-10">
                        <select name="type" class=" type form-control " style="color:#495057" >
                            <option vlaue="" selected disabled>Selecte Option</option>
                            <option value="1">Cuisines</option>
                            <option value="2">Food And Drink</option>
                            <option value="3">Diet</option>
                        </select>
                        </div>
                    </div>
                    <div class="mb-3 row category_data d-none">
                        <label for="example-text-input" class="col-md-2 col-form-label"><span
                                class="text-danger">*</span>{{__('Selecte Age')}}</label>
                        <div class="col-md-10">
                        <select name="category" class="category form-control " style="color:#495057">
                            <option vlaue="" selected disabled>Selecte Option</option>
                            @foreach($categories as $category)
                            <option value="{{$category->id}}">{{$category->name}}</option>
                            @endforeach
                        </select>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="example-text-input" class="col-md-2 col-form-label"><span
                                class="text-danger">*</span>{{__('Title')}}</label>
                        <div class="col-md-10">
                            <input type="text" name="title" class="form-control" data-error-container="#error_title">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="example-text-input" class="col-md-2 col-form-label"><span
                                class="text-danger">*</span>{{__('Image')}}</label>
                        <div class="col-md-10">
                        <input type="file" accept="image/*" style="color:#495057" class="form-control" name="image">
                        </div>
                    </div>
                    <div class="kt-portlet__foot">
                        <div class=" ">
                            <div class="row">
                                <div class="wd-sl-modalbtn">
                                    <button type="submit"
                                            class="btn btn-success waves-effect waves-light"
                                            id="save_changes">Submit
                                    </button>
                                    <a href="{{route("admin.interestedlist.index")}}" id="close">
                                        <button type="button"
                                                class="btn btn-outline-secondary waves-effect">
                                            Close
                                        </button>
                                    </a>
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
    $(".category_data").hide();
        $(document).on("change",".type",function(){
           var category =  $(".type").val();
          if(category == 2)
          {
            $(".category_data").removeClass("d-none");
            $(".category_data").show();
          }
          else
          {
             $(".category").val("");
            $(".category_data").addClass("d-none");
            (".category_data").hide();
          }
        });
    $("#main_form").validate({
                rules: {
                    type: {required: true},
                    category:{required:true},
                    title: {
                        required: true,
                        maxlength: 30,
                    },
                    image:{required:true},
                },
                messages: {
                    type: {required: "Please selecte option"},
                    category:{required: "Please selecte category"},
                    title: {
                        required: "Please enter title",
                        maxlength:"title should contains maximum 30 characters only"
                    },
                    image:{  required: "Please selecte image"},
                },
                invalidHandler: function (event, validator){
                    console.log(event);
                },
            });
    </script>
@endsection