<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Elevators;


Route::post('/addElevator',[Elevators::class,'addElevator']);
Route::post('/updateElevator',[Elevators::class,'updateElevator']);
Route::post('/deleteElevator',[Elevators::class,'deleteElevator']);
Route::post('/addElevatorPermissions',[Elevators::class,'addElevatorPermissions']);
Route::post('/editElevatorPermissions',[Elevators::class,'editElevatorPermissions']);
Route::post('/deleteElevatorPermission',[Elevators::class,'deleteElevatorPermission']);
Route::get('/getElevators',[Elevators::class,'getElevators']);
Route::post('/getElevatorPermissionByElevatorId',[Elevators::class,'getElevatorPermissionByElevatorId']);  
Route::get('/getElevatorsForApp',[Elevators::class,'getElevatorsForApp']);  
Route::post('/testDelete',[Elevators::class,'testDelete']);  