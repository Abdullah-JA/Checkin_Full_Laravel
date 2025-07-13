<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Suite;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Serviceorder;
use App\Models\Serviceemployee;
use App\Models\Projectsvariable;
use App\Models\Serverdevice;
use App\Models\Doorsopen;
use App\Models\Restaurantorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;

class Reservations extends Controller
{

    public $firebaseMessageUrl = 'https://fcm.googleapis.com/fcm/send';
    public $firebaseServerKey = 'AAAAXBAcZWU:APA91bHrhYpq6HfE6IsKIVF2mREXrOD0PuW5OpD6HhM92YTInukMF3AgzAhgmnvug3PGUhXhxfuIYWrF-lZ5dmBZh2iW-2mWRyrk6diP6YS4g-xfNVxPygqoLmOyJ92jbYhWfMEtzzJo';//'AAAAQmygXvw:APA91bFt5CiONiZPDDj4_kz9hmKXlL1cjfTa_ZNGfobMPmt0gamhzEoN2NHiOxypCDr_r5yfpLvJy-bQSgrykXvaqKkThAniTr-0hpXPBrXm7qWThMmkiaN9o6qaUqfIUwStMMuNedTw';
    public $firebaseUrl = 'https://checkin-62774-default-rtdb.asia-southeast1.firebasedatabase.app';
    public $projectName = 'apiTest';
    public $projectUrl = 'https://samples.checkin.ratco-solutions.com/Guest';
    public $smsUrl = 'https://api.taqnyat.sa/v1/messages';
    public $senderName = 'RATCO';
    public $SMSStart = "Welcome";
    public $SMSMid = " To ";
    public $SMSEnd = "RATCO";
    public $SMSLink = "https://samples.checkin.ratco-solutions.com/guest/car?number=";
    public $bearerToken = "Bearer 1e798ba819ca85b7847612e85afe709d";
    public $URL = 'https://samples.checkin.ratco-solutions.com/';
    public $rrrrr = "https://fcm.googleapis.com/v1/projects/checkin-62774/messages:send";


    // routed functions

