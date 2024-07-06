<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    public function questionUsers()
    {
        $user = Auth::guard('api')->user();
        $questions = $user->questions()->with('roles')->where('status', true)->pluck('id');
        dd($questions);

        foreach ($questions as $question) {
            dd($question->roles->pluck('name'));
        }

        dd($questions);

    }
}
