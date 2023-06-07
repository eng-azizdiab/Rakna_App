<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\Admin;
use App\Models\Parking;
use App\Models\Parking_Slot;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
//Illuminate\Contracts\Auth\Authenticatable




class AdminController extends Controller
{
    use GeneralTrait;
    public function all_Users(){
        $users=User::all();
        return $this->returnData('users',$users,);
    }

    public function user_By_Id(Request $request){
        $user=User::find($request->id);
        return $this->returnData('user',$user,);
    }

    public function all_Suppliers(){
        $suppliers=Supplier::all();
        return $this->returnData('users',$suppliers,);
    }

    public function supplier_By_Id(Request $request){

        $supplier=Supplier::find($request->id);
        return $this->returnData('user',$supplier,);
    }

    public function all_Suppliers_Parkings(){
        $date_today = Carbon::today()->toDateString();
//        $query = "SELECT suppliers.id as supplier_id,suppliers.name as supplier_name,suppliers.email,suppliers.phone,suppliers.gender,suppliers.job,suppliers.address,suppliers.age,parkings.id as parking_id,parkings.name as parking_name,parkings.credit,parkings.price_per_hour,parkings.number_of_slots,parkings.number_of_floors,parkings.busy_slots,parkings.total_rate,parkings.has_free_slot, ST_X(location) as latitude, ST_Y(location) as longitude FROM suppliers JOIN parkings ON parkings.supplier_id=suppliers.id";
      /*  $data = DB::select("
    SELECT suppliers.id as supplier_id, suppliers.name as supplier_name, suppliers.email, suppliers.phone, suppliers.gender, suppliers.job, suppliers.address, suppliers.age, parkings.id as parking_id, parkings.name as parking_name, parkings.credit, parkings.price_per_hour, parkings.number_of_slots, parkings.number_of_floors, parkings.busy_slots, parkings.total_rate, parkings.has_free_slot, ST_X(location) as latitude, ST_Y(location) as longitude, SUM(payments.cost) + COALESCE(SUM(CASE WHEN DATE(payments.created_at) = DATE(NOW()) THEN payments.cost ELSE 0 END), 0) as total_payments
    FROM suppliers
    JOIN parkings ON parkings.supplier_id = suppliers.id
    LEFT JOIN payments ON payments.parking_id = parkings.id
    GROUP BY parkings.id
");*/
        $query="SELECT
    suppliers.id as supplier_id,
    suppliers.name as supplier_name,
    suppliers.email,
    suppliers.phone,
    suppliers.gender,
    suppliers.job,
    suppliers.address,
    suppliers.age,
    parkings.id as parking_id,
    parkings.name as parking_name,
    parkings.credit,
    parkings.price_per_hour,
    parkings.number_of_slots,
    parkings.number_of_floors,
    parkings.busy_slots,
    parkings.total_rate,
    parkings.has_free_slot,
    ST_X(location) as latitude,
    ST_Y(location) as longitude,
    SUM(payments.cost) as total_payments,
    SUM(CASE WHEN DATE(payments.created_at) = ? THEN payments.cost ELSE 0 END) as payments_today
FROM
    suppliers
    JOIN parkings ON parkings.supplier_id = suppliers.id
    LEFT JOIN payments ON payments.parking_id = parkings.id
GROUP BY parkings.id
";
        $data=DB::select($query,[$date_today]);

        if ($data) {
            return $this->returnData('data', $data, "success");
        }
        else
            return $this->returnError(404,'there is no data');
    }

    public function parking_By_Supplier_Id(Request $request){
        $query="SELECT id,name,credit,busy_slots,total_rate,number_of_slots,number_of_floors,supplier_id,has_free_slot,price_per_hour, ST_X(location) as latitude, ST_Y(location) as longitude FROM parkings where supplier_id=$request->supplier_id";
        $parking=DB::select($query);
        if ($parking)
            return $this->returnData('parking',$parking,"success");
        else
            return $this->returnError(404,'the supplier dosn\'t add the parking yet');
    }

