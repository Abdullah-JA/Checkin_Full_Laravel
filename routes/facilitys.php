<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Facilitys;

Route::post('addfacility',[Facilitys::class , 'addFacility']);

Route::post('addfacilitytype',[Facilitys::class,'addFacilityType']);

Route::post('/addRestaurantOrder',[Facilitys::class,'addRestaurantOrder']);

Route::post('/loginFacilityUser',[Facilitys::class,'loginFacilityUser']);

Route::post('/setFacilityUserToken',[Facilitys::class,'setFacilityUserToken']);

Route::post('/getRestOrders',[Facilitys::class,'getRestOrders']);

Route::post('getRestOrder',[Facilitys::class,'getRestOrder']);

Route::post('/getRestOrderItems',[Facilitys::class,'getRestOrderItems']);

Route::post('/finishRestOrder',[Facilitys::class,'finishRestOrder']);

Route::get('getfacilitys',[Facilitys::class , 'getFacilitys']);

Route::get('getfacilitytypes',[Facilitys::class , 'getFacilityTypes']);

Route::post('/getRestaurantMenues',[Facilitys::class ,'getRestaurantMenues']);

Route::post('/getRestaurantMenuesForRoom',[Facilitys::class,'getRestaurantMenuesForRoom']);

Route::post('/getLaundryItems',[Facilitys::class,'getLaundryItems']);

Route::post('/getLaundryItemsRoomDevice',[Facilitys::class,'getLaundryItemsRoomDevice']);

Route::post('/getCoffeeShopMenues',[Facilitys::class ,'getCoffeeShopMenues']);

Route::post('/getCoffeeShopMenuesForRoom',[Facilitys::class,'getCoffeeShopMenuesForRoom']);

Route::post('/getRestaurantMenueMeals',[Facilitys::class,'getRestaurantMenueMeals']);

Route::post('/getRestaurantMenueMealsForRoom',[Facilitys::class,'getRestaurantMenueMealsForRoom']);

Route::post('/getCoffeShopMenueMeals',[Facilitys::class,'getCoffeShopMenueMeals']);

Route::post('/getCoffeShopMenueMealsForRoom',[Facilitys::class,'getCoffeShopMenueMealsForRoom']);

Route::post('/getOtherInvoices',[Facilitys::class,'getOtherInvoices']);

Route::post('/getFacilityInvoices',[Facilitys::class,'getFacilityInvoices']);

Route::post('/addRestaurantMenu',[Facilitys::class,'addRestaurantMenu']);

Route::post('/addCoffeeShopMenu',[Facilitys::class,'addCoffeeShopMenu']);

Route::post('/addRestaurantMenuMeal',[Facilitys::class,'addRestaurantMenuMeal']);

Route::post('/addOtherInvoice',[Facilitys::class,'addOtherInvoice']);

Route::post('/addLaundryItem',[Facilitys::class,'addLaundryItem']);

Route::post('/addCoffeshopMenuMeal',[Facilitys::class,'addCoffeshopMenuMeal']);

Route::post('/deleteMeal',[Facilitys::class,'deleteMeal']);

Route::post('/deleteCoffeeshopMeal',[Facilitys::class,'deleteCoffeeshopMeal']);

Route::post('/deleteCoffeeshopMenue',[Facilitys::class,'deleteCoffeeshopMenue']);

Route::post('/deleteLaundryItem',[Facilitys::class,'deleteLaundryItem']);

Route::post('/deleteMenue',[Facilitys::class,'deleteMenue']);

Route::post('/modifyMeal',[Facilitys::class,'modifyMeal']);

Route::post('/modifyCoffeeshopMeal',[Facilitys::class,'modifyCoffeeshopMeal']);

Route::post('/modifyLaundryItem',[Facilitys::class,'modifyLaundryItem']);