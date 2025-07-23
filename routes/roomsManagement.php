<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\roomsManagement;


Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/getbuildings', [roomsManagement::class, 'getBuildings']);
});
// Add Routes
Route::post('/addbuilding', [roomsManagement::class, 'addbuilding']);

Route::post('/addfloorrooms', [roomsManagement::class, 'addFloorRooms']);

Route::post('/addOneRoom', [roomsManagement::class, 'addOneRoom']);

Route::post('/loginRoom', [roomsManagement::class, 'loginRoom']);

Route::post('/logoutRoom', [roomsManagement::class, 'logoutRoom']);

Route::post('/addonefloor', [roomsManagement::class, 'addOneFloor']);

Route::post('/addSuite', [roomsManagement::class, 'addSuite']);

Route::post('/addClientDoorOpen', [roomsManagement::class, 'addClientDoorOpen']);

Route::post('/addUserDoorOpen', [roomsManagement::class, 'addUserDoorOpen']);

Route::post('addClientDoorOpenAndOpen', [roomsManagement::class, 'addClientDoorOpenAndOpen']);

Route::get('/addControlDevice', [roomsManagement::class, 'addControlDevice']);

Route::post('/updateProjectPassword', [roomsManagement::class, 'updateProjectPassword']);

// Delete Routes
Route::post('/deletebuilding', [roomsManagement::class, 'deleteBuilding']);

Route::post('/deletefloorandrooms', [roomsManagement::class, 'deleteFloorAndRooms']);

Route::post('/deleteroom', [roomsManagement::class, 'deleteRoom']);

Route::delete('/deleteSuite', [roomsManagement::class, 'deleteSuite']);

Route::delete('/deleteroomtype', [roomsManagement::class, 'deleteRoomType']);

Route::post('/deleteControlDevice', [roomsManagement::class, 'deleteControlDevice']);

// Get Routes


Route::get('/getfloors', [roomsManagement::class, 'getFloors']);

Route::get('/getRooms', [roomsManagement::class, 'getRooms']);

Route::post('/getRoomByNumber', [roomsManagement::class, 'getRoomByNumber']);

Route::post('/getRoomsForControllDevice', [roomsManagement::class, 'getRoomsForControllDevice']);

Route::post('/getRoomByRoomNumber', [roomsManagement::class, 'getRoomByRoomNumber']);

Route::post('/getReservationRooms', [roomsManagement::class, 'getReservationRooms']);

Route::get('/getSuites', [roomsManagement::class, 'getSuites']);

Route::post('getSuiteById', [roomsManagement::class, 'getSuiteById']);

Route::get('/getRoomTypes', [roomsManagement::class, 'getRoomTypes']);

Route::post('addRoomType', [roomsManagement::class, 'addRoomType']);

Route::get('/getFloorRooms', [roomsManagement::class, 'getFloorRooms']);

Route::get('/getFloorSuites', [roomsManagement::class, 'getFloorSuites']);

Route::get('/getBuildingFloors', [roomsManagement::class, 'getBuildingFloors']);

Route::get('/getBuildingRooms', [roomsManagement::class, 'getBuildingRooms']);

Route::get('/getServerDevices', [roomsManagement::class, 'getServerDevices']);

Route::post('getServerDeviceById', [roomsManagement::class, 'getServerDeviceById']);

Route::get('/getProjectVariables', [roomsManagement::class, 'getProjectVariables']);

Route::post('/setRoomLockId', [roomsManagement::class, 'setRoomLockId']);

Route::post('/modifyBuilding', [roomsManagement::class, 'modifyBuilding']);

Route::post('modifyRoom', [roomsManagement::class, 'modifyRoom']);

Route::post('/modifyRoomFirebaseToken', [roomsManagement::class, 'modifyRoomFirebaseToken']);

Route::post('/modifyServerDeviceActive', [roomsManagement::class, 'modifyServerDeviceActive']);

Route::post('/modifyServerDeviceRooms', [roomsManagement::class, 'modifyServerDeviceRooms']);

Route::post('/modifyServerDeviceFirebaseToken', [roomsManagement::class, 'modifyServerDeviceFirebaseToken']);

