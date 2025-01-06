<?php

use App\Http\Controllers\Api\V1\GuestController;
use App\Http\Controllers\Api\V1\ReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\S3Controller;
use App\Http\Controllers\Api\V2\UserController;
use App\Http\Controllers\Api\V2\PostController;
use App\Http\Controllers\Api\V2\PostLikeController;
use App\Http\Controllers\Api\V2\PostCommentController;

Route::post('/convert-video', [S3Controller::class, 'convertVideo']);

Route::get('generate-presigned-url', [S3Controller::class, 'generatePresignedUrl']);

Route::post('initiate-multipart-upload', [S3Controller::class, 'initiateMultipartUpload']);
Route::post('generate-multipart-presigned-url', [S3Controller::class, 'generateMultipartPresignedUrl']);
Route::post('complete-multipart-upload', [S3Controller::class, 'completeMultipartUpload']);

Route::group(['namespace' => 'Api\V2', 'prefix' => 'v2'], function () {
    Route::post('login', [UserController::class, 'login']);
    Route::put('register', [UserController::class, 'register']);
    Route::get('get_token', [UserController::class, 'getToken']);
    Route::get('getProfile', [UserController::class, 'getProfile'])->middleware('auth.api');
    Route::post('logout', [UserController::class, 'logout'])->middleware('auth.api');

    Route::get('users/search', [UserController::class, 'search'])->name('users.search');
    Route::get('users/searchProfile', [UserController::class, 'searchProfile'])->name('users.searchProfile');

    Route::get('user', [UserController::class, 'getUsers']);
    Route::get('user/{id}', [UserController::class, 'getUser']);
    Route::put('user/{id}', [UserController::class, 'updateUser']);
    Route::post('user/create', [UserController::class, 'addUserById']);
    Route::delete('user/{id}', [UserController::class, 'deleteUser']);

    Route::get('profile/{id}', [UserController::class, 'getUserProfile']);
    Route::get('comments', [PostCommentController::class, 'getCommentsByUserId']);
    Route::get('likes', [PostLikeController::class, 'getLikes']);
    Route::get('likes-grouped-by-cuisines', [PostLikeController::class, 'getLikesGroupedByCuisines']);

    Route::get('post-details/{id}', [PostController::class, 'details'])->name('posts.details');
});

Route::prefix('v2/posts')->group(function () {
    // Custom search route before other dynamic routes
    Route::get('/search', [PostController::class, 'search'])->name('posts.search');

    // Get all posts
    Route::get('/', [PostController::class, 'index'])->name('posts.index');

    // Get a single post by ID
    Route::get('/{id}', [PostController::class, 'show'])->name('posts.show');

    // Create a new post
    Route::post('/', [PostController::class, 'store'])->name('posts.store');

    // Update an existing post by ID
    Route::put('/{id}', [PostController::class, 'update'])->name('posts.update');

    // Delete a post by ID
    Route::delete('/{id}', [PostController::class, 'destroy'])->name('posts.destroy');
});

Route::group(['namespace' => 'Api\V1', 'prefix' => 'V1'], function () {

    Route::post('verify-OTP-mobile', [GuestController::class, 'verifyOTPmobile']);
    Route::post('send-OTP-email', [GuestController::class, 'sendOTPemail']);
    Route::post('verify-OTP-email', [GuestController::class, 'verifyOTPemail']);
    Route::post('resetPassword', [GuestController::class, 'resetPassword']);

    Route::post('verifyOTP', [GuestController::class, 'verifyOTP']);
    Route::post('check-social-ability', [GuestController::class, 'check_social_ability']);

    Route::post('login', [GuestController::class, 'login']);
    Route::post('signup', [GuestController::class, 'signup']);
    Route::post('forgot_password', [GuestController::class, 'forgot_password']);
    Route::get('content/{type}', [GuestController::class, 'content']);
    Route::post('forgot_password', [GuestController::class, 'forgot_password']);
    Route::post('check_ability', [GuestController::class, 'check_ability']);
    Route::post('version_checker', [GuestController::class, 'version_checker']);
    Route::post('home', [PostController::class, 'home']);
    Route::post('post-details', [PostController::class, 'postDetails']);
    Route::post('comment-list', [PostController::class, 'commentList']);

    Route::get('get-tag', [PostController::class, 'getTag']);

    //Country Selection apis here
    Route::group(['middleware' => 'ApiTokenChecker'], function () {
        Route::group(['prefix' => 'user'], function () {
            Route::post('add-user-name', [UserController::class, 'addUsername']);
            Route::post('add-user-fullname', [UserController::class, 'addFullname']);
            Route::post('serch-user-name', [UserController::class, 'serchUsername']);
            Route::post('save-user-Interested', [UserController::class, 'saveUserInterested']);
            Route::post('userDiet', [UserController::class, 'userDiet']);
            Route::post('createpassword', [UserController::class, 'createpassword']);
            Route::get('getProfile', [UserController::class, 'getProfile']);
            Route::get('logout', [UserController::class, 'logout']);

            Route::post('create-post', [PostController::class, 'createPost']);
            Route::post('interested-list', [UserController::class, 'interestedList']);
            Route::post('add-ingredient', [PostController::class, 'addIngredient']);
            Route::post('add-instruction', [PostController::class, 'addInstruction']);

            Route::post('post-comment-review', [PostController::class, 'postCommentReview']);
            Route::post('post-like', [PostController::class, 'postLike']);
            Route::post('comment-like', [PostController::class, 'commentLike']);
            Route::post('post-like-list', [PostController::class, 'postLikeList']);
            Route::post('delete-post', [PostController::class, 'destory']);
            Route::post('delete-comment', [PostController::class, 'deleteComment']);
            Route::post('update-comment', [PostController::class, 'updateComment']);
            Route::post('not-interested', [PostController::class, 'notInterested']);
            Route::post('search-username', [PostController::class, 'searchUsername']);
            Route::post('user-tag', [PostController::class, 'userTag']);
            Route::get('get-user-posts', [PostController::class, 'getUserPosts']);
            Route::get('get-user-liked-posts', [PostController::class, 'getUserLikedPosts']);
            Route::get('get-user-recipes-and-comments', [PostController::class, 'getUserRecipesAndComments']);

            Route::get('report-list', [ReportController::class, 'reportList']);
            Route::post('report-status', [ReportController::class, 'reportStatus']);
        });
    });
});


