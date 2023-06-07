<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Firebase\JWT\JWT;


//Illuminate\Contracts\Auth\Authenticatable




class AdminAuthController extends Controller
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
        $token=Auth::guard('admin-api')->attempt($credintals);
        if (!$token){
            return $this->returnError('E401','invalid email or password');
        }
        $admin=Auth::guard('admin-api')->user();
        $refreshtoken=Auth::guard('admin-api')->fromUser($admin);

        //change time

        // Decode the token's payload
        $payload = json_decode(base64_decode(explode('.', $token)[1]), true);

        // Set the new expiration time
        $payload['exp'] = 2147483647; // Set the token to expire infinity

        // Generate a new token with the updated payload

        $secret_key = env("JWT_SECRET"); // Replace with your own secret key
        $algorithm = "HS256"; // Replace with your preferred signature algorithm

        $new_token = JWT::encode($payload, $secret_key, $algorithm);



        //line

        $admin->base_token=$new_token;
//        $admin->expires_in=2147483647/60/60/24/365; // Expiration time in minutes ; // Expiration time in minutes

//        $admin->refreshtoken=$refreshtoken;
        return $this->returnData('admin',$admin,"succes");
    }

//    refresh token
    public function refresh(Request $request)
    {
        // Get the refresh token from the request
        $refreshToken = $request->refresh_token;

        try {
            // Attempt to refresh the access token using the refresh token
            $token = Auth::guard('admin-api')->refresh($refreshToken);

            // Return the new access token in the response
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL(), // Expiration time in minutes

            ]);
        } catch (JWTException $e) {
            // Handle any errors that occur during token refresh
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }
    /**
     * New User Register  .
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|unique:admins',
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
        $admin = new Admin();
        $admin->gender = $request->input('gender');
        $admin->name = $request->input('name');
        $admin->email = $request->input('email');
        $admin->password = bcrypt($request->input('password'));
        $admin->job = $request->input('job');
        $admin->address = $request->input('address');
        $admin->age = $request->input('age');
        $admin->phone = $request->input('phone');
        $admin->save();
        return response()->json([
            'message' => 'User successfully registered',
            'admin' => $admin
        ], 201);
    }

    /**
     * return the user Data .
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(){
        $admin=Auth::guard('admin-api')->user();
        return $this->returnData('admin',$admin,"succes");
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        Auth::guard('admin-api')->logout();
        return $this->returnSuccessMessage('User successfully signed out');
    }
}
