@extends('layouts.master')

@section('title')
    @lang('translation.Form_Layouts')
@endsection

@section('content')

    @include('components.breadcum')
    <div class="row">
        <div class="col-12">
        </div>
        <div class="card">
            <div class="card-body">
                <form class="" name="main_form" id="main_form" method="post" action="{{route('admin.user.store')}}" enctype="multipart/form-data">
                    {!! get_error_html($errors) !!}
                    @csrf
                    <input type="hidden" name="country_code" id="country_code" value="+1">

                    <div class="mb-3 row">
                        <label for="example-text-input" class="col-md-2 col-form-label">{{__('Profile Image')}}</label>
                        <div class="col-md-8">
                            <input type="file" accept="image/*" id="profile_image" class="form-control" name="profile_image">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="example-text-input" class="col-md-2 col-form-label"><span class="text-danger">*</span>Name</label>
                        <div class="col-md-10">
                            <input type="text" name="name" id="name" class="form-control" value="" maxlength="50">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="example-text-input" class="col-md-2 col-form-label"><span class="text-danger">*</span>Username</label>
                        <div class="col-md-10">
                            <input type="text" name="username" id="username" class="form-control" value="" maxlength="50">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="example-text-input" class="col-md-2 col-form-label"><span class="text-danger">*</span>Email</label>
                        <div class="col-md-10">
                            <input type="email" name="email" id="email" class="form-control" value="">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="example-text-input" class="col-md-2 col-form-label">Biography</label>
                        <div class="col-md-10">
                            <input type="text" name="bio" id="bio" class="form-control" value="" >
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="example-text-input" class="col-md-2 col-form-label">Website</label>
                        <div class="col-md-10">
                            <input type="text" name="website" id="website" class="form-control" value="" >
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="verified" class="col-md-2  col-form-label">Verified</label>
                        <div class="col-md-10">
                            <label class="switch">
                                <input type="checkbox" class="toggle" id="verified" name="verified" >
                                <span class="slider slider round"></span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="membership_level" class="col-md-2 col-form-label">Membership level</label>
                        <div class="col-md-10">
                            <select name="membership_level" id="membership_level" class="form-control">
                                @foreach($membershipOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="status" class="col-md-2 col-form-label">Status</label>
                        <div class="col-md-10">
                            <select name="status" id="status" class="form-control">
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="kt-portlet__foot">
                        <div class=" ">
                            <div class="row">
                                <div class="col-12 text-right">
                                    <button type="submit" class="btn btn-primary">Create</button>
                                    <a href="{{ route('admin.user.index') }}" class="btn btn-secondary">Back</a>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/16.0.8/js/intlTelInput-jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/16.0.8/css/intlTelInput.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/16.0.8/js/utils.js"></script>
@endsection
