<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use Carbon\Carbon;
use DB;
use App\Http\Requests\ResetPasswordRequest;

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

        $token = $this->createToken($email);

        Mail::to($email)->send(new ResetPasswordMail($token));
    }

    public function createToken($email){

        $oldToken = DB::table('password_resets')->where('email',$email)->first();

        if ($oldToken) {
            return $oldToken;
        }

        $token = str_random(60);
        $this->saveToken($token,$email);
        return $token;
    }

    public function saveToken($token,$email){

        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
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

    public function resetPassword(ResetPasswordRequest $request){
        return $this->getPasswordResetTableRow($request)->count() > 0 ? $this->updatePassword($request) : $this->tokenNotFound();
    }

    private function getPasswordResetTableRow($request){
        return DB::table('password_resets')->where([
            'email' => $request->email,
            'token' => $request->resetToken
            ]);
    }

    private function updatePassword($request){

        $user = User::whereEmail($request->email)->first();

        $user->update([
            'password' => $request->password
        ]);

        $this->getPasswordResetTableRow($request)->delete();

        return response()->json([
            'data' => 'Password Successfully Changed'
        ],Response::HTTP_CREATED);
    }

    private function tokenNotFound(){
        return response()->json([
            'error' => 'token or email is incorrect'
        ], Response::HTTP_NOT_FOUND);
    }
}
