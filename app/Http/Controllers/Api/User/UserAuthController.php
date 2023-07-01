<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\User;
use Firebase\JWT\JWT;
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
        // Decode the token's payload
        $payload = json_decode(base64_decode(explode('.', $token)[1]), true);

        // Set the new expiration time
        $payload['exp'] = 2147483647; // Set the token to expire infinity

        // Generate a new token with the updated payload

        $secret_key = env("JWT_SECRET"); // Replace with your own secret key
        $algorithm = "HS256"; // Replace with your preferred signature algorithm

        $new_token = JWT::encode($payload, $secret_key, $algorithm);
        $user=Auth::guard('user-api')->user();
        $user->token=$new_token;
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
            'profile_picture'=>'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $user = new User();
        $user->gender = $request->input('gender');
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->job = $request->input('job');
        $user->address = $request->input('address');
        $user->age = $request->input('age');
        $user->phone = $request->input('phone');
        $user->profile_picture = $request->input('profile_picture');
        $user->save();
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

    public function file_upload(Request $request){
        if ($request->hasFile('attachment')) {
            $user=Auth::guard('user-api')->user();
            $user->user_file=$request->file('attachment');
            return $this->returnSuccessMessage('User successfully signed out');
        }
        return $this->returnError('404','provide file pleas');
    }
}
