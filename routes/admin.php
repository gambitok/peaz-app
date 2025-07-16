<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\Admin\PostListController;
use App\Http\Controllers\Admin\BillboardViewController;
use App\Http\Controllers\Admin\RestaurantViewController;
use App\Http\Controllers\Admin\TagViewController;
use App\Http\Controllers\Admin\DietaryViewController;
use App\Http\Controllers\Admin\CuisineViewController;
use App\Http\Controllers\Admin\FilterViewController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\PostThumbnailController;

Route::group(['middleware' => 'guest', 'namespace' => 'General'], function () {
    Route::post('login', 'GeneralController@login')->name('login_post');
    Route::get('login', 'GeneralController@Panel_Login')->name('login');
    Route::get('forgot_password', 'GeneralController@Panel_Pass_Forget')->name('forgot_password');
    Route::post('forgot_password', 'GeneralController@ForgetPassword')->name('forgot_password_post');
});

Route::group(['middleware' => 'Is_Admin'], function () {

    Route::get('/', 'General\GeneralController@Admin_dashboard')->name('dashboard');

    Route::get('/upload', [FileUploadController::class, 'showForm'])->name('upload.form');
    Route::post('upload', [FileUploadController::class, 'uploadFile'])->name('upload.file');

    Route::get('/totalusers', 'General\GeneralController@totalusers')->name('totalusers');
    Route::get('/profile', 'General\GeneralController@get_profile')->name('profile');
    Route::post('/profile', 'General\GeneralController@post_profile')->name('post_profile');
    Route::get('/update_password', 'General\GeneralController@get_update_password')->name('get_update_password');
    Route::post('/update_password', 'General\GeneralController@update_password')->name('update_password');
    Route::get('/site_settings', 'General\GeneralController@get_site_settings')->name('get_site_settings');
    Route::post('/site_settings', 'General\GeneralController@site_settings')->name('site_settings');

    Route::group(['namespace' => 'Admin'], function () {
        // User Module
        Route::get('user/listing', 'UsersController@listing')->name('user.listing');
        Route::get('user/status_update/{id}', 'UsersController@status_update')->name('user.status_update');
        Route::resource('user', 'UsersController');

        // Content Module
        Route::resource('content', 'ContentController')->except(['show', 'create', 'store', 'destroy']);
        Route::get('content/listing', 'ContentController@listing')->name('content.listing');

        Route::resource('pages', \App\Http\Controllers\Admin\PageController::class);

        Route::get('interestedlist/listing', 'InterestedListController@listing')->name('interestedlist.listing');
        Route::resource('interestedlist','InterestedListController');

        Route::get('user_report/listing', 'UserReportController@listing')->name('user_report.listing');
        Route::resource('user_report','UserReportController');

        Route::get('postlist/listing','PostListController@listing')->name('post.listing');
        Route::get('post_details','PostListController@postDetails')->name('post.post_details');
        Route::get('post_details_create/{id}','PostListController@postDetailsCreate')->name('post.post_details_create');
        Route::post('post_details_store/{id}','PostListController@postDetailsStore')->name('post.post_details_store');
        Route::get('post_details_edit/{id}','PostListController@postDetailsEdit')->name('post.post_details_edit');
        Route::patch('post_details_update/{id}','PostListController@postDetailsUpdate')->name('post.post_details_update');
        Route::delete('post_details_destroy/{id}','PostListController@postDetailsDestroy')->name('post.post_details_destroy');
        Route::get('instruction','PostListController@instruction')->name('post.instruction');

        Route::resource('post','PostListController');
        Route::resource('ingredient','IngredientListController');
        Route::get('ingredientlist/listing','IngredientListController@listing')->name('ingredient.listing');

        Route::post('/post-thumbnails', [PostThumbnailController::class, 'store'])->name('post_thumbnail.store');
        Route::put('/post-thumbnails/{id}', [PostThumbnailController::class, 'update'])->name('post_thumbnail.update');
        Route::delete('/post-thumbnails/{id}', [PostThumbnailController::class, 'delete'])->name('post_thumbnail.delete');

        Route::delete('/post/{id}/delete-file',
            [PostListController::class, 'deleteFile'])->name('admin.post.deleteFile');
        Route::post('/post/{id}/upload-file',
            [PostListController::class, 'uploadFile'])->name('admin.post.uploadFile');

        Route::post('/post/status',
            [PostListController::class, 'updateStatus'])->name('post.status');

        Route::post('/post/verified',
            [PostListController::class, 'updateVerified'])->name('post.verified');

        Route::post('/user/verified',
            [UsersController::class, 'updateUserVerified'])->name('user.verified');

        Route::post('/user/membership',
            [UsersController::class, 'updateUserMembershipLevel'])->name('user.membership');

        Route::post('/user/status',
            [UsersController::class, 'updateUserStatus'])->name('user.status');

        Route::post('/user/duplicate',
            [UsersController::class, 'duplicate'])->name('user.duplicate');

        Route::get('/billboards', [BillboardViewController::class, 'index'])->name('billboards.index');
        Route::get('/billboards/create', [BillboardViewController::class, 'create'])->name('billboards.create');
        Route::post('/billboards', [BillboardViewController::class, 'store'])->name('billboards.store');
        Route::get('/billboards/{id}', [BillboardViewController::class, 'show'])->name('billboards.show');
        Route::get('/billboards/{id}/edit', [BillboardViewController::class, 'edit'])->name('billboards.edit');
        Route::put('/billboards/{id}', [BillboardViewController::class, 'update'])->name('billboards.update');
        Route::delete('/billboards/{id}', [BillboardViewController::class, 'destroy'])->name('billboards.destroy');

        Route::get('/restaurants', [RestaurantViewController::class, 'index'])->name('restaurants.index');
        Route::get('/restaurants/create', [RestaurantViewController::class, 'create'])->name('restaurants.create');
        Route::post('/restaurants', [RestaurantViewController::class, 'store'])->name('restaurants.store');
        Route::get('/restaurants/{id}', [RestaurantViewController::class, 'show'])->name('restaurants.show');
        Route::get('/restaurants/{id}/edit', [RestaurantViewController::class, 'edit'])->name('restaurants.edit');
        Route::put('/restaurants/{id}', [RestaurantViewController::class, 'update'])->name('restaurants.update');
        Route::delete('/restaurants/{id}', [RestaurantViewController::class, 'destroy'])->name('restaurants.destroy');

        Route::get('/tag', [TagViewController::class, 'index'])->name('tag.index');
        Route::get('/tag/create', [TagViewController::class, 'create'])->name('tag.create');
        Route::post('/tag', [TagViewController::class, 'store'])->name('tag.store');
        Route::get('/tag/{id}', [TagViewController::class, 'show'])->name('tag.show');
        Route::get('/tag/{id}/edit', [TagViewController::class, 'edit'])->name('tag.edit');
        Route::put('/tag/{id}', [TagViewController::class, 'update'])->name('tag.update');
        Route::delete('/tag/{id}', [TagViewController::class, 'destroy'])->name('tag.destroy');

        Route::get('/dietary', [DietaryViewController::class, 'index'])->name('dietary.index');
        Route::get('/dietary/create', [DietaryViewController::class, 'create'])->name('dietary.create');
        Route::post('/dietary', [DietaryViewController::class, 'store'])->name('dietary.store');
        Route::get('/dietary/{id}', [DietaryViewController::class, 'show'])->name('dietary.show');
        Route::get('/dietary/{id}/edit', [DietaryViewController::class, 'edit'])->name('dietary.edit');
        Route::put('/dietary/{id}', [DietaryViewController::class, 'update'])->name('dietary.update');
        Route::delete('/dietary/{id}', [DietaryViewController::class, 'destroy'])->name('dietary.destroy');

        Route::get('/cuisine', [CuisineViewController::class, 'index'])->name('cuisine.index');
        Route::get('/cuisine/create', [CuisineViewController::class, 'create'])->name('cuisine.create');
        Route::post('/cuisine', [CuisineViewController::class, 'store'])->name('cuisine.store');
        Route::get('/cuisine/{id}', [CuisineViewController::class, 'show'])->name('cuisine.show');
        Route::get('/cuisine/{id}/edit', [CuisineViewController::class, 'edit'])->name('cuisine.edit');
        Route::put('/cuisine/{id}', [CuisineViewController::class, 'update'])->name('cuisine.update');
        Route::delete('/cuisine/{id}', [CuisineViewController::class, 'destroy'])->name('cuisine.destroy');

        Route::get('/filter', [FilterViewController::class, 'index'])->name('filter.index');
        Route::get('/filter/create', [FilterViewController::class, 'create'])->name('filter.create');
        Route::post('/filter', [FilterViewController::class, 'store'])->name('filter.store');
        Route::get('/filter/{id}', [FilterViewController::class, 'show'])->name('filter.show');
        Route::get('/filter/{id}/edit', [FilterViewController::class, 'edit'])->name('filter.edit');
        Route::put('/filter/{id}', [FilterViewController::class, 'update'])->name('filter.update');
        Route::delete('/filter/{id}', [FilterViewController::class, 'destroy'])->name('filter.destroy');

    });
});
