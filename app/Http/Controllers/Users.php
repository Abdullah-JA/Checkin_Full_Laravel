<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Serviceemployee;
use App\Models\Projectsvariable;
use App\Models\Room;
use Illuminate\Support\Facades\Http;
use Exception;
class Users extends Controller
{
    //
    public $firebaseUrl = 'https://checkin-62774-default-rtdb.asia-southeast1.firebasedatabase.app';
    public $projectName = 'apiTest';

    // add
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_number' => 'required',
            'password' => 'required',
            'department' => 'max:40|in:Service,Cleanup,Laundry,RoomService,Restaurant,Reception'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'insertedRow' => null, 'error' => $validator->errors()];
        }
        // login from reception 
        if ($request->input('department') == null || $request->input('department') == "") {
            $employee = Serviceemployee::where('jobNumber', $request->job_number)->first();
            if ($employee == null) {
                return ['result' => 'failed', 'user' => null, 'error' => 'no such user'];
            } else if ($employee->department != "Admin" && $employee->department != "Reception") {
                return ['result' => 'failed', 'user' => null, 'error' => 'not allowed to login to Reception'];
            }
        }
        // login from service application 
        else {
            $employee = Serviceemployee::where('jobNumber', $request->job_number)->where('department', $request->department)->first();
            if ($employee == null) {
                return ['result' => 'failed', 'user' => null, 'error' => 'no such user'];
            } else if ($employee->department == "Admin" || $employee->department == "Reception") {
                return ['result' => 'failed', 'user' => null, 'error' => 'not allowed to login to Service app'];
            }
        }
        // check password 
        if (password_verify($request->input('password'), $employee->password)) 
        {
            $myToken = $employee->createToken('token')->plainTextToken;//Users::makeToken();
            $employee->mytoken = $myToken;
            $employee->logedin = 1;
            try 
            {
                $employee->save();
                $this->modifyUserMyTokenInFirebase($employee);
                $this->setLogedinUserInFirebase($employee, 1);
                return ['result' => 'success', 'my_token' => $employee->mytoken, 'user' => $employee, 'error' => ''];
            } 
            catch(Exception $e) 
            {
                return ['result' => 'failed', 'user' => '', 'error' => 'unable to verify user'];
            }
        } else {
            return ['result' => 'failed', 'user' => '', 'error' => 'invailed password'];
        }
    }

    public function loginProject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_name' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'project' => '', 'error' => $validator->errors()];
            return $result;
        }
        $project = Projectsvariable::where('projectName', $request->project_name)->get()->first();
        if ($project == null) {
            return ['result' => 'failed', 'project' => '', 'error' => 'no such project'];
        }
        if (password_verify($request->input('password'), $project->projectPassword)) {
            $myToken = Users::makeToken();
            return ['result' => 'success', 'token' => $myToken, 'error' => ''];
        } else {
            return ['result' => 'failed', 'project' => '', 'error' => 'invailed password'];
        }
    }

    // add
    public function addUser(Request $request)
    {
        $users = Serviceemployee::all();
        $firstStatus = false;
        if (count($users) > 0) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:100',
                'job_number' => 'required|unique:serviceemployees,jobNumber|numeric|digits_between:3,10',
                'password' => 'required',
                'department' => 'required|max:40',
                'mobile' => 'required',
                'my_token' => 'required'
            ]);
        } else {
            $firstStatus = true;
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:100',
                'job_number' => 'required|unique:serviceemployees,jobNumber|numeric|digits_between:3,10',
                'password' => 'required',
                'department' => 'required|max:40',
                'mobile' => 'required',
            ]);
        }
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'insertedRow' => null, 'error' => $validator->errors()];
            return $result;
        }
        if ($firstStatus = true) {
            $pass = password_hash($request->input('password'), PASSWORD_DEFAULT);
            $myToken = Users::makeToken();
            $serviceemployee = new Serviceemployee();
            $serviceemployee->projectId = 1;
            $serviceemployee->name = $request->input('name');
            $serviceemployee->jobNumber = $request->input('job_number');
            $serviceemployee->password = $pass;
            $serviceemployee->department = $request->input('department');
            $serviceemployee->mobile = $request->input('mobile');
            $serviceemployee->mytoken = $myToken;
            $serviceemployee->token = '';
            $serviceemployee->control = 'all';
            $serviceemployee->logedin = 1;
            try {
                $serviceemployee->save();
                $this->addUserToFIrebase($serviceemployee);
                return ['result' => 'success', 'user' => $serviceemployee->jobNumber . ' ' . $serviceemployee->name, 'error' => ''];
            } catch (Exception $e) {
                return ['result' => 'failed', 'insertedRow' => '', 'error' => 'unable to add user to db ' . $e->getMessage()];
            }
        } else {
            if (Users::checkAuth($request->input('my_token')) == false) {
                return ['result' => 'failed', 'insertedRow' => '', 'error' => 'you are un authorized user'];
            }
            $pass = password_hash($request->input('password'), PASSWORD_DEFAULT);
            $myToken = Users::makeToken();
            $serviceemployee = new Serviceemployee();
            $serviceemployee->projectId = 1;
            $serviceemployee->name = $request->input('name');
            $serviceemployee->jobNumber = $request->input('job_number');
            $serviceemployee->password = $pass;
            $serviceemployee->department = $request->input('department');
            $serviceemployee->mobile = $request->input('mobile');
            $serviceemployee->mytoken = $myToken;
            $serviceemployee->token = '';
            $serviceemployee->control = 'all';
            $serviceemployee->logedin = 1;
            try {
                $serviceemployee->save();
                $this->addUserToFIrebase($serviceemployee);
                return ['result' => 'success', 'user' => $serviceemployee->jobNumber . ' ' . $serviceemployee->name, 'error' => ''];
            } catch (Exception $e) {
                return ['result' => 'failed', 'insertedRow' => '', 'error' => 'unable to add user to db ' . $e->getMessage()];
            }
        }
    }

    public function checkUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'user' => '', 'error' => $validator->errors()];
        }
        $user = Serviceemployee::find($request->user_id);
        if ($user == null) {
            return ['result' => 'failed', 'user' => '', 'error' => 'user id ' . $request->user_id . ' is unavailable'];
        }
        return $user->logedin;
    }

    public function modifyUserFirebaseToken(Request $request)
    {
        $validator = validator::make($request->all(), [
            'id' => 'required|numeric',
            'token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }
        $user = Serviceemployee::find($request->id);
        if ($user == null) {
            return ['result' => 'failed', 'user' => '', 'error' => 'user id ' . $request->id . ' is unavailable'];
        }
        $user->token = $request->token;
        try {
            $user->save();
            return ['result' => 'success', 'token' => $user->token, 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'token' => '', 'error' => $e->getMessage()];
        }
    }

    // add
    public function modifyUser(Request $request)
    {
        $params = [];
        if (empty($request->name) == false && $request->name != 'undefined') {
            $params['name'] = 'max:10';
        }
        if (empty($request->job_number) == false && $request->job_number != 'undefined') {
            $params['job_number'] = 'unique:serviceemployees,jobNumber|numeric|digits_between:3,10';
        }
        if (empty($request->department) == false && $request->department != 'undefined') {
            $params['department'] = 'max:40';
        }
        $params['id'] = 'required';
        $params['my_token'] = 'required';
        $validator = Validator::make($request->all(), $params);
        if ($validator->fails()) {
            return ['result' => 'failed', 'user' => '' . $request->job_number, 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'user' => '', 'error' => 'you are unauthorized user'];
        }
        $User = Serviceemployee::find($request->id);
        if ($User == null) {
            return ['result' => 'failed', 'user' => '', 'error' => 'user id ' . $request->id . ' is unavailable'];
        }
        if ($request->name != null && $request->name != 'undefined') {
            $User->name = $request->name;
        }
        if ($request->job_number != null && $request->job_number != 'undefined') {
            $User->jobNumber = $request->job_number;
        }
        if ($request->department != null && $request->department != 'undefined') {
            $User->department = $request->department;
        }
        if ($request->mobile != null && $request->mobile != 'undefined') {
            $User->mobile = $request->mobile;
        }
        if ($User->save()) {
            $this->modifyUserInFirebase($User);
            return ['result' => 'success', 'user' => $User, 'error' => ''];
        } else {
            return ['result' => 'failed', 'user' => '', 'error' => 'unable to modify user'];
        }
    }


    // add
    public function modifyUserControl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
            'new_rooms' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'user' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'user' => '', 'error' => 'you are unauthorized user'];
        }
        $User = Serviceemployee::find($request->user_id);
        if ($User == null) {
            return ['result' => 'failed', 'user' => '', 'error' => 'user id ' . $request->user_id . ' is unavailable'];
        }
        $User->control = $request->new_rooms;
        if ($User->save()) {
            $this->modifyUserControlInFirebase($User);
            return ['result' => 'success', 'user' => $User, 'error' => ''];
        } else {
            return ['result' => 'failed', 'user' => '', 'error' => 'unable to modify user'];
        }
    }

    // add
    public function deleteUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_number' => 'required|numeric',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'user' => '', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token')) == false) {
            return ['result' => 'failed', 'user' => '', 'error' => 'you are unauthorized user'];
        }
        $jobNumber = $request->input('job_number');
        $user = Serviceemployee::where('jobNumber', $jobNumber)->first();
        if ($user == null) {
            return ['result' => 'failed', 'user' => '', 'error' => 'no users has ' . $jobNumber . ' jobnumber'];
        }
        $user->logedin = 0;
        $user->token = "";
        if ($user->save()) {
            $response = Http::delete($this->firebaseUrl . '/' . $this->projectName . 'ServiceUsers/' . $user->jobNumber . '.json');
            return ['result' => 'success', 'user' => 'user deleted', 'error' => ''];
        }
        return ['result' => 'failed', 'user' => '', 'error' => 'unable to delete user in db'];
    }

    // add
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_number' => 'required|numeric',
            'old_password' => 'required',
            'new_password' => 'required',
            'conf_password' => 'required',
            'my_token' => 'required'
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'update' => null, 'error' => $validator->errors()];
            return $result;
        }
        if (Users::checkAuth($request->input('my_token'))) {
            $employee = Serviceemployee::where('jobNumber', $request->input('job_number'))->first();
            if ($employee == null) {
                return ['result' => 'failed', 'update' => null, 'error' => 'job number is un available'];
            } else {
                if (password_verify($request->input('old_password'), $employee->password)) {
                    if ($request->input('new_password') == $request->input('conf_password')) {
                        $pass = password_hash($request->input('new_password'), PASSWORD_DEFAULT);
                        $employee->password = $pass;
                        $employee->save();
                        return ['result' => 'success', 'updated' => 'updated successfully', 'error' => ''];
                    } else {
                        return ['result' => 'failed', 'update' => '', 'error' => 'new password and confermation password are incompatible'];
                    }
                } else {
                    return ['result' => 'failed', 'update' => '', 'error' => 'old password is incorrect'];
                }
            }
        } else {
            return ['result' => 'failed', 'update' => '', 'error' => 'you are un authorized user'];
        }
    }

    public static function checkAuth($token)
    {
        $user = Serviceemployee::where('myToken', $token)->first();
        if ($user) {
            return TRUE;
        }
        return FALSE;
    }

    public static function makeToken()
    {
        $randomNumber = rand(100, 1000000);
        $myToken = password_hash($randomNumber, PASSWORD_DEFAULT);
        $myToken = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $myToken);
        return $myToken;
    }

    function addUserToFIrebase(Serviceemployee $user)
    {
        $arrUser = $this->convertUserToArray($user);
        $response = Http::retry(3, 100)->put($this->firebaseUrl . '/' . $this->projectName . 'ServiceUsers/' . $user->jobNumber . '.json', $arrUser);
        return $response->successful();
    }

    function convertUserToArray(Serviceemployee $user)
    {
        $arrUser = [
            'id' => $user->id,
            'projectId' => $user->projectId,
            'name' => $user->name,
            'jobNumber' => $user->jobNumber,
            'password' => $user->password,
            'department' => $user->department,
            'mobile' => $user->mobile,
            'token' => $user->token,
            'mytoken' => $user->mytoken,
            'control' => $user->control,
            'logedin' => 0
        ];
        return $arrUser;
    }

    function modifyUserMyTokenInFirebase(Serviceemployee $user)
    {
        $arrUser = ['mytoken' => $user->mytoken];
        $response = Http::patch($this->firebaseUrl . '/' . $this->projectName . 'ServiceUsers/' . $user->jobNumber . '.json', $arrUser);
        return $response->successful();
    }

    function modifyUserControlInFirebase(Serviceemployee $user)
    {
        $arrUser = ['control' => $user->control];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ServiceUsers/' . $user->jobNumber . '.json', $arrUser);
        return $response->successful();
    }

    function modifyUserInFirebase(Serviceemployee $user)
    {
        $arrUser = ['name' => $user->name, 'jobNumber' => $user->jobNumber, 'mobile' => $user->mobile, 'department' => $user->department];
        $response = Http::retry(3, 100)->patch($this->firebaseUrl . '/' . $this->projectName . 'ServiceUsers/' . $user->jobNumber . '.json', $arrUser);
        return $response->successful();
    }

    function setLogedinUserInFirebase(Serviceemployee $user, int $status)
    {
        if ($status == 0 || $status == 1) {
            $arrUser = ['logedin' => $status];
            $response = Http::patch($this->firebaseUrl . '/' . $this->projectName . 'ServiceUsers/' . $user->jobNumber . '.json', $arrUser);
            return $response->successful();
        }
        return null;
    }

    // add
    function getALlUsers(Request $request)
    {
        return Serviceemployee::where('logedin', 1)->get();
    }

    // add
    function getUserById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            $result = ['result' => 'failed', 'insertedRow' => null, 'error' => $validator->errors()];
            return $result;
        }
        return Serviceemployee::find($request->user_id);
    }

    // add
    function getUserRooms(Request $request)
    {
        $validator = validator::make($request->all(), [
            'id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }
        $user = Serviceemployee::find($request->id);
        if ($user == null) {
            return ['result' => 'failed', 'user' => '', 'error' => 'user id ' . $request->id . ' is unavailable'];
        }
        if ($user->control == "all") {
            return Room::all();
        } else {
            $roomsArr = explode("-", $user->control);
            $rrr = Room::all();
            $rooms = array();
            for ($i = 0; $i < count($rrr); $i++) {
                for ($j = 0; $j < count($roomsArr); $j++) {
                    if ($rrr[$i]->RoomNumber == $roomsArr[$j]) {
                        array_push($rooms, $rrr[$i]);
                        break;
                    }
                }
            }
            return $rooms;
        }
    }
}
