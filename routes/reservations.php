<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Reservations;

Route::post('/addClient', [Reservations::class, 'addClient']);

Route::post('/addReservation', [Reservations::class, 'addReservation']);

Route::post('/addRoomReservation', [Reservations::class, 'addRoomReservation']);

Route::post('/addSuiteReservation', [Reservations::class, 'addSuiteReservation']);

Route::post('/extendStay', [Reservations::class, 'extendStay']);

Route::get('car', [Reservations::class, 'checkAndRedirect']);
Route::post('guestLogin', [Reservations::class, 'guestLogin']);
Route::post('login', [Reservations::class, 'login']);

Route::post('sendClientMessage', [Reservations::class, 'sendClientMessage']);

Route::post('/sendMessage', [Reservations::class, 'sendMessage']);

Route::get('/getServiceUsers', [Reservations::class, 'getServiceUsers']);

Route::post('/checkoutReservation', [Reservations::class, 'checkoutReservation']);

Route::post('/prepareRoom', [Reservations::class, 'prepareRoom']);

Route::post('/setRoomOutOfService', [Reservations::class, 'setRoomOutOfService']);

Route::post('/setRoomOnlineOrOffline', [Reservations::class, 'setRoomOnlineOrOffline']);

Route::post('/getRoomId', [Reservations::class, 'getRoomId']);

Route::post('/getRoomReservation', [Reservations::class, 'getRoomReservation']);

Route::post('/getSuiteReservation', [Reservations::class, 'getSuiteReservation']);

Route::post('/poweronRoom', [Reservations::class, 'poweronRoom']);

Route::post('/powerOffRoom', [Reservations::class, 'powerOffRoom']);

Route::post('/powerByCardRoom', [Reservations::class, 'powerByCardRoom']);

Route::post('/sendMessageToRooms', [Reservations::class, 'sendMessageToRooms']);

Route::post('/addCleanupOrder', [Reservations::class, 'addCleanupOrder']);

Route::post('/addSOSOrder', [Reservations::class, 'addSOSOrder']);

Route::post('/addCleanupOrderControlDevice1', [Reservations::class, 'addCleanupOrderControlDevice1']);
Route::post('/addCleanupOrderControlDevice2', [Reservations::class, 'addCleanupOrderControlDevice2']);
Route::post('/addCleanupOrderControlDevice3', [Reservations::class, 'addCleanupOrderControlDevice3']);
Route::post('/addCleanupOrderControlDevice4', [Reservations::class, 'addCleanupOrderControlDevice4']);
Route::post('/addCleanupOrderControlDevice5', [Reservations::class, 'addCleanupOrderControlDevice5']);

Route::post('/addLaundryOrder', [Reservations::class, 'addLaundryOrder']);

Route::post('/addLaundryOrderControlDevice1', [Reservations::class, 'addLaundryOrderControlDevice1']);
Route::post('/addLaundryOrderControlDevice2', [Reservations::class, 'addLaundryOrderControlDevice2']);
Route::post('/addLaundryOrderControlDevice3', [Reservations::class, 'addLaundryOrderControlDevice3']);
Route::post('/addLaundryOrderControlDevice4', [Reservations::class, 'addLaundryOrderControlDevice4']);
Route::post('/addLaundryOrderControlDevice5', [Reservations::class, 'addLaundryOrderControlDevice5']);

Route::post('/addRoomServiceOrder', [Reservations::class, 'addRoomServiceOrder']);

Route::post('/addSOSOrderControlDevice', [Reservations::class, 'addSOSOrderControlDevice']);

Route::post('/addRoomServiceOrderRoomDevice', [Reservations::class, 'addRoomServiceOrderRoomDevice']);

Route::post('/addCheckoutOrder', [Reservations::class, 'addCheckoutOrder']);

