<?php

use App\Http\Controllers\Api\V1\GuestController;
use App\Http\Controllers\Api\V2\OTPController;
use App\Http\Controllers\Api\V2\ReportController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\S3Controller;

use App\Http\Controllers\Api\V2\UserController;
use App\Http\Controllers\Api\V1\UserController as UserV1Controller;

use App\Http\Controllers\Api\V2\PostController;
use App\Http\Controllers\Api\V2\IngredientController;
use App\Http\Controllers\Api\V2\PostIngredientController;
use App\Http\Controllers\Api\V2\PostThumbnailController;
use App\Http\Controllers\Api\V1\PostController as PostV1Controller;
use App\Http\Controllers\Api\V2\PostLikeController;
use App\Http\Controllers\Api\V2\PostCommentController;

use App\Http\Controllers\Api\V2\TagController;
use App\Http\Controllers\Api\V2\DietaryController;
use App\Http\Controllers\Api\V2\CuisineController;
use App\Http\Controllers\Api\V2\CommentRatingController;
use App\Http\Controllers\Api\UserInterestController;
use App\Http\Controllers\Api\PageController;

Route::post('v2/auth/firebase', [\App\Http\Controllers\Api\V2\FirebaseAuthController::class, 'loginWithFirebase']);

Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);
//Route::post('signup', [UserController::class, 'register']);
Route::post('logout', [UserController::class, 'logout'])->middleware('auth:api');

Route::post('comments/{comment}/rate', [CommentRatingController::class, 'rate'])->middleware('auth:api');

Route::prefix('profile')->middleware('auth:api')->group(function () {
    Route::get('/', [UserController::class, 'getProfile']);
    Route::get('get-user-posts', [PostController::class, 'getUserPosts']);
    Route::get('get-user-liked-posts', [PostController::class, 'getUserLikedPosts']);
    Route::get('get-user-recipes-and-comments', [PostController::class, 'getUserRecipesAndComments']);
});