    public function all_Parkings(){

        $query="SELECT id,name,credit,busy_slots,total_rate,number_of_slots,has_free_slot,price_per_hour, ST_X(location) as latitude, ST_Y(location) as longitude FROM parkings";
        $parkings=DB::select($query);
        if (count($parkings)>0)
            return $this->returnData('parkings',$parkings,"success");
        else
            return $this->returnError(404,'there is no any parkings');
    }

    public function empty_Parkings(){

        $query="SELECT id,name,credit,busy_slots,total_rate,number_of_slots,has_free_slot,price_per_hour, ST_X(location) as latitude, ST_Y(location) as longitude FROM parkings where busy_slots=0";
        $parkings=DB::select($query);
        if (count($parkings)>0)
            return $this->returnData('parkings',$parkings,"success");
        else
            return $this->returnError(404,'there is no any empty parkings');
    }

    public function busy_Parkings(){

        $query="SELECT id,name,credit,busy_slots,total_rate,number_of_slots,has_free_slot,price_per_hour, ST_X(location) as latitude, ST_Y(location) as longitude FROM parkings where busy_slots>0";
        $parkings=DB::select($query);
        if (count($parkings)>0)
            return $this->returnData('parkings',$parkings,"success");
        else
            return $this->returnError(404,'there is no any busy parkings');
    }

    public function full_Parkings(){

        $query="SELECT id,name,credit,busy_slots,total_rate,number_of_slots,has_free_slot,price_per_hour, ST_X(location) as latitude, ST_Y(location) as longitude FROM parkings where busy_slots=number_of_slots";
        $parkings=DB::select($query);
        if (count($parkings)>0)
            return $this->returnData('parkings',$parkings,"success");
        else
            return $this->returnError(404,'there is no any fully parkings');
    }

    public function max_Parking_profit(){
        $query="SELECT parking_id, SUM(cost) AS total_profit FROM payments GROUP BY parking_id ORDER BY total_profit DESC LIMIT 1";
        $max_profit=DB::select($query);
        if ($max_profit)
            return $this->returnData('parkings',$max_profit,"success");
        else
            return $this->returnError(404,'there is no any profits');
    }

    public function min_Parking_profit(){
        $query="SELECT parking_id, SUM(cost) AS total_profit FROM payments GROUP BY parking_id ORDER BY total_profit ASC LIMIT 1";
        $min_profit=DB::select($query);
        if ($min_profit)
            return $this->returnData('parkings',$min_profit,"success");
        else
            return $this->returnError(404,'there is no profits');
    }

    public function each_Parking_Profit(){
        $query="SELECT parking_id, SUM(cost) AS total_profit FROM payments GROUP BY parking_id ORDER BY total_profit DESC";
        $profits=DB::select($query);
        if ($profits)
            return $this->returnData('parkings',$profits,"success");
        else
            return $this->returnError(404,'there is no any profits');
    }

    public function parkings_Day_Profit(){
        $date_today = Carbon::today()->toDateString();
        $query = "SELECT parking_id, SUM(cost) AS total_profit FROM payments WHERE created_at > ? GROUP BY parking_id ORDER BY total_profit DESC";
        $profits = DB::select($query, [$date_today]);
        if ($profits)
            return $this->returnData('parkings',$profits,"success");
        else
            return $this->returnError(404,'there is no any profits');
    }

    public function Supplier_By_parking_Id(Request $request){
        $supplier_id=Parking::where('id',$request->parking_id)->select('supplier_id')->get();
        $supplier=Supplier::find($supplier_id);
        if ($supplier)
            return $this->returnData('supplier',$supplier,"success");
        else
            return $this->returnError(404,'there is no supplier with this parking id');
    }

    public function day_Profits(){

            $profits = Payment::where('created_at', '>', Carbon::today())->sum('cost');
            return $this->returnData('profits', $profits);

    }
    public function day_Profits_Parking(Request $request){

            $profits = Payment::where('parking_id',$request->id)->where('created_at', '>', Carbon::today())->sum('cost');
            return $this->returnData('profits', $profits);

    }

    public function total_Profits(){
            $profits = Payment::sum('cost');
            return $this->returnData('profits', $profits);

    }

    public function total_Profits_Parking(Request $request){
            $profits = Payment::where('parking_id',$request->id)->sum('cost');
            return $this->returnData('profits', $profits);
    }