Route::post('addCheckoutOrderControlDevice1', [Reservations::class, 'addCheckoutOrderControlDevice1']);
Route::post('addCheckoutOrderControlDevice2', [Reservations::class, 'addCheckoutOrderControlDevice2']);
Route::post('addCheckoutOrderControlDevice3', [Reservations::class, 'addCheckoutOrderControlDevice3']);
Route::post('addCheckoutOrderControlDevice4', [Reservations::class, 'addCheckoutOrderControlDevice4']);
Route::post('addCheckoutOrderControlDevice5', [Reservations::class, 'addCheckoutOrderControlDevice5']);

Route::post('/putRoomOnDNDMode', [Reservations::class, 'putRoomOnDNDMode']);

Route::post('putRoomOnDNDModeControlDevice1', [Reservations::class, 'putRoomOnDNDModeControlDevice1']);
Route::post('putRoomOnDNDModeControlDevice2', [Reservations::class, 'putRoomOnDNDModeControlDevice2']);
Route::post('putRoomOnDNDModeControlDevice3', [Reservations::class, 'putRoomOnDNDModeControlDevice3']);
Route::post('putRoomOnDNDModeControlDevice4', [Reservations::class, 'putRoomOnDNDModeControlDevice4']);
Route::post('putRoomOnDNDModeControlDevice5', [Reservations::class, 'putRoomOnDNDModeControlDevice5']);

Route::post('/finishServiceOrder', [Reservations::class, 'finishServiceOrder']);

Route::post('cancelServiceOrder', [Reservations::class, 'cancelServiceOrder']);

Route::post('/cancelServiceOrderControlDevice1', [Reservations::class, 'cancelServiceOrderControlDevice1']);
Route::post('/cancelServiceOrderControlDevice2', [Reservations::class, 'cancelServiceOrderControlDevice2']);
Route::post('/cancelServiceOrderControlDevice3', [Reservations::class, 'cancelServiceOrderControlDevice3']);
Route::post('/cancelServiceOrderControlDevice4', [Reservations::class, 'cancelServiceOrderControlDevice4']);
Route::post('/cancelServiceOrderControlDevice5', [Reservations::class, 'cancelServiceOrderControlDevice5']);

Route::post('/cancelDNDOrderControlDevice1', [Reservations::class, 'cancelDNDOrderControlDevice1']);
Route::post('/cancelDNDOrderControlDevice2', [Reservations::class, 'cancelDNDOrderControlDevice2']);
Route::post('/cancelDNDOrderControlDevice3', [Reservations::class, 'cancelDNDOrderControlDevice3']);
Route::post('/cancelDNDOrderControlDevice4', [Reservations::class, 'cancelDNDOrderControlDevice4']);
Route::post('/cancelDNDOrderControlDevice5', [Reservations::class, 'cancelDNDOrderControlDevice5']);

Route::post('/setTemperatureSetPoint', [Reservations::class, 'setTemperatureSetPoint']);

Route::post('/setACSenarioActive', [Reservations::class, 'setACSenarioActive']);

Route::post('/setGuestAppActive', [Reservations::class, 'setGuestAppActive']);

Route::post('setCheckoutTime', [Reservations::class, 'setCheckoutTime']);

Route::post('/setCheckinModeActive', [Reservations::class, 'setCheckinModeActive']);

Route::post('/setClientInPowerOff', [Reservations::class, 'setClientInPowerOff']);

Route::post('/setPoweroffAfterHK', [Reservations::class, 'setPoweroffAfterHK']);

Route::post('/setClientBackActions', [Reservations::class, 'setClientBackActions']);

Route::post('setHKPrepareTime', [Reservations::class, 'setHKPrepareTime']);

Route::post('/setCheckinModeActions', [Reservations::class, 'setCheckinModeActions']);

Route::post('/setCheckoutModeActions', [Reservations::class, 'setCheckoutModeActions']);

Route::post('/setCheckoutModeActive', [Reservations::class, 'setCheckoutModeActive']);

Route::post('/setSetPointInterval', [Reservations::class, 'setSetPointInterval']);

