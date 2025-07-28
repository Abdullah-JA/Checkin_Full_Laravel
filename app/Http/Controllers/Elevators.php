<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Elevator;
use App\Models\ElevatorPermissions;
use App\Models\ElevatorFloors;
use App\Models\Building;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Exception;

class Elevators extends Controller
{
    public function addElevator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|numeric',
            'buildingName' => 'required|string',
            'buildingNumber' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'project' => '', 'error' => $validator->errors()];
            return $result;
        }
        $checkElvNumber = Elevator::where('buildingNumber', $request->buildingNumber)->where('number', $request->number)->exists();
        if ($checkElvNumber) {
            return ['result' => 'error', 'error' => 'The elevator number already exists in this building.'];
        }
        $elv = Elevator::create($validator->validated());
        return ['result' => 'success', 'error' => '', 'elevator' => $elv];
    }

    public function updateElevator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:elevators,id',
            'number' => 'required|unique:elevators,number,' . $request->id,
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'project' => '', 'error' => $validator->errors()];
        }

        $elv = Elevator::find($request->id);
        if (!$elv) {
            return ['result' => 'failed', 'error' => 'Elevator not found'];
        }
        $elv->number = $request->number;
        $elv->save();
        return ['result' => 'success', 'error' => ''];
    }

    public function deleteElevator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:elevators,id',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'project' => '', 'error' => $validator->errors()];
        }
        $elv = Elevator::find($request->id);
        if (!$elv) {
            return ['result' => 'failed', 'error' => 'Elevator not found'];
        }
        DB::beginTransaction();
        try {
            $elvPerm = ElevatorPermissions::where('elevator_id', '=', $request->id)->delete();
            $elv->delete();
            DB::commit();
            return ['result' => 'success', 'error' => ''];
        } catch (Exception $e) {
            DB::rollBack();
            return ['result' => 'error', 'error' => 'Something went wrong. Please try again.'];
        }
    }

    public function getElevators(Request $request)
    {
        return Elevator::all();
    }

    public function getElevatorsForApp()
    {
        $elevatorsRaw = Elevator::all();

        if ($elevatorsRaw->isEmpty()) {
            return [
                'result' => 'empty',
                'error' => 'There are no elevators defined in the system.',
                'permissions' => [],
                'elevators' => [],
                'buildings' => []
            ];
        }

        $allPermissions = ElevatorFloors::all();
        $elevators = $elevatorsRaw->map(function ($elevator) {
            $permissionIds = ElevatorPermissions::where('elevator_id', $elevator->id)->pluck('permission_id');
            $permissions = ElevatorFloors::whereIn('id', $permissionIds)->select('id', 'code')->get();
            return [
                'id' => $elevator->id,
                'number' => $elevator->number,
                'buildingName' => $elevator->buildingName,
                'buildingNumber' => $elevator->buildingNumber,
                'permissions' => $permissions,
            ];
        });

        $buildings = Building::select("id", "buildingNo", "buildingName")->get();

        return [
            'result' => 'success',
            'error' => '',
            'permissions' => $allPermissions,
            'elevators' => $elevators,
            'buildings' => $buildings
        ];
    }

    public function addElevatorPermissions(Request $request)
    {
        if (is_array($request->permission_id) && count($request->permission_id) > 0) {
            $validator = Validator::make($request->all(), [
                'permission_id' => 'required|array',
                'elevator_id' => 'required|exists:elevators,id',
            ]);
            if ($validator->fails()) {
                return ['result' => 'failed', 'error' => $validator->errors()];
            }
            DB::beginTransaction();
            try {
                ElevatorPermissions::where('elevator_id', $request->elevator_id)->delete();
                $data = [];
                foreach ($request->permission_id as $permID) {
                    $data[] = [
                        'elevator_id' => $request->elevator_id,
                        'permission_id' => $permID,
                    ];
                }
                ElevatorPermissions::insert($data);
                DB::commit();
                return ['result' => 'success', 'error' => ''];
            } catch (Exception $e) {
                DB::rollBack();
                return ['result' => 'error', 'error' => 'Something went wrong. Please try again.'];
            }
        } else {
            $validator = Validator::make($request->all(), [
                'elevator_id' => 'required|exists:elevators,id',
            ]);
            if ($validator->fails()) {
                return ['result' => 'failed', 'error' => $validator->errors()];
            }
            ElevatorPermissions::where('elevator_id', $request->elevator_id)->delete();
            return ['result' => 'success', 'error' => ''];
        }
    }

    public function getElevatorPermissionByElevatorId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'elevator_id' => 'required|exists:elevators,id',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }
        $elevatorFloors = ElevatorFloors::all();
        $permissionIds = ElevatorPermissions::where('elevator_id', $request->elevator_id)->pluck('permission_id');
        $permissions = ElevatorFloors::whereIn('id', $permissionIds)->get(['id', 'code']);
        return ['result' => 'success', 'error' => '', 'permissions' => $permissions, 'elevatorFloors' => $elevatorFloors];
    }

    public function editElevatorPermissions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_elevator' => 'required|exists:elevators,id',
            'permission' => 'required',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'project' => '', 'error' => $validator->errors()];
        }
        $elvPermission = ElevatorPermissions::where('id_elevator', $request->id_elevator)->first();
        if (!$elvPermission) {
            return ['result' => 'failed', 'error' => 'Elevator permissions not found'];
        }
        $elvPermission->permission = $request->permission;
        $elvPermission->save();
        return ['result' => 'success', 'error' => ''];
    }

    public function getElevatorFloors()
    {
        return ElevatorFloors::all();
    }

    public function testDelete()
    {
        $te = ElevatorPermissions::all();
        return $te;
    }
}