Route::prefix('v2')->middleware('auth:api')->group(function () {

    Route::post('pages', [PageController::class, 'store']);

    Route::get('pages/{slug}', [PageController::class, 'show']);

    Route::post('update-password', [UserController::class, 'updatePassword']);

    Route::post('/send-OTP-mobile', [OTPController::class, 'sendOtpMobile']);
    Route::post('/verify-OTP-mobile', [OTPController::class, 'verifyOtpMobile']);
    Route::post('/send-otp-email', [OTPController::class, 'sendOtpEmail']);
    Route::post('/verify-OTP-email', [OTPController::class, 'verifyOtpEmail']);

    Route::apiResource('billboards', BillboardController::class);
    Route::apiResource('restaurants', RestaurantController::class);

    Route::apiResource('filters', FilterController::class);

    Route::get('user-interests/by-user', [UserInterestController::class, 'byUser']);

    Route::resource('user-interests', UserInterestController::class);

    Route::apiResource('user-relationships', UserRelationshipController::class);
    Route::post('follow', [App\Http\Controllers\Api\UserRelationshipController::class, 'follow'])->name('user-relationship.follow');
    Route::delete('unfollow/{id}', [App\Http\Controllers\Api\UserRelationshipController::class, 'unfollow'])->name('user-relationship.unfollow');
    Route::get('followers', [App\Http\Controllers\Api\UserRelationshipController::class, 'getFollowers'])->name('user-relationship.followers');
    Route::get('following', [App\Http\Controllers\Api\UserRelationshipController::class, 'getFollowing'])->name('user-relationship.following');

    Route::get('tags', [TagController::class, 'index'])->name('tags.index');
    Route::post('tags/', [TagController::class, 'store'])->name('tags.store');
    Route::get('dietaries', [DietaryController::class, 'index'])->name('dietaries.index');
    Route::post('dietaries/', [DietaryController::class, 'store'])->name('dietaries.store');
    Route::get('cuisines', [CuisineController::class, 'index'])->name('cuisines.index');
    Route::post('cuisines/', [CuisineController::class, 'store'])->name('cuisines.store');

    Route::get('users/search', [UserController::class, 'search'])->name('v2.users.search');
    Route::get('users/searchProfile', [UserController::class, 'searchProfile'])->name('v2.users.searchProfile');

    Route::get('user', [UserController::class, 'index'])->name('v2.user.index');
    Route::get('user/{id}', [UserController::class, 'show'])->name('v2.user.show');
    Route::put('user/{id}', [UserController::class, 'update'])->name('v2.user.update');
    Route::post('user/create', [UserController::class, 'create'])->name('v2.user.create');
    Route::delete('user/{id}', [UserController::class, 'destroy'])->name('v2.user.delete');

    Route::post('user/post-like', [PostController::class, 'postLike']);
    Route::post('user/comment-like', [PostController::class, 'commentLike']);
    Route::post('user/interested-list', [UserController::class, 'interestedList']);
    Route::post('user/add-ingredient', [PostController::class, 'addIngredient']);
    Route::post('user/add-instruction', [PostController::class, 'addInstruction']);

    Route::get('user/report-list', [ReportController::class, 'reportList']);
    Route::post('user/report-status', [ReportController::class, 'reportStatus']);
    Route::post('user/post-comment-review', [PostController::class, 'postCommentReview']);

    Route::get('comments', [PostCommentController::class, 'getCommentsByUserId']);
    Route::get('post-comments', [PostCommentController::class, 'getCommentsByPostId']);
    Route::get('likes', [PostLikeController::class, 'getLikes']);
    Route::get('likes-grouped-by-cuisines', [PostLikeController::class, 'getLikesGroupedByCuisines']);
    Route::get('post-details/{id}', [PostController::class, 'details'])->name('posts.details');

    Route::get('posts/by-filter/{filter_id}', [PostController::class, 'byFilter']);

    Route::get('ingredients', [IngredientController::class, 'index'])->name('ingredients.index');

    Route::get('post-ingredients', [PostIngredientController::class, 'index'])->name('posts.ingredients.index');
    Route::get('post-ingredients/{id}', [PostIngredientController::class, 'show'])->name('posts.ingredients.show');
    Route::post('post-ingredients/', [PostIngredientController::class, 'store'])->name('posts.ingredients.store');
    Route::put('post-ingredients/{id}', [PostIngredientController::class, 'update'])->name('posts.ingredients.update');
    Route::delete('post-ingredients/{id}', [PostIngredientController::class, 'destroy'])->name('posts.ingredients.destroy');

    Route::get('instructions', [PostThumbnailController::class, 'index'])->name('instructions.index');
    Route::get('instructions/{id}', [PostThumbnailController::class, 'show'])->name('instructions.show');
    Route::post('instructions/', [PostThumbnailController::class, 'store'])->name('instructions.store');
    Route::put('instructions/{id}', [PostThumbnailController::class, 'update'])->name('instructions.update');
    Route::delete('instructions/{id}', [PostThumbnailController::class, 'destroy'])->name('instructions.destroy');

    Route::get('posts/search', [PostController::class, 'search'])->name('posts.search');
    Route::get('posts/interests-search', [PostController::class, 'interestsSearch'])->name('posts.interests-search');
    Route::get('posts/user-search', [PostController::class, 'userSearch'])->name('posts.user-search');
    Route::get('posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('posts/{id}', [PostController::class, 'show'])->name('posts.show');
    Route::post('posts/', [PostController::class, 'store'])->name('posts.store');
    Route::put('posts/{id}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy');
});

Route::get('generate-presigned-url', [S3Controller::class, 'generatePresignedUrl']);
Route::post('initiate-multipart-upload', [S3Controller::class, 'initiateMultipartUpload']);
Route::post('generate-multipart-presigned-url', [S3Controller::class, 'generateMultipartPresignedUrl']);
Route::post('complete-multipart-upload', [S3Controller::class, 'completeMultipartUpload']);

Route::group(['namespace' => 'Api\V1', 'prefix' => 'V1'], function () {

    Route::post('check-social-ability', [GuestController::class, 'check_social_ability']);
    Route::get('content/{type}', [GuestController::class, 'content']);
    Route::post('check_ability', [GuestController::class, 'check_ability']);
    Route::post('version_checker', [GuestController::class, 'version_checker']);

    Route::post('post-details', [PostV1Controller::class, 'postDetails']);
    Route::post('comment-list', [PostV1Controller::class, 'commentList']);
    Route::get('get-tag', [PostV1Controller::class, 'getTag']);

    Route::post('user/delete-comment', [PostV1Controller::class, 'deleteComment']);
    Route::post('user/update-comment', [PostV1Controller::class, 'updateComment']);

    Route::post('user/post-like-list', [PostV1Controller::class, 'postLikeList']);

    Route::post('user/add-user-name', [UserV1Controller::class, 'addUsername']);
    Route::post('user/add-user-fullname', [UserV1Controller::class, 'addFullName']);
    Route::post('user/serch-user-name', [UserV1Controller::class, 'searchUsername']);
    Route::post('user/save-user-Interested', [UserV1Controller::class, 'saveUserInterested']);
    Route::post('user/userDiet', [UserV1Controller::class, 'userDiet']);
    Route::post('user/createpassword', [UserV1Controller::class, 'createPassword']);
    Route::post('user/not-interested', [PostV1Controller::class, 'notInterested']);
    Route::post('user/search-username', [PostV1Controller::class, 'searchUsername']);
    Route::post('user/user-tag', [PostV1Controller::class, 'userTag']);

});


