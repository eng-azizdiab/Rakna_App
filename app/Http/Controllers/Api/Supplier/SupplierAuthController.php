<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\Supplier;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SupplierAuthController extends Controller
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
        $token=Auth::guard('supplier-api')->attempt($credintals);
        if (!$token){
            return $this->returnError('E401','invalid email or password');
        }
        //change expire time of token
// Decode the token's payload
        $payload = json_decode(base64_decode(explode('.', $token)[1]), true);

        // Decode the token's payload
        $payload = json_decode(base64_decode(explode('.', $token)[1]), true);

        // Set the new expiration time
        $payload['exp'] = 2147483647; // Set the token to expire infinity

        // Generate a new token with the updated payload

        $secret_key = env("JWT_SECRET"); // Replace with your own secret key
        $algorithm = "HS256"; // Replace with your preferred signature algorithm

        $new_token = JWT::encode($payload, $secret_key, $algorithm);
        $supplier=Auth::guard('supplier-api')->user();
        $supplier->token=$new_token;
        return $this->returnData('supplier',$supplier,"succes");
    }

    /**
     * New User Register  .
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'gender'=>'required|in:male,female',
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:suppliers|unique:admins|unique:users',
            'password' => 'required|string|min:6',
            'job'=>'required',
            'address'=>'required|min:6',
            'age'=>'required',
            'phone'=>'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $supplier = new Supplier();
        $supplier->gender = $request->input('gender');
        $supplier->name = $request->input('name');
        $supplier->email = $request->input('email');
        $supplier->password = bcrypt($request->input('password'));
        $supplier->job = $request->input('job');
        $supplier->address = $request->input('address');
        $supplier->age = $request->input('age');
        $supplier->phone = $request->input('phone');
        $supplier->save();
      /*  $supplier = Supplier::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));*/
        return response()->json([
            'message' => 'User successfully registered',
            'supplier' => $supplier
        ], 201);
    }

    /**
     * return the user Data .
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(){
        $supplier=Auth::guard('supplier-api')->user();
        return $this->returnData('supplier',$supplier,"succes");
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        Auth::guard('supplier-api')->logout();
        return $this->returnSuccessMessage('User successfully signed out');
    }
}
