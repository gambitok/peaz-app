<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\Admin\BillboardViewController;
use App\Http\Controllers\Admin\RestaurantViewController;
use App\Http\Controllers\Admin\PostListController;

Route::group(['middleware' => 'guest', 'namespace' => 'General'], function () {
    Route::post('login', 'GeneralController@login')->name('login_post');
    Route::get('login', 'GeneralController@Panel_Login')->name('login');
    Route::get('forgot_password', 'GeneralController@Panel_Pass_Forget')->name('forgot_password');
    Route::post('forgot_password', 'GeneralController@ForgetPassword')->name('forgot_password_post');
});

Route::group(['middleware' => 'Is_Admin'], function () {

    Route::get('/', 'General\GeneralController@Admin_dashboard')->name('dashboard');

    Route::get('/upload', [FileUploadController::class, 'showForm']);
    Route::post('/upload', [FileUploadController::class, 'uploadFile'])->name('file.upload');

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
        Route::resource('user', 'UsersController')->except(['create', 'store']);

        // Content Module
        Route::resource('content', 'ContentController')->except(['show', 'create', 'store', 'destroy']);
        Route::get('content/listing', 'ContentController@listing')->name('content.listing');

        Route::get('interestedlist/listing', 'InterestedListController@listing')->name('interestedlist.listing');
        Route::resource('interestedlist','InterestedListController');

        Route::get('user_report/listing', 'UserReportController@listing')->name('user_report.listing');
        Route::resource('user_report','UserReportController');

        Route::get('postlist/listing','PostListController@listing')->name('post.listing');
        Route::get('post_details','PostListController@postDetails')->name('post.post_details');
        Route::get('post_details_edit/{id}','PostListController@postDetailsEdit')->name('post.post_details_edit');
        Route::patch('post_details_update/{id}','PostListController@postDetailsUpdate')->name('post.post_details_update');
        Route::delete('post_details_destroy/{id}','PostListController@postDetailsDestroy')->name('post.post_details_destroy');
        Route::get('instruction','PostListController@instruction')->name('post.instruction');

        Route::resource('post','PostListController');

        Route::delete('/post/{id}/delete-file',
            [PostListController::class, 'deleteFile'])->name('admin.post.deleteFile');
        Route::post('/post/{id}/upload-file',
            [PostListController::class, 'uploadFile'])->name('admin.post.uploadFile');

        Route::post('/post/status',
            [PostListController::class, 'updateStatus'])->name('post.status');
        Route::post('/post/verified',
            [PostListController::class, 'updateVerified'])->name('post.verified');

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
    });
});
