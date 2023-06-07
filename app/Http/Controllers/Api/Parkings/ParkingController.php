<?php

namespace App\Http\Controllers\Api\Parkings;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\Parking;
use App\Models\Parking_Slot;
use App\Models\Payment;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ParkingController extends Controller
{
    use GeneralTrait;
    protected $id=4;

    public function parking_Slots_Data(Request $request){
        $parking_id=isset($request->parking_id) ? $request->id:$this->id;
        $parking_slots=Parking_Slot::where('parking_id',$parking_id)->get();
        return $this->returnData('parking_slots',$parking_slots);
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
            if ($reservation->status == 'wait'){
                $reservation->start_time = Carbon::now();
                $reservation->status = 'active';
                $reservation->save();
                $parking_slot=Parking_Slot::find($reservation->parking_slot_id);
                $reservation_data=array_merge(["reservation_data"=>$reservation,'parking_slot'=>$parking_slot]);
                return $this->returnData("Reservation Data",$reservation_data);
            }else{
                return $this->returnError(500,'the reservation must be in wait status to be started');
            }
        }else{
            return $this->returnError(404,'reservation not found');
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
            if ($reservation->status == 'active'){
                $reservation->end_time = Carbon::now();
                $time1 = Carbon::parse($reservation->start_time);
                $reservation->parking_duration = round(($time1->diffInMinutes(Carbon::now())) / 60,);
                $reservation->status = 'done';
                DB::select("UPDATE `parkings` SET `busy_slots`=`busy_slots`-1 WHERE `id`=$reservation->parking_id");

                DB::select("UPDATE `parkings` SET `has_free_slot` = '1' WHERE `id` = $reservation->parking_id");
                DB::select("UPDATE `parking_slots` SET `status` = 'free' WHERE `id` = $reservation->parking_slot_id");

                $reservation->save();
                if ($reservation) {
                    $cost = $reservation->price_per_hour * $reservation->parking_duration;
                    $payment = DB::select("UPDATE `users` SET `credit` =`credit`-$cost WHERE id=$reservation->user_id");
                    $bill= Payment::create(
                        [
                            'reservation_id' => $reservation->id,
                            'user_id' => $reservation->user_id,
                            'parking_id' => $reservation->parking_id,
                            'cost' => $cost
                        ]
                    );
                    $parking_credit = DB::select("UPDATE `parkings` SET `credit`=`credit`+$cost WHERE `id`=$reservation->parking_id");
                    return $this->returnSuccessMessage($bill);
                } else {
                    return $this->returnSuccessMessage('end reservation fail');
                }
            }else{
                return $this->returnError(500,'the reservation must be in active status to be end');
            }
        }else{
            return $this->returnError(404,'reservation not found');
        }
    }

}
