<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserAuthController extends Controller
{
    use GeneralTrait;
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function login(Request $request){
        $rules=[
            "email"=>"required|email",
            "password"=>"required|min:6"
        ];
        $validator=Validator::make($request->all(),$rules);
        if ($validator->fails()){
            $code=$this->returnCodeAccordingToInput($validator);
            return $this->returnValidationError($code,$validator);
        }
        $credintals=$request->only(['email','password']);
        $token=Auth::guard('user-api')->attempt($credintals);
        if (!$token){
            return $this->returnError('E401','invalid email or password');
        }
        $user=Auth::guard('user-api')->user();
        $user->token=$token;
        return $this->returnData('user',$user,"succes");
    }

    /**
     * New User Register  .
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'gender'=>'required',
            'job'=>'required',
            'address'=>'required',
            'age'=>'required',
            'phone'=>'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));
        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    /**
     * return the user Data .
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(){
        $user=Auth::guard('user-api')->user();
        return $this->returnData('user',$user,"succes");
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        Auth::guard('user-api')->logout();
        return $this->returnSuccessMessage('User successfully signed out');
    }
}
