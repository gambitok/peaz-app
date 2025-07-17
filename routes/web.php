<?php

use App\Http\Controllers\Api\V2\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get("clear-cache","General\GeneralController@clearCache");
Route::get("php_info","General\GeneralController@phpInfo");

//Route::get('/login', [UserController::class, 'showLoginForm'])->name('login');
//Route::post('/login', [UserController::class, 'login']);
//use App\Http\Controllers\Admin\PostListController;
//
//Route::delete('/admin/post/{id}/delete-file', [PostListController::class, 'deleteFile'])
//    ->name('admin.post.deleteFile');

Route::group(['as' => 'front.'], function () {
    Route::redirect('/', 'admin/login')->name('dashboard');
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/user_availability_checker', 'General\GeneralController@userAvailabilityChecker')->name('user_availability_checker');
        Route::get('/logout', 'General\GeneralController@logout')->name('logout');
    });
    Route::get('availability_checker', 'General\GeneralController@availability_checker')->name('availability_checker');
    Route::get('forgot_password/{token}', 'General\GeneralController@forgotPasswordView')->name('forgot_password_view');
    Route::post('forgot_password', 'General\GeneralController@forgotPasswordPost')->name('forgot_password_post');
});
