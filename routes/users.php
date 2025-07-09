<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users;


Route::post('/login',[Users::class,'login']);

Route::post('/loginProject',[Users::class,'loginProject']);

Route::post('/addUser',[Users::class,'addUser']);

Route::post('/checkUser',[Users::class,'checkUser']);

Route::post('/modifyUser',[Users::class,'modifyUser']);

Route::post('/modifyUserFirebaseToken',[Users::class,'modifyUserFirebaseToken']);

Route::post('/modifyUserControl',[Users::class,'modifyUserControl']);

Route::post('/deleteUser',[Users::class,'deleteUser']);

Route::post('/updatePassword',[Users::class,'updatePassword']);

Route::get('/getAllUsers' ,[Users::class,'getAllUsers']);

Route::post('getUserById',[Users::class,'getUserById']);

Route::post('/getUserRooms',[Users::class,'getUserRooms']);