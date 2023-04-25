<?php

namespace App\Http\Controllers\Api\Parkings;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\Parking;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;


class SupplierParkingController extends Controller

{
    use GeneralTrait;

    public function add_parking(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'number_of_slots' => 'required',
            'number_of_floors' => 'required',
            'price_per_hour'=>'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $latitude=floatval($request->latitude);
        $longitude=floatval($request->longitude);
        $result=DB::table('parkings')->insert([[
            'location' => DB::raw("(ST_GeomFromText('POINT($latitude $longitude)'))"),
            'name'=>$request->name,
            'number_of_slots'=>$request->number_of_slots,
            'number_of_floors'=>$request->number_of_floors,
            'supplier_id'=>Auth::guard('supplier-api')->user()->id
            ]]);
        if ($result){
            return $this->returnData('parking',$result,"parking is registered successfully");
        }else{
            return $this->returnError();
        }
    }

    public function my_parking(){
        $supllier_id=Auth::guard('supplier-api')->user()->id;
        $query="SELECT id,name,credit,busy_slots,total_rate,number_of_slots,has_free_slot,price_per_hour, ST_X(location) as latitude, ST_Y(location) as longitude FROM parkings where supplier_id=$supllier_id";
        $parkings=DB::select($query);
        if ($parkings)
             return $this->returnData('parkings',$parkings,"success");
        else
           return $this->returnError(404,'the supplier dosn\'t add the parking yet');
    }

    public function day_profits(){
        $supplier_id=Auth::guard('supplier-api')->user()->id;
        $parking_id=Parking::where('supplier_id',$supplier_id)->select('id')->first();
        if ($parking_id) {
            $profits = Payment::where('parking_id', $parking_id->id)->where('created_at', '>', Carbon::today())->sum('cost');
            return $this->returnData('profits', $profits);
        }
        else
           return $this->returnError(404,'the supplier dosn\'t add the parking yet');
    }

    public function total_profits(){
        $supplier_id=Auth::guard('supplier-api')->user()->id;
        $parking_id=Parking::where('supplier_id',$supplier_id)->select('id')->first();
        if ($parking_id) {
            $profits = Payment::where('parking_id', $parking_id->id)->sum('cost');
            return $this->returnData('profits', $profits);
        } else
           return $this->returnError(404,'the supplier dosn\'t add the parking yet');
    }

    public function interval_profits(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date'=>'required|date',
            'second_date' => 'required|date',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $supplier_id=Auth::guard('supplier-api')->user()->id;
        $parking_id=Parking::where('supplier_id',$supplier_id)->select('id')->first();
        if ($parking_id) {
            $profits = Payment::where('parking_id', $parking_id->id)->whereBetween('created_at', [$request->first_date, $request->second_date])->sum('cost');
            return $this->returnData('profits', $profits);
        } else
           return $this->returnError(404,'the supplier dosn\'t add the parking yet');
    }

    public function day_payments(){
        $supplier_id=Auth::guard('supplier-api')->user()->id;
        $parking_id=Parking::where('supplier_id',$supplier_id)->select('id')->first();
        if ($parking_id) {
            $payments = Payment::where('parking_id', $parking_id->id)->where('created_at', '>', Carbon::today())->get();
            if ($payments) {
                return $this->returnData('payments', $payments);
            }else{
                return $this->returnError(404,'there is no any payments today');
            }
        } else
           return $this->returnError(404,'the supplier dosn\'t add the parking yet');
    }

    public function total_payments(){
        $supplier_id=Auth::guard('supplier-api')->user()->id;
        $parking_id=Parking::where('supplier_id',$supplier_id)->select('id')->first();
        if ($parking_id) {
            $payments = Payment::where('parking_id', $parking_id->id)->get();
            if ($payments) {
                return $this->returnData('payments', $payments);
            }else{
                return $this->returnError(404,'there is no any previous payments');
            }
        } else
           return $this->returnError(404,'the supplier dosn\'t add the parking yet');
    }

    public function interval_payments(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date'=>'required|date',
            'second_date' => 'required|date',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $supplier_id=Auth::guard('supplier-api')->user()->id;
        $parking_id=Parking::where('supplier_id',$supplier_id)->select('id')->first();
        if ($parking_id) {
            $payments = Payment::where('parking_id', $parking_id->id)->whereBetween('created_at', [$request->first_date, $request->second_date])->get();
            if ($payments) {
                return $this->returnData('payments', $payments);
            }else{
                return $this->returnError(404,'there is no any payments in this interval');
            }
        } else
           return $this->returnError(404,'the supplier dosn\'t add the parking yet');

    }


}
