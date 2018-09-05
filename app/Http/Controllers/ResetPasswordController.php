<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;

class ResetPasswordController extends Controller
{
    public function sendEmail(Request $request){

       if (!$this->validateEmail($request->email)) {
           # code...
            return $this->failedResponse();
       }

       $this->send($request->email);
       return $this->successResponse();
    }

    public function send($email){
        Mail::to($email)->send(new ResetPasswordMail);
    }

    public function validateEmail($email){
        return !!User::where('email',$email)->first();
    }

    public function failedResponse(){
        return response()->json([
            'error' => 'email does not found on database'
        ], Response::HTTP_NOT_FOUND);
    }

    public function successResponse(){
        return response()->json([
            'data' => 'email link was send, check your email inbox'
        ], Response::HTTP_OK);
    }
}
