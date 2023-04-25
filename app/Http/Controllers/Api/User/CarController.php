<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    use GeneralTrait;
    public function add_car(Request $request){
        $validator=Validator::make($request->all(),[
            'plat_number'=>'required|string|between:3,6',

        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $car=Car::create(array_merge($request->all(),['user_id'=>Auth::guard('user-api')->user()->id]));
        return response()->json([
            'message' => 'car successfully registered',
            'car' => $car
        ], 201);
    }
    public function remove_car($id){
        $car=Car::where('id',$id)->where('user_id',Auth::guard('user-api')->user()->id);
        if ($car){
            $car->delete();
            return $this->returnSuccessMessage("deleted successfully");
        }else{
            return $this->returnError("404","Car doesn't exist");
        }
    }
}
