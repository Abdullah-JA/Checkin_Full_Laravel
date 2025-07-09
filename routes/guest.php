<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Reservations;

Route::get('car',[Reservations::class,'checkAndRedirect']);