    public function interval_Profits(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date'=>'required|date',
            'second_date' => 'required|date',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $profits = Payment::whereBetween('created_at', [$request->first_date, $request->second_date])->sum('cost');
        return $this->returnData('profits', $profits);

    }
    public function interval_Profits_Parking(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date'=>'required|date',
            'second_date' => 'required|date',
            'parking_id' => 'required|numeric',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $profits = Payment::where('parking_id',$request->parking_id)->whereBetween('created_at', [$request->first_date, $request->second_date])->sum('cost');
        return $this->returnData('profits', $profits);
    }

    public function day_Payments(){
            $payments = Payment::where('created_at', '>', Carbon::today())->get();
            if (count($payments)>0) {
                return $this->returnData('payments', $payments);
            }else{
                return $this->returnError(404,'there is no any payments today');
            }
    }



    public function total_Payments(){

            $payments = Payment::all();
            if (count($payments)>0) {
                return $this->returnData('payments', $payments);
            }else{
                return $this->returnError(404,'there is no any previous payments');
            }

    }


    public function interval_Payments(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date'=>'required|date',
            'second_date' => 'required|date',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

            $payments = Payment::whereBetween('created_at', [$request->first_date, $request->second_date])->get();
            if (count($payments)>0) {
                return $this->returnData('payments', $payments);
            }else{
                return $this->returnError(404,'there is no any payments in this interval');
            }
    }

    public function payment_By_Id(Request $request){

            $payments = Payment::where('id', $request->payment_id)->get();
            if ($payments) {
                return $this->returnData('payments', $payments);
            }else{
                return $this->returnError(404,'there is no any payments with this id ');
            }

    }

    public function day_Payments_Parking(Request $request){
        $payments = Payment::where('parking_id',$request->id)->where('created_at', '>', Carbon::today())->get();
        if (count($payments)>0) {
            return $this->returnData('payments', $payments);
        }else{
            return $this->returnError(404,'there is no any payments today for this parking');
        }
    }

    public function total_Payments_Parking(Request $request){

        $payments = Payment::where('parking_id',$request->id)->get();
        if (count($payments)>0) {
            return $this->returnData('payments', $payments);
        }else{
            return $this->returnError(404,'there is no any previous payments');
        }

    }

    public function interval_Payments_Parking(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date'=>'required|date',
            'second_date' => 'required|date',
            'parking_id' => 'required|numeric',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $payments = Payment::where('parking_id',$request->parking_id)->whereBetween('created_at', [$request->first_date, $request->second_date])->get();
        if (count($payments)>0) {
            return $this->returnData('payments', $payments);
        }else{
            return $this->returnError(404,'there is no any payments in this interval');
        }
    }

    public function day_Payments_User(Request $request){
        $payments = Payment::where('user_id',$request->id)->where('created_at', '>', Carbon::today())->get();
        if (count($payments)>0) {
            return $this->returnData('payments', $payments);
        }else{
            return $this->returnError(404,'there is no any payments today for this parking');
        }
    }

    public function total_Payments_User(Request $request){

        $payments = Payment::where('user_id',$request->id)->get();
        if (count($payments)>0) {
            return $this->returnData('payments', $payments);
        }else{
            return $this->returnError(404,'there is no any previous payments');
        }

    }

    public function interval_Payments_User(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date'=>'required|date',
            'second_date' => 'required|date',
            'user_id' => 'required|numeric',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $payments = Payment::where('user_id',$request->user_id)->whereBetween('created_at', [$request->first_date, $request->second_date])->get();
        if (count($payments)>0) {
            return $this->returnData('payments', $payments);
        }else{
            return $this->returnError(404,'there is no any payments in this interval');
        }
    }

    //Reservations

    public function all_Reservations_UserId($user_id){
        $reservations=Reservation::where('user_id',$user_id)->get();
        if (count($reservations)>0)
            return $this->returnData('reservations',$reservations);
        else
            return $this->returnError(404,"there is no any reservations for this user");
    }

