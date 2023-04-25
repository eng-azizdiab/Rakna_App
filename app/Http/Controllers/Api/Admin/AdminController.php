<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\GeneralTrait;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
//Illuminate\Contracts\Auth\Authenticatable




class AdminController extends Controller
{
    use GeneralTrait;
    public function get_All_Users(){
        $users=User::all();
        return $this->returnData('users',$users,);
    }

}
