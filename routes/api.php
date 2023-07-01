<?php

//use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Parkings\SupplierParkingController;
use App\Http\Controllers\Api\Parkings\UserParkingController;
use App\Http\Controllers\Api\Parkings\ParkingController;
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
    Route::get('users',[AdminController::class,'all_Users']);
    Route::get('user',[AdminController::class,'user_By_Id']);
    Route::get('reservations-user/{user_id}',[AdminController::class,'all_Reservations_UserId']);
    Route::get('reservations-parking/{parking_id}',[AdminController::class,'all_Reservations_ParkingId']);
    Route::get('reservations',[AdminController::class,'all_Reservations']);
    Route::get('reservation/{uid}',[AdminController::class,'reservation_By_Uid']);

    Route::get('get-reservations-interval', [AdminController::class,'get_reservation_interval']);
    Route::get('get-wait-reservations', [AdminController::class,'get_wait_reservation']);
    Route::get('get-wait-reservations-interval', [AdminController::class,'get_wait_reservation_interval']);
    Route::get('get-active-reservations', [AdminController::class,'get_active_reservation']);
    Route::get('get-active-reservations-interval', [AdminController::class,'get_active_reservation_interval']);
    Route::get('get-done-reservations', [AdminController::class,'get_done_reservation']);
    Route::get('get-done-reservations-interval', [AdminController::class,'get_done_reservation_interval']);
    Route::get('get-canceled-reservations', [AdminController::class,'get_canceled_reservation']);
    Route::get('get-canceled-reservations-interval', [AdminController::class,'get_canceled_reservation_interval']);


    Route::get('suppliers',[AdminController::class,'all_Suppliers']);
    Route::get('supplier',[AdminController::class,'supplier_By_Id']);
    Route::get('suppliers-parkings',[AdminController::class,'all_Suppliers_Parkings']);
    Route::get('parking',[AdminController::class,'parking_By_Supplier_Id']);
    Route::get('parkings',[AdminController::class,'all_Parkings']);
    Route::get('empty-parkings',[AdminController::class,'empty_Parkings']);
    Route::get('busy-parkings',[AdminController::class,'busy_Parkings']);
    Route::get('full-parkings',[AdminController::class,'full_Parkings']);
    Route::get('parking-statistics',[AdminController::class,'parking_Statistics']);
    Route::get('max-parking-profit',[AdminController::class,'max_Parking_profit']);
    Route::get('min-parking-profit',[AdminController::class,'min_Parking_profit']);
    Route::get('each-parking-profit',[AdminController::class,'each_Parking_Profit']);
    Route::get('parkings-day-profit',[AdminController::class,'parkings_Day_Profit']);
    Route::get('free-parkings',[AdminController::class,'all_Parkings']);
    Route::get('supplier',[AdminController::class,'Supplier_By_parking_Id']);
    Route::get('day-profits',[AdminController::class,'day_Profits']);
    Route::get('day-profits-parking',[AdminController::class,'day_Profits_Parking']);
    Route::get('total-profits',[AdminController::class,'total_Profits']);
    Route::get('total-profits-parking',[AdminController::class,'total_Profits_Parking']);
    Route::get('interval-profits',[AdminController::class,'interval_Profits']);
    Route::get('interval-profits-parking',[AdminController::class,'interval_Profits_Parking']);
    Route::get('day-payments',[AdminController::class,'day_Payments']);
    Route::get('total-payments',[AdminController::class,'total_Payments']);
    Route::get('interval-payments',[AdminController::class,'interval_Payments']);
    Route::get('parking-day-payments',[AdminController::class,'day_Payments_Parking']);
    Route::get('parking-total-payments',[AdminController::class,'total_Payments_Parking']);
    Route::get('parking-interval-payments',[AdminController::class,'interval_Payments_Parking']);
    Route::get('user-day-payments',[AdminController::class,'day_Payments_User']);
    Route::get('user-total-payments',[AdminController::class,'total_Payments_User']);
    Route::get('user-interval-payments',[AdminController::class,'interval_Payments_User']);
    Route::get('payment',[AdminController::class,'payment_By_Id']);


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
    Route::get('car-data',[CarController::class,'car_Data']);
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
    Route::post('upload-file',[UserAuthController::class,'file_upload']);

});
Route::post('face-recognize',[AdminController::class,'face_Recognition']);
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
    Route::get('monthly-profits',[SupplierParkingController::class,'profits_Monthly']);
    Route::get('each-month-profits',[SupplierParkingController::class,'each_Month_Profits']);
    Route::get('weekly-profits',[SupplierParkingController::class,'profits_Weekly']);
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



Route::controller(ParkingController::class)->prefix('parking')->group(function (){
    Route::get('parking-slots-info', 'parking_Slots_Data');
    Route::post('start-reservation','start_reservation');
    Route::post('end-reservation','end_reservation');

});