    public function all_Reservations_ParkingId($parking_id){
        $reservations=Reservation::where('parking_id',$parking_id)->get();
        if (count($reservations)>0)
            return $this->returnData('reservations',$reservations);
        else
            return $this->returnError(404,"there is no any reservations for this Parking");
    }

    public function all_Reservations(){
        $reservations=Reservation::all();
        if (count($reservations)>0)
            return $this->returnData('reservations',$reservations);
        else
            return $this->returnError(404,"there is no any reservations for this Parking");
    }

    public function reservation_By_Uid($uid){
        $reservations=Reservation::where('uid',$uid)->get();
        if (count($reservations)>0)
            return $this->returnData('reservations',$reservations);
        else
            return $this->returnError(404,"there is no any reservations with this uid");
    }


    public function get_reservation_interval(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date' => 'required|date',
            'second_date' => 'required|date',

        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $reservations=Reservation::whereBetween('created_at',[$request->first_date,$request->second_date])->get();
        if (count($reservations)>0)
            return $this->returnData('reservations',$reservations);
        else
            return $this->returnError(404,"there is no any reservations in this interval");
    }

    public function get_active_reservation(){

        $reservations=Reservation::where('status','active')->get();
        if (count($reservations)>0)
            return $this->returnData('reservations',$reservations);
        else
            return $this->returnError(404,"there is no any active reservations ");
    }

    public function get_active_reservation_interval(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date'=>'required|date',
            'second_date' => 'required|date',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $reservations=Reservation::whereBetween('created_at',[$request->first_date,$request->second_date])->where('status','active')->get();
        if (count($reservations)>0)
            return $this->returnData('reservations',$reservations);
        else
            return $this->returnError(404,"there is no any active reservations in this interval");
    }

    public function get_wait_reservation(){

        $reservations=Reservation::where('status','wait')->get();
        if (count($reservations)>0)
            return $this->returnData('reservations',$reservations);
        else
            return $this->returnError(404,"there is no any wait reservations ");
    }

    public function get_wait_reservation_interval(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date'=>'required|date',
            'second_date' => 'required|date',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $reservations=Reservation::whereBetween('created_at',[$request->first_date,$request->second_date])->where('status','wait')->get();
        if (count($reservations)>0)
            return $this->returnData('reservations',$reservations);
        else
            return $this->returnError(404,"there is no any wait reservations in this interval");
    }

    public function get_done_reservation(){

        $reservations=Reservation::where('status','done')->get();
        if (count($reservations)>0)
            return $this->returnData('reservations',$reservations);
        else
            return $this->returnError(404,"there is no any done reservations");
    }

    public function get_done_reservation_interval(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date'=>'required|date',
            'second_date' => 'required|date',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $reservations=Reservation::whereBetween('created_at',[$request->first_date,$request->second_date])->where('status','done')->get();
        if (count($reservations)>0)
            return $this->returnData('reservations',$reservations);
        else
            return $this->returnError(404,"there is no any done reservations in this interval");
    }

    public function get_canceled_reservation(){

        $reservations=Reservation::where('status','canceled')->get();
        if (count($reservations)>0)
            return $this->returnData('reservations',$reservations);
        else
            return $this->returnError(404,"there is no any canceled reservations");
    }

    public function get_canceled_reservation_interval(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date'=>'required|date',
            'second_date' => 'required|date',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $reservations=Reservation::whereBetween('created_at',[$request->first_date,$request->second_date])->where('status','canceled')->get();
        if (count($reservations)>0)
            return $this->returnData('reservations',$reservations);
        else
            return $this->returnError(404,"there is no any canceled reservations in this interval");
    }

    public function parking_Statistics(){
        $done_reservations=Reservation::where('status','done')->where('created_at',today())->count();
        $active_reservations=Reservation::where('status','active')->where('created_at',today())->count();
        $free_slots=Parking_Slot::where('status','free')->count();
        $result=[
            "done reservations"=>$done_reservations,
            "active reservations"=>$active_reservations,
            "free slots"=>$free_slots
        ];

        return $this->returnData('Statistics',$result);
    }

    public function face_Recognition(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $user=User::where('email',$request->email)->get();
        if ($user){
            return  $this->returnSuccessMessage();
        }else{
            return $this->returnError();
        }

    }

}
