<?php

//use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Parkings\SupplierParkingController;
use App\Http\Controllers\Api\Parkings\UserParkingController;
use App\Http\Controllers\Api\Supplier\ReservationSupplierController;
use App\Http\Controllers\Api\Supplier\SupplierAuthController;
use App\Http\Controllers\Api\User\CarController;
use App\Http\Controllers\Api\User\RechargeController;
use App\Http\Controllers\Api\User\ReservationUserController;
use App\Http\Controllers\Api\User\UserAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//check api authentication key
/*Route::middleware('checkpassword')->get('test-api-key',function (){
    return response()->json(['message'=>"Authenticated successfully",'status'=>true]);
});*/
//check api jwt middleware
Route::middleware('verify.jwt')->get('check-auth',function (){
    return response()->json(['message'=>"Authenticated successfully",'status'=>true]);
});

//Admin routes
//----------un authenticated routes
Route::group(['prefix'=>'admin'],function (){
    Route::post('login',[AdminAuthController::class,'login']);
    Route::post('register',[AdminAuthController::class,'register']);
    Route::post('refresh',[AdminAuthController::class,'refresh']);
});
//----------authenticated routes----------
Route::group(['prefix'=>'admin','middleware'=>['verify.jwt']],function (){
    Route::post('logout',[AdminAuthController::class,'logout']);
    Route::get('profile',[AdminAuthController::class,'profile']);

});
//User routes
//----------un authenticated routes
Route::group(['prefix'=>'user'],function (){
    Route::post('login',[UserAuthController::class,'login']);
    Route::post('register',[UserAuthController::class,'register']);
    Route::get('all-parkings',[UserParkingController::class,'get_all_parkings_location']);
    Route::get('nearest-parkings',[UserParkingController::class,'get_nearest_parkings']);
    Route::get('nearest-one-parking',[UserParkingController::class,'get_nearest_one_parkings']);
});
//----------authenticated routes----------
Route::group(['prefix'=>'user','middleware'=>['verify.jwt']],function (){
    Route::post('logout',[UserAuthController::class,'logout']);
    Route::get('profile',[UserAuthController::class,'profile']);
    Route::post('add-car',[CarController::class,'add_car']);
    Route::get('remove-car/{id}',[CarController::class,'remove_car']);
    Route::post('make-reservation',[ReservationUserController::class,'make_reservation']);
    Route::post('cancel-reservation',[ReservationUserController::class,'cancel_reservation']);
    Route::post('start-reservation',[ReservationUserController::class,'start_reservation']);
    Route::post('end-reservation',[ReservationUserController::class,'end_reservation']);
    Route::get('get-all-reservations',[ReservationUserController::class,'get_all_reservation']);
    Route::post('recharge',[RechargeController::class,'recharge']);
    Route::get('all-recharges',[RechargeController::class,'get_All_Charges']);
    Route::get('day-recharges',[RechargeController::class,'get_Day_Charges']);
    Route::get('interval-recharges',[RechargeController::class,'get_Interval_Charges']);
    Route::get('day-payments',[RechargeController::class,'day_payments']);
    Route::get('all-payments',[RechargeController::class,'all_payments']);
    Route::get('interval-payments',[RechargeController::class,'interval_payments']);

});

//Supplier routes
//----------un authenticated routes
Route::group(['prefix'=>'supplier'],function (){
    Route::post('login',[SupplierAuthController::class,'login']);
    Route::post('register',[SupplierAuthController::class,'register']);
});
//----------authenticated routes----------
Route::group(['prefix'=>'supplier','middleware'=>['verify.jwt']],function (){
    Route::post('logout',[SupplierAuthController::class,'logout']);
    Route::get('profile',[SupplierAuthController::class,'profile']);
    Route::post('add-parking',[SupplierParkingController::class,'add_parking']);
    Route::get('parking',[SupplierParkingController::class,'my_parking']);
    Route::get('day-profits',[SupplierParkingController::class,'day_profits']);
    Route::get('total-profits',[SupplierParkingController::class,'total_profits']);
    Route::get('interval-profits',[SupplierParkingController::class,'interval_profits']);
    Route::get('day-payments',[SupplierParkingController::class,'day_payments']);
    Route::get('total-payments',[SupplierParkingController::class,'total_payments']);
    Route::get('interval-payments',[SupplierParkingController::class,'interval_payments']);

    Route::controller(ReservationSupplierController::class)->group(function (){
        Route::get('get-all-reservations', 'get_all_reservation');
        Route::get('get-reservations-interval', 'get_reservation_interval');
        Route::get('get-wait-reservations', 'get_wait_reservation');
        Route::get('get-wait-reservations-interval', 'get_wait_reservation_interval');
        Route::get('get-active-reservations', 'get_active_reservation');
        Route::get('get-active-reservations-interval', 'get_active_reservation_interval');
        Route::get('get-done-reservations', 'get_done_reservation');
        Route::get('get-done-reservations-interval', 'get_done_reservation_interval');
        Route::get('get-canceled-reservations', 'get_canceled_reservation');
        Route::get('get-canceled-reservations-interval', 'get_canceled_reservation_interval');
    });

});