    /*
        add functions
    */
    public static function addClient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:50|min:2',
            'last_name' => 'required|max:50|min:2',
            'mobile' => 'required|max:8|min:8|string|unique:clients,Mobile',
            'email' => 'max:50|nullable',
            'id_type' => 'required|in:ID,PASSPORT',
            'id_number' => 'required|max:20',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'insertedRow' => '', 'error' => $validator->errors()];
            return $result;
        }
        if (Users::checkAuth($request->input('my_token'))) {
            try {
                $client = new Client();
                $client->Hotel = 1;
                $client->FirstName = $request->input('first_name');
                $client->LastName = $request->input('last_name');
                $client->Mobile = '05' . $request->input('mobile');
                $client->Email = $request->input('email');
                $client->IdType = $request->input('id_type');
                $client->IdNumber = $request->input('id_number');
                $client->save();
                $result = ['result' => 'success', 'insertedRow' => $client, 'error' => ''];
            } catch (Exception $e) {
                $result = ['result' => 'failed', 'insertedRow' => null, 'error' => $e->getMessage()];
            }
        } else {
            $result = ['result' => 'failed', 'insertedRow' => null, 'error' => 'you are unauthorized user'];
        }
        return $result;
    }



    //   $validator = Validator::make($request->all(),[
    //     'room_number' => 'required|numeric',
    //     'first_name' => 'required|max:50|min:2',
    //     'last_name' => 'required|max:50|min:2',
    //     'mobile' => 'required|max:50|min:10',
    //     'email' => 'max:50|nullable',
    //     'id_type' => 'required|in:ID,PASSPORT',
    //     'id_number' => 'required|max:20',
    //     'start_date' => 'required',
    //     'nights' => 'required|numeric',
    //     'end_date' => 'required',
    //     'building_number' => 'required',
    //     'floor_number' => 'required|numeric',
    //     'room_or_suite' => 'required|numeric',
    //     'suite_id' => 'numeric',
    //     'suite_number' => 'numeric',
    //     'multi_rooms' => 'required|numeric',
    //     'add_rooms_number' => 'required',
    //     'add_rooms_id' => 'required',
    //     'client_id' => 'required',
    //     'my_token' => 'required'
    //   ]);
    //   if ($validator->fails())  {
    //     $result = ['result'=>'failed','reservation'=>null,'error'=>$validator->errors()];
    //     return $result ;
    //   }
    //   if (Users::checkAuth($request->input('my_token')) == false) {
    //     return ['result'=>'failed','reservation'=>'','error'=>'you are unauthorized user'];
    //   }
    //   $roomNumber = $request->input('room_number');
    //   $firstName = $request->input('first_name');
    //   $lastName = $request->input('last_name');
    //   $mobile = $request->input('mobile');
    //   $idType = $request->input('id_type');
    //   $idNumber = $request->input('id_number');
    //   $start = $request->input('start_date');
    //   $nights = $request->input('nights');
    //   $end = $request->input('end_date');
    //   $buildingNumber = $request->input('building_number');
    //   $floorNumber = $request->input('floor_number');
    //   $roomOrSuite = $request->input('room_or_suite');
    //   $suiteId = 0;
    //   $suiteNumber = 0;
    //   $multiRooms = $request->input('multi_rooms');
    //   $addRoomsNumber = $request->input('add_rooms_number');
    //   $addRoomsIds = $request->input('add_rooms_id');
    //   $clientId = $request->input('client_id');
    //   $email = '';
    //   if ($request->input('email') != null ) {
    //     $email = $request->input('email');
    //   }
    //   if ($request->input('suite_id') != null ) {
    //     $suiteId = $request->input('suite_id');
    //   }
    //   if ($request->input('suite_number') != null ) {
    //     $suiteNumber = $request->input('suite_number');
    //   }
    //   DB::beginTransaction();
    //   try {
    //     if ($roomOrSuite == 1) {
    //       // reservation is for room
    //       if ($multiRooms > 0) {
    //         // reservation is for room + additional room/s
    //         $room = Room::where('RoomNumber','=',$roomNumber)->first();
    //         if ($room != null) {
    //           if ($room->roomStatus == 1) {
    //             $ids = explode('-',$addRoomsIds);
    //             $rooms = array();
    //             for ($i=0;$i<count($ids);$i++) {
    //               $rooms[$i] = Room::find($ids[$i]);
    //             }
    //             $st = true ;
    //             for ($i=0;$i<count($rooms);$i++) {
    //               if ($rooms[$i]->roomStatus > 1) {
    //                 $st = false ;
    //                 break ;
    //               }
    //             }
    //             if ($st) {
    //               $reserveRes = $this->insertReservation([
    //                 'RoomNumber' => $roomNumber,
    //                 'ClientId' => $clientId,
    //                 'Status' => 1,
    //                 'RoomOrSuite' => $roomOrSuite,
    //                 'MultiRooms' => $multiRooms,
    //                 'AddRoomNumber' => $addRoomsNumber,
    //                 'AddRoomId' => $addRoomsIds,
    //                 'StartDate' => $start,
    //                 'Nights' => $nights,
    //                 'EndDate' => $end,
    //                 'Hotel' => 1,
    //                 'BuildingNo' =>$buildingNumber,
    //                 'Floor' => $floorNumber,
    //                 'ClientFirstName' => $firstName,
    //                 'ClientLastName' => $lastName,
    //                 'IdType' => $idType,
    //                 'IdNumber' => $idNumber,
    //                 'MobileNumber' => $mobile,
    //                 'Email' => $email,
    //                 'Rating' => 0
    //               ]);
    //               if ($reserveRes['res'] == 'success') {
    //                 $reservation = $reserveRes['reservation'];
    //                 $roomRes = $this->reserveRoomInDB($room,$reservation->id);
    //                 if ($roomRes['res'] == 'success') {
    //                   $room = $roomRes['room'];
    //                   if($this->reserveRoomInFirebase($room,$reservation)) {
    //                     $this->sendMessageToRoom($room,$reservation,'message');
    //                     $this->checkinRoom($room,$reservation);
    //                     for ($i=0;$i<count($rooms);$i++) {
    //                       $roomResult = $this->reserveRoomInDB($rooms[$i],$reservation->id);
    //                       if ($roomResult['res'] == 'success') {
    //                         $this->reserveRoomInFirebase($rooms[$i],$reservation);
    //                         $this->sendMessageToRoom($rooms[$i],$reservation,'message');
    //                         $this->checkinRoom($rooms[$i],$reservation);
    //                       }

    //                     }
    //                     $result = ['result'=>'success','reservation'=>$reservation,'error'=>null];
    //                     DB::commit();
    //                   }
    //                   else {
    //                     $result = ['result'=>'failed','reservation'=>null,'error'=>'unable to reserve room in firebase '];
    //                     DB::rollBack();
    //                   }
    //                 }
    //                 else {
    //                   $error = $roomRes['error'];
    //                   $result = ['result'=>'failed','reservation'=>null,'error'=>'unable to reserve room '.$error];
    //                   DB::rollBack();
    //                 }
    //               }
    //               else {
    //                 $error = $reserveRes['error'];
    //                 $result = ['result'=>'failed','reservation'=>null,'error'=>'unable to insert reservation '.$error];
    //                 DB::rollBack();
    //               }
    //             }
    //             else {
    //               $result = ['result'=>'failed','reservation'=>null,'error'=>'one of the additional rooms is already reserved or unready or out of service'];
    //             }
    //           }
    //           else if ($room->roomStatus == 2) {
    //             $result = ['result'=>'failed','reservation'=>null,'error'=>'room '.$roomNumber.' is already reserved'];
    //           }
    //           else if ($room->roomStatus == 3) {
    //             $result = ['result'=>'failed','reservation'=>null,'error'=>'room '.$roomNumber.' is unready'];
    //           }
    //           else if ($room->roomStatus == 4) {
    //             $result = ['result'=>'failed','reservation'=>null,'error'=>'room '.$roomNumber.' is out of service'];
    //           }
    //         }
    //         else {
    //           $result = ['result'=>'failed','reservation'=>null,'error'=>'room '.$roomNumber.' is unexists'];
    //         }
    //       }
    //       else {
    //         // reservation is for single room
    //         $room = Room::where('RoomNumber','=',$roomNumber)->first();
    //         if ($room != null) {
    //           if ($room->roomStatus == 1) {
    //               $insertResult = $this->insertReservation([
    //                 'RoomNumber' => $roomNumber,
    //                     'ClientId' => $clientId,
    //                     'Status' => 1,
    //                     'RoomOrSuite' => $roomOrSuite,
    //                     'MultiRooms' => $multiRooms,
    //                     'AddRoomNumber' => $addRoomsNumber,
    //                     'AddRoomId' => $addRoomsIds,
    //                     'StartDate' => $start,
    //                     'Nights' => $nights,
    //                     'EndDate' => $end,
    //                     'Hotel' => 1,
    //                     'BuildingNo' =>$buildingNumber,
    //                     'Floor' => $floorNumber,
    //                     'ClientFirstName' => $firstName,
    //                     'ClientLastName' => $lastName,
    //                     'IdType' => $idType,
    //                     'IdNumber' => $idNumber,
    //                     'MobileNumber' => $mobile,
    //                     'Email' => $email,
    //                     'Rating' => 0
    //               ]);
    //               if ($insertResult['res'] == 'success') {
    //                 $reservation = $insertResult['reservation'];
    //                 $roomRes = $this->reserveRoomInDB($room,$reservation->id);
    //                 if ($roomRes['res'] == 'success') {
    //                   if($this->reserveRoomInFirebase($room,$reservation)) {
    //                     $result = ['result'=>'success','reservation'=>$reservation,'error'=>null];
    //                     DB::commit();
    //                     $this->sendMessageToRoom($room,$reservation,'message');
    //                     $this->checkinRoom($room,$reservation);
    //                   }
    //                   else {
    //                     DB::rollback();
    //                     $result = ['result'=>'failed','reservation'=>null,'error'=>'error reserving room in firebase'];
    //                   }
    //                 }
    //                 else {
    //                   $error = $roomRes['error'];
    //                   DB::rollback();
    //                   $result = ['result'=>'failed','reservation'=>null,'error'=>'error reserve room in database '.$error];
    //                 }
    //               }
    //               else {
    //                 $error = $insertResult['error'];
    //                 DB::rollback();
    //                 $result = ['result'=>'failed','reservation'=>null,'error'=>'error saving reservation '.$error];
    //               }

    //           }
    //           else if ($room->roomStatus == 2) {
    //             $result = ['result'=>'failed','reservation'=>null,'error'=>'room '.$roomNumber.' is already reserved'];
    //           }
    //           else if ($room->roomStatus == 3) {
    //             $result = ['result'=>'failed','reservation'=>null,'error'=>'room '.$roomNumber.' is unready'];
    //           }
    //           else if ($room->roomStatus == 4) {
    //             $result = ['result'=>'failed','reservation'=>null,'error'=>'room '.$roomNumber.' is out of service'];
    //           }
    //         }
    //         else {
    //           $result = ['result'=>'failed','reservation'=>null,'error'=>'room '.$roomNumber.' is unexists'];
    //         }
    //       }
    //     }
    //     else if ($roomOrSuite == 2) {
    //       // reservation is for suite
    //       if ($multiRooms > 0) {
    //         // reservation is for one suite + additional room/s
    //         $suiteRooms = Room::where('SuiteId','=',$suiteId)->get();
    //         $st = true ;
    //         if ($suiteRooms != null ) {
    //           for ($i=0;$i<count($suiteRooms);$i++) {
    //             if ($suiteRooms[$i]->roomStatus > 1) {
    //               $st = false ;
    //               break ;
    //             }
    //           }
    //           if ($st) {
    //             $suite = Suite::find($suiteId);
    //             if ($suite != null) {
    //               $ids = explode('-',$addRoomsIds);
    //               $additionalRooms = array();
    //               for ($i=0;$i<count($ids);$i++) {
    //                 $additionalRooms[$i] = Room::find($ids[$i]);
    //               }
    //               $stt = true ;
    //               for ($i=0;$i<count($additionalRooms);$i++) {
    //                 if ($additionalRooms[$i]->roomStatus > 1) {
    //                   $stt = false ;
    //                   break ;
    //                 }
    //               }
    //               if ($stt) {
    //                 $reserveRes = $this->insertReservation([
    //                   'RoomNumber' => $suite->SuiteNumber,
    //                     'ClientId' => $clientId,
    //                     'Status' => 1,
    //                     'RoomOrSuite' => $roomOrSuite,
    //                     'MultiRooms' => $multiRooms,
    //                     'AddRoomNumber' => $addRoomsNumber,
    //                     'AddRoomId' => $addRoomsIds,
    //                     'StartDate' => $start,
    //                     'Nights' => $nights,
    //                     'EndDate' => $end,
    //                     'Hotel' => 1,
    //                     'BuildingNo' =>$buildingNumber,
    //                     'Floor' => $floorNumber,
    //                     'ClientFirstName' => $firstName,
    //                     'ClientLastName' => $lastName,
    //                     'IdType' => $idType,
    //                     'IdNumber' => $idNumber,
    //                     'MobileNumber' => $mobile,
    //                     'Email' => $email,
    //                     'Rating' => 0
    //                 ]);
    //                 if ($reserveRes['res'] == 'success') {
    //                   $reservation = $reserveRes['reservation'];
    //                   $suiteRes = $this->reserveSuiteInDB($suite);
    //                   if ($suiteRes['res'] == 'success') {
    //                     if ($this->reserveSuiteInFirebase($suite)) {
    //                       for ($i=0;$i<count($suiteRooms);$i++) {
    //                         $rrRes = $this->reserveSuiteRoomInDB($suiteRooms[$i],$reservation->id);
    //                         if ($rrRes['res'] == 'success') {
    //                           $this->reserveRoomInFirebase($suiteRooms[$i],$reservation);
    //                           $this->sendMessageToRoom($suiteRooms[$i],$reservation,'message');
    //                           $this->checkinRoom($suiteRooms[$i],$reservation);
    //                         }
    //                       }
    //                       for ($i=0;$i<count($additionalRooms);$i++) {
    //                         $rrRes = $this->reserveRoomInDB($additionalRooms[$i],$reservation->id);
    //                         if ($rrRes['res'] == 'success') {
    //                           $this->reserveRoomInFirebase($additionalRooms[$i],$reservation);
    //                           $this->sendMessageToRoom($additionalRooms[$i],$reservation,'message');
    //                           $this->checkinRoom($additionalRooms[$i],$reservation);
    //                         }
    //                       }
    //                       $result = ['result'=>'success','reservation'=>$reservation,'error'=>null];
    //                       DB::commit();
    //                     }
    //                   }
    //                 }

    //               }
    //               else {
    //                 $result = ['result'=>'failed','reservation'=>null,'error'=>'one of the additional rooms is already reserved or unready or out of service'];
    //               }
    //             }
    //             else {
    //               $result = ['result'=>'failed','reservation'=>null,'error'=>'suite id '.$suiteId.' is unexists'];
    //             }
    //           }
    //           else {
    //             $result = ['result'=>'failed','reservation'=>null,'error'=>'one of the suite rooms is already reserved or unready or out of service'];
    //           }
    //         }
    //         else {
    //           $result = ['result'=>'failed','reservation'=>null,'error'=>'suite id '.$suiteId.' is unexists'];
    //         }
    //       }
    //       else {
    //         // reservation is for only suite
    //         $rooms = Room::where('SuiteId','=',$suiteId)->get();
    //         $st = true ;
    //         if ($rooms != null ) {
    //           for ($i=0;$i<count($rooms);$i++) {
    //             if ($rooms[$i]->roomStatus > 1) {
    //               $st = false ;
    //               break ;
    //             }
    //           }
    //           if ($st) {
    //             $suite = Suite::find($suiteId);
    //             if ($suite != null) {
    //               $reservRes = $this->insertReservation([
    //                 'RoomNumber' => $suite->SuiteNumber,
    //                   'ClientId' => $clientId,
    //                   'Status' => 1,
    //                   'RoomOrSuite' => $roomOrSuite,
    //                   'MultiRooms' => $multiRooms,
    //                   'AddRoomNumber' => $addRoomsNumber,
    //                   'AddRoomId' => $addRoomsIds,
    //                   'StartDate' => $start,
    //                   'Nights' => $nights,
    //                   'EndDate' => $end,
    //                   'Hotel' => 1,
    //                   'BuildingNo' =>$buildingNumber,
    //                   'Floor' => $floorNumber,
    //                   'ClientFirstName' => $firstName,
    //                   'ClientLastName' => $lastName,
    //                   'IdType' => $idType,
    //                   'IdNumber' => $idNumber,
    //                   'MobileNumber' => $mobile,
    //                   'Email' => $email,
    //                   'Rating' => 0
    //               ]);
    //               if ($reservRes['res'] == 'success') {
    //                 $reservation = $reservRes['reservation'];
    //                 $suiteRes = $this->reserveSuiteInDB($suite);
    //                 if ($suiteRes['res'] == 'success') {
    //                   if($this->reserveSuiteInFirebase($suite)){
    //                     for ($i=0;$i<count($rooms);$i++) {
    //                       $this->reserveSuiteRoomInDB($rooms[$i],$reservation->id);
    //                       $this->reserveRoomInFirebase($rooms[$i],$reservation);
    //                       $this->sendMessageToRoom($rooms[$i],$reservation,'message');
    //                       $this->checkinRoom($rooms[$i],$reservation);
    //                     }
    //                     $result = ['result'=>'success','reservation'=>$reservation,'error'=>null];
    //                     DB::commit();
    //                   }
    //                   else {
    //                     $result = ['result'=>'failed','reservation'=>null,'unable to reserve suite in firebase'];
    //                     DB::rollBack();
    //                   }
    //                 }
    //                 else {
    //                   $error = $suiteRes['error'];
    //                   $result = ['result'=>'failed','reservation'=>null,'unable to save reservation '.$error];
    //                   DB::rollBack();
    //                 }
    //               }
    //               else {
    //                 $error = $reservRes['error'];
    //                 $result = ['result'=>'failed','reservation'=>null,'unable to save reservation '.$error];
    //                 DB::rollBack();
    //               }
    //             }
    //             else {
    //               $result = ['result'=>'failed','reservation'=>null,'error'=>'suite id '.$suiteId.' is unexists'];
    //             }
    //           }
    //           else {
    //             $result = ['result'=>'failed','reservation'=>null,'error'=>'one of the suite rooms is already reserved or unready or out of service'];
    //           }
    //         }
    //         else {
    //           $result = ['result'=>'failed','reservation'=>null,'error'=>'suite id '.$suiteId.' is unexists'];
    //         }
    //       }
    //     }
    //   }
    //   catch(Exception $e) {
    //     DB::rollback();
    //     return ['result'=>'failed','reservation'=>null,'error'=>$e->getMessage()];
    //   }
    // }



    //check


    // public function editIDRoom()
    // {
    //     $bookings = Booking::all();

    //     foreach ($bookings as $booking) {
    //         $room = Room::where('RoomNumber', $booking->RoomNumber)->first();
    //         if ($room) {
    //             $booking->RoomId = $room->id;
    //             $booking->save();
    //         }
    //     }
    // }


    function checkReservation(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'building_id' => 'required|numeric|exists:buildings,id',
            'floor_id' => 'nullable|numeric',
            'room_type' => 'nullable|numeric',
            'start_date' => 'required|date',
            'expire_date' => 'required|date',
            'type_search' => 'required|numeric|in:1,2',   // 1=>First Available Room 2=> All Room
        ]);
        if ($validation->fails()) {
            return response(['result' => 'failed', 'code' => 0, 'error' => $validation->errors()], 200);
        }
        $availableRooms = array();
        $roomsQuery = Room::where('building_id', '=', $request->building_id);
        if ($request->filled('floor_id')) {
            $roomsQuery->where('floor_id', '=', $request->floor_id);
        }
        if ($request->filled('room_type')) {
            $roomsQuery->where('RoomTypeId', '=', $request->room_type);
        }
        //roomStatus=4 => Room is not Available
        $rooms = $roomsQuery->where('roomStatus', '!=', 4)->get();
        if (count($rooms) > 0) {
            foreach ($rooms as $room) {
                $checkReservationForRoom = Booking::where('RoomId', '=', $room->id)
                    //تجاهل الحجوزات القديمة يلي خلصت قبل تاريخ البداية
                    ->where('EndDate', '>=', $request->start_date)
                    ->where(function ($query) use ($request) {
                        //بداية الحجز القديم داخلة ضمن المدة يلي طلبتها
                        $query->whereBetween('StartDate', [$request->start_date, $request->expire_date])
                            //نهاية الحجز القديم داخلة ضمن المدة يلي طلبتها
                            ->orWhereBetween('EndDate', [$request->start_date, $request->expire_date])
                            //الحجز القديم داخل يكل الفترة يلي طلبتها
                            ->orWhere(function ($query) use ($request) {
                            $query->where('StartDate', '<=', $request->start_date)
                                ->where('EndDate', '>=', $request->expire_date);
                        }, );
                    }, )->first();
                switch ($request->type_search) {
                    case 1: // غرفة فارغة: أول غرفة فارغة بترجع فوراً
                        if (!$checkReservationForRoom) {
                            $roomInfo = $room->toArray();
                            return ['result' => 'success', 'code' => 1, "AvailableRooms" => [$roomInfo], "error" => ""];
                        }
                        break;
                    case 2: // جميع الغرف الفارغة
                        if (!$checkReservationForRoom) {
                            $roomInfo = $room->toArray();
                            // $roomInfo = $room->RoomNumber;
                            array_push($availableRooms, $roomInfo);
                        }
                        break;
                }
            }

            if (count($availableRooms) > 0) {
                return ['result' => 'success', 'code' => 1, "AvailableRooms" => $availableRooms, "error" => ""];
            }
            return ['result' => 'failed', 'code' => 0, "AvailableRooms" => [], "error" => "No available rooms found"];
        } else {
            return ['result' => 'failed', 'code' => 0, "AvailableRooms" => [], "error" => "Rooms Not Found"];
        }
    }

    public function checkReservationByRoom($roomId, $startDate, $expireDate)
    {
        $check = false;
        $room = Room::where('id', $roomId)->where('roomStatus', '!=', 4)->first();
        if ($room) {
            $check = Booking::where('RoomId', $room->id)
                ->where('EndDate', '>=', $startDate)
                ->where(function ($query) use ($startDate, $expireDate) {
                    $query->whereBetween('StartDate', [$startDate, $expireDate])
                        ->orWhereBetween('EndDate', [$startDate, $expireDate])
                        ->orWhere(function ($query) use ($startDate, $expireDate) {
                            $query->where('StartDate', '<=', $startDate)
                                ->where('EndDate', '>=', $expireDate);
                        });
                })->exists();
        }
        return $check; // true = الغرفة محجوزة، false = الغرفة متاحة
    }

    public function addRoomReservation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'first_name' => 'required|max:50|min:2',
            'last_name' => 'required|max:50|min:2',
            'mobile' => 'required|max:50|min:10',
            'email' => 'max:50|nullable|email',
            'id_type' => 'required|in:ID,PASSPORT',
            'id_number' => 'required|max:20',
            'start_date' => 'required|date',
            'nights' => 'required|numeric|min:1',
            'multi_rooms' => 'required|numeric|in:0,1',
            'add_rooms_ids' => 'required',
            'client_id' => 'required|min:1',
            'guest_control' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'reservation' => null, 'error' => $validator->errors()];
            return $result;
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'reservation' => '', 'error' => 'you are unauthorized user'];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'reservation' => '', 'error' => 'room id ' . $request->input('room_id') . ' is unexists'];
        }
        $building = Building::find($room->building_id);
        $floor = Floor::find($room->floor_id);
        $firstName = $request->input('first_name');
        $lastName = $request->input('last_name');
        $idType = $request->input('id_type');
        $idNumber = $request->input('id_number');
        $mobile = $request->input('mobile');
        $multiRooms = $request->input('multi_rooms');
        $startDate = $request->input('start_date');
        $nights = $request->input('nights');
        $clientId = $request->input('client_id');
        $cardLink = $request->input('guest_control');
        $startMilli = strtotime($startDate);
        $endArr = getDate($startMilli + (60 * 60 * 24 * $nights));
        $endDate = $endArr['year'] . '-' . $endArr['mon'] . '-' . $endArr['mday'];
        $addRoomsNumber = '';
        $addRoomsIds = $request->input('add_rooms_ids');
        if ($addRoomsIds != 0) {
            $ids = explode('-', $addRoomsIds);
        }
        $email = '';
        if ($request->input('email') != null) {
            $email = $request->input('email');
        }
        if ($multiRooms > 0) {
            // reservation is for room + additional room/s
            if ($room->roomStatus == 1) {
                if (count($ids) == 0) {
                    return ['result' => 'failed', 'reservation' => '', 'error' => 'additional room ids ' . $request->input('add_rooms_ids') . ' is invailed'];
                }
                $rooms = array();
                for ($i = 0; $i < count($ids); $i++) {
                    if (empty($ids[$i]) == false) {
                        $rooms[$i] = Room::find($ids[$i]);
                        if ($rooms[$i] != null) {
                            if ($i == 0) {
                                $addRoomsNumber = $rooms[$i]->RoomNumber;
                            } else {
                                $addRoomsNumber = $addRoomsNumber . '-' . $rooms[$i]->RoomNumber;
                            }
                        }
                    }
                }
                $st = true;
                for ($i = 0; $i < count($rooms); $i++) {
                    if ($rooms[$i]->roomStatus > 1) {
                        $st = false;
                        break;
                    }
                }
                if ($st) {
                    $randomNumber = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                    $reserveRes = $this->insertReservation([
                        'RoomNumber' => $room->RoomNumber,
                        'ClientId' => $clientId,
                        'Status' => 1,
                        'RoomOrSuite' => 1,
                        'MultiRooms' => 1,
                        'AddRoomNumber' => $addRoomsNumber,
                        'AddRoomId' => $addRoomsIds,
                        'StartDate' => $startDate,
                        'Nights' => $nights,
                        'EndDate' => $endDate,
                        'Hotel' => 1,
                        'BuildingNo' => $building->buildingNo,
                        'Floor' => $floor->floorNumber,
                        'ClientFirstName' => $firstName,
                        'ClientLastName' => $lastName,
                        'IdType' => $idType,
                        'IdNumber' => $idNumber,
                        'MobileNumber' => $mobile,
                        'Email' => $email,
                        'Rating' => 0,
                        'password' => password_hash($randomNumber, PASSWORD_DEFAULT),
                        'GuestControl' => $cardLink
                    ]);
                    if ($reserveRes['res'] == 'success') {
                        $reservation = $reserveRes['reservation'];
                        $roomRes = $this->reserveRoomInDB($room, $reservation->id);
                        if ($roomRes['res'] == 'success') {
                            $room = $roomRes['room'];
                            if ($this->reserveRoomInFirebase($room, $reservation)) {
                                $this->sendMessageToRoom($room, $reservation, 'message');
                                $this->checkinRoom($room, $reservation);
                                $res = "";
                                if ($this->isGuestApp() && $cardLink == 1) {
                                    $res = $this->sendConfermationSMSToClient($room, $reservation, $randomNumber);
                                }
                                for ($i = 0; $i < count($rooms); $i++) {
                                    $roomResult = $this->reserveRoomInDB($rooms[$i], $reservation->id);
                                    if ($roomResult['res'] == 'success') {
                                        $this->reserveRoomInFirebase($rooms[$i], $reservation);
                                        $this->sendMessageToRoom($rooms[$i], $reservation, 'message');
                                        $this->checkinRoom($rooms[$i], $reservation);
                                    }

                                }
                                DB::commit();
                                return ['result' => 'success', 'reservation' => $reservation, 'error' => '', 'sms' => $res]; //,'sms'=>$res
                            } else {
                                DB::rollBack();
                                return ['result' => 'failed', 'reservation' => '', 'error' => 'unable to reserve room in firebase '];
                            }
                        } else {
                            DB::rollBack();
                            $error = $roomRes['error'];
                            return ['result' => 'failed', 'reservation' => '', 'error' => 'unable to reserve room ' . $error];
                        }
                    } else {
                        DB::rollBack();
                        $error = $reserveRes['error'];
                        return ['result' => 'failed', 'reservation' => '', 'error' => 'unable to insert reservation ' . $error];
                    }
                } else {
                    return ['result' => 'failed', 'reservation' => '', 'error' => 'one of the additional rooms is already reserved or unready or out of service'];
                }
            } else if ($room->roomStatus == 2) {
                return ['result' => 'failed', 'reservation' => '', 'error' => 'room is already reserved'];
            } else if ($room->roomStatus == 3) {
                return ['result' => 'failed', 'reservation' => '', 'error' => 'room is unready'];
            } else if ($room->roomStatus == 4) {
                return ['result' => 'failed', 'reservation' => '', 'error' => 'room is out of service'];
            }
        } else {
            // reservation is for single room
            if ($room->roomStatus == 1) {
                $randomNumber = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                $insertResult = $this->insertReservation([
                    'RoomNumber' => $room->RoomNumber,
                    'ClientId' => $clientId,
                    'Status' => 1,
                    'RoomOrSuite' => 1,
                    'MultiRooms' => 0,
                    'AddRoomNumber' => '0',
                    'AddRoomId' => $addRoomsIds,
                    'StartDate' => $startDate,
                    'Nights' => $nights,
                    'EndDate' => $endDate,
                    'Hotel' => 1,
                    'BuildingNo' => $building->buildingNo,
                    'Floor' => $floor->floorNumber,
                    'ClientFirstName' => $firstName,
                    'ClientLastName' => $lastName,
                    'IdType' => $idType,
                    'IdNumber' => $idNumber,
                    'MobileNumber' => $mobile,
                    'Email' => $email,
                    'Rating' => 0,
                    'password' => password_hash($randomNumber, PASSWORD_DEFAULT),
                    'GuestControl' => $cardLink
                ]);
                if ($insertResult['res'] != 'success') {
                    $error = $insertResult['error'];
                    DB::rollback();
                    return ['result' => 'failed', 'reservation' => '', 'error' => 'error saving reservation ' . $error];
                }
                $reservation = $insertResult['reservation'];
                $roomRes = $this->reserveRoomInDB($room, $reservation->id);
                if ($roomRes['res'] != 'success') {
                    $error = $roomRes['error'];
                    DB::rollback();
                    return ['result' => 'failed', 'reservation' => '', 'error' => 'error reserve room in database ' . $error];
                }
                if ($this->reserveRoomInFirebase($room, $reservation)) {
                    DB::commit();
                    $this->sendMessageToRoom($room, $reservation, 'message');
                    $this->checkinRoom($room, $reservation);
                    $res = "";
                    if ($this->isGuestApp() && $cardLink == 1) {
                        $res = $this->sendConfermationSMSToClient($room, $reservation, $randomNumber);
                    }
                    return ['result' => 'success', 'reservation' => $reservation, 'error' => '', 'sms' => $res];//,'sms'=>$res
                } else {
                    DB::rollback();
                    return ['result' => 'failed', 'reservation' => '', 'error' => 'error reserving room in firebase'];
                }
            } else if ($room->roomStatus == 2) {
                return ['result' => 'failed', 'reservation' => '', 'error' => 'room ' . $room->RoomNumber . ' is already reserved'];
            } else if ($room->roomStatus == 3) {
                return ['result' => 'failed', 'reservation' => '', 'error' => 'room ' . $room->RoomNumber . ' is unready'];
            } else if ($room->roomStatus == 4) {
                return ['result' => 'failed', 'reservation' => '', 'error' => 'room ' . $room->RoomNumber . ' is out of service'];
            }
        }
    }




    public function addSuiteReservation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'suite_id' => 'required|numeric',
            'first_name' => 'required|max:50|min:2',
            'last_name' => 'required|max:50|min:2',
            'mobile' => 'required|max:50|min:10',
            'email' => 'max:50|nullable',
            'id_type' => 'required|in:ID,PASSPORT',
            'id_number' => 'required|max:20',
            'start_date' => 'required|date',
            'nights' => 'required|numeric|min:1',
            'multi_rooms' => 'required|numeric|in:0,1',
            'add_rooms_ids' => 'required',
            'client_id' => 'required|min:1',
            'guest_control' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'reservation' => null, 'error' => $validator->errors()];
            return $result;
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'reservation' => '', 'error' => 'you are unauthorized user'];
        }
        $suite = Suite::find($request->input('suite_id'));
        if ($suite == null) {
            return ['result' => 'failed', 'reservation' => '', 'error' => 'suite id ' . $request->input('suite_id') . ' is unavailable'];
        }
        $firstName = $request->input('first_name');
        $lastName = $request->input('last_name');
        $mobile = $request->input('mobile');
        $idType = $request->input('id_type');
        $idNumber = $request->input('id_number');
        $startDate = $request->input('start_date');
        $nights = $request->input('nights');
        $startMilli = strtotime($startDate);
        $endArr = getDate($startMilli + (60 * 60 * 24 * $nights));
        $endDate = $endArr['year'] . '-' . $endArr['mon'] . '-' . $endArr['mday'];
        $clientId = $request->input('client_id');
        $multiRooms = $request->input('multi_rooms');
        $addRoomsIds = $request->input('add_rooms_ids');
        $cardLink = $request->input('guest_control');
        if ($addRoomsIds != 0) {
            $ids = explode('-', $addRoomsIds);
        }
        $addRoomsNumber = '';
        $email = '';
        if ($request->input('email') != null) {
            $email = $request->input('email');
        }
        if ($multiRooms > 0) {
            $suiteRooms = Room::where('SuiteId', $suite->id)->get();
            $st = true;
            if ($suiteRooms == null || count($suiteRooms) == 0) {
                return ['result' => 'failed', 'reservation' => '', 'error' => 'no rooms in suite number ' . $suite->SuiteNumber];
            }
            for ($i = 0; $i < count($suiteRooms); $i++) {
                if ($suiteRooms[$i]->roomStatus > 1) {
                    $st = false;
                    break;
                }
            }
            if ($st == false) {
                return ['result' => 'failed', 'reservation' => null, 'error' => 'one of the suite rooms is already reserved or unready or out of service'];
            }
            $additionalRooms = array();
            for ($i = 0; $i < count($ids); $i++) {
                if (empty($ids[$i]) == false) {
                    $additionalRooms[$i] = Room::find($ids[$i]);
                    if ($additionalRooms[$i] != null) {
                        if ($i == 0) {
                            $addRoomsNumber = $additionalRooms[$i]->RoomNumber;
                        } else {
                            $addRoomsNumber = $addRoomsNumber . '-' . $additionalRooms[$i]->RoomNumber;
                        }
                    }
                }

            }
            $stt = true;
            for ($i = 0; $i < count($additionalRooms); $i++) {
                if ($additionalRooms[$i]->roomStatus > 1) {
                    $stt = false;
                    break;
                }
            }
            if ($stt) {
                return ['result' => 'failed', 'reservation' => '', 'error' => 'one of the additional rooms is already reserved or unready or out of service'];
            }
            $randomNumber = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $reserveRes = $this->insertReservation([
                'RoomNumber' => $suite->SuiteNumber,
                'ClientId' => $clientId,
                'Status' => 1,
                'RoomOrSuite' => 2,
                'MultiRooms' => 1,
                'AddRoomNumber' => $addRoomsNumber,
                'AddRoomId' => $addRoomsIds,
                'StartDate' => $startDate,
                'Nights' => $nights,
                'EndDate' => $endDate,
                'Hotel' => 1,
                'BuildingNo' => $suite->Building,
                'Floor' => $suite->Floor,
                'ClientFirstName' => $firstName,
                'ClientLastName' => $lastName,
                'IdType' => $idType,
                'IdNumber' => $idNumber,
                'MobileNumber' => $mobile,
                'Email' => $email,
                'password' => password_hash($randomNumber, PASSWORD_DEFAULT),
                'Rating' => 0
            ]);
            if ($reserveRes['res'] == 'success') {
                $reservation = $reserveRes['reservation'];
                $suiteRes = $this->reserveSuiteInDB($suite);
                if ($suiteRes['res'] == 'success') {
                    if ($this->reserveSuiteInFirebase($suite)) {
                        $res = "";
                        if ($this->isGuestApp() && $cardLink == 1) {
                            $res = $this->sendConfermationSMSToClient($suiteRooms[0], $reservation, $randomNumber);
                        }
                        for ($i = 0; $i < count($suiteRooms); $i++) {
                            $rrRes = $this->reserveSuiteRoomInDB($suiteRooms[$i], $reservation->id);
                            if ($rrRes['res'] == 'success') {
                                $this->reserveRoomInFirebase($suiteRooms[$i], $reservation);
                                $this->sendMessageToRoom($suiteRooms[$i], $reservation, 'message');
                                $this->checkinRoom($suiteRooms[$i], $reservation);
                            }
                        }
                        for ($i = 0; $i < count($additionalRooms); $i++) {
                            $rrRes = $this->reserveRoomInDB($additionalRooms[$i], $reservation->id);
                            if ($rrRes['res'] == 'success') {
                                $this->reserveRoomInFirebase($additionalRooms[$i], $reservation);
                                $this->sendMessageToRoom($additionalRooms[$i], $reservation, 'message');
                                $this->checkinRoom($additionalRooms[$i], $reservation);
                            }
                        }
                        DB::commit();
                        return ['result' => 'success', 'reservation' => $reservation, 'error' => null, 'sms' => $res];//,'sms'=>$res
                    }
                }
            }
        } else {
            $rooms = Room::where('SuiteId', $suite->id)->get();
            $st = true;
            if ($rooms == null || count($rooms) == 0) {
                return ['result' => 'failed', 'reservation' => '', 'error' => 'suite id ' . $suite->id . ' is unexists'];
            }
            for ($i = 0; $i < count($rooms); $i++) {
                if ($rooms[$i]->roomStatus > 1) {
                    $st = false;
                    break;
                }
            }
            if ($st == false) {
                return ['result' => 'failed', 'reservation' => '', 'error' => 'one of the suite rooms is already reserved or unready or out of service'];
            }
            $randomNumber = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $reservRes = $this->insertReservation([
                'RoomNumber' => $suite->SuiteNumber,
                'ClientId' => $clientId,
                'Status' => 1,
                'RoomOrSuite' => 2,
                'MultiRooms' => $multiRooms,
                'AddRoomNumber' => 0,
                'AddRoomId' => $addRoomsIds,
                'StartDate' => $startDate,
                'Nights' => $nights,
                'EndDate' => $endDate,
                'Hotel' => 1,
                'BuildingNo' => $suite->Building,
                'Floor' => $suite->Floor,
                'ClientFirstName' => $firstName,
                'ClientLastName' => $lastName,
                'IdType' => $idType,
                'IdNumber' => $idNumber,
                'MobileNumber' => $mobile,
                'Email' => $email,
                'Rating' => 0,
                'password' => password_hash($randomNumber, PASSWORD_DEFAULT),
                'GuestControl' => $cardLink
            ]);
            if ($reservRes['res'] == 'success') {
                $reservation = $reservRes['reservation'];
                $suiteRes = $this->reserveSuiteInDB($suite);
                if ($suiteRes['res'] == 'success') {
                    if ($this->reserveSuiteInFirebase($suite)) {
                        $res = "";
                        if ($this->isGuestApp() && $cardLink == 1) {
                            $res = $this->sendConfermationSMSToClient($rooms[0], $reservation, $randomNumber);
                        }
                        for ($i = 0; $i < count($rooms); $i++) {
                            $this->reserveSuiteRoomInDB($rooms[$i], $reservation->id);
                            $this->reserveRoomInFirebase($rooms[$i], $reservation);
                            $this->sendMessageToRoom($rooms[$i], $reservation, 'message');
                            $this->checkinRoom($rooms[$i], $reservation);
                        }
                        DB::commit();
                        return ['result' => 'success', 'reservation' => $reservation, 'error' => '', 'sms' => $res]; //,'sms'=>$res
                    } else {
                        DB::rollBack();
                        return ['result' => 'failed', 'reservation' => '', 'unable to reserve suite in firebase'];
                    }
                } else {
                    DB::rollBack();
                    $error = $suiteRes['error'];
                    return ['result' => 'failed', 'reservation' => '', 'unable to save reservation ' . $error];
                }
            } else {
                DB::rollBack();
                $error = $reservRes['error'];
                return ['result' => 'failed', 'reservation' => '', 'unable to save reservation ' . $error];
            }
        }
    }

    public function addCleanupOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => null, 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'reservation' => null, 'error' => 'you are unauthorized user'];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room id is un exsists'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
        }
        if ($room->ReservationNumber == 0) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'Cleanup')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->Cleanup = $oldOrder->dateTime;
            $room->save();
            $arrRoom = [
                'dep' => 'Cleanup',
                'Cleanup' => $oldOrder->dateTime
            ];
            Http::patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            $ddd = $this->sendNotificationToServiceUsers('Cleanup', $room);
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'cleanup order already exists and notified again', 'notification' => $ddd];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'Cleanup', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);

        if ($addRes['result'] == 'failed') {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'unable to insert order to db '];
            return $result;
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'Cleanup';
        $room->Cleanup = $time;
        if ($room->save()) {
            $arrRoom = [
                'dep' => 'Cleanup',
                'Cleanup' => $time
            ];
            $response = Http::patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            $ddd = $this->sendNotificationToServiceUsers('Cleanup', $room);
            return ['result' => 'success', 'cleanup order' => $order, 'error' => $ddd];
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => null, 'error' => 'error saving updates to room in db'];
        }

    }

    public function addSOSOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => null, 'error' => $validator->errors()];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room id is un exsists'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
        }
        if ($room->ReservationNumber == 0) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'SOS')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->SOS = $oldOrder->dateTime;
            $room->save();
            $arrRoom = [
                'dep' => 'SOS',
                'SOS' => $oldOrder->dateTime
            ];
            $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            if ($response->successful()) {
                $this->sendNotificationToServiceUsers('SOS', $room);
            }
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'cleanup order already exists and notified again'];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'SOS', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'unable to insert order to db '];
            return $result;
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'SOS';
        $room->SOS = $time;
        if ($room->save()) {
            $arrRoom = [
                'dep' => 'SOS',
                'SOS' => $time
            ];
            $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            if ($response->successful()) {
                $this->sendNotificationToServiceUsers('SOS', $room);
                return ['result' => 'success', 'cleanup order' => $order, 'error' => null];
            } else {
                $room->dep = $oldDep;
                $room->Cleanup = 0;
                $room->save();
                $order->delete();
                return ['result' => 'failed', 'order' => null, 'error' => 'error saving updates to room in db'];
            }
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => null, 'error' => 'error saving updates to room in db'];
        }

    }

    public function extendStay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reservetion_id' => 'required|max:50|min:2|exists:bookings,id',
            'new_end' => 'required|date'
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'login' => '', 'error' => $validator->errors()];
            return $result;
        }
        $reservation = Booking::find($request->reservetion_id);
        $newEnd = $request->new_end;
        $oldEnd = $reservation->EndDate;
        if (strtotime($oldEnd) >= strtotime($newEnd)) {
            return ['result' => 'failed', 'reservation' => '', 'error' => 'new date is before or equal old date '];
        }
        $reservation->EndDate = $newEnd;
        $reservation->save();
        return ['result' => 'success', 'reservetion' => $reservation, 'error' => ''];
    }

    public function addCleanupOrderControlDevice1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => null, 'error' => $validator->errors()];
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room id is un exsists'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
        }
        if ($room->ReservationNumber == 0) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'Cleanup')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->Cleanup = $oldOrder->dateTime;
            $room->save();
            $this->sendNotificationToServiceUsers('Cleanup', $room);
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'cleanup order already exists and notified again '];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'Cleanup', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'unable to insert order to db '];
            return $result;
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'Cleanup';
        $room->Cleanup = $time;
        if ($room->save()) {
            $rrr = $this->sendNotificationToServiceUsers('Cleanup', $room);
            return ['result' => 'success', 'cleanup order' => $rrr, 'error' => null];
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => null, 'error' => 'error saving updates to room in db'];
        }
    }
    public function addCleanupOrderControlDevice2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => null, 'error' => $validator->errors()];
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room id is un exsists'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
        }
        if ($room->ReservationNumber == 0) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'Cleanup')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->Cleanup = $oldOrder->dateTime;
            $room->save();
            $this->sendNotificationToServiceUsers('Cleanup', $room);
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'cleanup order already exists and notified again '];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'Cleanup', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'unable to insert order to db '];
            return $result;
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'Cleanup';
        $room->Cleanup = $time;
        if ($room->save()) {
            $this->sendNotificationToServiceUsers('Cleanup', $room);
            return ['result' => 'success', 'cleanup order' => $order, 'error' => null];
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => null, 'error' => 'error saving updates to room in db'];
        }
    }
    public function addCleanupOrderControlDevice3(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => null, 'error' => $validator->errors()];
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room id is un exsists'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
        }
        if ($room->ReservationNumber == 0) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'Cleanup')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->Cleanup = $oldOrder->dateTime;
            $room->save();
            $this->sendNotificationToServiceUsers('Cleanup', $room);
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'cleanup order already exists and notified again '];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'Cleanup', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'unable to insert order to db '];
            return $result;
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'Cleanup';
        $room->Cleanup = $time;
        if ($room->save()) {
            $this->sendNotificationToServiceUsers('Cleanup', $room);
            return ['result' => 'success', 'cleanup order' => $order, 'error' => null];
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => null, 'error' => 'error saving updates to room in db'];
        }

    }
    public function addCleanupOrderControlDevice4(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => null, 'error' => $validator->errors()];
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room id is un exsists'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
        }
        if ($room->ReservationNumber == 0) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'Cleanup')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->Cleanup = $oldOrder->dateTime;
            $room->save();
            $this->sendNotificationToServiceUsers('Cleanup', $room);
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'cleanup order already exists and notified again '];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'Cleanup', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'unable to insert order to db '];
            return $result;
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'Cleanup';
        $room->Cleanup = $time;
        if ($room->save()) {
            $this->sendNotificationToServiceUsers('Cleanup', $room);
            return ['result' => 'success', 'cleanup order' => $order, 'error' => null];
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => null, 'error' => 'error saving updates to room in db'];
        }

    }
    public function addCleanupOrderControlDevice5(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => null, 'error' => $validator->errors()];
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room id is un exsists'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
        }
        if ($room->ReservationNumber == 0) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'Cleanup')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->Cleanup = $oldOrder->dateTime;
            $room->save();
            $this->sendNotificationToServiceUsers('Cleanup', $room);
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'cleanup order already exists and notified again '];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'Cleanup', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'unable to insert order to db '];
            return $result;
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'Cleanup';
        $room->Cleanup = $time;
        if ($room->save()) {
            $this->sendNotificationToServiceUsers('Cleanup', $room);
            return ['result' => 'success', 'cleanup order' => $order, 'error' => null];
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => null, 'error' => 'error saving updates to room in db'];
        }
    }

    public function addLaundryOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'room' => null, 'error' => $validator->errors()];
            return $result;
        }
        date_default_timezone_set('Asia/Riyadh');
        if (Users::checkAuth($request->input('my_token')) == false) {
            $result = ['result' => 'failed', 'reservation' => null, 'error' => 'you are unauthorized user'];
            return $result;
        }

        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room id is un exsists'];
            return $result;
        }
        if ($room->roomStatus != 2) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
            return $result;
        }
        if ($room->ReservationNumber == 0) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
            return $result;
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
            return $result;
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'Laundry')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->Laundry = $oldOrder->dateTime;
            $room->save();
            $arrRoom = [
                'dep' => 'Laundry',
                'Laundry' => $oldOrder->dateTime
            ];
            $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            if ($response->successful()) {
                $this->sendNotificationToServiceUsers('Laundry', $room);
            }
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'laundry order already exists and notified again'];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'Laundry', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to insert order to db '];
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'Laundry';
        $room->Laundry = $time;
        if ($room->save()) {
            $arrRoom = [
                'dep' => 'Laundry',
                'Laundry' => $time
            ];
            $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            if ($response->successful()) {
                $this->sendNotificationToServiceUsers('Laundry', $room);
                return ['result' => 'success', 'laundry order' => $order, 'error' => ''];
            } else {
                $room->dep = $oldDep;
                $room->Laundry = 0;
                $room->save();
                $order->delete();
                return ['result' => 'failed', 'order' => '', 'error' => 'error saving updates to room in db'];
            }
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => null, 'error' => 'error saving updates to room in db'];
        }
    }

    public function addLaundryOrderControlDevice1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id is unavailable'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
        }
        if ($room->ReservationNumber == 0) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
            return $result;
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            return ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'Laundry')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->Laundry = $oldOrder->dateTime;
            $room->save();
            $this->sendNotificationToServiceUsers('Laundry', $room);
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'laundry order already exists and notified again'];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'Laundry', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to insert order to db '];
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'Laundry';
        $room->Laundry = $time;
        if ($room->save()) {
            $this->sendNotificationToServiceUsers('Laundry', $room);
            return ['result' => 'success', 'laundry order' => $order, 'error' => ''];
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => null, 'error' => 'error saving updates to room in db'];
        }
    }
    public function addLaundryOrderControlDevice2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'room' => null, 'error' => $validator->errors()];
            return $result;
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room id is un exsists'];
            return $result;
        }
        if ($room->roomStatus != 2) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
            return $result;
        }
        if ($room->ReservationNumber == 0) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
            return $result;
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
            return $result;
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'Laundry')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->Laundry = $oldOrder->dateTime;
            $room->save();
            $this->sendNotificationToServiceUsers('Laundry', $room);
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'laundry order already exists and notified again'];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'Laundry', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to insert order to db '];
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'Laundry';
        $room->Laundry = $time;
        if ($room->save()) {
            $this->sendNotificationToServiceUsers('Laundry', $room);
            return ['result' => 'success', 'laundry order' => $order, 'error' => ''];
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => null, 'error' => 'error saving updates to room in db'];
        }
    }
    public function addLaundryOrderControlDevice3(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'room' => null, 'error' => $validator->errors()];
            return $result;
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room id is un exsists'];
            return $result;
        }
        if ($room->roomStatus != 2) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
            return $result;
        }
        if ($room->ReservationNumber == 0) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
            return $result;
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
            return $result;
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'Laundry')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->Laundry = $oldOrder->dateTime;
            $room->save();
            $this->sendNotificationToServiceUsers('Laundry', $room);
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'laundry order already exists and notified again'];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'Laundry', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to insert order to db '];
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'Laundry';
        $room->Laundry = $time;
        if ($room->save()) {
            $this->sendNotificationToServiceUsers('Laundry', $room);
            return ['result' => 'success', 'laundry order' => $order, 'error' => ''];
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => null, 'error' => 'error saving updates to room in db'];
        }
    }
    public function addLaundryOrderControlDevice4(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'room' => null, 'error' => $validator->errors()];
            return $result;
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room id is un exsists'];
            return $result;
        }
        if ($room->roomStatus != 2) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
            return $result;
        }
        if ($room->ReservationNumber == 0) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
            return $result;
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
            return $result;
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'Laundry')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->Laundry = $oldOrder->dateTime;
            $room->save();
            $this->sendNotificationToServiceUsers('Laundry', $room);
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'laundry order already exists and notified again'];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'Laundry', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to insert order to db '];
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'Laundry';
        $room->Laundry = $time;
        if ($room->save()) {
            $this->sendNotificationToServiceUsers('Laundry', $room);
            return ['result' => 'success', 'laundry order' => $order, 'error' => ''];
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => null, 'error' => 'error saving updates to room in db'];
        }
    }
    public function addLaundryOrderControlDevice5(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'room' => null, 'error' => $validator->errors()];
            return $result;
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room id is un exsists'];
            return $result;
        }
        if ($room->roomStatus != 2) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
            return $result;
        }
        if ($room->ReservationNumber == 0) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
            return $result;
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
            return $result;
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'Laundry')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->Laundry = $oldOrder->dateTime;
            $room->save();
            $this->sendNotificationToServiceUsers('Laundry', $room);
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'laundry order already exists and notified again'];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'Laundry', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to insert order to db '];
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'Laundry';
        $room->Laundry = $time;
        if ($room->save()) {
            $this->sendNotificationToServiceUsers('Laundry', $room);
            return ['result' => 'success', 'laundry order' => $order, 'error' => ''];
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => null, 'error' => 'error saving updates to room in db'];
        }
    }

    public function addRoomServiceOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
            'order' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        date_default_timezone_set('Asia/Riyadh');
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'order' => '', 'error' => 'you are unauthorized user'];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id is un exsists'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
        }
        if ($room->ReservationNumber == 0) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'RoomService')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->RoomServiceText = $oldOrder->orderText;
            $room->RoomService = $oldOrder->dateTime;
            $room->save();
            $arrRoom = [
                'dep' => 'RoomService',
                'RoomService' => $oldOrder->dateTime,
                'RoomServiceText' => $oldOrder->orderText
            ];
            $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            if ($response->successful()) {
                $this->sendNotificationToServiceUsers('RoomService', $room);
            }
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'room service order already exists and notified again'];
        }
        $time = intval(microtime(true) * 1000);
        $orderText = $request->input('order');
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'RoomService', 'time' => $time, 'orderText' => $orderText];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            return ['result' => 'failed', 'order' => null, 'error' => 'unable to insert order to db '];
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'RoomService';
        $room->RoomService = $time;
        $room->RoomServiceText = $orderText;
        if ($room->save()) {
            $arrRoom = [
                'dep' => 'RoomService',
                'RoomService' => $time,
                'RoomServiceText' => $orderText
            ];
            $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            if ($response->successful()) {
                $this->sendNotificationToServiceUsers('RoomService', $room);
                return ['result' => 'success', 'room service order' => $order, 'error' => null];
            } else {
                $room->dep = $oldDep;
                $room->RoomService = 0;
                $room->save();
                $order->delete();
                return ['result' => 'failed', 'order' => null, 'error' => 'error saving updates to room in db'];
            }
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => null, 'error' => 'error saving updates to room in db'];
        }
    }

    public function addRoomServiceOrderRoomDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
            'order' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id is un exsists'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
        }
        if ($room->ReservationNumber == 0) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'RoomService')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->RoomServiceText = $oldOrder->orderText;
            $room->RoomService = $oldOrder->dateTime;
            $room->save();
            // $arrRoom = [
            //   'dep'=> 'RoomService',
            //   'RoomService'=> $oldOrder->dateTime,
            //   'RoomServiceText'=> $oldOrder->orderText
            // ];
            // $response = Http::retry(3,100)->patch($this->firebaseUrl.'/'.$this->projectName.'/B'.$room->Building.'/F'.$room->Floor.'/R'.$room->RoomNumber.'.json',$arrRoom);
            $this->sendNotificationToServiceUsers('RoomService', $room);
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'room service order already exists and notified again'];
        }
        $time = intval(microtime(true) * 1000);
        $orderText = $request->input('order');
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'RoomService', 'time' => $time, 'orderText' => $orderText];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            return ['result' => 'failed', 'order' => null, 'error' => 'unable to insert order to db '];
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'RoomService';
        $room->RoomService = $time;
        $room->RoomServiceText = $orderText;
        if ($room->save()) {
            //   $arrRoom = [
            //     'dep'=> 'RoomService',
            //     'RoomService'=> $time,
            //     'RoomServiceText'=> $orderText
            //   ];
            //   $response = Http::retry(3,100)->patch($this->firebaseUrl.'/'.$this->projectName.'/B'.$room->Building.'/F'.$room->Floor.'/R'.$room->RoomNumber.'.json',$arrRoom);
            $this->sendNotificationToServiceUsers('RoomService', $room);
            return ['result' => 'success', 'room service order' => $order, 'error' => null];
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => null, 'error' => 'error saving updates to room in db'];
        }
    }

    public function addCheckoutOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        date_default_timezone_set('Asia/Riyadh');
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'order' => '', 'error' => 'you are unauthorized user'];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id is un exsists'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
        }
        if ($room->ReservationNumber == 0) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'Checkout')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->Checkout = $oldOrder->dateTime;
            $room->save();
            $arrRoom = [
                'dep' => 'Checkout',
                'Checkout' => $oldOrder->dateTime
            ];
            $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'room service order already exists and notified again'];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'Checkout', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to insert order to db '];
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'Checkout';
        $room->Checkout = $time;
        if ($room->save()) {
            $arrRoom = [
                'dep' => 'Checkout',
                'Checkout' => $time,
            ];
            $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            if ($response->successful()) {
                return ['result' => 'success', 'room service order' => $order, 'error' => ''];
            } else {
                $room->dep = $oldDep;
                $room->RoomService = 0;
                $room->save();
                $order->delete();
                return ['result' => 'failed', 'order' => '', 'error' => 'error saving updates to room in db'];
            }
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => '', 'error' => 'error saving updates to room in db'];
        }
    }

    public function addCheckoutOrderControlDevice1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id is un exsists'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
        }
        if ($room->ReservationNumber == 0) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'Checkout')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->Checkout = $oldOrder->dateTime;
            $room->save();
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'room service order already exists and notified again'];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'Checkout', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to insert order to db '];
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'Checkout';
        $room->Checkout = $time;
        if ($room->save()) {
            return ['result' => 'success', 'room service order' => $order, 'error' => ''];
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => '', 'error' => 'error saving updates to room in db'];
        }
    }
    public function addCheckoutOrderControlDevice2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id is un exsists'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
        }
        if ($room->ReservationNumber == 0) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'Checkout')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->Checkout = $oldOrder->dateTime;
            $room->save();
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'room service order already exists and notified again'];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'Checkout', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to insert order to db '];
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'Checkout';
        $room->Checkout = $time;
        if ($room->save()) {
            return ['result' => 'success', 'room service order' => $order, 'error' => ''];
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => '', 'error' => 'error saving updates to room in db'];
        }
    }
    public function addCheckoutOrderControlDevice3(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id is un exsists'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
        }
        if ($room->ReservationNumber == 0) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'Checkout')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->Checkout = $oldOrder->dateTime;
            $room->save();
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'room service order already exists and notified again'];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'Checkout', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to insert order to db '];
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'Checkout';
        $room->Checkout = $time;
        if ($room->save()) {
            return ['result' => 'success', 'room service order' => $order, 'error' => ''];
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => '', 'error' => 'error saving updates to room in db'];
        }
    }
    public function addCheckoutOrderControlDevice4(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id is un exsists'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
        }
        if ($room->ReservationNumber == 0) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'Checkout')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->Checkout = $oldOrder->dateTime;
            $room->save();
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'room service order already exists and notified again'];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'Checkout', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to insert order to db '];
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'Checkout';
        $room->Checkout = $time;
        if ($room->save()) {
            return ['result' => 'success', 'room service order' => $order, 'error' => ''];
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => '', 'error' => 'error saving updates to room in db'];
        }
    }
    public function addCheckoutOrderControlDevice5(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id is un exsists'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
        }
        if ($room->ReservationNumber == 0) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'Checkout')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->Checkout = $oldOrder->dateTime;
            $room->save();
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'room service order already exists and notified again'];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'Checkout', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to insert order to db '];
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'Checkout';
        $room->Checkout = $time;
        if ($room->save()) {
            return ['result' => 'success', 'room service order' => $order, 'error' => ''];
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => '', 'error' => 'error saving updates to room in db'];
        }
    }

    public function addSOSOrderControlDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id is un exsists'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved'];
        }
        if ($room->ReservationNumber == 0) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is unreserved or reservation number is unavailable'];
        }
        $b = Booking::find($room->ReservationNumber);
        if ($b == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room ' . $room->RoomNumber . ' is un reserved or reservation number is unavailable'];
        }
        $oldOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', 'SOS')->where('status', 0)->first();
        if ($oldOrder != null) {
            $room->SOS = $oldOrder->dateTime;
            $room->save();
            return ['result' => 'success', 'order' => $oldOrder, 'error' => 'room service order already exists and notified again'];
        }
        $time = intval(microtime(true) * 1000);
        $orderPars = ['RoomNumber' => $room->RoomNumber, 'reservationId' => $b->id, 'RorS' => $b->RoomOrSuite, 'dep' => 'SOS', 'time' => $time, 'orderText' => ''];
        $addRes = $this->addServiceOrder($orderPars);
        if ($addRes['result'] == 'failed') {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to insert order to db '];
        }
        $order = $addRes['order'];
        $oldDep = $room->dep;
        $room->dep = 'SOS';
        $room->SOS = $time;
        if ($room->save()) {
            return ['result' => 'success', 'room service order' => $order, 'error' => ''];
        } else {
            $order->delete();
            return ['result' => 'failed', 'order' => '', 'error' => 'error saving updates to room in db'];
        }
    }


    public function guestLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|max:50|min:2',
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'login' => '', 'error' => $validator->errors()];
            return $result;
        }
        date_default_timezone_set('Asia/Riyadh');
        $booking = Booking::find($request->number);
        if ($booking == null) {
            return ['error' => 'reservation is unavailable'];
        }
        if ($booking->Status == 0) {
            return ['error' => 'Reservation has checked out'];
        }
        $d = date("Y-m-d h:i:s");
        if ($booking->StartDate > $d) {
            return ['error' => 'Reservation Start Date is ' . $booking->StartDate . ' today is ' . $d];
        }
        $checkoutTime = Projectsvariable::first()->CheckoutTime;
        $time = strtotime($booking->EndDate . $checkoutTime) * 1000; //' 14:00:00'
        $EndDate = date('Y-m-d h:i:s', $time);
        if ($EndDate < $d) {
            return ['error' => 'Reservation Has Expired Please Call Reception'];
        }
        return ['result' => 'success', 'reservation' => $booking, 'time' => $d, 'end' => $EndDate];
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|numeric',
            'password' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'login' => '', 'error' => $validator->errors()];
            return $result;
        }
        $client = Client::where('Mobile', '=', $request->mobile)->first();
        if (!$client) {
            return ['result' => 'failed', 'login' => '', 'error' => 'Mobile number unrecorded '];
        }
        $reservation = Booking::where('ClientId', '=', $client->id)->where('Status', '=', 1)->first();
        if (!$reservation) {
            return ['result' => 'failed', 'login' => '', 'error' => 'You dont have active reservation'];
        }
        $room = Room::where('RoomNumber', '=', $reservation->RoomNumber)->first();
        if (password_verify($request->password, $reservation->password)) {
            return ['result' => 'success', 'reservation' => $reservation, 'room' => $room];
        } else {
            return ['result' => 'failed', 'login' => '', 'error' => 'Your passowrd is invaled'];
        }
    }

    public function checkAndRedirect(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|max:50|min:2',
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'login' => '', 'error' => $validator->errors()];
            return $result;
        }
        $booking = Booking::find($request->number);

        if ($booking == null) {
            return view('ReservationProblem', ['error' => 'reservation is unavailable']); //['result'=>'failed','error'=>'reservation is unavailable'];
        }
        if ($booking->Status == 0) {
            return view('ReservationProblem', ['error' => 'Reservation has checked out']); //['result'=>'failed','error'=>'Reservation has checked out','reservation'=>$booking];
        }
        date_default_timezone_set('Asia/Riyadh');
        $d = date("Y-m-d h:i:s");
        if ($booking->StartDate > $d) {
            return view('ReservationProblem', ['error' => 'Reservation Start Date is ' . $booking->StartDate . ' today is ' . $d]);//['result'=>'failed','error'=>'Reservation Start Date is '.$booking->StartDate.' today is '.$d,'reservation'=>$booking];
        }
        $checkoutTime = Projectsvariable::first()->CheckoutTime;
        $time = strtotime($booking->EndDate . ' ' . $checkoutTime) * 1000;
        $EndDate = date('Y-m-d h:i:s', $time);

        if ($EndDate < $d) {
            return view('ReservationProblem', ['error' => 'Reservation Date Has Finished Please Call Reception']); //['result'=>'failed','error'=>'Reservation Date Has Finished Please Call Reception','reservation'=>$booking];
        }
        if ($booking->RoomOrSuite == 1) {
            return redirect($this->projectUrl . "/" . $booking->RoomNumber);
        } else {
            $suite = Suite::where('SuiteNumber', $booking->RoomNumber)->first();
            $arr = explode("-", $suite->RoomsId);
            $room = Room::find($arr[0]);
            return redirect($this->projectUrl . "/" . $room->RoomNumber);
        }

    }

    // function checkReservation() {
    //   $validator = Validator::make($request->all(),[
    //     'type' => 'required|max:50|min:2',
    //     'number' => 'required|max:50|min:2',
    //     ]);
    //   if ($validator->fails())  {
    //     $result = ['result'=>'failed','insertedRow'=>'','error'=>$validator->errors()];
    //     return $result ;
    //   }
    //   $booking = null ;
    //   if ($request->type == "mob") {
    //       $bookings = Booking::where('MobileNumber',$request->number)->get();
    //       if ($bookings != null && count($bookings) != 0) {
    //           $booking = $bookings[count($bookings)-1];
    //       }
    //   }
    //   else if ($request->type == "id") {
    //       $bookings = Booking::where('IdNumber',$request->number)->get();
    //       if ($bookings != null && count($bookings) != 0) {
    //           $booking = $bookings[count($bookings)-1];
    //       }
    //   }
    //   if ($booking == null) {
    //       if ($request->type == 'mob') {
    //           return ['result'=>'failed','error'=>'Mobile Number is unavailable'.$request->number];
    //       }
    //       else {
    //           return ['result'=>'failed','error'=>'ID number is unavailable'];
    //       }

    //   }
    //   if ($booking->Status == 0) {
    //       return ['result'=>'failed','error'=>'Reservation has checked out','reservation'=>$booking];
    //   }
    //   date_default_timezone_set('Asia/Riyadh');
    //   $d = date("Y-m-d h:i:s") ;
    //   if ($booking->StartDate > $d) {
    //       return ['result'=>'failed','error'=>'Reservation Start Date is '.$booking->StartDate.' today is '.$d,'reservation'=>$booking];
    //   }
    //   $time = strtotime($booking->EndDate.' 14:00:00');
    //   $EndDate = date('Y-m-d h:i:s',$time);

    //   if ($EndDate < $d) {
    //       return ['result'=>'failed','error'=>'Reservation Date Has Finished Please Call Reception','reservation'=>$booking,'dates'=>$EndDate.' '.$d];
    //   }

    //   return ['result'=>'success','reservation'=>$booking,'time'=>$d,'end' => $EndDate];
    // }

    /*
        get functions
    */

    public function getRoomId(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'room_number' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'room' => null, 'error' => $validator->errors()];
            return $result;
        }
        if (Users::checkAuth($request->input('my_token'))) {
            $rooms = Room::where('RoomNumber', $request->input('room_number'))->get();
            if ($rooms == null || count($rooms) == 0) {
                $result = ['result' => 'failed', 'room' => null, 'error' => 'no sutch room number '];
                return $result;
            }
            if (count($rooms) > 1) {
                $result = ['result' => 'failed', 'room' => null, 'error' => 'many rooms has the same roomnumber '];
                return $result;
            }
            $room = Room::where('RoomNumber', $request->input('room_number'))->first();
            return $room->id;
        } else {
            $result = ['result' => 'failed', 'reservation' => null, 'error' => 'you are unauthorized user'];
            return $result;
        }
    }

    public function getRoomReservation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_number' => 'required|numeric',
            'reservation_id' => 'required'
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'reservation' => '', 'error' => $validator->errors()];
            return $result;
        }
        $R = Booking::find($request->reservation_id);
        if ($R == null) {
            return ['result' => 'failed', 'reservation' => '', 'error' => 'reservation id ' . $request->reservation_id . ' is unavailable'];
        }
        if ($R->RoomNumber != $request->room_number) {
            return ['result' => 'failed', 'reservation' => '', 'error' => 'reservation id ' . $request->reservation_id . ' and room number ' . $request->room_number . ' is uncompatible'];
        }
        if ($R->Status == 0) {
            return ['result' => 'failed', 'reservation' => '', 'error' => 'reservation is already closed'];
        }
        return ['result' => 'success', 'reservation' => $R, 'error' => ''];
    }

    public function getSuiteReservation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'suite_number' => 'required|numeric',
            'reservation_id' => 'required'
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'reservation' => '', 'error' => $validator->errors()];
            return $result;
        }
        $R = Booking::find($request->reservation_id);
        if ($R == null) {
            return ['result' => 'failed', 'reservation' => '', 'error' => 'reservation id ' . $request->reservation_id . ' is unavailable'];
        }
        if ($R->RoomNumber != $request->suite_number) {
            return ['result' => 'failed', 'reservation' => '', 'error' => 'reservation id ' . $request->reservation_id . ' and room number ' . $request->suite_number . ' is uncompatible'];
        }
        if ($R->Status == 0) {
            return ['result' => 'failed', 'reservation' => '', 'error' => 'reservation is already closed'];
        }
        return ['result' => 'success', 'reservation' => $R, 'error' => ''];
    }

    public function getDoorOpensByRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_number' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'doors' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'doors' => '', 'error' => 'you are unauthorized user'];
        }
        $room = Room::where('RoomNumber', $request->input('room_number'))->first();
        if ($room == null) {
            return ['result' => 'failed', 'doors' => '', 'error' => 'room number ' . $request->input('room_number') . ' is unavailable'];
        }
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        if (strtotime($end) < strtotime($start)) {
            return ['result' => 'failed', 'doors' => '', 'error' => 'start date is after end date '];
        }
        $opens = Doorsopen::where('Room', $room->RoomNumber)->whereBetween('Date', [$start, $end])->get();
        if ($opens != null && count($opens) > 0) {
            return ['result' => 'success', 'doors' => $opens, 'error' => ''];
        } else {
            return ['result' => 'failed', 'doors' => '', 'error' => 'no opens in this period for room ' . $room->RoomNumber];
        }
    }

    public function getDoorOpensByUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_jobnumber' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'doors' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'doors' => '', 'error' => 'you are unauthorized user'];
        }
        $user = Serviceemployee::where('jobNumber', $request->input('user_jobnumber'))->first();
        if ($user == null) {
            return ['result' => 'failed', 'doors' => '', 'error' => 'job number ' . $request->input('user_jobnumber') . ' is unavailable'];
        }
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        if (strtotime($end) < strtotime($start)) {
            return ['result' => 'failed', 'doors' => '', 'error' => 'start date is after end date '];
        }
        $opens = Doorsopen::where('JNum', $user->jobNumber)->whereBetween('Date', [$start, $end])->get();
        if ($opens != null && count($opens) > 0) {
            return ['result' => 'success', 'doors' => $opens, 'error' => ''];
        } else {
            return ['result' => 'success', 'doors' => '', 'error' => ''];
        }
    }

    public function getServiceOrdersByRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'orders' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'you are unauthorized user'];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'room id ' . $request->input('room_id') . ' is unavailable'];
        }
        $status = $request->input('status');
        if ($status > 1 || $status < 0) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'status must be 0 or 1'];
        }
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        if (strtotime($end) < strtotime($start)) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'start date is after end date '];
        }
        //$startMilli = strtotime($start)*1000;
        //$endMilli = strtotime($end)*1000+86399999;
        $orders = Serviceorder::where('roomNumber', $room->RoomNumber)->where('status', $status)->whereBetween('dateTime', [$start, $end])->get();
        if ($orders == null || count($orders) == 0) {
            return ['result' => 'success', 'orders' => '', 'error' => ''];
        }
        return ['result' => 'success', 'orders' => $orders, 'error' => ''];
    }

    public function getFacilityOrdersByRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'orders' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'you are unauthorized user'];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'room id ' . $request->input('room_id') . ' is unavailable'];
        }
        $status = $request->input('status');
        if ($status > 1 || $status < 0) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'status must be 0 or 1'];
        }
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        if (strtotime($end) < strtotime($start)) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'start date is after end date '];
        }
        $orders = Restaurantorder::where('room', $room->RoomNumber)->where('status', $status)->whereBetween('dateTime', [$start, $end])->get();
        if ($orders == null || count($orders) == 0) {
            return ['result' => 'success', 'orders' => '', 'error' => ''];
        }
        return ['result' => 'success', 'orders' => $orders, 'error' => ''];
    }

    public function getServiceOrdersBySuite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'suite_id' => 'required|numeric',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'orders' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'you are unauthorized user'];
        }
        $rooms = Room::where('SuiteId', $request->input('suite_id'))->get();
        if ($rooms == null || count($rooms) == 0) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'suite id ' . $request->input('suite_id') . ' is unavailable'];
        }
        $status = $request->input('status');
        if ($status > 1 || $status < 0) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'status must be 0 or 1'];
        }
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        if (strtotime($end) < strtotime($start)) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'start date is after end date '];
        }
        $orders = null;
        for ($i = 0; $i < count($rooms); $i++) {
            if ($orders == null) {
                $orders = Serviceorder::where('roomNumber', $rooms[$i]->RoomNumber)->where('status', $status)->whereBetween('dateTime', [$start, $end])->get();
            }
            // else {
            //     $orders = $orders->merge(Serviceorder::where('roomNumber',$rooms[$i]->RoomNumber)->where('status',$status)->whereBetween('dateTime',[$start,$end])->get());
            // }
        }
        if ($orders == null || count($orders) == 0) {
            return ['result' => 'success', 'orders' => '', 'error' => ''];
        }
        return ['result' => 'success', 'orders' => $orders, 'error' => ''];
    }

    public function getFacilityOrdersBySuite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'suite_id' => 'required|numeric',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'orders' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'you are unauthorized user'];
        }
        $rooms = Room::where('SuiteId', $request->input('suite_id'))->get();
        if ($rooms == null || count($rooms) == 0) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'suite id ' . $request->input('suite_id') . ' is unavailable'];
        }
        $status = $request->input('status');
        if ($status > 1 || $status < 0) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'status must be 0 or 1'];
        }
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        if (strtotime($end) < strtotime($start)) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'start date is after end date '];
        }
        $orders = null;
        for ($i = 0; $i < count($rooms); $i++) {
            if ($orders == null) {
                $orders = Restaurantorder::where('room', $rooms[$i]->RoomNumber)->where('status', $status)->whereBetween('dateTime', [$start, $end])->get();
            }
            // else {
            //     $orders = $orders->merge(Restaurantorder::where('room',$rooms[$i]->RoomNumber)->where('status',$status)->whereBetween('dateTime',[$start,$end])->get());
            // }
        }
        if ($orders == null || count($orders) == 0) {
            return ['result' => 'success', 'orders' => '', 'error' => ''];
        }
        return ['result' => 'success', 'orders' => $orders, 'error' => ''];
    }

    public function getServiceOrdersByReservation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reservation_id' => 'required|numeric',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'orders' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'you are unauthorized user'];
        }
        $reservation = Booking::find($request->input('reservation_id'));
        if ($reservation == null) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'reservation id ' . $request->input('reservation_id') . ' is unavailable'];
        }
        $status = $request->input('status');
        if ($status > 1 || $status < 0) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'status must be 0 or 1'];
        }
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        if (strtotime($end) < strtotime($start)) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'start date is after end date '];
        }
        $orders = Serviceorder::where('Reservation', $reservation->id)->where('status', $status)->whereBetween('dateTime', [$start, $end])->get();
        if ($orders == null || count($orders) == 0) {
            return ['result' => 'success', 'orders' => '', 'error' => ''];
        }
        return ['result' => 'success', 'orders' => $orders, 'error' => ''];
    }

    public function getFacilityOrdersByReservation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reservation_id' => 'required|numeric',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'orders' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'you are unauthorized user'];
        }
        $reservation = Booking::find($request->input('reservation_id'));
        if ($reservation == null) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'reservation id ' . $request->input('reservation_id') . ' is unavailable'];
        }
        $status = $request->input('status');
        if ($status > 1 || $status < 0) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'status must be 0 or 1'];
        }
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        if (strtotime($end) < strtotime($start)) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'start date is after end date '];
        }
        $orders = Restaurantorder::where('id', $reservation->id)->where('status', $status)->whereBetween('dateTime', [$start, $end])->get();
        if ($orders == null || count($orders) == 0) {
            return ['result' => 'success', 'orders' => '', 'error' => ''];
        }
        return ['result' => 'success', 'orders' => $orders, 'error' => ''];
    }

    public function getServiceOrdersByOrderType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_type' => 'required|in:Cleanup,Laundry,RoomService,Checkout,Restaurant',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'orders' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'you are unauthorized user'];
        }
        $status = $request->input('status');
        if ($status > 1 || $status < 0) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'status must be 0 or 1'];
        }
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        if (strtotime($end) < strtotime($start)) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'start date is after end date '];
        }
        //$startMilli = strtotime($start)*1000;
        //$endMilli = strtotime($end)*1000+86399999;
        $orderType = $request->input('order_type');
        $orders = Serviceorder::where('dep', $orderType)->where('status', $status)->whereBetween('dateTime', [$start, $end])->get();
        if ($orders == null || count($orders) == 0) {
            return ['result' => 'success', 'orders' => '', 'error' => ''];
        }
        return ['result' => 'success', 'orders' => $orders, 'error' => ''];
    }

    public function getServiceOrdersByClient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|numeric',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'orders' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'you are unauthorized user'];
        }
        $status = $request->input('status');
        if ($status > 1 || $status < 0) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'status must be 0 or 1'];
        }
        $clientId = $request->input('client_id');
        $reservations = Booking::where('ClientId', $clientId)->get();
        if ($reservations == null || count($reservations) == 0) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'no reservations for client id ' . $clientId];
        }
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        if (strtotime($end) < strtotime($start)) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'start date is after end date '];
        }
        $orders = array();
        foreach ($reservations as $reserv) {
            $ords = Serviceorder::where('Reservation', $reserv->id)->where('status', $status)->whereBetween('dateTime', [$start, $end])->get();
            if ($ords != null && count($ords) > 0) {
                foreach ($ords as $or) {
                    array_push($orders, $or);
                }
            }
        }
        if ($orders == null || count($orders) == 0) {
            return ['result' => 'success', 'orders' => '', 'error' => ''];
        }
        return ['result' => 'success', 'orders' => $orders, 'error' => ''];
    }

    public function getFacilityOrdersByClient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|numeric',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'orders' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'you are unauthorized user'];
        }
        $status = $request->input('status');
        if ($status > 1 || $status < 0) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'status must be 0 or 1'];
        }
        $clientId = $request->input('client_id');
        $reservations = Booking::where('ClientId', $clientId)->get();
        if ($reservations == null || count($reservations) == 0) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'no reservations for client id ' . $clientId];
        }
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        if (strtotime($end) < strtotime($start)) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'start date is after end date '];
        }
        $orders = array();
        foreach ($reservations as $reserv) {
            $ords = Restaurantorder::where('Reservation', $reserv->id)->where('status', $status)->whereBetween('dateTime', [$start, $end])->get();
            if ($ords != null && count($ords) > 0) {
                foreach ($ords as $or) {
                    array_push($orders, $or);
                }
            }
        }
        if ($orders == null || count($orders) == 0) {
            return ['result' => 'success', 'orders' => '', 'error' => ''];
        }
        return ['result' => 'success', 'orders' => $orders, 'error' => ''];
    }

    public function searchReservationsByDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required',
            'end_date' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'reservations' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'reservations' => '', 'error' => 'you are unauthorized user'];
        }
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        if ($end < $start) {
            return ['result' => 'failed', 'reservations' => '', 'error' => 'start date is after end date '];
        }
        $reservations = Booking::whereBetween('StartDate', [$start, $end])->get();
        if ($reservations == null || count($reservations) == 0) {
            return ['result' => 'success', 'reservations' => '', 'error' => ''];
        }
        return ['result' => 'success', 'reservations' => $reservations, 'error' => ''];
    }

    public function searchReservationsByRoomId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'reservations' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'reservations' => '', 'error' => 'you are unauthorized user'];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'reservations' => '', 'error' => 'room id is invailed'];
        }
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        if (strtotime($end) < strtotime($start)) {
            return ['result' => 'failed', 'reservations' => '', 'error' => 'start date is after end date '];
        }
        $reservations = Booking::where('RoomNumber', $room->RoomNumber)->whereBetween('StartDate', [$start, $end])->get();
        if ($reservations == null || count($reservations) == 0) {
            return ['result' => 'success', 'reservations' => '', 'error' => 'no reservations in period from ' . $start . ' to ' . $end . ' for room number ' . $room->RoomNumber];
        }
        return ['result' => 'success', 'reservations' => $reservations, 'error' => ''];
    }

    public function searchReservationsByClientId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'reservations' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'reservations' => '', 'error' => 'you are unauthorized user'];
        }
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        if (strtotime($end) < strtotime($start)) {
            return ['result' => 'failed', 'reservations' => '', 'error' => 'start date is after end date '];
        }
        $clientId = $request->input('client_id');
        $reservations = Booking::where('ClientId', $clientId)->whereBetween('StartDate', [$start, $end])->get();
        if ($reservations == null || count($reservations) == 0) {
            return ['result' => 'success', 'reservations' => '', 'error' => 'no reservations in period from ' . $start . ' to ' . $end . ' for client id ' . $clientId];
        }
        return ['result' => 'success', 'reservations' => $reservations, 'error' => ''];
    }

    public function getOpenReservations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'reservations' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'reservations' => '', 'error' => 'you are unauthorized user'];
        }
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        if (strtotime($end) < strtotime($start)) {
            return ['result' => 'failed', 'reservations' => '', 'error' => 'start date is after end date '];
        }
        $reservations = Booking::where('Status', 1)->whereBetween('StartDate', [$start, $end])->get();
        if ($reservations == null || count($reservations) == 0) {
            return ['result' => 'success', 'reservations' => '', 'error' => 'no reservations in period from ' . $start . ' to ' . $end . ' for client id '];
        }
        return ['result' => 'success', 'reservations' => $reservations, 'error' => ''];
    }

    public function getClosedReservations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'reservations' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'reservations' => '', 'error' => 'you are unauthorized user'];
        }
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        if (strtotime($end) < strtotime($start)) {
            return ['result' => 'failed', 'reservations' => '', 'error' => 'start date is after end date '];
        }
        $reservations = Booking::where('Status', 0)->whereBetween('StartDate', [$start, $end])->get();
        if ($reservations == null || count($reservations) == 0) {
            return ['result' => 'success', 'reservations' => '', 'error' => 'no reservations in period from ' . $start . ' to ' . $end . ' for client id '];
        }
        return ['result' => 'success', 'reservations' => $reservations, 'error' => ''];
    }

    public function getOpenCleanupOrders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'orders' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'you are unauthorized user'];
        }
        $serviceorders = Serviceorder::where('dep', 'Cleanup')->where('status', 0)->get();
        if ($serviceorders == null) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'unable to get orders from DB'];
        }
        return ['result' => 'success', 'orders' => $serviceorders, 'error' => ''];
    }

    public function getOpenCleanupOrdersCount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'orders' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'you are unauthorized user'];
        }
        $serviceorders = Serviceorder::where('dep', 'Cleanup')->where('status', 0)->get();
        if ($serviceorders == null) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'unable to get orders from DB'];
        }
        return ['result' => 'success', 'count' => count($serviceorders), 'error' => ''];
    }

    public function getOpenLaundryOrders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'orders' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'you are unauthorized user'];
        }
        $serviceorders = Serviceorder::where('dep', 'Laundry')->where('status', 0)->get();
        if ($serviceorders == null) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'unable to get orders from DB'];
        }
        return ['result' => 'success', 'orders' => $serviceorders, 'error' => ''];
    }

    public function getOpenLaundryOrdersCount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'orders' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'you are unauthorized user'];
        }
        $serviceorders = Serviceorder::where('dep', 'Laundry')->where('status', 0)->get();
        if ($serviceorders == null) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'unable to get orders from DB'];
        }
        return ['result' => 'success', 'count' => count($serviceorders), 'error' => ''];
    }

    public function getOpenRoomServiceOrders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'orders' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'you are unauthorized user'];
        }
        $serviceorders = Serviceorder::where('dep', 'RoomService')->where('status', 0)->get();
        if ($serviceorders == null) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'unable to get orders from DB'];
        }
        return ['result' => 'success', 'orders' => $serviceorders, 'error' => ''];
    }

    public function getOpenRoomServiceOrdersCount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'orders' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'you are unauthorized user'];
        }
        $serviceorders = Serviceorder::where('dep', 'RoomService')->where('status', 0)->get();
        if ($serviceorders == null) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'unable to get orders from DB'];
        }
        return ['result' => 'success', 'count' => count($serviceorders), 'error' => ''];
    }

    public function getOpenCheckoutOrders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'orders' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'you are unauthorized user'];
        }
        $serviceorders = Serviceorder::where('dep', 'Checkout')->where('status', 0)->get();
        if ($serviceorders == null) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'unable to get orders from DB'];
        }
        return ['result' => 'success', 'orders' => $serviceorders, 'error' => ''];
    }

    public function getOpenCheckoutOrdersCount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'orders' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'you are unauthorized user'];
        }
        $serviceorders = Serviceorder::where('dep', 'Checkout')->where('status', 0)->get();
        if ($serviceorders == null) {
            return ['result' => 'failed', 'orders' => '', 'error' => 'unable to get orders from DB'];
        }
        return ['result' => 'success', 'count' => count($serviceorders), 'error' => ''];
    }

    public function getLogo()
    {
        $project = Projectsvariable::all()->first();
        if ($project != null) {
            return ['result' => 'success', 'logo' => $project->Logo, 'error' => ''];
        }
        return ['result' => 'failed', 'logo' => '', 'error' => 'no project variables detected'];
    }

    public function searchClient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mode' => 'required',
            'word' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'clients' => '', 'error' => $validator->errors()];
        }
        if ($request->mode == 0) {
            $clients = Client::where('Mobile', 'LIKE', '%' . $request->word . '%')->get();
            if ($clients == null) {
                return ['result' => 'failed', 'clients' => '', 'error' => 'no clients found'];
            }
            return ['result' => 'success', 'clients' => $clients, 'error' => ''];
        } else if ($request->mode == 1) {
            $clients = Client::where('IdNumber', 'LIKE', '%' . $request->word . '%')->get();
            if ($clients == null) {
                return ['result' => 'failed', 'clients' => '', 'error' => 'no clients found'];
            }
            return ['result' => 'success', 'clients' => $clients, 'error' => ''];
        } else if ($request->mode == 2) {
            $clients = Client::where('FirstName', 'LIKE', '%' . $request->word . '%')->orWhere('LastName', 'LIKE', '%' . $request->word . '%')->get();
            if ($clients == null) {
                return ['result' => 'failed', 'clients' => '', 'error' => 'no clients found'];
            }
            return ['result' => 'success', 'clients' => $clients, 'error' => ''];
        }
    }  //*

    public function modifyClient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:clients,id',
            'first_name' => 'max:50|min:2',
            'last_name' => 'max:50|min:2',
            'mobile' => 'string|max:10|min:10',
            'email' => 'max:50|nullable',
            'id_type' => 'in:ID,PASSPORT',
            'id_number' => 'max:20',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'modify' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token'))) {
            return ['result' => 'failed', 'modify' => null, 'error' => 'you are unauthorized user'];
        }
        $client = Client::find($request->id);
        if ($request->first_name != null && $request->first_name != "") {
            $client->FirstName = $request->first_name;
        }
        if ($request->last_name != null && $request->last_name != "") {
            $client->LastName = $request->last_name;
        }
        if ($request->mobile != null && $request->mobile != "") {
            $client->Mobile = $request->mobile;
        }
        if ($request->email != null && $request->email != "") {
            $client->Email = $request->email;
        }
        if ($request->id_type != null && $request->id_type != "") {
            $client->IdType = $request->id_type;
        }
        if ($request->id_number != null && $request->id_number != "") {
            $client->IdNumber = $request->id_number;
        }
        try {
            $client->save();
            return ['result' => 'success', 'insertedRow' => $client, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'modify' => '', 'error' => $e->getMessage()];
        }
    }

    /*
        set functions
    */

    public function setTemperatureSetPoint(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_temp' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'temperatur' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'temperatur' => '', 'error' => 'you are unauthorized user'];
        }
        if ($this->checkIfProjectHasTermostate() == false) {
            return ['result' => 'failed', 'temperatur' => '', 'error' => 'no thermostate descovered in your project'];
        }
        $temp = $request->input('new_temp');
        if ($temp < 16) {
            return ['result' => 'failed', 'temperatur' => $temp, 'error' => 'temperature ' . $temp . ' is very low'];
        }
        if ($temp > 30) {
            return ['result' => 'failed', 'temperatur' => $temp, 'error' => 'temperature ' . $temp . ' is very high'];
        }
        $projestVariable = Projectsvariable::all()->first();
        if ($projestVariable == null) {
            return ['result' => 'failed', 'interval' => 0, 'error' => 'your project variables not registered'];
        }
        $projestVariable->Temp = $temp;
        if ($projestVariable->save()) {
            $this->setTemperature($temp);
            $this->setTemperatureSetPointInFirebase($temp);
            return ['result' => 'success', 'newTemp' => $temp, 'error' => ''];
        } else {
            return ['result' => 'failed', 'temperatur' => $temp, 'error' => 'unable to set temperature in project variables '];
        }
    }

    public function setACSenarioActive(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_status' => 'required|boolean',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'newStatus' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'newStatus' => '', 'error' => 'you are unauthorized user'];
        }
        if ($this->checkIfProjectHasTermostate() == false) {
            return ['result' => 'failed', 'newStatus' => '', 'error' => 'no thermostate descovered in your project'];
        }
        $status = $request->new_status;
        $projestVariable = Projectsvariable::all()->first();
        if ($projestVariable == null) {
            return ['result' => 'failed', 'newStatus' => 0, 'error' => 'your project variables not registered'];
        }
        $projestVariable->ACSenarioActive = $status;
        if ($projestVariable->save()) {
            $this->setACSenarioActiveInFirebase($status);
            return ['result' => 'success', 'newStatus' => $status, 'error' => ''];
        } else {
            return ['result' => 'failed', 'newStatus' => $status, 'error' => 'unable to set ac active'];
        }
    }

    public function setClientInPowerOff(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_status' => 'required|boolean',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'newStatus' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'newStatus' => '', 'error' => 'you are unauthorized user'];
        }
        $status = $request->new_status;
        $projestVariable = Projectsvariable::all()->first();
        if ($projestVariable == null) {
            return ['result' => 'failed', 'newStatus' => 0, 'error' => 'your project variables not registered'];
        }
        $projestVariable->PoweroffClientIn = $status;
        if ($projestVariable->save()) {
            $this->setClientInPowerOffInFirebase($status);
            return ['result' => 'success', 'newStatus' => $status, 'error' => ''];
        } else {
            return ['result' => 'failed', 'newStatus' => $status, 'error' => 'unable to set ac active'];
        }
    }

    public function setPoweroffAfterHK(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_status' => 'required|boolean',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'newStatus' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'newStatus' => '', 'error' => 'you are unauthorized user'];
        }
        $status = $request->new_status;
        $projestVariable = Projectsvariable::all()->first();
        if ($projestVariable == null) {
            return ['result' => 'failed', 'newStatus' => 0, 'error' => 'your project variables not registered'];
        }
        $projestVariable->PoweroffAfterHK = $status;
        if ($projestVariable->save()) {
            $this->setsetPoweroffAfterHKInFirebase($status);
            return ['result' => 'success', 'newStatus' => $status, 'error' => ''];
        } else {
            return ['result' => 'failed', 'newStatus' => $status, 'error' => 'unable to set ac active'];
        }
    }

    public function setClientBackActions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_actions' => 'required|string',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'newActions' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'newActions' => '', 'error' => 'you are unauthorized user'];
        }
        $actions = $request->new_actions;
        $projestVariable = Projectsvariable::all()->first();
        if ($projestVariable == null) {
            return ['result' => 'failed', 'newActions' => '', 'error' => 'your project variables not registered'];
        }
        $projestVariable->OnClientBack = $actions;
        if ($projestVariable->save()) {
            $this->setClientBackActionsInFirebase($actions);
            return ['result' => 'success', 'newActions' => $actions, 'error' => ''];
        } else {
            return ['result' => 'failed', 'newActions' => '', 'error' => 'unable to set ac active'];
        }
    }

    public function setHKPrepareTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_time' => 'required|string',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'newTime' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'newTime' => '', 'error' => 'you are unauthorized user'];
        }
        $time = $request->new_time;
        $projestVariable = Projectsvariable::all()->first();
        if ($projestVariable == null) {
            return ['result' => 'failed', 'newTime' => '', 'error' => 'your project variables not registered'];
        }
        $projestVariable->HKCleanupTime = $time;
        if ($projestVariable->save()) {
            $this->setHKPrepareTimeInFirebase($time);
            return ['result' => 'success', 'newTime' => $time, 'error' => ''];
        } else {
            return ['result' => 'failed', 'newTime' => '', 'error' => 'unable to set ac active'];
        }
    }

    public function setCheckinModeActive(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_status' => 'required|boolean',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'newStatus' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'newStatus' => '', 'error' => 'you are unauthorized user'];
        }
        $status = $request->new_status;
        $projestVariable = Projectsvariable::all()->first();
        if ($projestVariable == null) {
            return ['result' => 'failed', 'newStatus' => 0, 'error' => 'your project variables not registered'];
        }
        $projestVariable->CheckinModeActive = $status;
        if ($projestVariable->save()) {
            $this->setCheckinModeActiveInFirebase($status);
            return ['result' => 'success', 'newStatus' => $status, 'error' => ''];
        } else {
            return ['result' => 'failed', 'newStatus' => $status, 'error' => 'unable to set ac active'];
        }
    }

    public function setCheckinModeActions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_actions' => 'required|string',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'newActions' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'newActions' => '', 'error' => 'you are unauthorized user'];
        }
        $actions = $request->new_actions;
        $projestVariable = Projectsvariable::all()->first();
        if ($projestVariable == null) {
            return ['result' => 'failed', 'newActions' => 0, 'error' => 'your project variables not registered'];
        }
        $projestVariable->CheckinActions = $actions;
        if ($projestVariable->save()) {
            $this->setCheckinModeActionInFirebase($actions);
            return ['result' => 'success', 'newActions' => $actions, 'error' => ''];
        } else {
            return ['result' => 'failed', 'newActions' => $actions, 'error' => 'unable to set ac active'];
        }
    }

    public function setCheckoutModeActive(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_status' => 'required|boolean',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'newStatus' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'newStatus' => '', 'error' => 'you are unauthorized user'];
        }
        $status = $request->new_status;
        $projestVariable = Projectsvariable::all()->first();
        if ($projestVariable == null) {
            return ['result' => 'failed', 'newStatus' => 0, 'error' => 'your project variables not registered'];
        }
        $projestVariable->CheckoutModeActive = $status;
        if ($projestVariable->save()) {
            $this->setCheckoutModeActiveInFirebase($status);
            return ['result' => 'success', 'newStatus' => $status, 'error' => ''];
        } else {
            return ['result' => 'failed', 'newStatus' => $status, 'error' => 'unable to set ac active'];
        }
    }

    public function setCheckoutModeActions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_actions' => 'required|string',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'newActions' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'newActions' => '', 'error' => 'you are unauthorized user'];
        }
        $actions = $request->new_actions;
        $projestVariable = Projectsvariable::all()->first();
        if ($projestVariable == null) {
            return ['result' => 'failed', 'newActions' => 0, 'error' => 'your project variables not registered'];
        }
        $projestVariable->CheckoutActions = $actions;
        if ($projestVariable->save()) {
            $this->setCheckoutModeActionInFirebase($actions);
            return ['result' => 'success', 'newActions' => $actions, 'error' => ''];
        } else {
            return ['result' => 'failed', 'newActions' => $actions, 'error' => 'unable to set ac active'];
        }
    }

    public function setSetpointInterval(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'setpoint_interval' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'interval' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'interval' => '', 'error' => 'you are unauthorized user'];
        }
        if ($this->checkIfProjectHasTermostate() == false) {
            return ['result' => 'failed', 'interval' => '', 'error' => 'no thermostate descovered in your project'];
        }
        $projestVariable = Projectsvariable::all()->first();
        if ($projestVariable == null) {
            return ['result' => 'failed', 'interval' => '', 'error' => 'your project variables not registered'];
        }
        $interval = $request->input('setpoint_interval');
        if ($interval == 0) {
            return ['result' => 'failed', 'interval' => '', 'error' => 'setpoint interval could not be 0'];
        }
        //   if ($interval < 5) {
        //     return ['result'=>'failed','interval'=>'','error'=>'setpoint interval could not be less than 5'];
        //   }
        $projestVariable->Interval = $interval;
        if ($projestVariable->save()) {
            $this->setTempIntervalInDBs($interval);
            $this->setSetPointIntervalInFirebase($interval);
            return ['result' => 'success', 'interval' => $interval, 'error' => ''];
        } else {
            return ['result' => 'failed', 'interval' => '', 'error' => 'could not modify interval in project variables'];
        }
    }

    public function setDoorsWarningInterval(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'door_interval' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'interval' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'interval' => 0, 'error' => 'you are unauthorized user'];
        }
        if ($this->checkIfProjectHasDoorSensor() == false) {
            return ['result' => 'failed', 'interval' => 0, 'error' => 'no doorsensor descovered in your project'];
        }
        $projestVariable = Projectsvariable::all()->first();
        if ($projestVariable == null) {
            return ['result' => 'failed', 'interval' => 0, 'error' => 'your project variables not registered'];
        }
        $interval = $request->input('door_interval');
        if ($interval == 0) {
            return ['result' => 'failed', 'interval' => 0, 'error' => 'door interval could not be 0'];
        }
        //   if ($interval < 5) {
        //     return ['result'=>'failed','interval'=>0,'error'=>'door interval could not be less than 5'];
        //   }
        $projestVariable->DoorWarning = $interval;
        if ($projestVariable->save()) {
            $this->setDoorInterval($interval);
            $this->setDoorsWarningIntervalInFirebase($interval);
            return ['result' => 'success', 'interval' => $interval, 'error' => ''];
        } else {
            return ['result' => 'failed', 'interval' => '', 'error' => 'could not modify interval in project variables'];
        }
    }

    public function setWelcomeMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'message' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'message' => 0, 'error' => 'you are unauthorized user'];
        }
        if ($this->checkIfProjectRoomsHasControlScreens() == false) {
            return ['result' => 'failed', 'message' => 0, 'error' => 'no room control screen descovered in your project'];
        }
        $projestVariable = Projectsvariable::all()->first();
        if ($projestVariable == null) {
            return ['result' => 'failed', 'message' => 0, 'error' => 'your project variables not registered'];
        }
        $message = $request->input('message');
        $projestVariable->WelcomeMessage = $message;
        if ($projestVariable->save()) {
            $this->setWelcomeMessageInDBs($message);
            $this->setWelcomeMessageInFirebase($message);
            return ['result' => 'success', 'message' => $message, 'error' => ''];
        } else {
            return ['result' => 'failed', 'message' => '', 'error' => 'could not modify message in project variables'];
        }
    }

    public function setCheckinModeDuration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'duration' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'duration' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'duration' => '', 'error' => 'you are unauthorized user'];
        }
        $projestVariable = Projectsvariable::all()->first();
        if ($projestVariable == null) {
            return ['result' => 'failed', 'duration' => '', 'error' => 'your project variables not registered'];
        }
        $duration = $request->input('duration');
        //   if ($duration < 5) {
        //     return ['result'=>'failed','duration'=>'','error'=>'duration should not be less than 5'];
        //   }
        $projestVariable->CheckInModeTime = $duration;
        if ($projestVariable->save()) {
            $this->setCheckinModeDurationInDBs($duration);
            $this->setCheckinModeDurationInFirebase($duration);
            return ['result' => 'success', 'duration' => $duration, 'error' => ''];
        } else {
            return ['result' => 'failed', 'duration' => '', 'error' => 'could not modify duration in project variables'];
        }
    }

    public function setCheckoutModeDuration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'duration' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'duration' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'duration' => '', 'error' => 'you are unauthorized user'];
        }
        $projestVariable = Projectsvariable::all()->first();
        if ($projestVariable == null) {
            return ['result' => 'failed', 'duration' => '', 'error' => 'your project variables not registered'];
        }
        $duration = $request->input('duration');
        //   if ($duration < 5) {
        //     return ['result'=>'failed','duration'=>'','error'=>'duration should not be less than 5'];
        //   }
        $projestVariable->CheckOutModeTime = $duration;
        if ($projestVariable->save()) {
            $this->setCheckoutModeDurationInDBs($duration);
            $this->setCheckoutModeDurationInFirebase($duration);
            return ['result' => 'success', 'duration' => $duration, 'error' => ''];
        } else {
            return ['result' => 'failed', 'duration' => '', 'error' => 'could not modify duration in project variables'];
        }
    }

    public function setRoomOutOfService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => null, 'error' => $validator->errors()];

        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'room' => '', 'error' => 'you are unauthorized user'];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'room' => '', 'error' => 'room id' . $request->input('room_id') . ' is unavailable'];
        }
        if ($room->roomStatus == 1 || $room->roomStatus == 3) {
            $oldClean = $room->Cleanup;
            $oldLau = $room->Laundry;
            $oldDnd = $room->DND;
            $oldChe = $room->Checkout;
            $oldRes = $room->Restaurant;
            $oldSos = $room->SOS;
            $oldReeoms = $room->RoomService;
            if ($this->putRoomOutOfServiceInDB($room)) {
                if ($this->setRoomOutOfServiceInFirebase($room)) {
                    $result = ['result' => 'success', 'room ' . $room->RoomNumber => 'out of service', 'error' => ''];
                    return $result;
                } else {
                    $room->roomStatus = 1;
                    $room->Cleanup = $oldClean;
                    $room->Laundry = $oldLau;
                    $room->DND = $oldDnd;
                    $room->Checkout = $oldChe;
                    $room->Restaurant = $oldRes;
                    $room->SOS = $oldSos;
                    $room->RoomService = $oldReeoms;
                    $room->save();
                    $result = ['result' => 'failed', 'room ' . $room->RoomNumber => 'ready', 'error' => 'unable to set room out of service in Firebase'];
                    return $result;
                }
            } else {
                return ['result' => 'failed', 'room ' . $room->RoomNumber => 'ready', 'error' => 'unable to set room out of service in DB'];
            }
        } else if ($room->roomStatus == 4) {
            if ($this->backRoomFromOutOfServiceInDB($room)) {
                if ($this->backRoomFromOutOfServiceInFirebase($room)) {
                    $result = ['result' => 'success', 'room ' . $room->RoomNumber => 'ready', 'error' => ''];
                    return $result;
                } else {
                    $room->roomStatus = 4;
                    $room->save();
                    return ['result' => 'failed', 'room ' . $room->RoomNumber => 'out of service', 'error' => 'unable to back room from out of service in Firebase'];
                }
            } else {
                return ['result' => 'failed', 'room ' . $room->RoomNumber => 'out of service', 'error' => 'unable to back room from out of service in DB'];
            }
        } else {
            return ['result' => 'failed', 'room' => 'reserved', 'error' => 'room is reserved now'];
        }
    }

    public function setRoomOnlineOrOffline(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'status' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => null, 'error' => $validator->errors()];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'room' => '', 'error' => 'room id' . $request->input('room_id') . ' is unavailable'];
        }
        try {
            $room->online = $request->status;
            $room->save();
            $this->setRoomOnlineOrOfflineInFirebase($room, $request->status);
            return ['result' => 'success', 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'error' => $e->getMessage()];
        }
    }

    public function setLogo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'logo' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'logo' => '', 'error' => 'you are unauthorized user'];
        }
        $projestVariable = Projectsvariable::all()->first();
        if ($projestVariable == null) {
            return ['result' => 'failed', 'logo' => '', 'error' => 'your project variables not registered'];
        }
        $image_path = $request->file('image')->store('images', 'public');
        $projestVariable->Logo = $this->URL . $image_path;
        if ($projestVariable->save()) {
            $this->setLogoInDBs($projestVariable->Logo);
            return ['result' => 'success', 'logo' => $image_path, 'error' => ''];
        } else {
            return ['result' => 'failed', 'logo' => '', 'error' => 'could not modify logo in project variables'];
        }
    }

    public function setDoorOpenOrClosed(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'door_status' => 'required|numeric',
            'room_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'interval' => '', 'error' => $validator->errors()];
        }
        if ($this->checkIfProjectHasDoorSensor() == false) {
            return ['result' => 'failed', 'status' => '', 'error' => 'no doorsensor descovered in your project'];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'status' => '', 'error' => 'room id is unavailable'];
        }
        try {
            $room->doorStatus = $request->door_status;
            $room->save();
            $this->setDoorOpenOrClosedInFirebase($room, $request->door_status);
            return ['result' => 'success', 'status' => $request->door_status, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'status' => '', 'error' => $e->getMessage()];
        }
    }

    public function setPowerOnOrOff(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'power_status' => 'required|numeric',
            'room_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'interval' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'status' => '', 'error' => 'room id is unavailable'];
        }
        try {
            $room->powerStatus = $request->power_status;
            $room->save();
            $this->setPowerOnOrOffInFirebase($room, $request->power_status);
            return ['result' => 'success', 'status' => $request->power_status, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'status' => '', 'error' => $e->getMessage()];
        }
    }

    public function setClientInOrOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|numeric',
            'room_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'interval' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'status' => '', 'error' => 'room id is unavailable'];
        }
        try {
            $room->ClientIn = $request->status;
            $room->save();
            $this->setClientInOrOutInFirebase($room, $request->status);
            return ['result' => 'success', 'status' => $request->status, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'status' => '', 'error' => $e->getMessage()];
        }
    }

    public function setGuestAppActive(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_status' => 'required|boolean',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'newStatus' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'newStatus' => '', 'error' => 'you are unauthorized user'];
        }
        $status = $request->new_status;
        $projestVariable = Projectsvariable::all()->first();
        if ($projestVariable == null) {
            return ['result' => 'failed', 'newStatus' => 0, 'error' => 'your project variables not registered'];
        }
        $projestVariable->GuestApp = $status;
        if ($projestVariable->save()) {
            $this->setGuestAppActiveInFireBase($status);
            return ['result' => 'success', 'newStatus' => $status, 'error' => ''];
        } else {
            return ['result' => 'failed', 'newStatus' => $status, 'error' => 'unable to set guestapp active'];
        }
    }

    public function setCheckoutTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_time' => 'required|string',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'newStatus' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'newStatus' => '', 'error' => 'you are unauthorized user'];
        }
        $projestVariable = Projectsvariable::all()->first();
        if ($projestVariable == null) {
            return ['result' => 'failed', 'newStatus' => 0, 'error' => 'your project variables not registered'];
        }
        $projestVariable->CheckoutTime = $request->new_time;
        if ($projestVariable->save()) {
            $this->setCheckoutTimeInFireBase($request->new_time);
            return ['result' => 'success', 'variables' => $projestVariable, 'error' => ''];
        } else {
            return ['result' => 'failed', 'variables' => '', 'error' => 'unable to set guestapp active'];
        }
    }

    /*
        orders functions
    */

    public function prepareRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'job_number' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'room' => null, 'error' => $validator->errors()];
            return $result;
        }
        if (Users::checkAuth($request->input('my_token'))) {
            $room = Room::find($request->input('room_id'));
            if ($room == null) {
                $result = ['result' => 'failed', 'room' => null, 'error' => 'no such room id '];
                return $result;
            }
            if ($room->roomStatus != 3) {
                $s = '';
                if ($room->roomStatus == 1) {
                    $s = 'ready';
                } else if ($room->roomStatus == 2) {
                    $s = 'reserved';
                } else if ($room->roomStatus == 4) {
                    $s = 'out of service';
                }
                return ['result' => 'failed', 'room status' => $s, 'error' => 'room is not in unready mode '];
            }
            $user = Serviceemployee::where('jobNumber', $request->job_number)->get();
            if ($user == null) {
                return ['result' => 'failed', 'room' => '', 'error' => 'jobnumber is unavailable'];
            }
            $res = $this->prepareRoomInDB($room);
            if ($res['res'] == 'success') {
                if ($this->prepareRoomInFirebase($room)) {
                    $orders = Serviceorder::where('roomNumber', $room->RoomNumber)->get();
                    if ($orders != null && count($orders) > 0) {
                        $time = intval(microtime(true) * 1000);
                        for ($i = 0; $i < count($orders); $i++) {
                            $orders[$i]->responseDateTime = $time;
                            $orders[$i]->responseEmployee = $request->job_number;
                            $orders[$i]->status = 1;
                            $orders[$i]->save();
                        }
                    }
                    return ['result' => 'success', 'room' => $room, 'error' => null];
                } else {
                    $time = intval(microtime(true) * 1000);
                    $room->roomStatus = 3;
                    $room->dep = 'Cleanup';
                    $room->Cleanup = $time;
                    $room->save();
                    return ['result' => 'failed', 'room' => $room, 'error' => 'unable to prepare room in firebase'];
                }
            } else {
                $error = $res['error'];
                return ['result' => 'failed', 'room' => $room, 'error' => $error];
            }
        } else {
            return ['result' => 'failed', 'room' => null, 'error' => 'you are unauthorized user'];
        }
    }

    public function poweronRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => '', 'error' => $validator->errors()];
        }
        if (!Users::checkAuth($request->input('my_token'))) {
            return ['result' => 'failed', 'reservation' => '', 'error' => 'you are unauthorized user'];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'room' => '', 'error' => 'no sutch room number '];
        }
        $devices = Serverdevice::all();
        if ($devices == null || count($devices) == 0) {
            return ['result' => 'failed', 'room' => '', 'error' => 'no control device'];
        }
        for ($i = 0; $i < count($devices); $i++) {
            if ($devices[$i]->roomsIds == 'all') {
                $resp = $this->sendOrderToRoom($devices[$i]->token, 'poweron', $room->RoomNumber);
                if ($resp['success'] == 1) {
                    return ['result' => 'success', 'error' => ''];
                } else {
                    $rere = $resp['results'];
                    $error = $rere[0]['error'];
                    return ['result' => 'failed', 'error' => 'sending order failed "' . $error . '"'];
                }
            } else {
                $roomsArr = explode("-", $devices[$i]->roomsIds);
                $found = false;
                for ($j = 0; $j < count($roomsArr); $j++) {
                    if ($roomsArr[$j] == $room->id) {
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    $resp = $this->sendOrderToRoom($devices[$i]->token, 'poweron', $room->RoomNumber);
                    if ($resp['success'] == 1) {
                        return ['result' => 'success', 'error' => ''];
                    } else {
                        $rere = $resp['results'];
                        $error = $rere[0]['error'];
                        return ['result' => 'failed', 'error' => 'sending order failed "' . $error . '"'];
                    }
                } else {
                    return ['result' => 'failed', 'error' => 'room has no control device'];
                }
            }
        }
    }

    public function powerByCardRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'reservation' => '', 'error' => 'you are unauthorized user'];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'room' => '', 'error' => 'no sutch room number '];
        }
        $devices = Serverdevice::all();
        if ($devices == null || count($devices) == 0) {
            return ['result' => 'failed', 'room' => '', 'error' => 'no control device'];
        }
        for ($i = 0; $i < count($devices); $i++) {
            if ($devices[$i]->roomsIds == 'all') {
                $resp = $this->sendOrderToRoom($devices[$i]->token, 'bycard', $room->RoomNumber);
                if ($resp['success'] == 1) {
                    return ['result' => 'success', 'error' => ''];
                } else {
                    $rere = $resp['results'];
                    $error = $rere[0]['error'];
                    return ['result' => 'failed', 'error' => 'sending order failed "' . $error . '"'];
                }
            } else {
                $roomsArr = explode("-", $devices[$i]->roomsIds);
                $found = false;
                for ($j = 0; $j < count($roomsArr); $j++) {
                    if ($roomsArr[$j] == $room->id) {
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    $resp = $this->sendOrderToRoom($devices[$i]->token, 'bycard', $room->RoomNumber);
                    if ($resp['success'] == 1) {
                        return ['result' => 'success', 'error' => ''];
                    } else {
                        $rere = $resp['results'];
                        $error = $rere[0]['error'];
                        return ['result' => 'failed', 'error' => 'sending order failed "' . $error . '"'];
                    }
                } else {
                    return ['result' => 'failed', 'error' => 'room has no control device'];
                }
            }
        }
    }

    public function poweroffRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => null, 'error' => $validator->errors()];
        }
        if (!Users::checkAuth($request->input('my_token'))) {
            return ['result' => 'failed', 'reservation' => null, 'error' => 'you are unauthorized user'];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'room' => '', 'error' => 'no sutch room number '];
        }
        $devices = Serverdevice::all();
        if ($devices == null || count($devices) == 0) {
            return ['result' => 'failed', 'room' => '', 'error' => 'no control device'];
        }
        for ($i = 0; $i < count($devices); $i++) {
            if ($devices[$i]->roomsIds == 'all') {
                $resp = $this->sendOrderToRoom($devices[$i]->token, 'poweroff', $room->RoomNumber);
                if ($resp['success'] == 1) {
                    return ['result' => 'success', 'error' => ''];
                } else {
                    $rere = $resp['results'];
                    $error = $rere[0]['error'];
                    return ['result' => 'failed', 'error' => 'sending order failed "' . $error . '"'];
                }
            } else {
                $roomsArr = explode("-", $devices[$i]->roomsIds);
                $found = false;
                for ($j = 0; $j < count($roomsArr); $j++) {
                    if ($roomsArr[$j] == $room->id) {
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    $resp = $this->sendOrderToRoom($devices[$i]->token, 'poweroff', $room->RoomNumber);
                    if ($resp['success'] == 1) {
                        return ['result' => 'success', 'error' => ''];
                    } else {
                        $rere = $resp['results'];
                        $error = $rere[0]['error'];
                        return ['result' => 'failed', 'error' => 'sending order failed "' . $error . '"'];
                    }
                } else {
                    return ['result' => 'failed', 'error' => 'room has no control device'];
                }
            }
        }
    }

    public function sendMessageToRooms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rooms_ids' => 'required',
            'message' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => null, 'error' => $validator->errors()];
        }
        $roomsIds = $request->input('rooms_ids');
        $ids = explode('-', $roomsIds);
        if (count($ids) == 0) {
            return ['result' => 'failed', 'messages' => 0, 'error' => 'rooms ids is in bad form please make sure to send it splited by - ex: 15-20-23'];
        }
        if (Users::checkAuth($request->input('my_token'))) {
            $message = $request->input('message');
            $results = array();
            for ($i = 0; $i < count($ids); $i++) {
                $room = Room::find($ids[$i]);
                if ($room != null) {
                    if ($room->roomStatus != 2) {
                        $results += [$room->RoomNumber => 'room is not reserved'];
                    } else {
                        $b = Booking::find($room->ReservationNumber);
                        if ($b != null) {
                            $message = str_replace(" *G ", " " . $b->ClientFirstName . " " . $b->ClientLastName . " ", $message);
                        }
                        $r = $this->sendRoomMessage($room, 'message', $message);
                        $res = $r['success'];
                        if ($res == 1) {
                            $results += [$room->RoomNumber => 'success'];
                        } else {
                            $error = $r['results'][0]['error'];
                            $results += [$room->RoomNumber => 'failed " ' . $error . ' "'];
                        }
                    }
                } else {
                    $results += ['room id ' . $ids[$i] => 'this room id is invailed '];
                }
            }
        } else {
            $result = ['result' => 'failed', 'reservation' => null, 'error' => 'you are unauthorized user'];
            return $result;
        }
        return $results;
    }

    public function putRoomOnDNDMode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'room' => '', 'error' => 'you are unauthorized user'];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id is un exsists'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room is not reserved'];
        }
        if ($room->DND > 0) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room already on DND mode'];
        }
        $time = intval(microtime(true) * 1000);
        $room->DND = $time;
        $room->dep = 'DND';
        if ($room->save()) {
            $this->copyRoomFromDBToFirebase($room);
            return ['result' => 'success', 'order' => 'DND', 'error' => ''];
        } else {
            return ['result' => 'failed', 'order' => '', 'error' => 'could not put room on dnd in DB'];
        }
    }

    public function putRoomOnDNDModeControlDevice1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id is unavailable'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room is not reserved'];
        }
        if ($room->DND > 0) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room already on DND mode'];
        }
        $time = intval(microtime(true) * 1000);
        $room->DND = $time;
        $room->dep = 'DND';
        if ($room->save()) {
            return ['result' => 'success', 'order' => 'DND', 'error' => ''];
        } else {
            return ['result' => 'failed', 'order' => '', 'error' => 'could not put room on dnd in DB'];
        }
    }
    public function putRoomOnDNDModeControlDevice2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id is unavailable'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room is not reserved'];
        }
        if ($room->DND > 0) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room already on DND mode'];
        }
        $time = intval(microtime(true) * 1000);
        $room->DND = $time;
        $room->dep = 'DND';
        if ($room->save()) {
            return ['result' => 'success', 'order' => 'DND', 'error' => ''];
        } else {
            return ['result' => 'failed', 'order' => '', 'error' => 'could not put room on dnd in DB'];
        }
    }
    public function putRoomOnDNDModeControlDevice3(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id is unavailable'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room is not reserved'];
        }
        if ($room->DND > 0) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room already on DND mode'];
        }
        $time = intval(microtime(true) * 1000);
        $room->DND = $time;
        $room->dep = 'DND';
        if ($room->save()) {
            return ['result' => 'success', 'order' => 'DND', 'error' => ''];
        } else {
            return ['result' => 'failed', 'order' => '', 'error' => 'could not put room on dnd in DB'];
        }
    }
    public function putRoomOnDNDModeControlDevice4(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id is unavailable'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room is not reserved'];
        }
        if ($room->DND > 0) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room already on DND mode'];
        }
        $time = intval(microtime(true) * 1000);
        $room->DND = $time;
        $room->dep = 'DND';
        if ($room->save()) {
            return ['result' => 'success', 'order' => 'DND', 'error' => ''];
        } else {
            return ['result' => 'failed', 'order' => '', 'error' => 'could not put room on dnd in DB'];
        }
    }
    public function putRoomOnDNDModeControlDevice5(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'room' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id is unavailable'];
        }
        if ($room->roomStatus != 2) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room is not reserved'];
        }
        if ($room->DND > 0) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room already on DND mode'];
        }
        $time = intval(microtime(true) * 1000);
        $room->DND = $time;
        $room->dep = 'DND';
        if ($room->save()) {
            return ['result' => 'success', 'order' => 'DND', 'error' => ''];
        } else {
            return ['result' => 'failed', 'order' => '', 'error' => 'could not put room on dnd in DB'];
        }
    }

    public function finishServiceOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
            'jobnumber' => 'required',
            'order_type' => 'required|in:Cleanup,Laundry,RoomService,Checkout,Restaurant,SOS,MinibarCheck',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'order' => null, 'error' => $validator->errors()];
            return $result;
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            $result = ['result' => 'failed', 'reservation' => null, 'error' => 'you are unauthorized user'];
            return $result;
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            $result = ['result' => 'failed', 'order' => null, 'error' => 'room id is unexsists'];
            return $result;
        }
        $orderType = $request->input('order_type');
        $user = Serviceemployee::where('jobNumber', $request->input('jobnumber'))->first();
        if ($user == null) {
            return ['result' => 'failed', 'order' => $orderType, 'error' => 'jobnumber ' . $request->input('jobnumber') . ' is unavailable'];
        }
        $serviceOrder = Serviceorder::where('roomNumber', $room->RoomNumber)->where('dep', $orderType)->where('status', 0)->first();
        if ($serviceOrder == null) {
            // return ['result'=>'failed','order'=>$orderType,'error'=>'no '.$orderType.' orders on room number '.$room->RoomNumber];
            $arrRoom = [
                'dep' => $this->setDepRoom($room),
                $orderType => '0'
            ];
            $response = Http::patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            return ['result' => 'success', $orderType => 'finished', 'error' => ''];
        }
        $time = intval(microtime(true) * 1000);
        $serviceOrder->status = 1;
        $serviceOrder->responseDateTime = $time;
        $serviceOrder->responseEmployee = $user->jobNumber;
        if ($serviceOrder->save()) {
            $oldOrderV = $room->$orderType;
            $room->$orderType = 0;
            $room->dep = $this->setDepRoom($room);
            if ($room->save()) {
                $arrRoom = [
                    'dep' => $this->setDepRoom($room),
                    $orderType => '0'
                ];
                if ($orderType == "RoomService") {
                    $arrRoom = [
                        'dep' => $this->setDepRoom($room),
                        $orderType => '0',
                        'RoomServiceText' => ''
                    ];
                }
                $response = Http::patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
                return ['result' => 'success', $orderType => 'finished', 'error' => ''];
            } else {
                $serviceOrder->status = 0;
                $serviceOrder->responseDateTime = 0;
                $serviceOrder->responseEmployee = 0;
                $serviceOrder->save();
                return ['result' => 'failed', 'order' => $orderType, 'error' => 'unable to finish order in DB'];
            }
        } else {
            return ['result' => 'failed', 'order' => $orderType, 'error' => 'unable to finish order in DB'];
        }

    }

    public function cancelServiceOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'order_type' => 'required|in:Cleanup,Laundry,RoomService,Checkout,Restaurant,MinibarCheck',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'order' => '', 'error' => 'you are unauthorized user'];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id ' . $request->input('room_id') . ' is invailed'];
        }
        $orderType = $request->input('order_type');
        $orders = Serviceorder::where('dep', $orderType)->where('roomNumber', $room->RoomNumber)->where('status', 0)->get();
        if ($orders == null || count($orders) == 0) {
            return ['result' => 'failed', 'order' => '', 'error' => 'no ' . $orderType . ' orders for ' . $room->RoomNumber];
        }
        $oldvalue = $room->$orderType;
        $room->$orderType = 0;
        $room->dep = $this->setDepRoom($room);
        if ($room->save()) {
            foreach ($orders as $order) {
                $order->delete();
            }
            $this->copyRoomFromDBToFirebase($room);
            if ($orderType != 'Checkout') {
                $this->sendCancelNotificationToServiceUsers($orderType, $room);
            }
            return ['result' => 'success', 'order' . $orderType => 'canceled', 'error' => ''];
        } else {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to finish order in DB'];
        }
    }

    public function cancelServiceOrderControlDevice1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'order_type' => 'required|in:Cleanup,Laundry,RoomService,Checkout,Restaurant,MinibarCheck,SOS',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id ' . $request->input('room_id') . ' is invailed'];
        }
        $orderType = $request->input('order_type');
        $orders = Serviceorder::where('dep', $orderType)->where('roomNumber', $room->RoomNumber)->where('status', 0)->get();
        if ($orders == null || count($orders) == 0) {
            //return ['result'=>'failed','order'=>'','error'=>'no '.$orderType.' orders for '.$room->RoomNumber];
            return ['result' => 'success', 'order' . $orderType => 'canceled', 'error' => ''];
        }
        $oldvalue = $room->$orderType;
        $room->$orderType = 0;
        $room->dep = $this->setDepRoom($room);
        if ($room->save()) {
            foreach ($orders as $order) {
                $order->delete();
            }
            if ($orderType != 'Checkout') {
                $this->sendCancelNotificationToServiceUsers($orderType, $room);
            }
            return ['result' => 'success', 'order' . $orderType => 'canceled', 'error' => ''];
        } else {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to finish order in DB'];
        }
    }
    public function cancelServiceOrderControlDevice2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'order_type' => 'required|in:Cleanup,Laundry,RoomService,Checkout,Restaurant,MinibarCheck,SOS',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id ' . $request->input('room_id') . ' is invailed'];
        }
        $orderType = $request->input('order_type');
        $orders = Serviceorder::where('dep', $orderType)->where('roomNumber', $room->RoomNumber)->where('status', 0)->get();
        if ($orders == null || count($orders) == 0) {
            //return ['result'=>'failed','order'=>'','error'=>'no '.$orderType.' orders for '.$room->RoomNumber];
            return ['result' => 'success', 'order' . $orderType => 'canceled', 'error' => ''];
        }
        $oldvalue = $room->$orderType;
        $room->$orderType = 0;
        $room->dep = $this->setDepRoom($room);
        if ($room->save()) {
            foreach ($orders as $order) {
                $order->delete();
            }
            if ($orderType != 'Checkout') {
                $this->sendCancelNotificationToServiceUsers($orderType, $room);
            }
            return ['result' => 'success', 'order' . $orderType => 'canceled', 'error' => ''];
        } else {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to finish order in DB'];
        }
    }
    public function cancelServiceOrderControlDevice3(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'order_type' => 'required|in:Cleanup,Laundry,RoomService,Checkout,Restaurant,MinibarCheck,SOS',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id ' . $request->input('room_id') . ' is invailed'];
        }
        $orderType = $request->input('order_type');
        $orders = Serviceorder::where('dep', $orderType)->where('roomNumber', $room->RoomNumber)->where('status', 0)->get();
        if ($orders == null || count($orders) == 0) {
            //return ['result'=>'failed','order'=>'','error'=>'no '.$orderType.' orders for '.$room->RoomNumber];
            return ['result' => 'success', 'order' . $orderType => 'canceled', 'error' => ''];
        }
        $oldvalue = $room->$orderType;
        $room->$orderType = 0;
        $room->dep = $this->setDepRoom($room);
        if ($room->save()) {
            foreach ($orders as $order) {
                $order->delete();
            }
            if ($orderType != 'Checkout') {
                $this->sendCancelNotificationToServiceUsers($orderType, $room);
            }
            return ['result' => 'success', 'order' . $orderType => 'canceled', 'error' => ''];
        } else {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to finish order in DB'];
        }
    }
    public function cancelServiceOrderControlDevice4(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'order_type' => 'required|in:Cleanup,Laundry,RoomService,Checkout,Restaurant,MinibarCheck,SOS',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id ' . $request->input('room_id') . ' is invailed'];
        }
        $orderType = $request->input('order_type');
        $orders = Serviceorder::where('dep', $orderType)->where('roomNumber', $room->RoomNumber)->where('status', 0)->get();
        if ($orders == null || count($orders) == 0) {
            // return ['result'=>'failed','order'=>'','error'=>'no '.$orderType.' orders for '.$room->RoomNumber];
            return ['result' => 'success', 'order' . $orderType => 'canceled', 'error' => ''];
        }
        $oldvalue = $room->$orderType;
        $room->$orderType = 0;
        $room->dep = $this->setDepRoom($room);
        if ($room->save()) {
            foreach ($orders as $order) {
                $order->delete();
            }
            if ($orderType != 'Checkout') {
                $this->sendCancelNotificationToServiceUsers($orderType, $room);
            }
            return ['result' => 'success', 'order' . $orderType => 'canceled', 'error' => ''];
        } else {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to finish order in DB'];
        }
    }
    public function cancelServiceOrderControlDevice5(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'order_type' => 'required|in:Cleanup,Laundry,RoomService,Checkout,Restaurant,MinibarCheck,SOS',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id ' . $request->input('room_id') . ' is invailed'];
        }
        $orderType = $request->input('order_type');
        $orders = Serviceorder::where('dep', $orderType)->where('roomNumber', $room->RoomNumber)->where('status', 0)->get();
        if ($orders == null || count($orders) == 0) {
            //return ['result'=>'failed','order'=>'','error'=>'no '.$orderType.' orders for '.$room->RoomNumber];
            return ['result' => 'success', 'order' . $orderType => 'canceled', 'error' => ''];
        }
        $oldvalue = $room->$orderType;
        $room->$orderType = 0;
        $room->dep = $this->setDepRoom($room);
        if ($room->save()) {
            foreach ($orders as $order) {
                $order->delete();
            }
            if ($orderType != 'Checkout') {
                $this->sendCancelNotificationToServiceUsers($orderType, $room);
            }
            return ['result' => 'success', 'order' . $orderType => 'canceled', 'error' => ''];
        } else {
            return ['result' => 'failed', 'order' => '', 'error' => 'unable to finish order in DB'];
        }
    }

    public function cancelDNDOrderControlDevice1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id ' . $request->input('room_id') . ' is invailed'];
        }
        $rrr = $this->cancelDNDMode($room);
        if ($rrr) {
            return ['result' => 'success', 'order' => '', 'error' => ''];
        } else {
            return ['result' => 'failed', 'order' => '', 'error' => 'dnd cancel failed'];
        }
    }
    public function cancelDNDOrderControlDevice2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id ' . $request->input('room_id') . ' is invailed'];
        }
        $rrr = $this->cancelDNDMode($room);
        if ($rrr) {
            return ['result' => 'success', 'order' => '', 'error' => ''];
        } else {
            return ['result' => 'failed', 'order' => '', 'error' => 'dnd cancel failed'];
        }
    }
    public function cancelDNDOrderControlDevice3(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id ' . $request->input('room_id') . ' is invailed'];
        }
        $rrr = $this->cancelDNDMode($room);
        if ($rrr) {
            return ['result' => 'success', 'order' => '', 'error' => ''];
        } else {
            return ['result' => 'failed', 'order' => '', 'error' => 'dnd cancel failed'];
        }
    }
    public function cancelDNDOrderControlDevice4(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id ' . $request->input('room_id') . ' is invailed'];
        }
        $rrr = $this->cancelDNDMode($room);
        if ($rrr) {
            return ['result' => 'success', 'order' => '', 'error' => ''];
        } else {
            return ['result' => 'failed', 'order' => '', 'error' => 'dnd cancel failed'];
        }
    }
    public function cancelDNDOrderControlDevice5(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'order' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'order' => '', 'error' => 'room id ' . $request->input('room_id') . ' is invailed'];
        }
        $rrr = $this->cancelDNDMode($room);
        if ($rrr) {
            return ['result' => 'success', 'order' => '', 'error' => ''];
        } else {
            return ['result' => 'failed', 'order' => '', 'error' => 'dnd cancel failed'];
        }
    }

    public function checkoutReservation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reservation_id' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'reservation' => null, 'error' => $validator->errors()];
            return $result;
        }
        if (!Users::checkAuth($request->input('my_token'))) {
            return ['result' => 'failed', 'reservation' => '', 'error' => 'you are unauthorized user'];
        }
        $reservationId = $request->input('reservation_id');
        $reservation = Booking::find($reservationId);
        if ($reservation == null) {
            return ['result' => 'failed', 'reservation' => '', 'error' => 'reservation id ' . $reservationId . ' is unavailable'];
        }
        $rooms = Room::where('ReservationNumber', $reservationId)->get();
        if ($rooms == null || count($rooms) == 0) {
            return ['result' => 'failed', 'reservation' => $reservation, 'error' => 'this reservation id ' . $reservationId . ' is not registred on any room or already checked out'];
        }
        $st = true;
        for ($i = 0; $i < count($rooms); $i++) {
            if ($rooms[$i]->roomStatus == 1 || $rooms[$i]->roomStatus == 4) {
                $st = false;
                break;
            }
        }
        if ($st == false) {
            return ['result' => 'failed', 'reservation' => $reservation, 'error' => 'room is not in checkin mode'];
        }
        DB::beginTransaction();
        try {
            if ($this->closeReservation($reservation)) {
                for ($i = 0; $i < count($rooms); $i++) {
                    $roomRes = $this->checkOutRoomInDB($rooms[$i]);
                    if ($roomRes['res'] == 'success') {
                        $this->checkoutRoomInFirebase($rooms[$i], $reservation);
                        $this->addCleanupOrderToRoom($rooms[$i], $reservationId, $reservation->RoomOrSuite);
                    } else {
                        $error = $roomRes['error'];
                        $result = ['result' => 'failed', 'reservation' => null, 'error' => $error];
                        DB::rollBack();
                        return $result;
                    }
                }
                DB::commit();
                $result = ['result' => 'success', 'reservation' => 'checkout done', 'error' => null];
                return $result;
            } else {
                $result = ['result' => 'failed', 'reservation' => null, 'error' => 'reservation couldnot be closed'];
                DB::rollBack();
                return $result;
            }
        } catch (Exception $e) {
            $result = ['result' => 'failed', 'reservation' => null, 'error' => $e->getMessage()];
            DB::rollBack();
            return $result;
        }
    }

    // helpers functions

    public function setCheckinModeActiveInFirebase($status)
    {
        $arrStatus = [
            'CheckinModeActive' => $status,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ProjectVariables' . '.json', $arrStatus);
        return $response->successful();
    }

    public function setClientInPowerOffInFirebase($status)
    {
        $arrStatus = [
            'PoweroffClientIn' => $status,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ProjectVariables' . '.json', $arrStatus);
        return $response->successful();
    }

    public function setsetPoweroffAfterHKInFirebase($status)
    {
        $arrStatus = [
            'PoweroffAfterHK' => $status,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ProjectVariables' . '.json', $arrStatus);
        return $response->successful();
    }

    public function setClientBackActionsInFirebase($actions)
    {
        $arrStatus = [
            'OnClientBack' => $actions,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ProjectVariables' . '.json', $arrStatus);
        return $response->successful();
    }

    public function setHKPrepareTimeInFirebase($time)
    {
        $arrStatus = [
            'HKCleanupTime' => $time,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ProjectVariables' . '.json', $arrStatus);
        return $response->successful();
    }

    public function setCheckinModeActionInFirebase($status)
    {
        $arrStatus = [
            'CheckinActions' => $status,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ProjectVariables' . '.json', $arrStatus);
        return $response->successful();
    }

    public function setACSenarioActiveInFirebase($status)
    {
        $arrStatus = [
            'ACSenarioActive' => $status,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ProjectVariables' . '.json', $arrStatus);
        return $response->successful();
    }

    public function setGuestAppActiveInFireBase($status)
    {
        $arrStatus = [
            'GuestApp' => $status,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ProjectVariables' . '.json', $arrStatus);
        return $response->successful();
    }

    public function setCheckoutTimeInFireBase($status)
    {
        $arrStatus = [
            'CheckoutTime' => $status,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ProjectVariables' . '.json', $arrStatus);
        return $response->successful();
    }

    public function setCheckoutModeActionInFirebase($status)
    {
        $arrStatus = [
            'CheckoutActions' => $status,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ProjectVariables' . '.json', $arrStatus);
        return $response->successful();
    }

    public function setCheckoutModeActiveInFirebase($status)
    {
        $arrStatus = [
            'CheckoutModeActive' => $status,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ProjectVariables' . '.json', $arrStatus);
        return $response->successful();
    }

    public function setTemperatureSetPointInFirebase($temperature)
    {
        $arrStatus = [
            'Temp' => $temperature,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ProjectVariables' . '.json', $arrStatus);
        return $response->successful();
    }

    public function setSetPointIntervalInFirebase($inter)
    {
        $arrStatus = [
            'Interval' => $inter,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ProjectVariables' . '.json', $arrStatus);
        return $response->successful();
    }

    public function setDoorsWarningIntervalInFirebase($inter)
    {
        $arrStatus = [
            'DoorWarning' => $inter,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ProjectVariables' . '.json', $arrStatus);
        return $response->successful();
    }

    public function setCheckinModeDurationInFirebase($inter)
    {
        $arrStatus = [
            'CheckInModeTime' => $inter,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ProjectVariables' . '.json', $arrStatus);
        return $response->successful();
    }

    public function setCheckoutModeDurationInFirebase($inter)
    {
        $arrStatus = [
            'CheckOutModeTime' => $inter,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ProjectVariables' . '.json', $arrStatus);
        return $response->successful();
    }

    public function setWelcomeMessageInFirebase($message)
    {
        $arrStatus = [
            'WelcomeMessage' => $message,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ProjectVariables' . '.json', $arrStatus);
        return $response->successful();
    }

    public function reserveRoomInFirebase(Room $room, Booking $b)
    {

        if ($b->RoomOrSuite == 1) {
            $arrRoom = [
                'ReservationNumber' => $b->id,
                'roomStatus' => 2,
                'dep' => '0',
                'Cleanup' => 0,
                'Laundry' => 0,
                'RoomService' => 0,
                'RoomServiceText' => '',
                'Checkout' => 0,
                'Restaurant' => 0,
                'MiniBarCheck' => 0,
                'SOS' => 0,
                'DND' => 0,
                'reservationType' => $b->GuestControl
            ];
        } else {
            $arrRoom = [
                'SuiteStatus' => 2,
                'ReservationNumber' => $b->id,
                'roomStatus' => 2,
                'dep' => '0',
                'Cleanup' => 0,
                'Laundry' => 0,
                'RoomService' => 0,
                'RoomServiceText' => '',
                'Checkout' => 0,
                'Restaurant' => 0,
                'MiniBarCheck' => 0,
                'DND' => 0,
                'SOS' => 0,
            ];
        }
        $response = Http::patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
        return $response->successful();
    }

    public function reserveSuiteInFirebase(Suite $suite)
    {
        $suites = [
            'Status' => 1,
        ];
        $response = Http::patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $suite->Building . '/F' . $suite->Floor . '/S' . $suite->SuiteNumber . '.json', $suites);
        return $response->successful();
    }

    public function checkoutRoomInFirebase(Room $room, Booking $b)
    {
        $time = intval(microtime(true) * 1000);
        if ($b->RoomOrSuite == 1) {
            $arrRoom = [
                'ReservationNumber' => 0,
                'roomStatus' => 3,
                'dep' => 'Cleanup',
                'Cleanup' => $time,
                'Laundry' => 0,
                'RoomService' => 0,
                'DND' => 0,
                'SOS' => 0,
                'Checkout' => 0,
                'Restaurant' => 0,
                'reservationType' => 0
            ];
        } else {
            $arrRoom = [
                'SuiteStatus' => 1,
                'ReservationNumber' => 0,
                'roomStatus' => 3,
                'dep' => 'Cleanup',
                'Cleanup' => $time,
                'Laundry' => 0,
                'RoomService' => 0,
                'DND' => 0,
                'SOS' => 0,
                'Checkout' => 0,
                'Restaurant' => 0,
                'reservationType' => 0
            ];
        }
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
        return $response->successful();
    }

    public function checkoutSuiteInFirebase(Suite $suite)
    {
        $suites = [
            'Status' => 0,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $suite->Building . '/F' . $suite->Floor . '/S' . $suite->SuiteNumber . '.json', $suites);
        return $response->successful();
    }

    public function prepareRoomInFirebase(Room $room)
    {
        $arrRoom = [
            'roomStatus' => 1,
            'dep' => '0',
            'Cleanup' => 0,
            'Laundry' => 0,
            'DND' => 0,
            'RoomService' => 0,
            'SOS' => 0,
            'Checkout' => 0
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
        return $response->successful();
    }

    public function setRoomOutOfServiceInFirebase(Room $room)
    {
        $arrRoom = [
            'roomStatus' => 4,
            'dep' => '0',
            'DND' => 0,
            'Cleanup' => 0,
            'Laundry' => 0,
            'RoomService' => 0,
            'RoomServiceText' => '',
            'Checkout' => 0,
            'Restaurant' => 0,
            'MiniBarCheck' => 0,
            'SOS' => 0,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
        return $response->successful();
    }

    public function setDoorOpenOrClosedInFirebase(Room $room, $status)
    {
        $arrRoom = [
            'doorStatus' => $status,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
        return $response->successful();
    }

    public function setRoomOnlineOrOfflineInFirebase(Room $room, $status)
    {
        $arrRoom = [
            'online' => $status,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
        return $response->successful();
    }

    public function setPowerOnOrOffInFirebase(Room $room, $status)
    {
        $arrRoom = [
            'powerStatus' => $status,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
        return $response->successful();
    }

    public function setClientInOrOutInFirebase(Room $room, $status)
    {
        $arrRoom = [
            'ClientIn' => $status,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
        return $response->successful();
    }

    public function backRoomFromOutOfServiceInFirebase(Room $room)
    {
        $arrRoom = [
            'roomStatus' => 1,
            'dep' => '0',
            'DND' => 0,
            'Cleanup' => 0,
            'Laundry' => 0,
            'RoomService' => 0,
            'RoomServiceText' => '',
            'Checkout' => 0,
            'Restaurant' => 0,
            'MiniBarCheck' => 0,
            'SOS' => 0,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
        return $response->successful();
    }

    public function finishDNDInFirebase(Room $room)
    {
        $arrRoom = [
            'dep' => $this->setDepRoom($room),
            'DND' => 0,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
        return $response->successful();
    }

    public function putRoomOnDNDInFirebase(Room $room)
    {
        $time = intval(microtime(true) * 1000);
        $arrRoom = [
            'dep' => 'DND',
            'DND' => $time,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
        return $response->successful();
    }

    public function copyRoomFromDBToFirebase(Room $room)
    {
        $arrRoom = [
            'id' => $room->id,
            'RoomNumber' => $room->RoomNumber,
            'Status' => $room->Status,
            'hotel' => $room->hotel,
            'Building' => $room->Building,
            'building_id' => $room->building_id,
            'Floor' => $room->Floor,
            'floor_id' => $room->floor_id,
            'RoomType' => $room->RoomType,
            'SuiteStatus' => $room->SuiteStatus,
            'SuiteNumber' => $room->SuiteNumber,
            'SuiteId' => $room->SuiteId,
            'ReservationNumber' => $room->ReservationNumber,
            'roomStatus' => $room->roomStatus,
            'Tablet' => $room->Tablet,
            'dep' => $room->dep,
            'DND' => $room->DND,
            'Cleanup' => $room->Cleanup,
            'Laundry' => $room->Laundry,
            'RoomService' => $room->RoomService,
            'RoomServiceText' => $room->RoomServiceText,
            'Checkout' => $room->Checkout,
            'Restaurant' => $room->Restaurant,
            'MiniBarCheck' => $room->MiniBarCheck,
            'Facility' => $room->Facility,
            'SOS' => $room->SOS,
            'PowerSwitch' => $room->PowerSwitch,
            'DoorSensor' => $room->DoorSensor,
            'MotionSensor' => $room->MotionSensor,
            'Thermostat' => $room->Thermostat,
            'ZBGateway' => $room->ZBGateway,
            'online' => $room->online,
            'CurtainSwitch' => $room->CurtainSwitch,
            'ServiceSwitch' => $room->ServiceSwitch,
            'lock' => $room->lock,
            'Switch1' => $room->Switch1,
            'Switch2' => $room->Switch2,
            'Switch3' => $room->Switch3,
            'Switch4' => $room->Switch4,
            'LockGateway' => $room->LockGateway,
            'LockName' => $room->LockName,
            'powerStatus' => $room->powerStatus,
            'curtainStatus' => $room->curtainStatus,
            'doorStatus' => $room->doorStatus,
            'DoorWarning' => $room->DoorWarning,
            'temp' => $room->temp,
            'TempSetPoint' => $room->TempSetPoint,
            'SetPointInterval' => $room->SetPointInterval,
            'CheckInModeTime' => $room->CheckInModeTime,
            'CheckOutModeTime' => $room->CheckOutModeTime,
            'WelcomeMessage' => $room->WelcomeMessage,
            'Logo' => $room->Logo,
            'token' => $room->token
        ];
        $response = Http::retry(3, 100)->put($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
        return $response->successful();
    }

    function setTemperature(int $temp)
    {
        $rooms = Room::all();
        $results = array();
        foreach ($rooms as $room) {
            $room->TempSetPoint = $temp;
            $room->save();
            $arrRoom = [
                'TempSetPoint' => $temp,
            ];
            $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            array_push($results, $response['TempSetPoint']);
        }
        return $results;
    }

    function setTempIntervalInDBs($interval)
    {
        $rooms = Room::all();
        $results = array();
        foreach ($rooms as $room) {
            $room->SetPointInterval = $interval;
            $room->save();
            $arrRoom = [
                'SetPointInterval' => $interval,
            ];
            $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            array_push($results, $response['SetPointInterval']);
        }
        return $results;
    }

    function setWelcomeMessageInDBs(string $message)
    {
        $rooms = Room::all();
        $results = array();
        foreach ($rooms as $room) {
            $room->WelcomeMessage = $message;
            $room->save();
            $arrRoom = [
                'WelcomeMessage' => $message,
            ];
            $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            array_push($results, $response['WelcomeMessage']);
        }
        return $results;
    }

    function setDoorInterval(int $interval)
    {
        $rooms = Room::all();
        $results = array();
        foreach ($rooms as $room) {
            $room->DoorWarning = $interval;
            $room->save();
            $arrRoom = [
                'DoorWarning' => $interval,
            ];
            $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            array_push($results, $response['DoorWarning']);
        }
        return $results;
    }

    function setCheckinModeDurationInDBs(int $duration)
    {
        $rooms = Room::all();
        $results = array();
        foreach ($rooms as $room) {
            $room->CheckInModeTime = $duration;
            $room->save();
            $arrRoom = [
                'CheckInModeTime' => $duration,
            ];
            $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            array_push($results, $response['CheckInModeTime']);
        }
        return $results;
    }

    function setCheckoutModeDurationInDBs(int $duration)
    {
        $rooms = Room::all();
        $results = array();
        foreach ($rooms as $room) {
            $room->CheckOutModeTime = $duration;
            $room->save();
            $arrRoom = [
                'CheckOutModeTime' => $duration,
            ];
            $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            array_push($results, $response['CheckOutModeTime']);
        }
        return $results;
    }

    function setLogoInDBs(string $path)
    {
        $rooms = Room::all();
        $results = array();
        foreach ($rooms as $room) {
            $room->Logo = $path;
            $room->save();
            $arrRoom = [
                'Logo' => $path,
            ];
            $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            array_push($results, $response['Logo']);
        }
        return $results;
    }

    public function getRoomToken(Room $room)
    {
        $response = Http::get($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '/token.json');
        return $response;
    }

    public function getRoomMessage(Room $room, Booking $b)
    {
        $response = Http::get($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '/WelcomeMessage.json');
        $response = str_replace(" *G ", " " . $b->ClientFirstName . " " . $b->ClientLastName . " ", $response);
        return $response;
    }

    public function sendMessageToRoom(Room $room, Booking $b, string $title)
    {
        $FcmToken = $this->getRoomToken($room);
        $message = $this->getRoomMessage($room, $b);
        return $this->sendWebNotification($FcmToken, $message, $title);
    }

    public function sendRoomMessage(Room $room, string $title, string $message)
    {
        $FcmToken = $this->getRoomToken($room);
        $token = str_replace('"', "", $FcmToken);
        $resp = Http::withHeaders([
            'Authorization' => 'key=' . $this->firebaseServerKey,
            'Content-Type' => 'application/json'
        ])->post($this->firebaseMessageUrl, [
                    'to' => $token,
                    'data' => [
                        'message' => $message,
                        'title' => $title,
                    ]
                ]);
        $arrRoom = [
            'ClientMessage' => $message,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
        return $resp;
    }

    public function sendWebNotification(string $token, string $message, string $title)
    {
        $token = str_replace('"', "", $token);
        $message = str_replace('"', "", $message);
        $resp = Http::withHeaders([
            'Authorization' => 'key=' . $this->firebaseServerKey,
            'Content-Type' => 'application/json'
        ])->post($this->firebaseMessageUrl, [
                    'to' => $token,
                    'data' => [
                        'message' => $message,
                        'title' => $title,
                    ]
                ]);
        return $resp->successful();
    }

    public function sendOrderToRoom(string $token, string $order, int $roomNumber)
    {
        $token = str_replace('"', "", $token);
        $order = str_replace('"', "", $order);
        $resp = Http::withHeaders([
            'Authorization' => 'key=' . $this->firebaseServerKey,
            'Content-Type' => 'application/json'
        ])->post($this->firebaseMessageUrl, [
                    'to' => $token,
                    'data' => [
                        'room' => $roomNumber,
                        'title' => $order,
                    ]
                ]);
        return $resp;
    }

    public function checkinRoom(Room $room, Booking $b)
    {
        $FcmToken = $this->getRoomToken($room);
        $message = $this->getRoomMessage($room, $b);
        return $this->sendCheckinOrder($FcmToken, $room->RoomNumber);
    }

    public function sendCheckinOrder(string $token, string $roomNumber)
    {
        $token = str_replace('"', "", $token);
        $resp = Http::withHeaders([
            'Authorization' => 'key=' . $this->firebaseServerKey,
            'Content-Type' => 'application/json'
        ])->post($this->firebaseMessageUrl, [
                    'to' => $token,
                    'data' => [
                        'title' => 'checkin',
                        'room' => $roomNumber,
                    ]
                ]);
        return $resp->successful();
    }

    public function addCleanupOrderToRoom(Room $room, string $reservationId, int $RorS)
    {
        $time = intval(microtime(true) * 1000);
        $room->Cleanup = $time;
        $room->dep = 'Cleanup';
        $order = new Serviceorder();
        $order->roomNumber = $room->RoomNumber;
        $order->Reservation = $room->ReservationNumber;
        $order->RorS = $RorS;
        $order->Hotel = 1;
        $order->dep = 'Cleanup';
        $order->dateTime = $time;
        $order->orderText = '';
        $order->status = 0;
        $order->responseDateTime = 0;
        $order->responseEmployee = 0;
        if ($order->save()) {
            if ($room->save()) {
                $arrRoom = [
                    'dep' => 'Cleanup',
                    'Cleanup' => $time,
                ];
                $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
                if ($response->successful()) {
                    $this->sendNotificationToServiceUsers('Cleanup', $room);
                    $result = ['result' => 'success', 'cleanup order' => $order, 'error' => null];
                    return $result;
                } else {
                    $room->Cleanup = 0;
                    $room->dep = '0';
                    $room->save();
                    $order->delete();
                    $result = ['result' => 'failed', 'cleanup order' => null, 'error' => 'error saving room changes in firebase'];
                    return $result;
                }
            } else {
                $order->delete();
                $result = ['result' => 'failed', 'cleanup order' => null, 'error' => 'error saving room changes'];
                return $result;
            }
        } else {
            $result = ['result' => 'failed', 'cleanup order' => null, 'error' => 'error saving order'];
            return $result;
        }
    }

    public function addServiceOrder(array $params)
    {
        $order = new Serviceorder();
        $order->roomNumber = $params['RoomNumber'];
        $order->Reservation = $params['reservationId'];
        $order->RorS = $params['RorS'];
        $order->Hotel = 1;
        $order->dep = $params['dep'];
        $order->dateTime = $params['time'];
        $order->orderText = $params['orderText'];
        ;
        $order->status = 0;
        $order->responseDateTime = 0;
        $order->responseEmployee = 0;
        if ($order->save()) {
            $res = ['result' => 'success', 'order' => $order];
            return $res;
        } else {
            $res = ['result' => 'failed'];
            return $res;
        }
    }

    public function getServiceUsers()
    {
        $response = Http::get($this->firebaseUrl . '/' . $this->projectName . 'ServiceUsers.json');
        $jsonObj = json_decode($response, true);
        return $jsonObj;
    }

    public function sendNotificationToServiceUsers(string $order, Room $room)
    {
        $arr = $this->getServiceUsers();
        $results = array();
        foreach ($arr as $newArr) {
            // code...
            if (count($newArr) > 5) {
                if ($newArr['department'] == 'Service') {
                    $token = str_replace('"', "", $newArr['token']);
                    // $r = Http::withHeaders([
                    //   'Authorization' => 'key='.$this->firebaseServerKey,
                    //   'Content-Type' => 'application/json'
                    //   ])->post($this->firebaseMessageUrl,[
                    //     'to' => $token,
                    //     'data' => [
                    //       'message' => 'New '.$order.' order from room '.$room->RoomNumber,
                    //       'title' => $room->RoomNumber.' '.$order,
                    //         'RoomNumber' => $room->RoomNumber
                    //     ]
                    //   ]);
                    $r = $this->sendFBMessage($room->RoomNumber . ' ' . $order, 'New ' . $order . ' order from room ' . $room->RoomNumber, $token);
                    array_push($results, [$newArr['jobNumber'] => $r]);
                } else if ($newArr['department'] == $order) {
                    $token = str_replace('"', "", $newArr['token']);
                    // $r = Http::withHeaders([
                    //   'Authorization' => 'key='.$this->firebaseServerKey,
                    //   'Content-Type' => 'application/json'
                    //   ])->post($this->firebaseMessageUrl,[
                    //     'to' => $token,
                    //     'data' => [
                    //       'message' => 'New '.$order.' Order From Room '.$room->RoomNumber,
                    //       'title' => $room->RoomNumber.' '.$order,
                    //         'RoomNumber' => $room->RoomNumber
                    //     ]
                    //   ]);
                    $r = $this->sendFBMessage($room->RoomNumber . ' ' . $order, 'New ' . $order . ' Order From Room ' . $room->RoomNumber, $token);
                    array_push($results, [$newArr['jobNumber'] => $r]);
                }
            }
        }
        return $results;
    }

    public function sendCancelNotificationToServiceUsers(string $order, Room $room)
    {
        $arr = $this->getServiceUsers();
        $results = array();
        foreach ($arr as $newArr) {
            // code...
            if (in_array('department', $newArr)) {
                if ($newArr['department'] == 'Service') {
                    $token = str_replace('"', "", $newArr['token']);

                    $r = Http::withHeaders([
                        'Authorization' => 'key=' . $this->firebaseServerKey,
                        'Content-Type' => 'application/json'
                    ])->post($this->firebaseMessageUrl, [
                                'to' => $token,
                                'data' => [
                                    'message' => $order . ' order cancelled from room ' . $room->RoomNumber,
                                    'title' => $room->RoomNumber . ' ' . $order . ' cancelled',
                                    'RoomNumber' => $room->RoomNumber
                                ]
                            ]);
                    array_push($results, $r->successful());
                } else if ($newArr['department'] == $order) {
                    $token = str_replace('"', "", $newArr['token']);
                    $r = Http::withHeaders([
                        'Authorization' => 'key=' . $this->firebaseServerKey,
                        'Content-Type' => 'application/json'
                    ])->post($this->firebaseMessageUrl, [
                                'to' => $token,
                                'data' => [
                                    'message' => $order . ' order cancelled from room ' . $room->RoomNumber,
                                    'title' => $room->RoomNumber . ' ' . $order . ' cancelled',
                                    'RoomNumber' => $room->RoomNumber
                                ]
                            ]);
                    array_push($results, $r->successful());
                }
            }

        }
        return $results;
    }

    public function insertReservation(array $params)
    {
        $reserve = new Booking();
        $reserve->RoomNumber = $params['RoomNumber'];
        $reserve->ClientId = $params['ClientId'];
        $reserve->Status = $params['Status'];
        $reserve->RoomOrSuite = $params['RoomOrSuite'];
        $reserve->MultiRooms = $params['MultiRooms'];
        $reserve->AddRoomNumber = $params['AddRoomNumber'];
        $reserve->AddRoomId = $params['AddRoomId'];
        $reserve->StartDate = $params['StartDate'];
        $reserve->Nights = $params['Nights'];
        $reserve->EndDate = $params['EndDate'];
        $reserve->Hotel = $params['Hotel'];
        $reserve->BuildingNo = $params['BuildingNo'];
        $reserve->Floor = $params['Floor'];
        $reserve->ClientFirstName = $params['ClientFirstName'];
        $reserve->ClientLastName = $params['ClientLastName'];
        $reserve->IdType = $params['IdType'];
        $reserve->IdNumber = $params['IdNumber'];
        $reserve->MobileNumber = $params['MobileNumber'];
        $reserve->Email = $params['Email'];
        $reserve->Rating = $params['Rating'];
        $reserve->password = $params['password'];
        $reserve->GuestControl = $params['GuestControl'];
        try {
            $reserve->save();
            return ['res' => 'success', 'reservation' => $reserve];
        } catch (Exception $e) {
            return ['res' => 'failed', 'error' => $e->getMessage()];
        }
    }

    public function prepareRoomInDB(Room $room)
    {
        $room->roomStatus = 1;
        $room->dep = '0';
        $room->Cleanup = 0;
        try {
            $room->save();
            $res = ['res' => 'success', 'room', $room];
            return $res;
        } catch (Exception $e) {
            $res = ['res' => 'failed', 'error', $e];
            return $res;
        }
    }

    public function reserveRoomInDB(Room $room, int $reservId)
    {
        $room->Cleanup = 0;
        $room->Laundry = 0;
        $room->RoomService = 0;
        $room->DND = 0;
        $room->Restaurant = 0;
        $room->RoomServiceText = '';
        $room->dep = '0';
        $room->Checkout = 0;
        $room->SOS = 0;
        $room->MiniBarCheck = 0;
        $room->ReservationNumber = $reservId;
        $room->roomStatus = 2;
        try {
            $room->save();
            $res = ['res' => 'success', 'room' => $room];
            return $res;
        } catch (Exception $e) {
            $res = ['res' => 'failed', 'error' => $e->getMessage()];
            return $res;
        }
    }

    public function reserveSuiteRoomInDB(Room $room, int $reservId)
    {
        $room->Cleanup = 0;
        $room->Laundry = 0;
        $room->RoomService = 0;
        $room->DND = 0;
        $room->Restaurant = 0;
        $room->RoomServiceText = '';
        $room->dep = '0';
        $room->Checkout = 0;
        $room->SOS = 0;
        $room->MiniBarCheck = 0;
        $room->ReservationNumber = $reservId;
        $room->SuiteStatus = 2;
        $room->roomStatus = 2;
        try {
            $room->save();
            return ['res' => 'success', 'room' => $room];
        } catch (Exception $e) {
            return ['res' => 'failed', 'error' => $e->getMessage()];
        }
    }

    public function reserveSuiteInDB(Suite $suite)
    {
        $suite->Status = 1;
        try {
            $suite->save();
            return ['res' => 'success', 'suite' => $suite];
        } catch (Exception $e) {
            echo $e->getMessage();
            return ['res' => 'failed', 'error' => $e->getMessage()];
        }
    }

    public function checkOutRoomInDB(Room $room)
    {
        $time = intval(microtime(true) * 1000);
        try {
            $room->ReservationNumber = 0;
            $room->roomStatus = 3;
            $room->dep = "0";
            $room->Laundry = 0;
            $room->Cleanup = $time;
            $room->SOS = 0;
            $room->DND = 0;
            $room->Restaurant = 0;
            $room->RoomService = 0;
            $room->Checkout = 0;
            if ($room->SuiteStatus == 2) {
                $room->SuiteStatus = 1;
                $suite = Suite::find($room->SuiteId);
                if ($suite != null) {
                    $this->checkoutSuiteInDB($suite);
                }
            }
            $s = $room->save();
            if ($s) {
                return ['res' => 'success', 'room' => $room];
            } else {
                return ['res' => 'failed', 'error' => 'room not saved'];
            }
        } catch (Exception $e) {
            return ['res' => 'failed', 'error' => $e->getMessage()];
        }
    }

    public function putRoomOutOfServiceInDB(Room $room)
    {
        $room->roomStatus = 4;
        $room->Cleanup = 0;
        $room->Laundry = 0;
        $room->RoomService = 0;
        $room->Checkout = 0;
        $room->SOS = 0;
        $room->DND = 0;
        $room->Restaurant = 0;
        return $room->save();
    }

    public function backRoomFromOutOfServiceInDB(Room $room)
    {
        $room->roomStatus = 1;
        return $room->save();
    }

    public function checkoutSuiteInDB(Suite $suite)
    {
        $suite->Status = 0;
        try {
            $suite->save();
            $this->checkoutSuiteInFirebase($suite);
            $res = ['res' => 'succes', 'suite' => $suite];
            return $res;
        } catch (Exception $e) {
            $res = ['res' => 'failed', 'error' => $e->getMessage()];
            return $res;
        }
    }

    public function closeReservation(Booking $b)
    {
        $b->Status = 0;
        return $b->save();
    }

    public function setDepRoom(Room $room)
    {
        $orders = array();
        array_push($orders, $room->Cleanup);
        array_push($orders, $room->Laundry);
        array_push($orders, $room->RoomService);
        array_push($orders, $room->DND);
        array_push($orders, $room->SOS);
        array_push($orders, $room->Restaurant);
        array_push($orders, $room->Checkout);
        sort($orders);
        $biggest = $orders[(count($orders) - 1)];
        $result = '';
        switch ($biggest) {
            case 0:
                $result = '0';
                break;
            case $room->Cleanup:
                $result = 'Cleanup';
                break;
            case $room->Laundry:
                $result = 'Laundry';
                break;
            case $room->RoomService:
                $result = 'RoomService';
                break;
            case $room->DND:
                $result = 'DND';
                break;
            case $room->SOS:
                $result = 'SOS';
                break;
            case $room->Restaurant:
                $result = 'Restaurant';
                break;
            case $room->Checkout:
                $result = 'Checkout';
                break;

            default:
                $result = '0';
                break;
        }
        return $result;
    }

    function checkIfProjectHasTermostate()
    {
        $room = Room::where('Thermostat', 1)->first();
        if ($room) {
            return true;
        } else {
            return false;
        }
    }

    function checkIfProjectHasDoorSensor()
    {
        $room = Room::where('DoorSensor', 1)->first();
        if ($room) {
            return true;
        } else {
            return false;
        }
    }

    function checkIfProjectRoomsHasControlScreens()
    {
        $room = Room::where('Status', 1)->first();
        if ($room) {
            return true;
        } else {
            return false;
        }
    }

    function cancelAllServiceOrdersOnRoom(Room $room)
    {
        $orders = Serviceorder::where('status', 0)->where(function ($q) {
            $q->where('dep', 'Cleanup')
                ->orWhere('dep', 'Laundry')
                ->orWhere('dep', 'RoomService')
                ->orWhere('dep', 'Checkout');
        })->get();
        if ($orders == null) {
            return true;
            if (count($orders) == 0) {
                return true;
            }
        }
        $room->Cleanup = 0;
        $room->Laundry = 0;
        $room->Checkout = 0;
        $room->RoomService = 0;
        if ($room->save()) {
            foreach ($orders as $order) {
                $order->delete();
            }
            $this->copyRoomFromDBToFirebase($room);
        } else {
            return false;
        }
    }

    function cancelDNDMode(Room $room)
    {
        if ($room->roomStatus != 2) {
            return false;
        }
        if ($room->DND == 0) {
            return true;
        }
        $old = $room->DND;
        $room->DND = 0;
        $room->dep = $this->setDepRoom($room);
        if ($room->save()) {
            return true;
        } else {
            $room->DND = $old;
            $room->dep = 'DND';
            return false;
        }
    }

    function setRoomMessage(Room $room, string $message)
    {
        $arrRoom = [
            'message' => $message,
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
    }

    function sendConfermationSMSToClient(Room $room, Booking $reservation, $randomNumber)
    {

        $recipients = [$reservation->MobileNumber];

        $curl = curl_init();

        $fields = ["sender" => $this->senderName, "recipients" => $recipients, "body" => 'Welcome to RATCO your password is ' . $randomNumber];
        $f = json_encode($fields);
        $headers = ["Content-Type" => "application/json", "Authorization: " => $this->bearerToken];
        $h = json_encode($headers);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->smsUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $f,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer 1e798ba819ca85b7847612e85afe709d'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $rrr = json_decode($response, true);

        return $rrr;
    }

    function sendClientMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reservation' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'login' => '', 'error' => $validator->errors()];
            return $result;
        }
        $reservation = Booking::find($request->reservation);
        $room = Room::where('RoomNumber', $reservation->RoomNumber)->first();
        if ($this->isGuestApp() == false) {
            return ["statusCode" => 401, "message" => "no guest app for this project"];
        }
        $randomNumber = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        return $this->sendConfermationSMSToClient($room, $reservation, $randomNumber);
    }

    function isGuestApp()
    {
        $variables = Projectsvariable::all()->first();
        if ($variables == null) {
            return false;
        }
        if ($variables->GuestApp == 1) {
            return true;
        }
        return false;
    }

    function sendReRunMessage(Request $request)
    {

        $title = "reRun";
        $description = "reRun";
        $projectId = config('services.fcm.checkin-62774'); # INSERT COPIED PROJECT ID

        $credentialsFilePath = Storage::path('json/checkin-62774-firebase-adminsdk-4k0jw-a134bad884.json');
        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();

        $access_token = $token['access_token'];

        $headers = [
            "Authorization: Bearer $access_token",
            'Content-Type: application/json'
        ];

        $data = [
            "message" => [
                "token" => $request->token,
                "data" => [
                    "title" => $title,
                    "body" => $description,
                ],
            ]
        ];
        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/checkin-62774/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output for debugging
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return response()->json([
                'message' => 'Curl Error: ' . $err
            ], 500);
        } else {
            return response()->json([
                'message' => 'Notification has been sent',
                'response' => json_decode($response, true)
            ]);
        }
    }

    function sendFBMessage($title, $message, $receipt)
    {

        $description = $message;
        $projectId = config('services.fcm.checkin-62774'); # INSERT COPIED PROJECT ID

        $credentialsFilePath = Storage::path('json/checkin-62774-firebase-adminsdk-4k0jw-a134bad884.json');
        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();

        $access_token = $token['access_token'];

        $headers = [
            "Authorization: Bearer $access_token",
            'Content-Type: application/json'
        ];

        $data = [
            "message" => [
                "token" => $receipt,
                "notification" => [
                    "title" => $title,
                    "body" => $description,
                ],
            ]
        ];
        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/checkin-62774/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output for debugging
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return response()->json([
                'message' => 'Curl Error: ' . $err
            ], 500);
        } else {
            return response()->json([
                'message' => 'Notification has been sent',
                'response' => json_decode($response, true)
            ]);
        }
    }

    function sendWhatsupMessage(Request $request)
    {
        // $validator = Validator::make($request->all(),[
        //     'room_id' => 'required|numeric',
        //  ]);
        // if ($validator->fails())  {
        //     return ['result'=>'failed','order'=>'','error'=>$validator->errors()];
        // }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/v22.0/403496699513645/messages');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "{ \"messaging_product\": \"whatsapp\",\"1\":\"Abdullah\", \"to\": \"966537238827\", \"type\": \"template\", \"template\": { \"name\": \"hello_world\", \"language\": { \"code\": \"en_us\" } } }");

        $headers = array();
        $headers[] = 'Authorization: Bearer EAAOYQYK8kwYBOzBtWzZCIwWAjcnyav20L0NpIMxwdLy276JvL3te37T9edKZB7DAPSJhGXap7Q64D8WZAlRCq65SyWzKnSDKYkVhRgIEBDQnn1JKbZAMzkU5uQo6Hq8y4gbot57ga8V6mN0qNfUJZAYpJUsjNQp6599SIlKBVAvbYY0DbgHHPmLZC6DV6EVDqHO7DrdalPIMZBmf0CJy0DCkmz9';
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }

}
