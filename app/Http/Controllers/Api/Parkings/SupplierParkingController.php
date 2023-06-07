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

    public function profits_Monthly(){

        $start_date = now()->subMonth()->startOfDay();
        $end_date = now()->endOfDay();
//        $parking_id = $request->parking_id; // replace with the desired parking ID
        $supplier_id=Auth::guard('supplier-api')->user()->id;
        $parking_id=Parking::where('supplier_id',$supplier_id)->select('id')->first();
        if ($parking_id) {
            $dates_with_profits = Payment::select(DB::raw('DATE(created_at) as date_only'), DB::raw('SUM(cost) as total_cost'))
                ->where('parking_id', $parking_id->id)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->groupBy('date_only')
                ->get();

            $dates = [];
            $date = Carbon::today();
            $oneMonthAgo = Carbon::today()->subMonth();

            while ($date->gte($oneMonthAgo)) {
                $dates[] = $date->format('Y-m-d');
                $date = $date->subDay();
            }

// Assume $dates_with_profits is the array you fetched from the database
            foreach ($dates as $date) {
                $found = false;
                foreach ($dates_with_profits as $item) {
                    if ($item['date_only'] === $date) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $dates_with_profits[] = [
                        'date_only' => $date,
                        'total_cost' => '0.00'
                    ];
                }
            }
            $dates_with_profits = $dates_with_profits->toArray();
            usort($dates_with_profits, function($a, $b) {
                return strtotime($a['date_only']) - strtotime($b['date_only']);
            });

            return $this->returnData('profits', $dates_with_profits);
        }
    }
/*
    public function profits_Monthly(){

//        $start_date = now()->subMonth()->startOfDay()->format('Y-m-d H:i:s');
        $start_date = '2023-03-30';
//        $end_date = now()->endOfDay()->format('Y-m-d H:i:s');
        $end_date = '2023-04-30';
        $supplier_id=Auth::guard('supplier-api')->user()->id;
        $parking_id=Parking::where('supplier_id',$supplier_id)->select('id')->first();
        if ($parking_id) {
            $profits = DB::select("
            SELECT
              dates.date_only AS date_only,
              COALESCE(SUM(payments.cost), 0) AS total_cost
            FROM (
              SELECT DATE(?) + INTERVAL a DAY AS date_only
              FROM (
                SELECT @a := @a + 1 AS a
                FROM (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3) AS a
                CROSS JOIN (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3) AS b
                CROSS JOIN (SELECT @a := -1) AS init
                LIMIT 31
              ) AS days
            ) AS dates
            LEFT JOIN payments
              ON DATE(payments.created_at) = dates.date_only
              AND payments.parking_id = ?
              AND payments.created_at BETWEEN ? AND ?
            GROUP BY dates.date_only
        ", [$start_date, $parking_id->id, $start_date, $end_date]);
            return $this->returnData('profits', $profits);
        }
    }
*/
   /* public function each_Month_Profits(){
        $supplier_id=Auth::guard('supplier-api')->user()->id;
        $parking_id=Parking::where('supplier_id',$supplier_id)->select('id')->first();
        if ($parking_id){
            $query="SELECT MONTH(payments.created_at) as month, SUM(payments.cost) as total_payments FROM payments WHERE payments.parking_id =1 AND YEAR(payments.created_at) = YEAR(CURDATE()) GROUP BY MONTH(payments.created_at)";
            $each_month_profits=DB::select($query);
            return $this->returnData('profits', $each_month_profits);
        }
    }*/
    public function each_Month_Profits()
    {
        $supplier_id = Auth::guard('supplier-api')->user()->id;
        $parking_id=Parking::where('supplier_id',$supplier_id)->select('id')->first();
        if ($parking_id){
            $profits = DB::table('payments')
                ->selectRaw('MONTH(created_at) as month, SUM(cost) as total_payments')
                ->where('parking_id', $parking_id->id)
                ->whereYear('created_at', '=', date('Y'))
                ->groupBy('month')
                ->get();

            // Convert stdClass objects to arrays
            $profits = json_decode(json_encode($profits), true);

            $all_months = range(1,12);
            $profits_months = array_column($profits, 'month');
            $missing_months = array_diff($all_months, $profits_months);

            foreach($missing_months as $month){
                $profits[] = ['month' => $month, 'total_payments' => 0];
            }

            usort($profits, function($a, $b) {
                return $a['month'] - $b['month'];
            });

            return $this->returnData('profits', $profits);
        }
    }
    public function profits_Weekly(){
        $supplier_id=Auth::guard('supplier-api')->user()->id;
        $parking_id=Parking::where('supplier_id',$supplier_id)->select('id')->first();
        $previous_friday = Carbon::now()->previous(Carbon::FRIDAY);
        if ($parking_id)
        {
            $profits = Payment::select(DB::raw('DATE(created_at) as date_only'), DB::raw('SUM(cost) as total_cost'))
                ->where('parking_id', $parking_id->id)->where('created_at','<',$previous_friday->toDate())->where('created_at','>',$previous_friday->subDays(7)->toDate())
                ->groupBy('date_only')
                ->get();
            return $this->returnData('profits', $profits);
        }
    }

}
