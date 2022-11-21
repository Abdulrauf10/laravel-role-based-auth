<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MiscController;
use App\Http\Controllers\Api\ParticipantController;
use App\Http\Controllers\Api\QuestionBankController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\TestController;
use App\Models\QuestionBank;
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

Route::prefix('v1')->middleware('json.response')->group(function() {

    Route::get('/', function() {
        return response(['message' => 'Hi, Welcome to Talentfit API. Please use the correct endpoint and params :D']);
    });

    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        
        Route::controller(AuthController::class)->prefix('auth')->group(function() {
            Route::get('/me','me');
        });

        Route::middleware('auth.superadmin')->group(function() {
            
    
            Route::resource('question', QuestionController::class)->except('create','edit');
    
            Route::resource('question-bank', QuestionBankController::class)->except('create','edit');
            Route::get('question-bank/{id}/questions', [QuestionBankController::class,'questions'])->name('question-bank.questions');
    
            Route::resource('category', CategoryController::class)->except('create','edit');
            Route::get('category/{id}/questions', [CategoryController::class,'questions'])->name('category.questions');

            Route::controller(TestController::class)->prefix('test')->group(function () {
                Route::get('/', 'index')->name('test.index');
                Route::post('/', 'store')->name('test.store');

                Route::post('/{test}/store-session','storeSession')->name('test.store-session');
                Route::post('/{test}/update','update')->name('test.update');
                Route::get('/{test}/detail','show')->name('test.show');
                Route::get('/{test}/participants','participants')->name('test.participants');
                Route::get('/{testsession}/sessions','sessions')->name('test.session');
                Route::delete('/{test}/delete','destroy')->name('test.delete');
                
                Route::post('/parse-participant','parseParticipant')->name('test.parse-participant');
                Route::post('/{test}/store-participant','storeParticipant')->name('test.store-participant');

                Route::post('/{testsession}/update-session','updateSession')->name('test.update-session');
                Route::delete('/{testsession}/delete-session','deleteSession')->name('test.delete-session');

                Route::post('/{testParticipant}/update-participant','updateParticipant')->name('test.update-participant');
                Route::delete('/{testParticipant}/delete-participant','deleteParticipant')->name('test.delete-participant');
            });
            
        });

        Route::controller(MiscController::class)->prefix('misc')->group(function () {
            Route::get('/assesors','assesor')->name('misc.assesor');
            Route::get('/verbatimers','verbatimer')->name('misc.verbatimer');
            Route::get('/provinces','getProvince')->name('misc.getProvince');
            Route::get('/cities','getCity')->name('misc.getCities');
        });

        Route::resource('participants', ParticipantController::class);

    });
});