Route::post('/setDoorsWarningInterval', [Reservations::class, 'setDoorsWarningInterval']);

Route::post('/setWelcomeMessage', [Reservations::class, 'setWelcomeMessage']);

Route::post('/setDoorOpenOrClosed', [Reservations::class, 'setDoorOpenOrClosed']);

Route::post('/setPowerOnOrOff', [Reservations::class, 'setPowerOnOrOff']);

Route::post('setClientInOrOut', [Reservations::class, 'setClientInOrOut']);

Route::post('/setCheckinModeDuration', [Reservations::class, 'setCheckinModeDuration']);

Route::post('/setCheckoutModeDuration', [Reservations::class, 'setCheckoutModeDuration']);

Route::post('/setLogo', [Reservations::class, 'setLogo']);

Route::get('/getLogo', [Reservations::class, 'getLogo']);

Route::post('/getDoorOpensByRoom', [Reservations::class, 'getDoorOpensByRoom']);

Route::post('/getDoorOpensByUser', [Reservations::class, 'getDoorOpensByUser']);

Route::post('/getServiceOrdersByRoom', [Reservations::class, 'getServiceOrdersByRoom']);

Route::post('/getFacilityOrdersByRoom', [Reservations::class, 'getFacilityOrdersByRoom']);

Route::post('/getServiceOrdersBySuite', [Reservations::class, 'getServiceOrdersBySuite']);

Route::post('/getFacilityOrdersBySuite', [Reservations::class, 'getFacilityOrdersBySuite']);

Route::post('/getServiceOrdersByReservation', [Reservations::class, 'getServiceOrdersByReservation']);

Route::post('/getFacilityOrdersByReservation', [Reservations::class, 'getFacilityOrdersByReservation']);

Route::post('/getServiceOrdersByOrderType', [Reservations::class, 'getServiceOrdersByOrderType']);

Route::post('/getServiceOrdersByClient', [Reservations::class, 'getServiceOrdersByClient']);

Route::post('getFacilityOrdersByClient', [Reservations::class, 'getFacilityOrdersByClient']);

Route::post('/searchReservationsByDate', [Reservations::class, 'searchReservationsByDate']);

Route::post('/searchReservationsByRoomId', [Reservations::class, 'searchReservationsByRoomId']);

Route::post('/searchClient', [Reservations::class, 'searchClient']);

Route::post('/modifyClient', [Reservations::class, 'modifyClient']);

Route::post('/searchReservationsByClientId', [Reservations::class, 'searchReservationsByClientId']);

Route::post('/getOpenReservations', [Reservations::class, 'getOpenReservations']);

Route::post('/getClosedReservations', [Reservations::class, 'getClosedReservations']);

Route::post('/getOpenCleanupOrders', [Reservations::class, 'getOpenCleanupOrders']);

Route::post('/getOpenCleanupOrdersCount', [Reservations::class, 'getOpenCleanupOrdersCount']);

Route::post('/getOpenLaundryOrders', [Reservations::class, 'getOpenLaundryOrders']);

Route::post('/getOpenLaundryOrdersCount', [Reservations::class, 'getOpenLaundryOrdersCount']);

Route::post('/getOpenRoomServiceOrders', [Reservations::class, 'getOpenRoomServiceOrders']);

Route::post('/getOpenRoomServiceOrdersCount', [Reservations::class, 'getOpenRoomServiceOrdersCount']);

Route::post('/getOpenCheckoutOrders', [Reservations::class, 'getOpenCheckoutOrders']);

Route::post('/getOpenCheckoutOrdersCount', [Reservations::class, 'getOpenCheckoutOrdersCount']);

Route::post('sendReRunMessage', [Reservations::class, 'sendReRunMessage']);

Route::post('sendWhatsupMessage', [Reservations::class, 'sendWhatsupMessage']);

Route::post('checkReservation', [Reservations::class, 'checkReservation']);
Route::post('checkReservationByRoom', [Reservations::class, 'checkReservationByRoom']);
