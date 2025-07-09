<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\Room;
use App\Models\Booking;
use App\Models\Facilitytype;
use App\Models\Facilityuser;
use App\Models\Restaurantmenue;
use App\Models\Coffeeshopmenue;
use App\Models\Restaurantitem;
use App\Models\Coffeeshopitem;
use App\Models\Laundryitem;
use App\Models\Otherinvoice;
use App\Models\Restaurantorder;
use App\Models\Restaurantorderitem;
use Validator;
use Exception;
use Http;


class Facilitys extends Controller
{
    //
      public $firebaseUrl = 'https://checkin-62774-default-rtdb.asia-southeast1.firebasedatabase.app/';
      public $URL = "https://samples.checkin.ratco-solutions.com/";


    public function addFacility(Request $request) {
      $validator = Validator::make($request->all(),[
        'type_id' => 'required|numeric',
        'type_name' => 'required',
        'name' => 'required|max:100|min:2',
        'control' => 'numeric',
        'photo' => 'required',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        $result = ['result'=>'failed','insertedRow'=>null,'error'=>$validator->errors()];
        return $result ;
      }
      if (Users::checkAuth($request->input('my_token'))) {
        try{
          $facility = new Facility();
          $facility->Hotel = 1 ;
          $facility->TypeId = $request->input('type_id');
          $facility->TypeName = $request->input('type_name');
          $facility->Name = $request->input('name');
          $facility->Control = $request->input('control');
          $image_path = $request->file('photo')->store('images','public');
          $facility->photo = $this->URL.$image_path;
          $facility->save();
          $fuser = new Facilityuser();
          $fuser->facility_id = $facility->id;
          $fuser->UserName = $facility->Name;
          $fuser->Password = $facility->Name;
          $fuser->Name = $facility->Name;
          $fuser->Mobile = 0;
          $fuser ->token = "0";
          $fuser->save();
          $result = ['result'=>'success','insertedRow'=>$facility,'error'=>null];
        }
        catch(Exception $e){
          $result = ['result'=>'failed','insertedRow'=>null,'error'=>'error '.$e->getMessage()];
        }
      }
      else {
        $result = ['result'=>'failed','insertedRow'=>null,'error'=>'you are unauthorized user'];
      }
      return $result ;
    }

    public function addFacilityType(Request $request) {
      $validator = Validator::make($request->all(),[
        'name' => 'required|max:50|unique:facilitytypes,Name',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        $result = ['result'=>'failed','insertedRow'=>null,'error'=>$validator->errors()];
        return $result ;
      }
      if (Users::checkAuth($request->input('my_token'))) {
        try{
          $facilityType = new Facilitytype();
          $facilityType->Name = $request->input('name');
          $facilityType->save();
          $result = ['result'=>'success','insertedRow'=>$facilityType,'error'=>null];
        }
        catch(Exception $e){
          $result = ['result'=>'failed','insertedRow'=>null,'error'=>'error '.$e->getMessage()];
        }
      }
      else {
        $result = ['result'=>'failed','insertedRow'=>null,'error'=>'you are un authorized user'];
      }
      return $result ;
    }

    public function addRestaurantMenu(Request $request) {
      $validator = Validator::make($request->all(),[
        'english_name' => 'required|max:50',
        'arabic_name' => 'required|max:50',
        'facility_id' => 'required|numeric',
        'image' => 'required',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','menue'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
        return ['result'=>'failed','menue'=>'','error'=>'you are un authorized user'];
      }
      $facility = Facility::find($request->facility_id);
      if ($facility == null) {
          return ['result'=>'failed','menue'=>'','error'=>'facility id unavailable'];
      }
      try {
          $menue = new Restaurantmenue();
          $menue->Hotel = 1 ;
          $menue->FacilityId = $request->facility_id ;
          $menue->name = $request->english_name ;
          $menue->arabicName = $request->arabic_name ;
          $image_path = $request->file('image')->store('images','public');
          $menue->photo = $this->URL.$image_path ;
          $menue->save();
          return ['result'=>'success','menue'=>$menue,'error'=>''];
      }
      catch(Exception $e) {
          return ['result'=>'failed','menue'=>'','error'=>$e];
      }
    }

    public function addCoffeeShopMenu(Request $request) {
      $validator = Validator::make($request->all(),[
        'english_name' => 'required|max:50',
        'arabic_name' => 'required|max:50',
        'facility_id' => 'required|numeric',
        'image' => 'required',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','menue'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
        return ['result'=>'failed','menue'=>'','error'=>'you are un authorized user'];
      }
      $facility = Facility::find($request->facility_id);
      if ($facility == null) {
          return ['result'=>'failed','menue'=>'','error'=>'facility id unavailable'];
      }
      try {
          $menue = new Coffeeshopmenue();
          $menue->Hotel = 1 ;
          $menue->facility_id = $request->facility_id ;
          $menue->Name = $request->english_name ;
          $menue->arabicName = $request->arabic_name ;
          $image_path = $request->file('image')->store('images','public');
          $menue->photo = $this->URL.$image_path ;
          $menue->save();
          return ['result'=>'success','menue'=>$menue,'error'=>''];
      }
      catch(Exception $e) {
          return ['result'=>'failed','menue'=>'','error'=>$e];
      }
    }

    public function addLaundryItem(Request $request) {
      $validator = Validator::make($request->all(),[
        'price' => 'required|max:50',
        'name' => 'required|max:50',
        'facility_id' => 'required|numeric',
        'icon' => 'required',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','item'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
        return ['result'=>'failed','item'=>'','error'=>'you are un authorized user'];
      }
      $facility = Facility::find($request->facility_id);
      if ($facility == null) {
          return ['result'=>'failed','item'=>'','error'=>'facility id unavailable'];
      }
      try {
          $item = new Laundryitem();
          $item->Hotel = 1 ;
          $item->Facility = $request->facility_id ;
          $item->Name = $request->name ;
          $item->Price = $request->price ;
          $image_path = $request->file('icon')->store('images','public');
          $item->icon = $this->URL.$image_path ;
          $item->save();
          return ['result'=>'success','item'=>$item,'error'=>''];
      }
      catch(Exception $e) {
          return ['result'=>'failed','item'=>'','error'=>$e];
      }
    }

    public function addRestaurantMenuMeal(Request $request) {
      $validator = Validator::make($request->all(),[
        'facility_id' => 'required|numeric',
        'menue_id' => 'required|numeric',
        'name' => 'required',
        'description' => 'required',
        'price' => 'required|numeric',
        'image'=>'required',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','meal'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
        return ['result'=>'failed','meal'=>'','error'=>'you are un authorized user'];
      }
      $facility = Facility::find($request->facility_id);
      if ($facility == null) {
          return ['result'=>'failed','meal'=>'','error'=>'facility id unavailable'];
      }
      $menue = Restaurantmenue::find($request->menue_id);
      if ($menue == null) {
          return ['result'=>'failed','meal'=>'','error'=>'menue id unavailable'];
      }
      try {
          $meal = new Restaurantitem();
          $meal->Hotel = 1 ;
          $meal->facility_id = $facility->id ;
          $meal->restaurantmenue_id = $menue->id ;
          $meal->menu = $menue->name ;
          $meal->name = $request->name ;
          $meal->desc = $request->description ;
          $meal->price = $request->price ;
          $meal->descount = 0 ;
          $image_path = $request->file('image')->store('images','public');
          $meal->photo = $this->URL.$image_path ;
          $meal->save();
          return ['result'=>'success','meal'=>$meal,'error'=>''];
      }
      catch(Exception $e) {
          return ['result'=>'failed','meal'=>'','error'=>$e];
      }
    }

    public function addCoffeshopMenuMeal(Request $request) {
      $validator = Validator::make($request->all(),[
        'facility_id' => 'required|numeric',
        'menue_id' => 'required|numeric',
        'name' => 'required',
        'description' => 'required',
        'price' => 'required|numeric',
        'image'=>'required',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','meal'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
        return ['result'=>'failed','meal'=>'','error'=>'you are un authorized user'];
      }
      $facility = Facility::find($request->facility_id);
      if ($facility == null) {
          return ['result'=>'failed','meal'=>'','error'=>'facility id unavailable'];
      }
      $menue = Coffeeshopmenue::find($request->menue_id);
      if ($menue == null) {
          return ['result'=>'failed','meal'=>'','error'=>'menue id unavailable'];
      }
      try {
          $meal = new Coffeeshopitem();
          $meal->Hotel = 1 ;
          $meal->facility_id = $facility->id ;
          $meal->coffeeshopmenue_id = $menue->id ;
          $meal->Menu = $menue->Name ;
          $meal->Name = $request->name ;
          $meal->Desc = $request->description ;
          $meal->Price = $request->price ;
          $meal->Discount = 0 ;
          $image_path = $request->file('image')->store('images','public');
          $meal->photo = $this->URL.$image_path ;
          $meal->save();
          return ['result'=>'success','meal'=>$meal,'error'=>''];
      }
      catch(Exception $e) {
          return ['result'=>'failed','meal'=>'','error'=>$e];
      }
    }

    public function addOtherInvoice(Request $request) {
        $validator = Validator::make($request->all(),[
        'room_id' => 'required|numeric',
        'reservation_id' => 'required|numeric',
        'invoice_number' => 'required|numeric',
        'invoice_type' => 'required',
        'date' => 'required|date',
        'total'=>'required|numeric',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','invoice'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
        return ['result'=>'failed','invoice'=>'','error'=>'you are un authorized user'];
      }
      $room = Room::find($request->room_id);
      if ($room == null) {
          return ['result'=>'failed','invoice'=>'','error'=>'room id is unavailable'];
      }
      $reservation = Booking::find($request->reservation_id);
      if ($room == null) {
          return ['result'=>'failed','invoice'=>'','error'=>'reservation id is unavailable'];
      }
      if ($reservation->Status == 0) {
          return ['result'=>'failed','invoice'=>'','error'=>'reservation already checkedout'];
      }
      try{
      $invoice = new Otherinvoice();
      $invoice->Room = $room->RoomNumber;
      $invoice->Reservation = $reservation->id;
      $invoice->InvoiceNumber = $request->invoice_number;
      $invoice->InvoiceType = $request->invoice_type;
      $invoice->Date = $request->date;
      $invoice->Total = $request->total;
      $invoice->save();
      return ['result'=>'success','invoice'=>$invoice,'error'=>''];
      }
      catch(Exception $e){
          return ['result'=>'failed','invoice'=>'','error'=>$e];
      }
    }

    public function addRestaurantOrder(Request $request) {
        $validator = Validator::make($request->all(),[
        'room_id' => 'required|numeric',
        'facility_id' => 'required|numeric',
        'total' => 'required|numeric',
        'countItems' => 'required|numeric',
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','invoice'=>'','error'=>$validator->errors()];
      }
      $room = Room::find($request->room_id);
      if ($room == null) {
          return ['result'=>'failed','error'=>'room id is unavailable'];
      }
      $facility = Facility::find($request->facility_id);
      if ($facility == null) {
          return ['result'=>'failed','error'=>'facility id is unavailable'];
      }
      if ($request->countItems == 0) {
          return ['result'=>'failed','error'=>'items should not be 0'];
      }
      $reservation = Booking::find($room->ReservationNumber);
      if ($reservation == null || $reservation->id == 0) {
          return ['result'=>'failed','error'=>'no reservation on this room '];
      }
      try{
          $milliseconds = intval(microtime(true) * 1000);
          $order = new Restaurantorder();
          $order->Hotel = 1 ;
          $order->Facility = $facility->id ;
          $order->Reservation = $room->ReservationNumber ;
          $order->room = $room->RoomNumber ;
          $order->RorS = $reservation->RoomOrSuite ;
          $order->roomId = $room->id ;
          $order->dateTime = $milliseconds ;
          $order->total = $request->total ;
          $order->status = 0 ;
          $order->save();
          for ($i=0;$i<$request->countItems;$i++) {
              $item = new Restaurantorderitem();
              $item->restaurantorder_id = $order->id;
              $item->room = $room->RoomNumber;
              $item->itemNo = $request['itemNo'.$i];
              $item->name = $request['name'.$i];
              $item->quantity = $request['quantity'.$i];
              $item->price = $request['price'.$i];
              $item->total = $request['total'.$i];
              $item->desc = $request['desc'.$i];
              $item->notes = "";
              $item->save();
          }
          return ['result'=>'success','error'=>'','order'=>$order];
      }
      catch(Exception $e){
          return ['result'=>'failed','error'=>$e->getMessage()];
      }

    }

    public function loginFacilityUser(Request $request) {
        $validator = Validator::make($request->all(),[
          'facility_id' => 'required|numeric',
          'user' => 'required',
          'password' => 'required'
        ]);
        if ($validator->fails()) {
          $result = ['result'=>'failed','project'=>'','error'=>$validator->errors()];
          return $result ;
        }
        $facility = Facility::find($request->facility_id);
        if ($facility == null) {
          return ['result'=>'failed','error'=>'no such facility'];
        }
        $user = Facilityuser::where('UserName',$request->user)->first();
        if ($user == null) {
            return ['result'=>'failed','error'=>'no such user'];
        }
        if ($user->UserName == $request->user && $user->Password == $request->password) {
            $myToken = Users::makeToken();
            $user->mytoken = $myToken;
            $user->save();
            return ['result'=>'success','user'=>$user,'my_token'=>$myToken,'mytoken'=>$myToken,'error'=>''];
        }
        else {
          return ['result'=>'failed','error'=>'invailed password'];
        }
    }

    public function setFacilityUserToken(Request $request) {
        $validator = Validator::make($request->all(),[
          'facility_id' => 'required|numeric',
          'user' => 'required',
          'token' => 'required'
        ]);
        if ($validator->fails()) {
          $result = ['result'=>'failed','project'=>'','error'=>$validator->errors()];
          return $result ;
        }
        $facility = Facility::find($request->facility_id);
        if ($facility == null) {
          return ['result'=>'failed','error'=>'no such facility'];
        }
        $user = Facilityuser::where('UserName',$request->user)->first();
        if ($user == null) {
            return ['result'=>'failed','error'=>'no such user'];
        }
        $user->token = $request->token ;
        try{
            $user->save();
            return ['result'=>'success'];
        }catch(Exception $e){
            return ['result'=>'failed','error'=>$e->getMessage()];
        }
    }


    // get functions

    public function getFacilityTypes () {
      return Facilitytype::all();
    }

    public function getFacilitys () {
      return Facility::all();
    }

    public function getRestaurantMenues(Request $request) {
        $validator = Validator::make($request->all(),[
        'facility_id' => 'required|numeric',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','menues'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
          return ['result'=>'failed','menues'=>'','error'=>'you are unauthorized user'];
      }
      $facility = Facility::find($request->facility_id);
      if ($facility == null) {
          return ['result'=>'failed','menues'=>'','error'=>'facility id unavailable'];
      }
      $menues = Restaurantmenue::where('FacilityId',$facility->id)->get();
      if ($menues == null || count($menues) == 0) {
          return ['result'=>'success','menues'=>'','error'=>'no menues for facility '.$facility->Name];
      }
      return ['result'=>'success','menues'=>$menues,'error'=>''];
    }

    public function getRestaurantMenuesForRoom(Request $request) {
        $validator = Validator::make($request->all(),[
        'facility_id' => 'required|numeric',
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','menues'=>'','error'=>$validator->errors()];
      }
      $facility = Facility::find($request->facility_id);
      if ($facility == null) {
          return ['result'=>'failed','menues'=>'','error'=>'facility id unavailable'];
      }
      $menues = Restaurantmenue::where('FacilityId',$facility->id)->get();
      if ($menues == null || count($menues) == 0) {
          return ['result'=>'success','menues'=>'','error'=>'no menues for facility '.$facility->Name];
      }
      return ['result'=>'success','menues'=>$menues,'error'=>''];
    }

    public function getCoffeeShopMenues(Request $request) {
        $validator = Validator::make($request->all(),[
        'facility_id' => 'required|numeric',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','menues'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
          return ['result'=>'failed','menues'=>'','error'=>'you are unauthorized user'];
      }
      $facility = Facility::find($request->facility_id);
      if ($facility == null) {
          return ['result'=>'failed','menues'=>'','error'=>'facility id unavailable'];
      }
      $menues = Coffeeshopmenue::where('facility_id',$facility->id)->get();
      if ($menues == null || count($menues) == 0) {
          return ['result'=>'success','menues'=>'','error'=>'no menues for facility '.$facility->Name];
      }
      return ['result'=>'success','menues'=>$menues,'error'=>''];
    }

    public function getCoffeeShopMenuesForRoom(Request $request) {
        $validator = Validator::make($request->all(),[
        'facility_id' => 'required|numeric',
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','menues'=>'','error'=>$validator->errors()];
      }
      $facility = Facility::find($request->facility_id);
      if ($facility == null) {
          return ['result'=>'failed','menues'=>'','error'=>'facility id unavailable'];
      }
      $menues = Coffeeshopmenue::where('facility_id',$facility->id)->get();
      if ($menues == null || count($menues) == 0) {
          return ['result'=>'success','menues'=>'','error'=>'no menues for facility '.$facility->Name];
      }
      return ['result'=>'success','menues'=>$menues,'error'=>''];
    }

    public function getLaundryItems(Request $request) {
        $validator = Validator::make($request->all(),[
        'facility_id' => 'required|numeric',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','items'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
          return ['result'=>'failed','items'=>'','error'=>'you are unauthorized user'];
      }
      $facility = Facility::find($request->facility_id);
      if ($facility == null) {
          return ['result'=>'failed','items'=>'','error'=>'facility id unavailable'];
      }
      $items = Laundryitem::where('Facility',$facility->id)->get();
      if ($items == null || count($items) == 0) {
          return ['result'=>'success','items'=>'','error'=>'no items for facility '.$facility->Name];
      }
      return ['result'=>'success','items'=>$items,'error'=>''];
    }

    public function getLaundryItemsRoomDevice(Request $request) {
        $validator = Validator::make($request->all(),[
        'facility_id' => 'required|numeric',
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','items'=>'','error'=>$validator->errors()];
      }
      $facility = Facility::find($request->facility_id);
      if ($facility == null) {
          return ['result'=>'failed','items'=>'','error'=>'facility id unavailable'];
      }
      $items = Laundryitem::where('Facility',$facility->id)->get();
      if ($items == null || count($items) == 0) {
          return ['result'=>'success','items'=>'','error'=>'no items for facility '.$facility->Name];
      }
      return ['result'=>'success','items'=>$items,'error'=>''];
    }

    public function getRestaurantMenueMeals(Request $request) {
        $validator = Validator::make($request->all(),[
        'facility_id' => 'required|numeric',
        'menue_id' => 'required|numeric',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','meals'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
          return ['result'=>'failed','meals'=>'','error'=>'you are unauthorized user'];
      }
      $facility = Facility::find($request->facility_id);
      if ($facility == null) {
          return ['result'=>'failed','meals'=>'','error'=>'facility id unavailable'];
      }
      $menue = Restaurantmenue::find($request->menue_id);
      if ($menue == null) {
          return ['result'=>'failed','meals'=>'','error'=>'menue id unavailable'];
      }
      $meals = Restaurantitem::where('facility_id',$facility->id)->where('restaurantmenue_id',$menue->id)->get();
      if ($meals == null || count($meals) == 0) {
          return ['result'=>'success','meals'=>'','error'=>'no meals for menue '.$menue->name];
      }
      return ['result'=>'success','meals'=>$meals,'error'=>''];
    }

    public function getRestaurantMenueMealsForRoom(Request $request) {
        $validator = Validator::make($request->all(),[
        'facility_id' => 'required|numeric',
        'menue_id' => 'required|numeric',
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','meals'=>'','error'=>$validator->errors()];
      }
      $facility = Facility::find($request->facility_id);
      if ($facility == null) {
          return ['result'=>'failed','meals'=>'','error'=>'facility id unavailable'];
      }
      $menue = Restaurantmenue::find($request->menue_id);
      if ($menue == null) {
          return ['result'=>'failed','meals'=>'','error'=>'menue id unavailable'];
      }
      $meals = Restaurantitem::where('facility_id',$facility->id)->where('restaurantmenue_id',$menue->id)->get();
      if ($meals == null || count($meals) == 0) {
          return ['result'=>'success','meals'=>'','error'=>'no meals for menue '.$menue->name];
      }
      return ['result'=>'success','meals'=>$meals,'error'=>''];
    }

    public function getCoffeShopMenueMeals(Request $request) {
        $validator = Validator::make($request->all(),[
        'facility_id' => 'required|numeric',
        'menue_id' => 'required|numeric',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','meals'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
          return ['result'=>'failed','meals'=>'','error'=>'you are unauthorized user'];
      }
      $facility = Facility::find($request->facility_id);
      if ($facility == null) {
          return ['result'=>'failed','meals'=>'','error'=>'facility id unavailable'];
      }
      $menue = Coffeeshopmenue::find($request->menue_id);
      if ($menue == null) {
          return ['result'=>'failed','meals'=>'','error'=>'menue id unavailable'];
      }
      $meals = Coffeeshopitem::where('facility_id',$facility->id)->where('coffeeshopmenue_id',$menue->id)->get();
      if ($meals == null || count($meals) == 0) {
          return ['result'=>'success','meals'=>'','error'=>'no meals for menue '.$menue->name];
      }
      return ['result'=>'success','meals'=>$meals,'error'=>''];
    }

    public function getCoffeShopMenueMealsForRoom(Request $request) {
        $validator = Validator::make($request->all(),[
        'facility_id' => 'required|numeric',
        'menue_id' => 'required|numeric',
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','meals'=>'','error'=>$validator->errors()];
      }
      $facility = Facility::find($request->facility_id);
      if ($facility == null) {
          return ['result'=>'failed','meals'=>'','error'=>'facility id unavailable'];
      }
      $menue = Coffeeshopmenue::find($request->menue_id);
      if ($menue == null) {
          return ['result'=>'failed','meals'=>'','error'=>'menue id unavailable'];
      }
      $meals = Coffeeshopitem::where('facility_id',$facility->id)->where('coffeeshopmenue_id',$menue->id)->get();
      if ($meals == null || count($meals) == 0) {
          return ['result'=>'success','meals'=>'','error'=>'no meals for menue '.$menue->name];
      }
      return ['result'=>'success','meals'=>$meals,'error'=>''];
    }

    public function getOtherInvoices(Request $request) {
        $validator = Validator::make($request->all(),[
        'room_number' => 'required|numeric',
        'reservation_id' => 'required|numeric',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','invoices'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
          return ['result'=>'failed','invoices'=>'','error'=>'you are unauthorized user'];
      }
      $room = Room::where('RoomNumber',$request->room_number)->first();
      if ($room == null) {
          return ['result'=>'failed','invoices'=>'','error'=>'room number unavailable'];
      }
      $reservation = Booking::find($request->reservation_id);
      if ($reservation == null) {
          return ['result'=>'failed','invoices'=>'','error'=>'reservation id unavailable'];
      }
      $invoices = Otherinvoice::where('Room',$request->room_number)->where('Reservation',$reservation->id)->get();
      if ($invoices == null || count($invoices) == 0) {
          return ['result'=>'success','invoices'=>'','error'=>'no invoices for room '.$room->RoomNumber];
      }
      return ['result'=>'success','invoices'=>$invoices,'error'=>''];
    }

    public function getFacilityInvoices(Request $request) {
        $validator = Validator::make($request->all(),[
        'room_number' => 'required|numeric',
        'reservation_id' => 'required|numeric',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','invoices'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
          return ['result'=>'failed','invoices'=>'','error'=>'you are unauthorized user'];
      }
      $room = Room::where('RoomNumber',$request->room_number)->get();
      if ($room == null) {
          return ['result'=>'failed','invoices'=>'','error'=>'room number unavailable'];
      }
      $reservation = Booking::find($request->reservation_id);
      if ($reservation == null) {
          return ['result'=>'failed','invoices'=>'','error'=>'reservation id unavailable'];
      }
      $invoices = Restaurantorder::where('room',$request->room_number)->where('Reservation',$reservation->id)->get();
      if ($invoices == null || count($invoices) == 0) {
          return ['result'=>'success','invoices'=>'','error'=>'no invoices for room '.$request->room_number];
      }
      return ['result'=>'success','invoices'=>$invoices,'error'=>''];
    }

    public function getRestOrders(Request $request) {
        $validator = Validator::make($request->all(),[
          'facility_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
          $result = ['result'=>'failed','project'=>'','error'=>$validator->errors()];
          return $result ;
        }
        $facility = Facility::find($request->facility_id);
        if ($facility == null) {
          return ['result'=>'failed','error'=>'no such facility'];
        }
        $orders = Restaurantorder::where('Facility',$facility->id)->where('status',0)->get();
        if ($orders == null) {
            return ['result'=>'failed','error'=>'no orders'];
        }
        return ['result'=>'success','orders'=>$orders,'error'=>''];
    }

    public function getRestOrder(Request $request) {
        $validator = Validator::make($request->all(),[
          'facility_id' => 'required|numeric',
          'room_number' => 'required|numeric'
        ]);
        if ($validator->fails()) {
          $result = ['result'=>'failed','project'=>'','error'=>$validator->errors()];
          return $result ;
        }
        $facility = Facility::find($request->facility_id);
        if ($facility == null) {
          return ['result'=>'failed','error'=>'no such facility'];
        }
        $room = $request->room_number;
        $order = Restaurantorder::where('Facility',$facility->id)->where('room',$room)->where('status',0)->orderBy('id', 'DESC')->first();
        if ($order == null) {
            return ['result'=>'failed','error'=>'no orders'];
        }
        return ['result'=>'success','order'=>$order,'error'=>''];
    }

    public function getRestOrderItems(Request $request) {
        $validator = Validator::make($request->all(),[
          'order_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
          $result = ['result'=>'failed','project'=>'','error'=>$validator->errors()];
          return $result ;
        }
        $order = Restaurantorder::find($request->order_id);
        if ($order == null) {
          return ['result'=>'failed','error'=>'no such order'];
        }
        $items = Restaurantorderitem::where('restaurantorder_id',$order->id)->get();
        if ($items == null) {
            return ['result'=>'failed','error'=>'no items'];
        }
        return ['result'=>'success','items'=>$items,'error'=>''];
    }

    public function finishRestOrder(Request $request) {
        $validator = Validator::make($request->all(),[
          'order_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
          return ['result'=>'failed','project'=>'','error'=>$validator->errors()];
        }
        $order = Restaurantorder::find($request->order_id);
        if ($order == null) {
          return ['result'=>'failed','error'=>'no such order'];
        }
        $order->status = 1 ;
        try{
            $room = Room::where('RoomNumber',$order->room)->get()->first();
            $order->save();
            //$this->finishRestInFirebase($room);
            return ['result'=>'success','error'=>''];

        }catch(Exception $e){
            return ['result'=>'failed','error'=>$e->getMessage()];
        }
    }

    // delete functions

    public function deleteMeal(Request $request) {
        $validator = Validator::make($request->all(),[
        'meal_id' => 'required|numeric',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','meal'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
        return ['result'=>'failed','meal'=>'','error'=>'you are un authorized user'];
      }
      $meal = Restaurantitem::find($request->meal_id);
      if ($meal == null) {
          return ['result'=>'failed','meal'=>'','error'=>'meal id is unavailable'];
      }
      try{
          $meal->delete();
          return ['result'=>'success','meal'=>'deleted','error'=>''];
      }catch(Exception $e){
          return ['result'=>'failed','meal'=>'','error'=>$e];
      }
    }

    public function deleteMenue(Request $request) {
        $validator = Validator::make($request->all(),[
        'menue_id' => 'required|numeric',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','menue'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
        return ['result'=>'failed','menue'=>'','error'=>'you are un authorized user'];
      }
      $menue = Restaurantmenue::find($request->menue_id);
      if ($menue == null) {
          return ['result'=>'failed','menue'=>'','error'=>'menue id is unavailable'];
      }
      $meals = Restaurantitem::where('restaurantmenue_id',$menue->id)->get();
      try{
          for ($i=0;$i<count($meals);$i++) {
              $meals[$i]->delete();
          }
          $menue->delete();
          return ['result'=>'success','menue'=>'deleted','error'=>''];
      }catch(Exception $e){
          return ['result'=>'failed','menue'=>'','error'=>$e];
      }
    }

    public function deleteLaundryItem(Request $request) {
        $validator = Validator::make($request->all(),[
        'item_id' => 'required|numeric',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','item'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
        return ['result'=>'failed','item'=>'','error'=>'you are un authorized user'];
      }
      $item = Laundryitem::find($request->item_id);
      if ($item == null) {
          return ['result'=>'failed','item'=>'','error'=>'item id is unavailable'];
      }
      try{
          $item->delete();
          return ['result'=>'success','item'=>'deleted','error'=>''];
      }catch(Exception $e){
          return ['result'=>'failed','item'=>'','error'=>$e];
      }
    }

    public function deleteCoffeeshopMeal(Request $request) {
        $validator = Validator::make($request->all(),[
        'meal_id' => 'required|numeric',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','meal'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
        return ['result'=>'failed','meal'=>'','error'=>'you are un authorized user'];
      }
      $meal = Coffeeshopitem::find($request->meal_id);
      if ($meal == null) {
          return ['result'=>'failed','meal'=>'','error'=>'meal id is unavailable'];
      }
      try{
          $meal->delete();
          return ['result'=>'success','meal'=>'deleted','error'=>''];
      }catch(Exception $e){
          return ['result'=>'failed','meal'=>'','error'=>$e];
      }
    }

    public function deleteCoffeeshopMenue(Request $request) {
        $validator = Validator::make($request->all(),[
        'menue_id' => 'required|numeric',
        'my_token' => 'required'
      ]);
      if ($validator->fails())  {
        return ['result'=>'failed','menue'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
        return ['result'=>'failed','menue'=>'','error'=>'you are un authorized user'];
      }
      $menue = Coffeeshopmenue::find($request->menue_id);
      if ($menue == null) {
          return ['result'=>'failed','menue'=>'','error'=>'menue id is unavailable'];
      }
      $meals = Coffeeshopitem::where('coffeeshopmenue_id',$menue->id)->get();
      try{
          for ($i=0;$i<count($meals);$i++) {
              $meals[$i]->delete();
          }
          $menue->delete();
          return ['result'=>'success','menue'=>'deleted','error'=>''];
      }catch(Exception $e){
          return ['result'=>'failed','menue'=>'','error'=>$e];
      }
    }

    // modify function

    public function modifyMeal(Request $request) {
        $params = [];
        if (empty($request->new_name) == false && $request->new_name != 'undefined') {
            $params['new_name'] = 'string|min:2';
        }
        if (empty($request->new_desc) == false && $request->new_desc != 'undefined') {
            $params['new_desc'] = 'string|min:2';
        }
        if (empty($request->new_price) == false && $request->new_price != 'undefined') {
            $params['new_price'] = 'numeric';
        }
        $params['meal_id'] = 'required|numeric' ;
        $params['my_token'] = 'required' ;
        $validator = Validator::make($request->all(),$params);
      if ($validator->fails())  {
        return ['result'=>'failed','meal'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
        return ['result'=>'failed','meal'=>'','error'=>'you are un authorized user'];
      }
      $meal = Restaurantitem::find($request->meal_id);
      if ($meal == null) {
          return ['result'=>'failed','meal'=>'','error'=>'meal id is unavailable'];
      }
      if ($request->new_name != null && $request->new_name != 'undefined') {
          $meal->name = $request->new_name;
      }
      if ($request->new_price != null && $request->new_price != 'undefined') {
          $meal->price = $request->new_price;
      }
      if ($request->new_desc != null && $request->new_desc != 'undefined') {
          $meal->desc = $request->new_desc;
      }
      if ($meal->save()) {
          return ['result'=>'success','meal'=>$meal,'error'=>''];
      }
      else {
          return ['result'=>'failed','meal'=>'','error'=>'unable to save changes'];
      }
    }

    public function modifyCoffeeshopMeal(Request $request) {
        $params = [];
        if (empty($request->new_name) == false && $request->new_name != 'undefined') {
            $params['new_name'] = 'string|min:2';
        }
        if (empty($request->new_desc) == false && $request->new_desc != 'undefined') {
            $params['new_desc'] = 'string|min:2';
        }
        if (empty($request->new_price) == false && $request->new_price != 'undefined') {
            $params['new_price'] = 'numeric';
        }
        $params['meal_id'] = 'required|numeric' ;
        $params['my_token'] = 'required' ;
        $validator = Validator::make($request->all(),$params);
      if ($validator->fails())  {
        return ['result'=>'failed','meal'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
        return ['result'=>'failed','meal'=>'','error'=>'you are un authorized user'];
      }
      $meal = Coffeeshopitem::find($request->meal_id);
      if ($meal == null) {
          return ['result'=>'failed','meal'=>'','error'=>'meal id is unavailable'];
      }
      if ($request->new_name != null && $request->new_name != 'undefined') {
          $meal->Name = $request->new_name;
      }
      if ($request->new_price != null && $request->new_price != 'undefined') {
          $meal->Price = $request->new_price;
      }
      if ($request->new_desc != null && $request->new_desc != 'undefined') {
          $meal->Desc = $request->new_desc;
      }
      if ($meal->save()) {
          return ['result'=>'success','meal'=>$meal,'error'=>''];
      }
      else {
          return ['result'=>'failed','meal'=>'','error'=>'unable to save changes'];
      }
    }

    public function modifyLaundryItem(Request $request) {
        $params = [];
        if (empty($request->new_name) == false && $request->new_name != 'undefined') {
            $params['new_name'] = 'string|min:2';
        }
        if (empty($request->new_price) == false && $request->new_price != 'undefined') {
            $params['new_price'] = 'numeric';
        }
        $params['item_id'] = 'required|numeric' ;
        $params['my_token'] = 'required' ;
        $validator = Validator::make($request->all(),$params);
      if ($validator->fails())  {
        return ['result'=>'failed','item'=>'','error'=>$validator->errors()];
      }
      if (!Users::checkAuth($request->input('my_token'))) {
        return ['result'=>'failed','item'=>'','error'=>'you are un authorized user'];
      }
      $item = Laundryitem::find($request->item_id);
      if ($item == null) {
          return ['result'=>'failed','item'=>'','error'=>'item id is unavailable'];
      }
      if ($request->new_name != null && $request->new_name != 'undefined') {
          $item->Name = $request->new_name;
      }
      if ($request->new_price != null && $request->new_price != 'undefined') {
          $item->Price = $request->new_price;
      }
      if ($item->save()) {
          return ['result'=>'success','item'=>$item,'error'=>''];
      }
      else {
          return ['result'=>'failed','item'=>'','error'=>'unable to save changes'];
      }
    }

    public function finishRestInFirebase(Room $room) {
      $arrRoom = [
        'dep'=> $this->setDepRoom($room),
        'Restaurant'=> 0,
        'Facility'=> 0,
      ];
      $h = new Http();
      $response = $h->patch($this->firebaseUrl.'/'.$this->projectName.'/B'.$room->Building.'/F'.$room->Floor.'/R'.$room->RoomNumber.'.json',$arrRoom);
      return $response->successful();
    }

    public function setDepRoom(Room $room) {
        $orders = array();
        array_push($orders,$room->Cleanup);
        array_push($orders,$room->Laundry);
        array_push($orders,$room->RoomService);
        array_push($orders,$room->DND);
        array_push($orders,$room->SOS);
        array_push($orders,$room->Restaurant);
        array_push($orders,$room->Checkout);
        sort($orders);
        $biggest = $orders[(count($orders)-1)];
        $result = '';
        switch ($biggest) {
            case 0 :
            $result = '0';
            break;
          case $room->Cleanup :
            $result = 'Cleanup';
            break;
          case $room->Laundry :
            $result = 'Laundry';
            break;
          case $room->RoomService :
            $result = 'RoomService';
            break;
          case $room->DND :
            $result = 'DND';
            break;
          case $room->SOS :
            $result = 'SOS';
            break;
          case $room->Restaurant :
            $result = 'Restaurant';
            break;
          case $room->Checkout :
            $result = 'Checkout';
            break;

          default:
            $result = '0';
            break;
        }
        return $result;
    }
}
