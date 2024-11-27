<?php

use Illuminate\Support\Facades\Route;


Route::group(['namespace' => 'Api\V1', 'prefix' => 'V1'], function () {

    // Route::post('send-OTP-mobile', 'GuestController@sendOTPMobile')->middleware('Message_Limit');
    Route::post('verify-OTP-mobile', 'GuestController@verifyOTPmobile');
    Route::post('send-OTP-email', 'GuestController@sendOTPemail');
    Route::post('verify-OTP-email', 'GuestController@verifyOTPemail');
    Route::post('resetPassword', 'GuestController@resetPassword');

    Route::post('verifyOTP', 'GuestController@verifyOTP');
    Route::post('check-social-ability','GuestController@check_social_ability');

    Route::post('login', 'GuestController@login');
    Route::post('signup', 'GuestController@signup');
    Route::post('forgot_password', 'GuestController@forgot_password');
    Route::get('content/{type}', 'GuestController@content');
    Route::post('forgot_password', 'GuestController@forgot_password');
    Route::post('check_ability', 'GuestController@check_ability');
    Route::post('version_checker', 'GuestController@version_checker');
    Route::post('home', 'PostController@home');
    Route::post('post-details', 'PostController@postDetails');
    Route::post('comment-list', 'PostController@commentList');

    Route::get('get-tag', 'PostController@getTag');

    //Country Selection apis here
    Route::group(['middleware' => 'ApiTokenChecker'], function () {
        Route::group(['prefix' => 'user'], function () {
            Route::post('add-user-name', 'UserController@addUsername');
            Route::post('add-user-fullname', 'UserController@addFullname');
            Route::post('serch-user-name', 'UserController@serchUsername');
            Route::post('save-user-Interested', 'UserController@saveUserInterested');
            Route::post('userDiet', 'UserController@userDiet');
            Route::post('createpassword', 'UserController@createpassword');
            Route::get('getProfile', 'UserController@getProfile');
            Route::get('logout', 'UserController@logout');
          
            Route::post('create-post', 'PostController@createPost');
            Route::post('interested-list', 'UserController@interestedList');
            Route::post('add-ingredient', 'PostController@addIngredient');
            Route::post('add-instruction', 'PostController@addInstruction');
           
            Route::post('post-comment-review', 'PostController@postCommentReview');
            Route::post('post-like', 'PostController@postLike');
            Route::post('comment-like', 'PostController@commentLike');
            Route::post('post-like-list', 'PostController@postLikeList');
            Route::post('delete-post', 'PostController@destory');
            Route::post( 'update-post',  'PostController@updatePost');
            Route::post('update-ingredient', 'PostController@updateIngredient');
            Route::post('update-instruction', 'PostController@updateInstruction');
            Route::post('delete-comment', 'PostController@deleteComment');
            Route::post('update-comment', 'PostController@updateComment');
            Route::post('not-interested', 'PostController@notInterested');
            Route::post('search-username','PostController@searchUsername');
            Route::post('user-tag','PostController@userTag');
            Route::get('get-user-posts','PostController@getUserPosts');
            Route::get('get-user-liked-posts','PostController@getUserLikedPosts');
            Route::get('get-user-recipes-and-comments','PostController@getUserRecipesAndComments');

            Route::get('report-list','ReportController@reportList');
            Route::post('report-status','ReportController@reportStatus');
            
        });
    });
});


