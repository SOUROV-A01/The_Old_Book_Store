<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FrontendApiController;
use App\Http\Controllers\OrderController;
use App\Models\User;
use App\Services\FCMService;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::group([

    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'v1'

], function ($router) {
    Route::post('login', [AuthController::class, 'login'])->name('login');

    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::apiResource('books', BookController::class);
    Route::get('category', [FrontendApiController::class, 'category']);
    Route::get('category-books/{id}', [FrontendApiController::class, 'categoryBooks']);
    Route::get('user-books/{id}', [FrontendApiController::class, 'userBooks']);
    Route::get('homepage-books', [FrontendApiController::class, 'homePageBooks']);
    Route::post('create-order', [OrderController::class, 'store']);
    Route::get('user-all-buy-orders', [OrderController::class, 'userBuyOrder']);
    Route::get('user-all-sell-orders', [OrderController::class, 'userSellOrder']);
    Route::get('accept-order/{orderId}',[OrderController::class,'acceptOrder']);
    Route::get('search-book',[FrontendApiController::class,'searchBook']);
    Route::post('books/v2',[FrontendApiController::class,'storeV2']);
    Route::post('update-device-key',[FrontendApiController::class,'updateDeviceKey']);
    Route::post('/push-test-notification',function(Request $request){
        $user = User::findOrFail(1);

       $data =  FCMService::send(
            $user->device_key,
            [
                'title' => $request->title,
                'body' => $request->body,
            ]
        );
        });
    });
