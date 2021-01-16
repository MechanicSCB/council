<?php

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

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/threads', 'ThreadController@index')->name('threads');
Route::get('/threads/create', 'ThreadController@create')->middleware('must-be-confirmed');
Route::get('/threads/search', 'SearchController@show');
Route::get('/threads/{channel}/{thread}', 'ThreadController@show');
Route::patch('/threads/{channel}/{thread}', 'ThreadController@update');
Route::delete('/threads/{channel}/{thread}', 'ThreadController@destroy');
Route::post('/threads', 'ThreadController@store')->middleware('must-be-confirmed');
Route::get('/threads/{channel}', 'ThreadController@index');

Route::post('/locked-threads/{thread}', 'LockedThreadController@store')->name('locked-threads.store')->middleware('admin');
Route::delete('/locked-threads/{thread}', 'LockedThreadController@destroy')->name('locked-threads.destroy')->middleware('admin');


Route::get('/threads/{channel}/{thread}/replies', 'ReplyController@index');
Route::post('/threads/{channel}/{thread}/replies', 'ReplyController@store');
Route::patch('/replies/{reply}', 'ReplyController@update');
Route::delete('/replies/{reply}', 'ReplyController@destroy')->name('replies.destroy');

Route::post('/replies/{reply}/best', 'BestRepliesController@store')->name('best-replies.store');

Route::post('/threads/{channel}/{thread}/subscriptions', 'ThreadSubscriptionController@store')->middleware('auth');
Route::delete('/threads/{channel}/{thread}/subscriptions', 'ThreadSubscriptionController@destroy')->middleware('auth');

Route::post('/replies/{reply}/favorites', 'FavoriteController@store');
Route::delete('/replies/{reply}/favorites', 'FavoriteController@destroy');

Route::get('/profiles/{user}', 'ProfileController@show')->name('profile');

Route::get('/profiles/{user}/notifications', 'UserNotificationController@index');
Route::delete('/profiles/{user}/notifications/{notification}', 'UserNotificationController@destroy');

Route::get('/register/confirm', 'Auth\RegisterConfirmationController@index')->name('register.confirm');

Route::get('api/users', 'Api\UserController@index');
Route::post('api/users/{user}/avatar', 'Api\UserAvatarController@store')->middleware('auth')->name('avatar');

Route::group([
    'prefix' => 'admin',
    'middleware' => 'admin',
    'namespace' => 'Admin'
], function() {
    Route::get('/', 'DashboardController@index')->name('admin.dashboard.index');
    Route::post('/channels', 'ChannelController@store')->name('admin.channels.store');
    Route::get('/channels', 'ChannelController@index')->name('admin.channels.index');
    Route::get('/channels/create', 'ChannelController@create')->name('admin.channels.create');
});

// очистка кэша
Route::get('/clear', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
//    Artisan::call('backup:clean');
    return back();
});
