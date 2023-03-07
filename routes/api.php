<?php

use App\Http\Controllers\AuthController;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('pre_registration', [AuthController::class, 'apiVerifyRegistration'])->name('api.register');

Route::get('csrf', function () {
    return response()->json(csrf_token());
});

Route::post('/change_password', [AuthController::class, 'change_pass']);

Route::post('/login', [AuthController::class, 'apiLogin']);

Route::get('/redirect', [AuthController::class, 'redirects'])->name('redirect');
