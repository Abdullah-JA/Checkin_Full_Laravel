<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Roomtype;
use App\Models\Suite;
use App\Models\Doorsopen;
use App\Models\Serverdevice;
use App\Models\Projectsvariable;
use App\Models\Serviceemployee;
use App\Models\Booking;
use App\Http\Controllers\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use FFI\Exception;

class roomsManagement extends Controller
{
    public $firebaseUrl = 'https://checkin-62774-default-rtdb.asia-southeast1.firebasedatabase.app';
    public $projectName = 'apiTest';
    public $client_id = "d9hyvtdshnm3uvaun59d";


    //___________________________________________________________________
    // add functions

    public function addBuilding(Request $request)
    {

        $validator = validator::make($request->all(), [
            'building_number' => 'required|unique:buildings,buildingNo|numeric',
            'building_name' => 'required|unique:buildings,buildingName|max:40',
            'floors_number' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'insertedRow' => null, 'error' => $validator->errors()];
            return $result;
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'insertedRow' => null, 'error' => 'you are un authorized user'];
        }
        $bid = $request->input('building_number');
        $bname = $request->input('building_name');
        $fnum = $request->input('floors_number');
        $pars = [$bid, $bname, $fnum];
        DB::beginTransaction();
        try {
            $building = new Building();
            $building->projectId = 1;
            $building->buildingNo = $bid;
            $building->buildingName = $bname;
            $building->floorsNumber = $fnum;
            $floors = array();
            if ($building->save()) {
                for ($i = 0; $i < $fnum; $i++) {
                    $fl = new Floor();
                    $fl->building_id = $building->id;
                    $fl->floorNumber = $i + 1;
                    $fl->rooms = 0;
                    $fl->save();
                    array_push($floors, $fl);
                }
                $this->addBuildingToFirebase($building, $floors);
                DB::commit();
                return ['result' => 'success', 'insertedRow' => $building, 'error' => null];
            }
        } catch (Exception $e) {
            DB::rollback();
            return ['result' => 'failed', 'insertedRow' => null, 'error' => 'error ' . $e->getMessage()];
        }
    }

    public function addFloorRooms(Request $request)
    {
        $validator = validator::make($request->all(), [
            'BuildingId' => 'required|numeric',
            'FloorId' => 'required|numeric',
            'Rooms' => 'required|numeric',
            'start' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'you are un authorized user'];
        }
        $building_id = $request->input('BuildingId');
        $startRoom = $request->input('start');
        $roomsNum = $request->input('Rooms');
        $floorId = $request->input('FloorId');
        $insertedRooms = array();
        $F = Floor::find($floorId);
        if ($F == null) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'this floor id ' . $floorId . ' is unavailable'];
        }
        $B = Building::find($building_id);
        if ($B == null) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'this building id ' . $building_id . ' is unavailable'];
        }
        if ($F->building_id != $building_id) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'building id ' . $building_id . ' does not match floor ' . $F . ' !.. floor ' . $F . ' building id is ' . $F->building_id];
        }
        $rrr = Room::where('building_id', $B->id)->where('RoomNumber', '=', $startRoom)->first();
        if ($rrr != null) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'room number ' . $startRoom . ' already taken '];
        }
        DB::beginTransaction();
        try {
            for ($i = 0; $i < $roomsNum; $i++) {
                $RoomNumber = $startRoom + $i;
                $insertedRooms[$i] = roomsManagement::addRoom($RoomNumber, $B->buildingNo, $B->id, $F->floorNumber, $F->id);
                if ($insertedRooms[$i] != null) {
                    $this->addRoomToFirebase($insertedRooms[$i]);
                }
            }
            $floor = Floor::find($floorId);
            $floor->rooms = $floor->rooms + $roomsNum;
            $floor->save();
            DB::commit();
            return ['result' => 'success', 'insertedRow' => $insertedRooms, 'error' => ''];
        } catch (Exception $e) {
            DB::rollback();
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'error ' . $e->getMessage()];
        }
    }

    public function addOneRoom(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_number' => 'required|numeric',
            'building_id' => 'required|numeric',
            'floor_id' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => $validator->errors()];
        }
        $b = Building::find($request->input('building_id'));
        if ($b == null) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'buiiding id ' . $request->input('building_id') . ' is unavalable'];
        }
        $f = Floor::find($request->input('floor_id'));
        if ($f == null) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'floor id ' . $request->input('floor_id') . ' is unavalable'];
        }
        if ($f->building_id != $b->id) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'floor id ' . $f->id . ' is not related to building id ' . $b->id];
        }
        $room = Room::where('RoomNumber', $request->input('room_number'))->first();
        if ($room != null) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'room number ' . $request->input('room_number') . ' is already exists in building number ' . $room->Building];
        }
        DB::beginTransaction();
        try {
            $room = new Room();
            $room->RoomNumber = $request->input('room_number');
            $room->hotel = 1;
            $room->Building = $b->buildingNo;
            $room->building_id = $b->id;
            $room->Floor = $f->floorNumber;
            $room->floor_id = $f->id;
            $room->save();
            $f->rooms = $f->rooms + 1;
            $f->save();
            DB::commit();
            $this->addRoomToFirebase($room);
            return ['result' => 'success', 'insertedRow' => $room, 'error' => ''];
        } catch (Exception $e) {
            DB::rollBack();
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'error ' . $e->getMessage()];
        }
    }

    public function loginRoom(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'error' => 'room id is unavailable'];
        }
        if ($room->Status == 1) {
            return ['result' => 'failed', 'error' => 'room is rigisted on other device'];
        }
        try {
            $room->Status = 1;
            $room->save();
            $arrRoom = ['Status' => 1];
            $response = Http::patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            return ['result' => 'success', 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'error ' . $e->getMessage()];
        }
    }

    public function logoutRoom(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'error' => 'room id is unavailable'];
        }
        try {
            $room->Status = 0;
            $room->save();
            $arrRoom = ['Status' => 0];
            $response = Http::patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            return ['result' => 'success', 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'error ' . $e->getMessage()];
        }
    }

    public function addOneFloor(Request $request)
    {
        $validator = validator::make($request->all(), [
            'start' => 'required|numeric',
            'building_id' => 'required|numeric',
            'floor_number' => 'required|numeric',
            'rooms' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'insertedRow' => null, 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'you are unauthorized user'];
        }
        $b = Building::find($request->input('building_id'));
        if ($b == null) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'building id ' . $request->input('building_id') . ' is unavailable'];
        }
        $f = Floor::where('floorNumber', $request->input('floor_number'))->where('building_id', $b->id)->first();
        if ($f != null) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'floor number ' . $f->floorNumber . ' is already exists in building ' . $b->buildingNo];
        }
        $startRoom = Room::where('building_id', $b->id)->where('RoomNumber', $request->input('start'))->first();
        if ($startRoom != null) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'room number ' . $request->input('start') . ' already taken '];
        }
        if ($request->input('rooms') > 99) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'rooms must be less than 100'];
        }
        DB::beginTransaction();
        try {
            $floor = new Floor();
            $floor->building_id = $request->input('building_id');
            $floor->floorNumber = $request->input('floor_number');
            $floor->rooms = $request->input('rooms');
            $floor->save();
            $building = Building::find($floor->building_id);
            $building->floorsNumber = $building->floorsNumber + 1;
            $building->save();
            for ($i = 0; $i < $floor->rooms; $i++) {
                $RoomNumber = $request->input('start') + $i;
                $room = roomsManagement::addRoom($RoomNumber, $building->buildingNo, $building->id, $floor->floorNumber, $floor->id);
                if ($room != null) {
                    $this->addRoomToFirebase($room);
                }
            }
            DB::commit();
            return ['result' => 'success', 'insertedRow' => $floor, 'error' => null];
        } catch (Exception $e) {
            DB::rollback();
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'error ' . $e->getMessage()];
        }
    }

    public function addSuite(Request $request)
    {
        $validator = validator::make($request->all(), [
            'suite_number' => 'required|numeric|unique:suites,SuiteNumber',
            'building_id' => 'required|numeric',
            'floor_id' => 'required|numeric',
            'room_ids' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'insertedRow' => null, 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'you are un authorized user'];
        }
        $b = Building::find($request->input('building_id'));
        if ($b == null) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'building id ' . $request->input('building_id') . 'is unavailable'];
        }
        $f = Floor::find($request->input('floor_id'));
        if ($f == null) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'floor id ' . $request->input('floor_id') . 'is unavailable'];
        }
        if ($f->building_id != $b->id) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'floor id ' . $f->id . ' does not exists in building id ' . $b->id];
        }
        $Ids = explode("-", $request->input('room_ids'));
        if ($Ids == null || count($Ids) == 0) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'rooms ids is unavailable'];
        }
        $roomsNum = '';
        for ($i = 0; $i < count($Ids); $i++) {
            $r = Room::find($Ids[$i]);
            if ($i == 0) {
                $roomsNum = $r->RoomNumber;
            } else {
                $roomsNum = $roomsNum . '-' . $r->RoomNumber;
            }
        }
        $suite = $this->addSuiteToDBs($b, $f, $roomsNum, $request->input('room_ids'), $request->input('suite_number'));
        if ($suite != null) {
            for ($i = 0; $i < count($Ids); $i++) {
                $room = Room::find($Ids[$i]);
                if ($room != null) {
                    $this->convertRoomToSuiteRoom($suite, $room);
                }
            }
            return ['result' => 'success', 'insertedRow' => $suite, 'error' => ''];
        }
        return ['result' => 'failed', 'insertedRow' => '', 'error' => 'unable to save suite in DB '];
    }

    public function addControlDevice()
    {
        $devices = Serverdevice::all();
        $device = new Serverdevice;
        if ($devices == null || count($devices) == 0) {
            $device->name = "First";
            $device->roomsIds = "all";
            $device->status = 1;
            $device->token = "";
        } else if (count($devices) == 1) {
            $device->name = "Second";
            $device->roomsIds = "all";
            $device->status = 1;
            $device->token = "";
        } else if (count($devices) == 2) {
            $device->name = "Third";
            $device->roomsIds = "all";
            $device->status = 1;
            $device->token = "";
        } else if (count($devices) == 3) {
            $device->name = "Forth";
            $device->roomsIds = "all";
            $device->status = 1;
            $device->token = "";
        } else if (count($devices) == 4) {
            $device->name = "Fifth";
            $device->roomsIds = "all";
            $device->status = 1;
            $device->token = "";
        } else if (count($devices) == 5) {
            $device->name = "Sixth";
            $device->roomsIds = "all";
            $device->status = 1;
            $device->token = "";
        } else if (count($devices) == 6) {
            $device->name = "Seventh";
            $device->roomsIds = "all";
            $device->status = 1;
            $device->token = "";
        } else if (count($devices) == 7) {
            $device->name = "Eighth";
            $device->roomsIds = "all";
            $device->status = 1;
            $device->token = "";
        } else if (count($devices) == 8) {
            $device->name = "Ninth";
            $device->roomsIds = "all";
            $device->status = 1;
            $device->token = "";
        } else if (count($devices) == 9) {
            $device->name = "Tenth";
            $device->roomsIds = "all";
            $device->status = 1;
            $device->token = "";
        } else {
            $device->name = "Tenth";
            $device->roomsIds = "all";
            $device->status = 1;
            $device->token = "";
        }
        try {
            $device->save();
            $this->addControlDeviceToFirebase($device);
            return ['result' => 'success', 'device' => $device, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'device' => '', 'error' => $e];
        }
    }

    public function deleteControlDevice(Request $request)
    {
        $validator = validator::make($request->all(), [
            'device_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }
        $device = Serverdevice::find($request->device_id);
        if ($device == null) {
            return ['result' => 'failed', 'error' => 'device id is unavailable'];
        }
        try {
            $device->delete();
            $response = Http::delete($this->firebaseUrl . '/' . $this->projectName . 'ServerDevices/' . $device->name . '.json');
            return ['result' => 'success', 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'delete' => '', 'error' => $e];
        }
    }

    public function deleteBuilding(Request $request)
    {
        $validator = validator::make($request->all(), [
            'building_id' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'delete' => null, 'error' => $validator->errors()];
            return $result;
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'delete' => '', 'error' => 'you are un authorized user'];
        }
        $building = Building::find($request->input('building_id'));
        if ($building == null) {
            return ['result' => 'failed', 'delete' => null, 'error' => 'building id ' . $request->input('building_id') . ' is unavailable'];
        }
        DB::beginTransaction();
        try {
            $rooms = Room::where('building_id', '=', $building->id)->get();
            if ($rooms != null && count($rooms) > 0) {
                for ($i = 0; $i < count($rooms); $i++) {
                    $rooms[$i]->delete();
                }
            }
            $suites = Suite::where('BuildingId', $building->id)->get();
            if ($suites != null && count($suites) > 0) {
                foreach ($suites as $suite) {
                    $suite->delete();
                }
            }
            $floors = Floor::where('building_id', '=', $building->id)->get();
            for ($i = 0; $i < count($floors); $i++) {
                $floors[$i]->delete();
            }
            $building->delete();
            DB::commit();
            $response = Http::delete($this->firebaseUrl . '/' . $this->projectName . '/B' . $building->buildingNo . '.json');
            return ['result' => 'success', 'delete' => 'building deleted', 'error' => ''];
        } catch (Exception $e) {
            DB::rollBack();
            return ['result' => 'failed', 'delete' => '', 'error' => $e->getMessage()];
        }
    }

    public function deleteFloorAndRooms(Request $request)
    {
        $validator = validator::make($request->all(), [
            'floor_id' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'delete' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'you are unauthorized user'];
        }
        $floor = Floor::find($request->input('floor_id'));
        if ($floor == null) {
            return ['result' => 'failed', 'delete' => '', 'error' => 'floor id ' . $request->input('floor_id') . ' is unavailable'];
        }
        DB::beginTransaction();
        try {
            $rooms = Room::where('floor_id', '=', $floor->id)->get();
            if ($rooms != null && count($rooms) > 0) {
                foreach ($rooms as $room) {
                    $room->delete();
                }
            }
            $suites = Suite::where('FloorId', $floor->id)->get();
            if ($suites != null && count($suites) > 0) {
                foreach ($suites as $suite) {
                    $suite->delete();
                }
            }
            $building = Building::find($floor->building_id);
            $building->floorsNumber = $building->floorsNumber - 1;
            $building->save();
            $floor->delete();
            DB::commit();
            $response = Http::delete($this->firebaseUrl . '/' . $this->projectName . '/B' . $building->buildingNo . '/F' . $floor->floorNumber . '.json');
            return ['result' => 'success', 'delete' => 'floor deleted', 'error' => ''];
        } catch (Exception $e) {
            DB::rollBack();
            return ['result' => 'failed', 'delete' => '', 'error' => $e->getMessage()];
        }
    }

    public function deleteRoom(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'delete' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'you are un authorized user'];
        }
        $room = Room::find($request->input('room_id'));
        if ($room == null) {
            return ['result' => 'failed', 'delete' => 'delete failed ', 'error' => 'room id is unavailable'];
        }
        DB::beginTransaction();
        try {
            $floor = Floor::find($room->floor_id);
            $floor->rooms = $floor->rooms - 1;
            $floor->save();
            $room->delete();
            DB::commit();
            $response = Http::delete($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $floor->floorNumber . '/R' . $room->RoomNumber . '.json');
            return ['result' => 'success', 'delete' => 'room deleted', 'error' => ''];
        } catch (Exception $e) {
            DB::rollBack();
            return ['result' => 'failed', 'insertedRow' => '', 'error' => $e->getMessage()];
        }
    }

    public function addRoomType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'NameAr' => 'required|string|max:100',
            'NameEn' => 'required|string|max:100',
            'RentDay' => 'required|numeric|min:0',
            'RentMonth' => 'required|numeric|min:0',
            'RentYear' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token'))) {
            $roomType = new RoomType();
            $roomType->NameAr = $request->NameAr;
            $roomType->NameEn = $request->NameEn;
            $roomType->RentDay = $request->RentDay;
            $roomType->RentMonth = $request->RentMonth;
            $roomType->RentYear = $request->RentYear;
            $roomType->save();
            return ['result' => 'success', 'room_type' => $roomType];
        } else {
            return ['result' => 'failed', 'room_type' => '', 'error' => 'you are un authorized user'];
        }
    }

    public function deleteRoomType(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_type_id' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'delete' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token'))) {
            $room = Roomtype::find($request->input('room_type_id'));
            if ($room != null) {
                $room->delete();
                return ['result' => 'success', 'delete' => 'room type deleted', 'error' => ''];
            } else {
                return ['result' => 'failed', 'delete' => 'delete failed ', 'error' => 'room type id is unavailable'];
            }
        } else {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'you are un authorized user'];
        }
    }

    public function deleteSuite(Request $request)
    {
        $validator = validator::make($request->all(), [
            'suite_id' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'delete' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'you are un authorized user'];
        }
        $suite = Suite::find($request->input('suite_id'));
        if ($suite == null) {
            return ['result' => 'failed', 'insertedRow' => '', 'error' => 'suite id ' . $request->input('suite_id') . ' is unavailable'];
        }
        DB::beginTransaction();
        try {
            $rooms = Room::where('SuiteId', $suite->id)->get();
            if ($rooms != null && count($rooms) > 0) {
                foreach ($rooms as $room) {
                    $room->SuiteId = 0;
                    $room->SuiteStatus = 0;
                    $room->SuiteNumber = 0;
                    $room->save();
                    $roomArr = [
                        'SuiteId' => 0,
                        'SuiteStatus' => 0,
                        'SuiteNumber' => 0
                    ];
                    $resp = Http::patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $roomArr);
                }
            }
            $suite->delete();
            DB::commit();
            $response = Http::delete($this->firebaseUrl . '/' . $this->projectName . '/B' . $suite->Building . '/F' . $suite->Floor . '/S' . $suite->SuiteNumber . '.json');
            return ['result' => 'success', 'delete' => 'suite deleted', 'error' => ''];
        } catch (Exception $e) {
            DB::rollBack();
            return ['result' => 'failed', 'insertedRow' => '', 'error' => $e->getMessage()];
        }
    }

    public function addClientDoorOpen(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'delete' => '', 'error' => $validator->errors()];
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'error' => 'room id is unavailable'];
        }
        try {
            $open = new Doorsopen();
            $open->EmpID = 0;
            $open->JNum = 0;
            $open->Name = 'client';
            $open->Department = 'client';
            $open->Room = $room->RoomNumber;
            $open->Date = date("Y-m-d");
            $open->Time = date("h:i");
            $open->save();
            return ['result' => 'success', 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'error' => $e->getMessage()];
        }
    }

    public function addClientDoorOpenAndOpen(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'tuya_client_id' => 'required',
            'tuya_client_secret' => 'required'
        ]);
        $lockUrl = '/v1.0/token?grant_type=1';
        $tokenUrl = 'https://openapi.tuyaeu.com/v1.0/token?grant_type=1';
        $stringToSign = "GET" . "\n" . "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855" . "\n" . "\n" . $lockUrl;
        if ($validator->fails()) {
            return ['result' => 'failed', 'delete' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'error' => 'room id is unavailable'];
        }
        try {
            $open = new Doorsopen();
            $open->EmpID = 0;
            $open->JNum = 0;
            $open->Name = 'client';
            $open->Department = 'client';
            $open->Room = $room->RoomNumber;
            $open->Date = date("Y-m-d");
            $open->Time = date("h:i");
            $open->save();
            $time = intval(microtime(true) * 1000);
            $sign = $request->tuya_client_id . $time . $stringToSign;
            $s = strtoupper(hash_hmac("sha256", $sign, $request->tuya_client_secret));
            $resp = Http::get($tokenUrl, ['client_id' => $request->tuya_client_id, 't' => $time, 'sign_method' => 'HMAC-SHA256', 'sign' => $s]);

            return $resp; //['result'=>'success','token'=>$resp,'error'=>''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'error' => $e->getMessage()];
        }
    }

    public function addUserDoorOpen(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'user_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'delete' => '', 'error' => $validator->errors()];
        }
        date_default_timezone_set('Asia/Riyadh');
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'error' => 'room id is unavailable'];
        }
        $user = Serviceemployee::find($request->user_id);
        if ($user == null) {
            return ['result' => 'failed', 'user' => '', 'error' => 'user id ' . $request->user_id . ' is unavailable'];
        }
        try {
            $open = new Doorsopen();
            $open->EmpID = $user->id;
            $open->JNum = $user->jobNumber;
            $open->Name = $user->name;
            $open->Department = $user->department;
            $open->Room = $room->RoomNumber;
            $open->Date = date("Y-m-d");
            $open->Time = date("h:i");
            $open->save();
            return ['result' => 'success', 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'error' => $e->getMessage()];
        }
    }

    public function updateProjectPassword(Request $request)
    {
        $validator = validator::make($request->all(), [
            'new_password' => 'required',
            'old_password' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'delete' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'error' => 'you are un authorized user'];
        }
        $pv = Projectsvariable::find(1);
        if ($pv == null) {
            return ['result' => 'failed', 'error' => 'no project variables '];
        }
        if (password_verify($request->old_password, $pv->projectPassword)) {
            $pass = password_hash($request->input('new_password'), PASSWORD_DEFAULT);
            $pv->projectPassword = $pass;
            $pv->save();
            return ['result' => 'success', 'error' => ''];
        } else {
            return ['result' => 'failed', 'error' => 'old password is invailed'];
        }
    }

    // ___________________________________________________________________

    static function addFloor($building_id, $floor_number)
    {
        $floor = new Floor();
        $floor->building_id = $building_id;
        $floor->floorNumber = $floor_number;
        $res = $floor->save();
        return $floor;
    }

    static function addRoom($RoomNumber, $building, $building_id, $floor, $floorId)
    {
        $room = new Room();
        $room->RoomNumber = $RoomNumber;
        $room->hotel = 1;
        $room->Building = $building;
        $room->building_id = $building_id;
        $room->Floor = $floor;
        $room->floor_id = $floorId;
        if ($room->save()) {
            return $room;
        }
        return null;
    }

    static function deleteFloor($id)
    {
        $floor = Floor::find($id);
        $res = $floor->delete();
        return $res;
    }

    public function addRoomToFirebase(Room $room)
    {
        $arrRoom = [
            'id' => $room->id,
            'RoomNumber' => $room->RoomNumber,
            'Status' => 0,
            'hotel' => $room->hotel,
            'Building' => $room->Building,
            'building_id' => $room->building_id,
            'Floor' => $room->Floor,
            'floor_id' => $room->floor_id,
            'RoomType' => '',
            'SuiteStatus' => 0,
            'SuiteNumber' => 0,
            'SuiteId' => 0,
            'ReservationNumber' => 0,
            'roomStatus' => 1,
            'Tablet' => 0,
            'dep' => '',
            'Cleanup' => 0,
            'Laundry' => 0,
            'RoomService' => 0,
            'RoomServiceText' => '',
            'Checkout' => 0,
            'Restaurant' => 0,
            'MiniBarCheck' => 0,
            'Facility' => '',
            'SOS' => 0,
            'DND' => 0,
            'PowerSwitch' => 0,
            'DoorSensor' => 0,
            'MotionSensor' => 0,
            'Thermostat' => 0,
            'ZBGateway' => 0,
            'online' => 0,
            'CurtainSwitch' => 0,
            'ServiceSwitch' => 0,
            'lock' => 0,
            'Switch1' => 0,
            'Switch2' => 0,
            'Switch3' => 0,
            'Switch4' => 0,
            'LockGateway' => '',
            'LockName' => '',
            'powerStatus' => 0,
            'curtainStatus' => 0,
            'doorStatus' => 0,
            'DoorWarning' => 0,
            'temp' => 0,
            'TempSetPoint' => 25,
            'SetPointInterval' => 1,
            'CheckInModeTime' => 1,
            'CheckOutModeTime' => 1,
            'WelcomeMessage' => 'welcome G* ',
            'Logo' => '',
            'token' => ''
        ];
        $response = Http::retry(3, 100)->put($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
        return $response->successful();
    }

    public function addControlDeviceToFirebase(Serverdevice $d)
    {
        $arrRoom = [
            'id' => $d->id,
            'name' => $d->name,
            'roomsIds' => $d->roomsIds,
            'status' => $d->status,
            'token' => $d->token
        ];
        $response = Http::retry(3, 100)->put($this->firebaseUrl . '/' . $this->projectName . 'ServerDevices/' . $d->name . '/' . '.json', $arrRoom);
        return $response->successful();
    }

    function addBuildingToFirebase(Building $b, array $floors)
    {
        $bArr = $this->putFloorsOfBuilding($floors);
        $resp = Http::retry(3, 100)->put($this->firebaseUrl . '/' . $this->projectName . '/B' . $b->buildingNo . '.json', $bArr);
        return $resp->successful();
    }

    function addFloorToFirebase(Floor $f)
    {
        $building = Building::find($f->building_id);
        if ($building != null) {
            $fArr = $this->copyFloorToArray($f);
            $response = Http::retry(3, 100)->put($this->firebaseUrl . '/' . $this->projectName . '/B' . $building->buildingNo . '/F' . $f->floorNumber . '.json', $fArr);
            return $response->successful();
        }
        return null;
    }

    function addSuiteToDBs(Building $b, Floor $f, String $rooms, String $roomsIds, int $suiteNumber)
    {
        $suite = new Suite();
        $suite->SuiteNumber = $suiteNumber;
        $suite->BuildingId = $b->id;
        $suite->FloorId = $f->id;
        $suite->Rooms = $rooms;
        $suite->RoomsId = $roomsIds;
        $suite->Building = $b->buildingNo;
        $suite->Floor = $f->floorNumber;
        $suite->Status = 0;
        $suite->Hotel = 1;
        if ($suite->save()) {
            $arrRoom = [
                'id' => $suite->id,
                'SuiteNumber' => $suite->SuiteNumber,
                'Rooms' => $suite->Rooms,
                'RoomsId' => $suite->RoomsId,
                'Hotel' => 1,
                'Building' => $suite->Building,
                'BuildingId' => $suite->BuildingId,
                'FloorId' => $suite->FloorId,
                'Floor' => $suite->Floor,
                'Status' => $suite->Status
            ];
            $response = Http::retry(3, 100)->put($this->firebaseUrl . '/' . $this->projectName . '/B' . $suite->Building . '/F' . $suite->Floor . '/S' . $suite->SuiteNumber . '.json', $arrRoom);
            return $suite;
        }
        return false;
    }

    function copyBuildingToArray(Building $b)
    {
        return $bArr = [
            'id' => $b->id,
            'projectId' => $b->projectId,
            'buildingNo' => $b->buildingNo,
            'buildingName' => $b->buildingName,
            'floorsNumber' => $b->floorsNumber
        ];
    }

    function copyFloorToArray(Floor $f)
    {
        return $fArr = [
            'id' => $f->id,
            'building_id' => $f->building_id,
            'floorNumber' => $f->floorNumber,
            'rooms' => $f->rooms
        ];
    }

    function putFloorsOfBuilding(array $floors)
    {
        $arr = array();
        $i = 0;
        foreach ($floors as $fl) {
            $i++;
            $arr['F' . ($i)] = '';
        }
        return $arr;
    }

    function convertRoomToSuiteRoom(Suite $suite, Room $room)
    {
        $room->SuiteStatus = 1;
        $room->SuiteNumber = $suite->SuiteNumber;
        $room->SuiteId = $suite->id;
        if ($room->save()) {
            $arrRoom = [
                'SuiteStatus' => 1,
                'SuiteNumber' => $suite->SuiteNumber,
                'SuiteId' => $suite->id
            ];
            $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . '/B' . $room->Building . '/F' . $room->Floor . '/R' . $room->RoomNumber . '.json', $arrRoom);
            return $response->successful();
        } else {
            return false;
        }
    }

    //___________________________________________________________________
    // get functions

    public function getBuildings()
    {
        return Building::all();
    }

    public function getFloors()
    {
        return Floor::all();
    }

    public function getRooms()
    {
        return Room::all();
    }

    public function getRoomByNumber(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_number' => 'required|numeric|exists:rooms,RoomNumber',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }
        $room = Room::where('RoomNumber', $request->room_number)->first();
        return $room;
    }

    public function getRoomsForControllDevice(Request $request)
    {
        $validator = validator::make($request->all(), [
            'device_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'rooms' => '', 'error' => $validator->errors()];
        }
        $device = Serverdevice::find($request->device_id);
        if ($device == null) {
            return ['result' => 'failed', 'rooms' => '', 'error' => 'device id is unavailable'];
        }
        if ($device->roomsIds == "all") {
            $rooms = Room::all();
            return ['result' => 'success', 'rooms' => $rooms, 'error' => ''];
        } else {
            $rooms = [];
            $roomsArr = explode('-', $device->roomsIds);
            for ($i = 0; $i < count($roomsArr); $i++) {
                $r = Room::find($roomsArr[$i]);
                if ($r != null) {
                    array_push($rooms, $r);
                }
            }
            if (count($rooms) > 0) {
                return ['result' => 'success', 'rooms' => $rooms, 'error' => ''];
            } else {
                return ['result' => 'failed', 'rooms' => '', 'error' => 'no rooms'];
            }
        }
    }

    public function getSuites()
    {
        return Suite::all();
    }

    public function getRoomTypes()
    {
        return Roomtype::all();
    }

    public function getProjectVariables(Request $request)
    {
        return Projectsvariable::all()->first();
    }

    public function getFloorRooms(Request $request)
    {
        $validator = validator::make($request->all(), [
            'floor_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'rooms' => '', 'error' => $validator->errors()];
        }
        $floor = Floor::find($request->input('floor_id'));
        if ($floor == null) {
            return ['result' => 'failed', 'rooms' => '', 'error' => 'floor id ' . $request->input('floor_id') . ' is unavailable'];
        }
        $rooms = Room::where('floor_id', $request->input('floor_id'))->get();
        if ($rooms == null || count($rooms) == 0) {
            return ['result' => 'failed', 'rooms' => '', 'error' => 'no rooms registered in floor number ' . $floor->floorNumber];
        }
        return ['result' => 'success', 'rooms' => $rooms, 'error' => ''];
    }

    public function getFloorSuites(Request $request)
    {
        $validator = validator::make($request->all(), [
            'floor_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'suites' => '', 'error' => $validator->errors()];
        }
        $floor = Floor::find($request->input('floor_id'));
        if ($floor == null) {
            return ['result' => 'failed', 'suites' => '', 'error' => 'floor id ' . $request->input('floor_id') . ' is unavailable'];
        }
        $suites = Suite::where('FloorId', $request->input('floor_id'))->get();
        if ($suites == null || count($suites) == 0) {
            return ['result' => 'failed', 'suites' => '', 'error' => 'no suites registered in floor number ' . $floor->floorNumber];
        }
        return ['result' => 'success', 'suites' => $suites, 'error' => ''];
    }

    public function getBuildingFloors(Request $request)
    {
        $validator = validator::make($request->all(), [
            'building_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'floors' => '', 'error' => $validator->errors()];
        }
        $building = Building::find($request->input('building_id'));
        if ($building == null) {
            return ['result' => 'failed', 'floors' => '', 'error' => 'building id ' . $request->input('building_id') . ' is unavailable'];
        }
        $floors = Floor::where('building_id', $building->id)->get();
        if ($floors == null || count($floors) == 0) {
            return ['result' => 'failed', 'floors' => '', 'error' => 'no floors registered in building number ' . $building->buildingNo];
        }
        return ['result' => 'success', 'floors' => $floors, 'error' => ''];
    }

    public function getBuildingRooms(Request $request)
    {
        $validator = validator::make($request->all(), [
            'building_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'rooms' => '', 'error' => $validator->errors()];
        }
        $building = Building::find($request->input('building_id'));
        if ($building == null) {
            return ['result' => 'failed', 'rooms' => '', 'error' => 'building id ' . $request->input('building_id') . ' is unavailable'];
        }
        $rooms = Room::where('building_id', $building->id)->get();
        if ($rooms == null || count($rooms) == 0) {
            return ['result' => 'failed', 'rooms' => '', 'error' => 'no floors registered in building number ' . $building->buildingNo];
        }
        return ['result' => 'success', 'rooms' => $rooms, 'error' => ''];
    }

    public function getServerDevices(Request $request)
    {
        return Serverdevice::all();
    }

    public function getServerDeviceById(Request $request)
    {
        $validator = validator::make($request->all(), [
            'device_id' => 'required|numeric|exists:serverdevices,id',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }
        return Serverdevice::find($request->device_id);
    }

    public function getRoomByRoomNumber(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_number' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'rooms' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'error' => 'you are un authorized user'];
        }
        $room = Room::where('RoomNumber', $request->room_number)->get();
        if ($room = null) {
            return ['result' => 'failed', 'error' => 'room number is unavailable'];
        }
        return ['result' => 'success', 'error' => '', 'room' => $room];
    }

    public function getSuiteById(Request $request)
    {
        $validator = validator::make($request->all(), [
            'id' => 'required|numeric',
        ]);
        $suite = Suite::find($request->id);
        if ($suite == null) {
            return ['result' => 'failed', 'error' => 'suite id is unavailable'];
        }
        return ['result' => 'success', 'error' => '', 'suite' => $suite];
    }

    public function getReservationRooms(Request $request)
    {
        $validator = validator::make($request->all(), [
            'reservation_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'rooms' => '', 'error' => $validator->errors()];
        }
        $booking = Booking::find($request->reservation_id);
        if ($booking == null) {
            return ['result' => 'failed', 'error' => 'reservation is unavailable'];
        }
        $rooms = array();
        if ($booking->RoomOrSuite == 1) {
            $r = Room::where('RoomNumber', $booking->RoomNumber)->first();
            if ($r == null) {
                return ['result' => 'failed', 'error' => 'reservation room is unavailable'];
            }
            array_push($rooms, $r);
        } else if ($booking->RoomOrSuite == 2) {
            $s = Suite::where('SuiteNumber', $booking->RoomNumber)->first();
            if ($s == null) {
                return ['result' => 'failed', 'error' => 'reservation suite is unavailable'];
            }
            $RR = Room::where('SuiteId', $s->id)->get();
            for ($i = 0; $i < count($RR); $i++) {
                array_push($rooms, $RR[$i]);
            }
        }
        if ($booking->MultiRooms == 1) {
            $rrr = explode('-', $booking->AddRoomNumber);
            for ($i = 0; $i < count($rrr); $i++) {
                $R = Room::where('RoomNumber', $rrr[$i])->first();
                array_push($rooms, $R);
            }
        }

        return ['result' => 'success', 'error' => '', 'rooms' => $rooms];
    }

    //___________________________________________________________________
    // modify functions

    public function setRoomLockId(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'lock_id' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'error' => 'room id is unavailable'];
        }
        $room->LockName = $request->lock_id;
        try {
            $room->save();
            return ['result' => 'success', 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'error' => $e->getMessage()];
        }
    }

    public function modifyBuilding(Request $request)
    {
        $validator = validator::make($request->all(), [
            'building_id' => 'required|numeric',
            'building_number' => 'numeric|min:1|unique:buildings,buildingNo',
            'building_name' => 'unique:buildings,buildingName|max:40',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
            return $result;
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'update' => '', 'error' => 'you are un authorized user'];
        }
        $b = Building::find($request->building_id);
        if ($b == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'building id invailed'];
        }
        $b->buildingNo = $request->building_number;
        $b->buildingName = $request->building_name;
        $b->save();
        return ['result' => 'success', 'update' => $b, 'error' => ''];
    }

    public function modifyRoom(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'room_number' => 'required|numeric|min:1|unique:App\Models\Room,RoomNumber',
            'room_type' => '',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'update' => '', 'error' => 'you are unauthorized user'];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room id invailed'];
        }
        $room->RoomNumber = $request->room_number;
        if ($request->room_type != null) {
            $room->RoomType = $request->room_type;
        }

        $room->save();
        return ['result' => 'success', 'update' => $room, 'error' => ''];
    }

    public function modifyRoomPowerSwitchInstalled(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room id invailed'];
        }
        try {
            $room->PowerSwitch = $request->room_status;
            $room->save();
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomFirebaseToken(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'token' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room id invailed'];
        }
        try {
            $room->token = $request->token;
            $room->save();
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomsPowerSwitchInstalled(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_ids' => 'required|string',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $roomsArr = explode("-", $request->room_ids);
        if ($roomsArr == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room ids invailed'];
        }
        try {
            for ($i = 0; $i < count($roomsArr); $i++) {
                $room = Room::find($roomsArr[$i]);
                if ($room != null) {
                    $room->PowerSwitch = $request->room_status;
                    $room->save();
                }
            }
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomDoorSensorInstalled(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room id invailed'];
        }
        try {
            $room->DoorSensor = $request->room_status;
            $room->save();
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomsDoorSensorInstalled(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_ids' => 'required|string',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $roomsArr = explode("-", $request->room_ids);
        if ($roomsArr == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room ids invailed'];
        }
        try {
            for ($i = 0; $i < count($roomsArr); $i++) {
                $room = Room::find($roomsArr[$i]);
                if ($room != null) {
                    $room->DoorSensor = $request->room_status;
                    $room->save();
                }
            }
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomMotionSensorInstalled(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room id invailed'];
        }
        try {
            $room->MotionSensor = $request->room_status;
            $room->save();
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e->getMessage()];
        }
    }

    public function modifyRoomsMotionSensorInstalled(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_ids' => 'required|string',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $roomsArr = explode("-", $request->room_ids);
        if ($roomsArr == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room ids invailed'];
        }
        try {
            for ($i = 0; $i < count($roomsArr); $i++) {
                $room = Room::find($roomsArr[$i]);
                if ($room != null) {
                    $room->MotionSensor = $request->room_status;
                    $room->save();
                }
            }
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomThermostatInstalled(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room id invailed'];
        }
        try {
            $room->Thermostat = $request->room_status;
            $room->save();
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomsThermostatInstalled(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_ids' => 'required|string',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $roomsArr = explode("-", $request->room_ids);
        if ($roomsArr == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room ids invailed'];
        }
        try {
            for ($i = 0; $i < count($roomsArr); $i++) {
                $room = Room::find($roomsArr[$i]);
                if ($room != null) {
                    $room->Thermostat = $request->room_status;
                    $room->save();
                }
            }
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomCurtainInstalled(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room id invailed'];
        }
        try {
            $room->CurtainSwitch = $request->room_status;
            $room->save();
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomsCurtainInstalled(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_ids' => 'required|string',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $roomsArr = explode("-", $request->room_ids);
        if ($roomsArr == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room ids invailed'];
        }
        try {
            for ($i = 0; $i < count($roomsArr); $i++) {
                $room = Room::find($roomsArr[$i]);
                if ($room != null) {
                    $room->CurtainSwitch = $request->room_status;
                    $room->save();
                }
            }
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomGatewayInstalled(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room id invailed'];
        }
        try {
            $room->ZBGateway = $request->room_status;
            $room->save();
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomsGatewayInstalled(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_ids' => 'required|string',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $roomsArr = explode("-", $request->room_ids);
        if ($roomsArr == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room ids invailed'];
        }
        try {
            for ($i = 0; $i < count($roomsArr); $i++) {
                $room = Room::find($roomsArr[$i]);
                if ($room != null) {
                    $room->ZBGateway = $request->room_status;
                    $room->save();
                }
            }
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomServiceSwitchInstalled(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room id invailed'];
        }
        try {
            $room->ServiceSwitch = $request->room_status;
            $room->save();
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomsServiceSwitchInstalled(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_ids' => 'required|string',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $roomsArr = explode("-", $request->room_ids);
        if ($roomsArr == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room ids invailed'];
        }
        try {
            for ($i = 0; $i < count($roomsArr); $i++) {
                $room = Room::find($roomsArr[$i]);
                if ($room != null) {
                    $room->ServiceSwitch = $request->room_status;
                    $room->save();
                }
            }
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomSwitch1Installed(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room id invailed'];
        }
        try {
            $room->Switch1 = $request->room_status;
            $room->save();
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomsSwitch1Installed(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_ids' => 'required|string',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $roomsArr = explode("-", $request->room_ids);
        if ($roomsArr == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room ids invailed'];
        }
        try {
            for ($i = 0; $i < count($roomsArr); $i++) {
                $room = Room::find($roomsArr[$i]);
                if ($room != null) {
                    $room->Switch1 = $request->room_status;
                    $room->save();
                }
            }
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomSwitch2Installed(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room id invailed'];
        }
        try {
            $room->Switch2 = $request->room_status;
            $room->save();
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomsSwitch2Installed(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_ids' => 'required|string',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $roomsArr = explode("-", $request->room_ids);
        if ($roomsArr == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room ids invailed'];
        }
        try {
            for ($i = 0; $i < count($roomsArr); $i++) {
                $room = Room::find($roomsArr[$i]);
                if ($room != null) {
                    $room->Switch2 = $request->room_status;
                    $room->save();
                }
            }
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomSwitch3Installed(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room id invailed'];
        }
        try {
            $room->Switch3 = $request->room_status;
            $room->save();
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomsSwitch3Installed(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_ids' => 'required|string',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $roomsArr = explode("-", $request->room_ids);
        if ($roomsArr == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room ids invailed'];
        }
        try {
            for ($i = 0; $i < count($roomsArr); $i++) {
                $room = Room::find($roomsArr[$i]);
                if ($room != null) {
                    $room->Switch3 = $request->room_status;
                    $room->save();
                }
            }
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomSwitch4Installed(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_id' => 'required|numeric',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $room = Room::find($request->room_id);
        if ($room == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room id invailed'];
        }
        try {
            $room->Switch4 = $request->room_status;
            $room->save();
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomsSwitch4Installed(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_ids' => 'required|string',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $roomsArr = explode("-", $request->room_ids);
        if ($roomsArr == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room ids invailed'];
        }
        try {
            for ($i = 0; $i < count($roomsArr); $i++) {
                $room = Room::find($roomsArr[$i]);
                if ($room != null) {
                    $room->Switch4 = $request->room_status;
                    $room->save();
                }
            }
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomsSwitch5Installed(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_ids' => 'required|string',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $roomsArr = explode("-", $request->room_ids);
        if ($roomsArr == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room ids invailed'];
        }
        try {
            for ($i = 0; $i < count($roomsArr); $i++) {
                $room = Room::find($roomsArr[$i]);
                if ($room != null) {
                    $room->Switch5 = $request->room_status;
                    $room->save();
                }
            }
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }
    public function modifyRoomsSwitch6Installed(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_ids' => 'required|string',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $roomsArr = explode("-", $request->room_ids);
        if ($roomsArr == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room ids invailed'];
        }
        try {
            for ($i = 0; $i < count($roomsArr); $i++) {
                $room = Room::find($roomsArr[$i]);
                if ($room != null) {
                    $room->Switch6 = $request->room_status;
                    $room->save();
                }
            }
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }
    public function modifyRoomsSwitch7Installed(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_ids' => 'required|string',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $roomsArr = explode("-", $request->room_ids);
        if ($roomsArr == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room ids invailed'];
        }
        try {
            for ($i = 0; $i < count($roomsArr); $i++) {
                $room = Room::find($roomsArr[$i]);
                if ($room != null) {
                    $room->Switch7 = $request->room_status;
                    $room->save();
                }
            }
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }
    public function modifyRoomsSwitch8Installed(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_ids' => 'required|string',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $roomsArr = explode("-", $request->room_ids);
        if ($roomsArr == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room ids invailed'];
        }
        try {
            for ($i = 0; $i < count($roomsArr); $i++) {
                $room = Room::find($roomsArr[$i]);
                if ($room != null) {
                    $room->Switch8 = $request->room_status;
                    $room->save();
                }
            }
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyRoomsLockInstalled(Request $request)
    {
        $validator = validator::make($request->all(), [
            'room_ids' => 'required|string',
            'room_status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'update' => '', 'error' => $validator->errors()];
        }
        $roomsArr = explode("-", $request->room_ids);
        if ($roomsArr == null) {
            return ['result' => 'failed', 'update' => '', 'error' => 'room ids invailed'];
        }
        try {
            for ($i = 0; $i < count($roomsArr); $i++) {
                $room = Room::find($roomsArr[$i]);
                if ($room != null) {
                    $room->lock = $request->room_status;
                    $room->save();
                }
            }
            return ['result' => 'success', 'update' => $room, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'update' => '', 'error' => $e];
        }
    }

    public function modifyServerDeviceActive(Request $request)
    {
        $validator = validator::make($request->all(), [
            'device_id' => 'required|numeric',
            'new_status' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'status' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'status' => '', 'error' => 'you are unauthorized user'];
        }
        $device = Serverdevice::find($request->device_id);
        if ($device == null) {
            return ['result' => 'failed', 'status' => '', 'error' => 'device id invailed'];
        }
        $device->status = $request->new_status;
        try {
            $device->save();
            return ['result' => 'success', 'status' => $device, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'status' => '', 'error' => $e];
        }
    }

    public function modifyServerDeviceRooms(Request $request)
    {
        $validator = validator::make($request->all(), [
            'device_id' => 'required|numeric',
            'new_rooms' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'status' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'status' => '', 'error' => 'you are unauthorized user'];
        }
        $device = Serverdevice::find($request->device_id);
        if ($device == null) {
            return ['result' => 'failed', 'status' => '', 'error' => 'device id invailed'];
        }
        $device->roomsIds = $request->new_rooms;
        try {
            $device->save();
            $this->modifyControlDeviceRoomsFirebase($device, $request->new_rooms);
            return ['result' => 'success', 'status' => $device, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'status' => '', 'error' => $e->getMessage()];
        }
    }

    public function modifyServerDeviceFirebaseToken(Request $request)
    {
        $validator = validator::make($request->all(), [
            'device_id' => 'required|numeric',
            'token' => 'required|string',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'status' => '', 'error' => $validator->errors()];
        }
        $device = Serverdevice::find($request->device_id);
        if ($device == null) {
            return ['result' => 'failed', 'status' => '', 'error' => 'device id invailed'];
        }
        $device->token = $request->token;
        try {
            $device->save();
            $this->modifyControlDeviceFirebaseToken($device, $request->token);
            return ['result' => 'success', 'status' => $device, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'status' => '', 'error' => $e];
        }
    }

    public function modifyServerDeviceFirebaseStatus(Request $request)
    {
        $validator = validator::make($request->all(), [
            'device_id' => 'required|numeric',
            'status' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'status' => '', 'error' => $validator->errors()];
        }
        $device = Serverdevice::find($request->device_id);
        if ($device == null) {
            return ['result' => 'failed', 'status' => '', 'error' => 'device id invailed'];
        }
        $device->status = $request->status;
        try {
            $device->save();
            $this->modifyControlDeviceFirebaseStatus($device, $request->status);
            return ['result' => 'success', 'status' => $device, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'status' => '', 'error' => $e];
        }
    }


    //________________________

    public function modifyControlDeviceFirebaseToken(Serverdevice $d, $token)
    {
        $arrRoom = [
            'token' => $token
        ];
        $response = Http::patch($this->firebaseUrl . '/' . $this->projectName . 'ServerDevices/' . $d->name . '/' . '.json', $arrRoom);
        return $response->successful();
    }

    public function modifyControlDeviceFirebaseStatus(Serverdevice $d, $status)
    {
        $arrRoom = [
            'status' => $status
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ServerDevices/' . $d->name . '/' . '.json', $arrRoom);
        return $response->successful();
    }

    public function modifyControlDeviceRoomsFirebase(Serverdevice $d, $ids)
    {
        $arrRoom = [
            'roomsIds' => $ids
        ];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ServerDevices/' . $d->name . '/' . '.json', $arrRoom);
        return $response->successful();
    }

    public function hmacSha265(Request $request)
    {
        $validator = validator::make($request->all(), [
            'value' => 'required',
            'key' => 'required',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'delete' => '', 'error' => $validator->errors()];
        }

        $res = strtoupper(hash_hmac('sha256', $request->value, $request->key, false));
        return $res;
    }

    public function getTuyaToken(Request $request)
    {
        $validator = validator::make($request->all(), [
            'signed' => 'required',
            'time' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'delete' => '', 'error' => $validator->errors()];
        }
        $signed = $request->signed;
        $t = $request->time;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://openapi.tuyaeu.com/v1.0/token?grant_type=1',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'client_id: ' . $this->client_id,
                't: ' . $t,
                'sign_method: HMAC-SHA256',
                'sign: ' . $signed
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
    public function getTuyaTicket(Request $request)
    {

        $validator = validator::make($request->all(), [
            'signed' => 'required',
            'time' => 'required',
            'token' => 'required',
            'device_id' => 'required'
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'delete' => '', 'error' => $validator->errors()];
        }

        $signed = $request->signed;
        $t = $request->time;
        $token = $request->token;
        $deviceId = $request->device_id;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://openapi.tuyaeu.com/v1.0/devices/' . $deviceId . '/door-lock/password-ticket',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'client_id: ' . $this->client_id,
                't: ' . $t,
                'sign_method: HMAC-SHA256',
                'sign: ' . $signed,
                'access_token: ' . $token
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
    public function unlockWithoutPassword(Request $request)
    {

        $validator = validator::make($request->all(), [
            'sign' => 'required',
            'time' => 'required',
            'token' => 'required',
            'device_id' => 'required',
            'ticket_id' => 'required'
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'delete' => '', 'error' => $validator->errors()];
        }

        $sign = $request->sign;
        $t = $request->time;
        $token = $request->token;
        $deviceId = $request->device_id;

        $jsonData = json_encode(["ticket_id" => $request->ticket_id]);

        $headers = [
            "Content-Type: application/json",
            "client_id: " . $this->client_id,
            "t: $t",
            "sign_method: HMAC-SHA256",
            "sign: $sign",
            "access_token: $token"
        ];

        $url = "https://openapi.tuyaeu.com/v1.0/devices/" . $deviceId . "/door-lock/password-free/open-door";

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }


    public static function getRoomsByStringIds(string $stringIds)
    {
        $ids = array();
        $rooms = array();
        if ($stringIds != 0) {
            $ids = explode('-', $stringIds);
        }
        for ($i = 0; $i < count($ids); $i++) {
            if (!empty($ids[$i])) {
                $r = Room::find($ids[$i]);
                $rooms[$i] = $r;
            }
        }
        return $rooms;
    }
    public static function getRoomsNumbersByStringIds(string $stringIds)
    {
        $addRoomsNumber = '';
        $rooms = roomsManagement::getRoomsByStringIds($stringIds);
        for ($i = 0; $i < count($rooms); $i++) {
            if ($rooms[$i] != null) {
                if ($i == 0) {
                    $addRoomsNumber = $rooms[$i]->RoomNumber;
                } else {
                    $addRoomsNumber = $addRoomsNumber . '-' . $rooms[$i]->RoomNumber;
                }
            }
        }
        return $addRoomsNumber;
    }
    public static function isAnyRoomReserved($rooms)
    {
        $st = false;
        for ($i = 0; $i < count($rooms); $i++) {
            if ($rooms[$i]->roomStatus > 2) {
                $st = true;
                break;
            }
        }
        return $st;
    }
}
