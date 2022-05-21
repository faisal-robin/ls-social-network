<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//login and register route
Route::group(['prefix' => 'auth/'], function () {
    Route::post('register', [UserController::class, 'register']);
    Route::post('login', [UserController::class, 'login']);
});

Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::group(['prefix' => 'page/'], function () {
        Route::post('{pageId}/attach-post', [PageController::class, 'attachPost']);
        Route::post('create', [PageController::class, 'create']);
    });

    Route::group(['prefix' => 'person/'], function () {
        Route::post('attach-post', [PostController::class, 'attachPost']);
        Route::post('feed', [UserController::class, 'personFeeds']);
    });

    Route::group(['prefix' => 'follow/'], function () {
        Route::post('person/{personId}', [UserController::class, 'followPerson']);
        Route::post('page/{pageId}', [PageController::class, 'followPage']);
    });
});