Route::post('/modifyServerDeviceFirebaseStatus', [roomsManagement::class, 'modifyServerDeviceFirebaseStatus']);

Route::post('/modifyRoomPowerSwitchInstalled', [roomsManagement::class, 'modifyRoomPowerSwitchInstalled']);

Route::post('/modifyRoomsPowerSwitchInstalled', [roomsManagement::class, 'modifyRoomsPowerSwitchInstalled']);

Route::post('/modifyRoomDoorSensorInstalled', [roomsManagement::class, 'modifyRoomDoorSensorInstalled']);

Route::post('/modifyRoomsDoorSensorInstalled', [roomsManagement::class, 'modifyRoomsDoorSensorInstalled']);

Route::post('/modifyRoomMotionSensorInstalled', [roomsManagement::class, 'modifyRoomMotionSensorInstalled']);

Route::post('/modifyRoomsMotionSensorInstalled', [roomsManagement::class, 'modifyRoomsMotionSensorInstalled']);

Route::post('/modifyRoomThermostatInstalled', [roomsManagement::class, 'modifyRoomThermostatInstalled']);

Route::post('/modifyRoomsThermostatInstalled', [roomsManagement::class, 'modifyRoomsThermostatInstalled']);

Route::post('/modifyRoomCurtainInstalled', [roomsManagement::class, 'modifyRoomCurtainInstalled']);

Route::post('/modifyRoomsCurtainInstalled', [roomsManagement::class, 'modifyRoomsCurtainInstalled']);

Route::post('/modifyRoomGatewayInstalled', [roomsManagement::class, 'modifyRoomGatewayInstalled']);

Route::post('/modifyRoomsGatewayInstalled', [roomsManagement::class, 'modifyRoomsGatewayInstalled']);

Route::post('/modifyRoomServiceSwitchInstalled', [roomsManagement::class, 'modifyRoomServiceSwitchInstalled']);

Route::post('/modifyRoomsServiceSwitchInstalled', [roomsManagement::class, 'modifyRoomsServiceSwitchInstalled']);

Route::post('/modifyRoomSwitch1Installed', [roomsManagement::class, 'modifyRoomSwitch1Installed']);

Route::post('/modifyRoomsSwitch1Installed', [roomsManagement::class, 'modifyRoomsSwitch1Installed']);

Route::post('/modifyRoomSwitch2Installed', [roomsManagement::class, 'modifyRoomSwitch2Installed']);

Route::post('/modifyRoomsSwitch2Installed', [roomsManagement::class, 'modifyRoomsSwitch2Installed']);

Route::post('/modifyRoomSwitch3Installed', [roomsManagement::class, 'modifyRoomSwitch3Installed']);

Route::post('/modifyRoomsSwitch3Installed', [roomsManagement::class, 'modifyRoomsSwitch3Installed']);

Route::post('/modifyRoomSwitch4Installed', [roomsManagement::class, 'modifyRoomSwitch4Installed']);

Route::post('/modifyRoomsSwitch4Installed', [roomsManagement::class, 'modifyRoomsSwitch4Installed']);

Route::post('/modifyRoomsSwitch5Installed', [roomsManagement::class, 'modifyRoomsSwitch5Installed']);

Route::post('/modifyRoomsSwitch6Installed', [roomsManagement::class, 'modifyRoomsSwitch6Installed']);

Route::post('/modifyRoomsSwitch7Installed', [roomsManagement::class, 'modifyRoomsSwitch7Installed']);

Route::post('/modifyRoomsSwitch8Installed', [roomsManagement::class, 'modifyRoomsSwitch8Installed']);

Route::post('/modifyRoomsLockInstalled', [roomsManagement::class, 'modifyRoomsLockInstalled']);

Route::post('hmacSha265', [roomsManagement::class, 'hmacSha265']);

Route::post('getTuyaToken', [roomsManagement::class, 'getTuyaToken']);

Route::post('getTuyaTicket', [roomsManagement::class, 'getTuyaTicket']);

Route::post('unlockWithoutPassword', [roomsManagement::class, 'unlockWithoutPassword']);
