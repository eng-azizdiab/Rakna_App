<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\Parking;
use App\Models\Parking_Slot;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use MongoDB\BSON\Timestamp;

class ReservationUserController extends Controller
{
    use GeneralTrait;
    /*
     * make reservation it take reservation data like car user parking and make new reservation and return uid
     * */
    public function make_reservation(Request $request){
        $validator=Validator::make($request->all(),[
            'parking_id'=>'required|exists:parkings,id|',
            'car_id'=>'required|exists:cars,id',
            'start_time'=>'required',
            'price_per_hour'=>'required'
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $uid=Str::orderedUuid().Auth::guard('user-api')->user()->name;
        $parking=Parking::where('id',$request->parking_id)->select('has_free_slot')->first();
        $parking_slot_id=Parking_Slot::where('status','free')->where('parking_id',$request->parking_id)->first();
        if ($parking_slot_id and $parking->has_free_slot==1)
        {
            $reservation = Reservation::create(
                [
                    'start_time' => $request->start_time,
                    'uid' => $uid,
                    'user_id' => Auth::guard('user-api')->user()->id,
                    'car_id' => $request->car_id,
                    'parking_id' => $request->parking_id,
                    'parking_slot_id' => $parking_slot_id->id,
                    'price_per_hour'=>$request->price_per_hour
                ]
            );
            if ($reservation) {

                $parking_slot_id->update(['status' => 'booked']);


                DB::select("UPDATE `parkings` SET `busy_slots`=`busy_slots`+1 WHERE `id`=$reservation->parking_id");
                $parking = Parking::select('busy_slots', 'number_of_slots')->where('id', $reservation->parking_id)->first();
                if ($parking->busy_slots >= $parking->number_of_slots) {
                    DB::select("UPDATE `parkings` SET `has_free_slot` = '0' WHERE `id` = $reservation->parking_id");
                }
                return $this->returnData('reservation', $reservation, "Success");
            } else {
                return $this->returnError('404', 'Error142');
            }
        }else {
            return $this->returnError('404', 'there is no empty slots');
        }

    }

    public function cancel_reservation(Request $request){
        $validator=Validator::make($request->all(),[
            'uid'=>'required',
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $reservation=Reservation::where('uid',$request->uid)->first();
        if ($reservation){
            $reservation->status='canceled';
            $reservation->save();
            return $this->returnSuccessMessage('reservation canceled');
        }else{
            return $this->returnError(404,$reservation);
        }
    }

    /*
     * start reservation it take uid and check if exists would start reservation else return not found
     * */
    public function start_reservation(Request $request){
        $validator=Validator::make($request->all(),[
            'uid'=>'required',
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $reservation=Reservation::where('uid',$request->uid)->first();
        if ($reservation){
            $reservation->start_time=Carbon::now();
            $reservation->status='active';
            $reservation->save();
            return $this->returnSuccessMessage('reservation start');
        }else{
            return $this->returnError(404,$reservation);
        }
    }
    /*
     * take uid and check if exits end it and update values in reservation and parkings
     * */
    public function end_reservation(Request $request){
        $validator=Validator::make($request->all(),[
            'uid'=>'required',
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $reservation=Reservation::where('uid',$request->uid)->first();
        if ($reservation){
            $reservation->end_time=Carbon::now();
            $time1=Carbon::parse($reservation->start_time);
            $reservation->parking_duration=round(($time1->diffInMinutes(Carbon::now()))/60,);
            $reservation->status='done';
            DB::select("UPDATE `parkings` SET `busy_slots`=`busy_slots`-1 WHERE `id`=$reservation->parking_id");

            DB::select("UPDATE `parkings` SET `has_free_slot` = '1' WHERE `id` = $reservation->parking_id");

            $reservation->save();
            if ($reservation) {
                $cost=$reservation->price_per_hour*$reservation->parking_duration;
                $payment=DB::select("UPDATE `users` SET `credit` =`credit`-$cost WHERE id=$reservation->user_id");
                Payment::create(
                    [
                        'reservation_id'=>$reservation->id,
                        'user_id'=>$reservation->user_id,
                        'parking_id'=>$reservation->parking_id,
                        'cost'=>$cost
                    ]
                );
                $parking_credit=DB::select("UPDATE `parkings` SET `credit`=`credit`+$cost WHERE `id`=$reservation->parking_id");
                return $this->returnSuccessMessage($reservation->parking_duration);
            }
            else {
                return $this->returnSuccessMessage('reservation fail');
            }
        }else{
            return $this->returnError(404,$reservation);
        }
    }

    public function get_all_reservation(){
        $user_id=Auth::guard('user-api')->user()->id;
        $reservation=Reservation::where('user_id',$user_id)->get();
        return $this->returnData('reservations',$reservation);
    }
}
