<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\Parking;
use App\Models\Reservation;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReservationSupplierController extends Controller
{
    use GeneralTrait;
    public function get_all_reservation(){
        $supllier_id=Auth::guard('supplier-api')->user()->id;

        $parking_id=Parking::select('id')->where('supplier_id',$supllier_id)->first();
        $reservation=Reservation::where('parking_id',$parking_id->id)->get();
        return $this->returnData('reservations',$reservation);
    }

    public function get_reservation_interval(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date' => 'required|date',
            'second_date' => 'required|date',

        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $supllier_id=Auth::guard('supplier-api')->user()->id;

        $parking_id=Parking::select('id')->where('supplier_id',$supllier_id)->first();
        $reservation=Reservation::where('parking_id',$parking_id->id)->whereBetween('created_at',[$request->first_date,$request->second_date])->get();
        return $this->returnData('reservations',$reservation);
    }

    public function get_active_reservation(){
        $supllier_id=Auth::guard('supplier-api')->user()->id;

        $parking_id=Parking::select('id')->where('supplier_id',$supllier_id)->first();
        $reservation=Reservation::where('parking_id',$parking_id->id)->where('status','active')->get();
        return $this->returnData('reservations',$reservation);
    }

    public function get_active_reservation_interval(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date'=>'required|date',
            'second_date' => 'required|date',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $supllier_id=Auth::guard('supplier-api')->user()->id;

        $parking_id=Parking::select('id')->where('supplier_id',$supllier_id)->first();
        $reservation=Reservation::where('parking_id',$parking_id->id)->whereBetween('created_at',[$request->first_date,$request->second_date])->where('status','active')->get();
        return $this->returnData('reservations',$reservation);
    }

    public function get_wait_reservation(){
        $supllier_id=Auth::guard('supplier-api')->user()->id;

        $parking_id=Parking::select('id')->where('supplier_id',$supllier_id)->first();
        $reservation=Reservation::where('parking_id',$parking_id->id)->where('status','wait')->get();
        return $this->returnData('reservations',$reservation);
    }

    public function get_wait_reservation_interval(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date'=>'required|date',
            'second_date' => 'required|date',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $supllier_id=Auth::guard('supplier-api')->user()->id;

        $parking_id=Parking::select('id')->where('supplier_id',$supllier_id)->first();
        $reservation=Reservation::where('parking_id',$parking_id->id)->whereBetween('created_at',[$request->first_date,$request->second_date])->where('status','wait')->get();
        return $this->returnData('reservations',$reservation);
    }

    public function get_done_reservation(){
        $supllier_id=Auth::guard('supplier-api')->user()->id;

        $parking_id=Parking::select('id')->where('supplier_id',$supllier_id)->first();
        $reservation=Reservation::where('parking_id',$parking_id->id)->where('status','done')->get();
        return $this->returnData('reservations',$reservation);
    }

    public function get_done_reservation_interval(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date'=>'required|date',
            'second_date' => 'required|date',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $supllier_id=Auth::guard('supplier-api')->user()->id;

        $parking_id=Parking::select('id')->where('supplier_id',$supllier_id)->first();
        $reservation=Reservation::where('parking_id',$parking_id->id)->whereBetween('created_at',[$request->first_date,$request->second_date])->where('status','done')->get();
        return $this->returnData('reservations',$reservation);
    }

    public function get_canceled_reservation(){
        $supllier_id=Auth::guard('supplier-api')->user()->id;

        $parking_id=Parking::select('id')->where('supplier_id',$supllier_id)->first();
        $reservation=Reservation::where('parking_id',$parking_id->id)->where('status','canceled')->get();
        return $this->returnData('reservations',$reservation);
    }

    public function get_canceled_reservation_interval(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date'=>'required|date',
            'second_date' => 'required|date',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $supllier_id=Auth::guard('supplier-api')->user()->id;

        $parking_id=Parking::select('id')->where('supplier_id',$supllier_id)->first();
        $reservation=Reservation::where('parking_id',$parking_id->id)->whereBetween('created_at',[$request->first_date,$request->second_date])->where('status','canceled')->get();
        return $this->returnData('reservations',$reservation);
    }
}
