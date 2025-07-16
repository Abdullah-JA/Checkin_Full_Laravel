<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users;


Route::post('/login', [Users::class, 'login']);

Route::post('/loginProject', [Users::class, 'loginProject']);

Route::post('/addUser', [Users::class, 'addUser']);

Route::post('/checkUser', [Users::class, 'checkUser']);

Route::post('/modifyUser', [Users::class, 'modifyUser']);

Route::post('/modifyUserFirebaseToken', [Users::class, 'modifyUserFirebaseToken']);

Route::post('/modifyUserControl', [Users::class, 'modifyUserControl']);

Route::post('/deleteUser', [Users::class, 'deleteUser']);

Route::post('/updatePassword', [Users::class, 'updatePassword']);

Route::get('/getAllUsers', [Users::class, 'getAllUsers']);

Route::post('getUserById', [Users::class, 'getUserById']);

Route::post('/getUserRooms', [Users::class, 'getUserRooms']);

Route::post('/addGuestCategory', [Users::class, 'addGuestCategory']);

Route::post('/updateGuestCategory', [Users::class, 'updateGuestCategory']);

Route::post('/deleteGuestCategory', [Users::class, 'deleteGuestCategory']);

Route::get('/getGuestCategory', [Users::class, 'getGuestCategory']);

Route::post('/addOtherFeature', [Users::class, 'addOtherFeature']);

Route::post('/updateOtherFeature', [Users::class, 'updateOtherFeature']);

Route::post('/deleteOtherFeature', [Users::class, 'deleteOtherFeature']);

Route::get('/getOtherFeatures', [Users::class, 'getOtherFeatures']);

Route::post('/addStayReason', [Users::class, 'addStayReason']);

Route::post('/updateStayReason', [Users::class, 'updateStayReason']);

Route::post('/deleteStayReason', [Users::class, 'deleteStayReason']);

Route::get('/getStayReasons', [Users::class, 'getStayReasons']);

Route::post('/addBookingSource', [Users::class, 'addBookingSource']);

Route::post('/updateBookingSource', [Users::class, 'updateBookingSource']);

Route::post('/deleteBookingSource', [Users::class, 'deleteBookingSource']);

Route::get('/getBookingSources', [Users::class, 'getBookingSources']);


