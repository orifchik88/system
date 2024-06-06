<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function profile()
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();

        }else{
        }
    }
}
