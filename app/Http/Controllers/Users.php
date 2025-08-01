<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Serviceemployee;
use App\Models\Projectsvariable;
use App\Models\Room;
use Illuminate\Support\Facades\Http;
use Exception;
use App\Models\GuestCategory;
use App\Models\OtherFeatures;
use App\Models\StayRason;
use App\Models\BookingSources;
use App\Models\TaxName;
use App\Models\Penaltie;
use App\Models\BookingTaxe;
use App\Models\Pricingplan;
use App\Models\RoomtypePricingplan;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class Users extends Controller
{
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
        if (password_verify($request->input('password'), $employee->password)) {
            $myToken = $employee->createToken('token')->plainTextToken; //Users::makeToken();
            $employee->mytoken = $myToken;
            $employee->logedin = 1;
            try {
                $employee->save();
                $this->modifyUserMyTokenInFirebase($employee);
                $this->setLogedinUserInFirebase($employee, 1);
                return ['result' => 'success', 'my_token' => $employee->mytoken, 'user' => $employee, 'error' => ''];
            } catch (Exception $e) {
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

    public function addGuestCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'NameCategoryAr' => 'required|string|max:50',
            'NameCategoryEn' => 'required|string|max:50',
            'DiscountType' => 'required|in:1,2,3',
            'DiscountValue' => 'nullable|numeric',
            'OtherFeaturesIds' => 'nullable|string',
            'FacilityIds' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'category' => '', 'error' => $validator->errors()];
        }
        $category = GuestCategory::create([
            'NameCategoryAr' => $request->NameCategoryAr,
            'NameCategoryEn' => $request->NameCategoryEn,
            'DiscountType' => $request->DiscountType,
            'DiscountValue' => $request->DiscountValue ?? 0,
            'OtherFeaturesIds' => $request->OtherFeaturesIds,
            'FacilityIds' => $request->FacilityIds,
        ]);
        return ['result' => 'success', 'category' => $category, 'error' => ''];
    }

    public function updateGuestCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:guestcategorys,id',
            'NameCategoryAr' => 'nullable|string|max:50',
            'NameCategoryEn' => 'nullable|string|max:50',
            'DiscountType' => 'nullable|in:1,2,3',
            'DiscountValue' => 'nullable|numeric',
            'OtherFeaturesIds' => 'nullable|string',
            'FacilityIds' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'category' => '', 'error' => $validator->errors()];
        }
        $category = GuestCategory::find($request->id);
        $category->update($request->only([
            'NameCategoryAr',
            'NameCategoryEn',
            'DiscountType',
            'DiscountValue',
            'OtherFeaturesIds',
            'FacilityIds',
        ]));
        return ['result' => 'success', 'error' => ''];
    }

    public function deleteGuestCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:guestcategorys,id',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'category' => '', 'error' => $validator->errors()];
        }

        try {
            GuestCategory::find($request->id)->delete();
            return ['result' => 'success', 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'error' => $e->getMessage()];
        }
    }

    public function getGuestCategory()
    {
        return GuestCategory::all();
    }

    public function addOtherFeature(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'NameAr' => 'required|string|max:255',
            'NameEn' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'feature' => '', 'error' => $validator->errors()];
        }

        $feature = OtherFeatures::create([
            'NameAr' => $request->NameAr,
            'NameEn' => $request->NameEn,
        ]);

        return ['result' => 'success', 'feature' => $feature, 'error' => ''];
    }

    public function updateOtherFeature(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:otherfeatures,id',
            'NameAr' => 'nullable|string|max:255',
            'NameEn' => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'feature' => '', 'error' => $validator->errors()];
        }

        $feature = OtherFeatures::find($request->id);
        $feature->update($request->only(['NameAr', 'NameEn']));

        return ['result' => 'success', 'error' => ''];
    }

    public function deleteOtherFeature(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:otherfeatures,id',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'feature' => '', 'error' => $validator->errors()];
        }

        try {
            OtherFeatures::find($request->id)->delete();
            return ['result' => 'success', 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'error' => $e->getMessage()];
        }
    }

    public function getOtherFeatures()
    {
        return OtherFeatures::all();
    }

    public function addStayReason(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'NameAr' => 'required|string|max:255',
            'NameEn' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'reason' => '', 'error' => $validator->errors()];
        }

        $reason = StayRason::create([
            'NameAr' => $request->NameAr,
            'NameEn' => $request->NameEn,
        ]);

        return ['result' => 'success', 'reason' => $reason, 'error' => ''];
    }

    public function updateStayReason(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:stayreasons,id',
            'NameAr' => 'nullable|string|max:255',
            'NameEn' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'reason' => '', 'error' => $validator->errors()];
        }

        $reason = StayRason::find($request->id);
        $reason->update($request->only(['NameAr', 'NameEn']));

        return ['result' => 'success', 'error' => ''];
    }

    public function deleteStayReason(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:stayreasons,id',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'reason' => '', 'error' => $validator->errors()];
        }

        try {
            StayRason::find($request->id)->delete();
            return ['result' => 'success', 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'error' => $e->getMessage()];
        }
    }

    public function getStayReasons()
    {
        return StayRason::all();
    }

    public function addBookingSource(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'NameAr' => 'required|string|max:255',
            'NameEn' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'source' => '', 'error' => $validator->errors()];
        }

        $source = BookingSources::create([
            'NameAr' => $request->NameAr,
            'NameEn' => $request->NameEn,
        ]);

        return ['result' => 'success', 'source' => $source, 'error' => ''];
    }

    public function updateBookingSource(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:bookingsources,id',
            'NameAr' => 'nullable|string|max:255',
            'NameEn' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'source' => '', 'error' => $validator->errors()];
        }

        $source = BookingSources::find($request->id);
        $source->update($request->only(['NameAr', 'NameEn']));

        return ['result' => 'success', 'error' => ''];
    }

    public function deleteBookingSource(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:bookingsources,id',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'source' => '', 'error' => $validator->errors()];
        }

        try {
            BookingSources::find($request->id)->delete();
            return ['result' => 'success', 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'failed', 'error' => $e->getMessage()];
        }
    }

    public function getBookingSources()
    {
        return BookingSources::all();
    }

    public function addTaxName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:0,1', // 0 => fixed amount, 1 => percentage
            'optional' => 'required|in:0,1', // 1=>Required 0=>NonRequired
            'value' => 'required|numeric|min:0',
            'name_ar' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'my_token' => 'required'
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }

        if (Users::checkAuth($request->input('my_token'))) {
            // $tax = new TaxName();
            // $tax->type = $request->type;
            // $tax->value = $request->value;
            // $tax->name_ar = $request->name_ar;
            // $tax->name_en = $request->name_en;
            // $tax->save();
            $tax = TaxName::create($request->all());
            return ['result' => 'success', 'tax_name' => $tax];
        } else {
            return ['result' => 'failed', 'tax_name' => '', 'error' => 'you are un authorized user'];
        }
    }

    public function getTaxNames(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_token' => 'required'
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token'))) {
            $taxes = TaxName::all();
            return ['result' => 'success', 'tax_names' => $taxes];
        } else {
            return ['result' => 'failed', 'tax_names' => '', 'error' => 'you are un authorized user'];
        }
    }

    public function deleteTaxName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:taxnames,id',
            'my_token' => 'required'
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }

        if (Users::checkAuth($request->input('my_token'))) {
            $tax = TaxName::find($request->id);
            $tax->delete();
            return ['result' => 'success', 'message' => 'Tax name deleted successfully'];
        } else {
            return ['result' => 'failed', 'error' => 'you are un authorized user'];
        }
    }

    public function addPenaltie(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:0,1', // 0 => fixed amount, 1 => percentage
            'value' => 'required|numeric|min:0',
            'name_ar' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'my_token' => 'required'

        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }

        if (Users::checkAuth($request->input('my_token'))) {
            $penaltie = new Penaltie();
            $penaltie->type = $request->type;
            $penaltie->value = $request->value;
            $penaltie->name_ar = $request->name_ar;
            $penaltie->name_en = $request->name_en;
            $penaltie->save();

            return ['result' => 'success', 'penaltie' => $penaltie];
        } else {
            return ['result' => 'failed', 'penaltie' => '', 'error' => 'you are un authorized user'];
        }
    }

    public function getPenalties(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_token' => 'required'
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }
        if (Users::checkAuth($request->input('my_token'))) {
            $penalties = Penaltie::all();
            return ['result' => 'success', 'penalties' => $penalties];
        } else {
            return ['result' => 'failed', 'penalties' => '', 'error' => 'you are un authorized user'];
        }
    }

    public function deletePenaltie(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:penalties,id',
            'my_token' => 'required'
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }

        if (Users::checkAuth($request->input('my_token'))) {
            $penalty = Penaltie::find($request->id);
            $penalty->delete();
            return ['result' => 'success', 'message' => 'Penalty deleted successfully'];
        } else {
            return ['result' => 'failed', 'error' => 'you are un authorized user'];
        }
    }

    public function createUserReception(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'jobNumber' => 'required|integer|unique:users,jobNumber',
            'password' => 'required|string|min:6',
            'mobile' => 'required|string|unique:users,mobile',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }

        $user = new User();
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->jobNumber = $request->jobNumber;
        $user->password   = password_hash($request->password, PASSWORD_DEFAULT);
        $user->mobile = $request->mobile;
        $user->mytoken = Users::makeToken();
        $user->active = 0;
        $user->save();
        return ['result' => 'success', 'user' => $user];
    }

    public function updateUserinfo(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id',
            'mobile' => 'required|numeric|unique:users,mobile,' . $request->id,
        ]);
        $user = User::find($request->id);
        if (!$user) {
            return ['result' => 'failed', 'error' => 'User not found'];
        }
        $user->mobile = $request->mobile;
        $user->save();
        return ['result' => 'success', 'user' => 'updated successfully'];
    }

    public function deleteUserReception(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }

        $user = User::find($request->id);
        $user->active = 1;
        $user->save();
        return ['result' => 'success', 'message' => 'delete successfully'];
    }

    public function updateUserPasswordReception(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
            'old_password' => 'required|string',
            'new_password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }

        $user = User::find($request->id);

        if (!Hash::check($request->old_password, $user->password)) {
            return ['result' => 'failed', 'message' => 'Current password is incorrect'];
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return ['result' => 'success', 'message' => 'Password updated successfully'];
    }

    public function addPermissionUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'permission_id' => 'required|array',
            'user_id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }
        DB::beginTransaction();
        try {
            UserPermission::where('UserId', $request->user_id)->delete();
            $data = [];
            foreach ($request->permission_id as $permID) {
                $data[] = [
                    'UserId' => $request->user_id,
                    'PermissionId' => $permID,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            UserPermission::insert($data);
            DB::commit();
            return ['result' => 'success', 'error' => ''];
        } catch (Exception $e) {
            DB::rollBack();
            return ['result' => 'error', 'error' => 'Something went wrong. Please try again.'];
        }
    }

    public function addBookingTax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'tax_id' => 'required|exists:taxnames,id',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }

        try {
            BookingTaxe::create([
                'BookingId' => $request->booking_id,
                'TaxId' => $request->tax_id,
            ]);
            return ['result' => 'success', 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'error', 'error' => 'Something went wrong. Please try again.'];
        }
    }

    public function addPricingPlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'NameAr' => 'required|string|max:255',
            'NameEn' => 'required|string|max:255',
            'StartDate' => 'required|date',
            'EndDate' => 'required|date|after_or_equal:StartDate',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }

        try {
            Pricingplan::create([
                'NameAr' => $request->NameAr,
                'NameEn' => $request->NameEn,
                'StartDate' => $request->StartDate,
                'EndDate' => $request->EndDate,
            ]);

            return ['result' => 'success', 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'error', 'error' => 'Something went wrong. Please try again.' . $e];
        }
    }

    public function getAllPricingplans()
    {
        $plans = Pricingplan::all();
        return ['result' => 'success', 'data' => $plans];
    }

    public function getPricingplansByDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'StartDate' => 'required|date',
            'EndDate' => 'required|date|after_or_equal:StartDate',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }

        try {
            $plans = Pricingplan::where('StartDate', '>=', $request->StartDate)
                ->where('EndDate', '<=', $request->EndDate)
                ->get();

            return ['result' => 'success', 'data' => $plans];
        } catch (Exception $e) {
            return ['result' => 'error', 'error' => 'Something went wrong. Please try again.'];
        }
    }

    public function updatePricingplan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:pricingplans,id',
            'NameAr' => 'sometimes|string|max:255',
            'NameEn' => 'sometimes|string|max:255',
            'StartDate' => 'sometimes|date',
            'EndDate' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }

        try {
            $plan = Pricingplan::find($request->id);
            if (!$plan) {
                return ['result' => 'error', 'error' => 'Pricing plan not found'];
            }

            if ($request->has('NameAr')) $plan->NameAr = $request->NameAr;
            if ($request->has('NameEn')) $plan->NameEn = $request->NameEn;
            if ($request->has('StartDate')) $plan->StartDate = $request->StartDate;
            if ($request->has('EndDate')) $plan->EndDate = $request->EndDate;

            $plan->save();

            return ['result' => 'success', 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'error', 'error' => 'Something went wrong. Please try again.'];
        }
    }

    public function deletePricingplan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:pricingplans,id',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }

        try {
            Pricingplan::where('id', $request->id)->delete();
            return ['result' => 'success', 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'error', 'error' => 'Something went wrong. Please try again.'];
        }
    }

    public function addRoomtypePricingplan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'roomtype_id' => 'required|exists:roomtypes,id',
            'pricingplan_id' => 'required|exists:pricingplans,id',
            'DailyPrice' => 'required|numeric|min:0',
            'MonthlyPrice' => 'required|numeric|min:0',
            'YearlyPrice' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }

        try {
            RoomtypePricingplan::create([
                'roomtype_id' => $request->roomtype_id,
                'pricingplan_id' => $request->pricingplan_id,
                'DailyPrice' => $request->DailyPrice,
                'MonthlyPrice' => $request->MonthlyPrice,
                'YearlyPrice' => $request->YearlyPrice,
            ]);

            return ['result' => 'success', 'error' => ''];
        } catch (Exception $e) {
            return ['result' => 'error', 'error' => 'Something went wrong. Please try again.' . $e];
        }
    }

    public function getRoomtypePricingByDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'roomtype_id' => 'required|exists:roomtypes,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }

        $pricing = RoomtypePricingplan::where('roomtype_id', $request->roomtype_id)
            ->whereHas('pricingplan', function ($query) use ($request) {
                $query->where('StartDate', '<=', $request->start_date)
                    ->where('EndDate', '>=', $request->end_date);
            })->first();

        if (!$pricing) {
            return ['result' => 'not_found', 'error' => 'No active pricing plan found for this date.'];
        }
        // return ['result' => 'success', 'data' => $pricing];
        return ['result' => 'success', 'data' => ['DailyPrice' => $pricing->DailyPrice, 'MonthlyPrice' => $pricing->MonthlyPrice, 'YearlyPrice' => $pricing->YearlyPrice, 'plan_name' => $pricing->pricingplan->NameEn]];
    }
}
