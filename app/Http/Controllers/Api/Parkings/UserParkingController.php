<?php

namespace App\Http\Controllers\Api\Parkings;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\Parking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserParkingController extends Controller
{
    use GeneralTrait;
    //to get all parkings include name, is empty,price,location
    public function get_all_parkings_location(){
        $query="SELECT id,name,has_free_slot,price_per_hour, ST_X(location) as latitude, ST_Y(location) as longitude FROM parkings";
        $parkings=DB::select($query);
        return $this->returnData('parkings',$parkings,"success");
    }
    //take a point and get the nearest parkings from this point
    public function get_nearest_parkings(Request $request){
        $latitude=floatval($request->latitude);
        $longtiude=floatval($request->longitude);
        $distance=isset($request->distance)? floatval($request->distance):500;
        $query="SELECT id, name,has_free_slot,price_per_hour,ST_X(location) as latitude, ST_Y(location) as longitude,
       ST_Distance_Sphere(location, POINT($latitude,$longtiude)) AS distance
FROM parkings WHERE ST_Distance_Sphere(location, POINT($latitude,$longtiude)) <= $distance
              ORDER BY distance ASC";

        $parkings=DB::select($query);
        return $this->returnData('parkings',$parkings,"success");
    }
    //take a point and get the nearest parking from this point
    public function get_nearest_one_parkings(Request $request){
        $latitude=floatval($request->latitude);
        $longtiude=floatval($request->longitude);
        $distance=isset($request->distance)? floatval($request->distance):500;
        $query="SELECT id,name,has_free_slot,price_per_hour,ST_X(location) as latitude, ST_Y(location) as longitude,
       ST_Distance_Sphere(location, POINT($latitude,$longtiude)) AS distance
FROM parkings WHERE ST_Distance_Sphere(location, POINT($latitude,$longtiude)) <= $distance
              ORDER BY distance ASC LIMIT 1";

        $parkings=DB::select($query);
        return $this->returnData('parkings',$parkings,"success");
    }
}
