<?php

use App\Http\Controllers\Api\V1\GuestController;
use App\Http\Controllers\Api\V2\ReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\S3Controller;
use App\Http\Controllers\Api\V2\UserController;
use App\Http\Controllers\Api\V1\UserController as UserV1Controller;
use App\Http\Controllers\Api\V2\PostController;
use App\Http\Controllers\Api\V1\PostController as PostV1Controller;
use App\Http\Controllers\Api\V2\PostLikeController;
use App\Http\Controllers\Api\V2\PostCommentController;

Route::post('login', [UserController::class, 'login']);
Route::put('register', [UserController::class, 'register']);
Route::post('logout', [UserController::class, 'logout'])->middleware('auth:api');

Route::prefix('profile')->middleware('auth:api')->group(function () {
    Route::get('/', [UserController::class, 'getProfile']);
    Route::get('get-user-posts', [PostController::class, 'getUserPosts']);
    Route::get('get-user-liked-posts', [PostController::class, 'getUserLikedPosts']);
    Route::get('get-user-recipes-and-comments', [PostController::class, 'getUserRecipesAndComments']);
});

Route::prefix('v2')->middleware('auth:api')->group(function () {
    Route::get('users/search', [UserController::class, 'search'])->name('v2.users.search');
    Route::get('users/searchProfile', [UserController::class, 'searchProfile'])->name('v2.users.searchProfile');

    Route::get('user', [UserController::class, 'getUsers'])->name('v2.user.index');
    Route::get('user/{id}', [UserController::class, 'getUser'])->name('v2.user.show');
    Route::put('user/{id}', [UserController::class, 'updateUser'])->name('v2.user.update');
    Route::post('user/create', [UserController::class, 'addUserById'])->name('v2.user.create');
    Route::delete('user/{id}', [UserController::class, 'deleteUser'])->name('v2.user.delete');

    Route::post('user/post-like', [PostController::class, 'postLike']);
    Route::post('user/comment-like', [PostController::class, 'commentLike']);
    Route::post('user/interested-list', [UserController::class, 'interestedList']);

    Route::get('user/report-list', [ReportController::class, 'reportList']);
    Route::post('user/report-status', [ReportController::class, 'reportStatus']);

    Route::get('comments', [PostCommentController::class, 'getCommentsByUserId']);
    Route::get('likes', [PostLikeController::class, 'getLikes']);
    Route::get('likes-grouped-by-cuisines', [PostLikeController::class, 'getLikesGroupedByCuisines']);
    Route::get('post-details/{id}', [PostController::class, 'details'])->name('posts.details');

    Route::get('posts/search', [PostController::class, 'search'])->name('posts.search');
    Route::get('posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('posts/{id}', [PostController::class, 'show'])->name('posts.show');
    Route::post('posts/', [PostController::class, 'store'])->name('posts.store');
    Route::put('posts/{id}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy');
});

//Route::post('convert-video', [S3Controller::class, 'convertVideo']);
Route::get('generate-presigned-url', [S3Controller::class, 'generatePresignedUrl']);
Route::post('initiate-multipart-upload', [S3Controller::class, 'initiateMultipartUpload']);
Route::post('generate-multipart-presigned-url', [S3Controller::class, 'generateMultipartPresignedUrl']);
Route::post('complete-multipart-upload', [S3Controller::class, 'completeMultipartUpload']);

Route::group(['namespace' => 'Api\V1', 'prefix' => 'V1'], function () {

//    Route::post('verify-OTP-mobile', [GuestController::class, 'verifyOTPmobile']);
//    Route::post('send-OTP-email', [GuestController::class, 'sendOTPemail']);
//    Route::post('verify-OTP-email', [GuestController::class, 'verifyOTPemail']);
//    Route::post('verifyOTP', [GuestController::class, 'verifyOTP']);
//    Route::post('resetPassword', [GuestController::class, 'resetPassword']);
//    Route::post('login', [GuestController::class, 'login']);
//    Route::post('signup', [GuestController::class, 'signup']);
//    Route::post('forgot_password', [GuestController::class, 'forgot_password']);
//    Route::post('home', [PostV1Controller::class, 'home']);

    Route::post('check-social-ability', [GuestController::class, 'check_social_ability']);
    Route::get('content/{type}', [GuestController::class, 'content']);
    Route::post('check_ability', [GuestController::class, 'check_ability']);
    Route::post('version_checker', [GuestController::class, 'version_checker']);

    Route::post('post-details', [PostV1Controller::class, 'postDetails']);
    Route::post('comment-list', [PostV1Controller::class, 'commentList']);
    Route::get('get-tag', [PostV1Controller::class, 'getTag']);

    Route::post('user/post-comment-review', [PostV1Controller::class, 'postCommentReview']);
    Route::post('user/delete-comment', [PostV1Controller::class, 'deleteComment']);
    Route::post('user/update-comment', [PostV1Controller::class, 'updateComment']);

    Route::post('user/post-like-list', [PostV1Controller::class, 'postLikeList']);

    Route::post('user/add-ingredient', [PostV1Controller::class, 'addIngredient']);
    Route::post('user/add-instruction', [PostV1Controller::class, 'addInstruction']);

    Route::post('user/add-user-name', [UserV1Controller::class, 'addUsername']);
    Route::post('user/add-user-fullname', [UserV1Controller::class, 'addFullname']);
    Route::post('user/serch-user-name', [UserV1Controller::class, 'serchUsername']);
    Route::post('user/save-user-Interested', [UserV1Controller::class, 'saveUserInterested']);
    Route::post('user/userDiet', [UserV1Controller::class, 'userDiet']);
    Route::post('user/createpassword', [UserV1Controller::class, 'createpassword']);
    Route::post('user/not-interested', [PostV1Controller::class, 'notInterested']);
    Route::post('user/search-username', [PostV1Controller::class, 'searchUsername']);
    Route::post('user/user-tag', [PostV1Controller::class, 'userTag']);

//    Route::group(['prefix' => 'user'], function () {
//        Route::get('getProfile', [UserV1Controller::class, 'getProfile']);
//        Route::get('logout', [UserV1Controller::class, 'logout']);
//        Route::post('create-post', [PostV1Controller::class, 'createPost']);
//        Route::post('delete-post', [PostV1Controller::class, 'destory']);
//    });

//    Route::group(['middleware' => 'ApiTokenChecker'], function () {
//        Route::group(['prefix' => 'user'], function () {
//
//
//        });
//    });
});


