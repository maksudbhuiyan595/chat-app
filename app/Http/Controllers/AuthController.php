<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function home(){
        return view("frontend.page.home");
    }
    public function login(Request $request){

            $credentials = $request->only("email","password");
          $user= auth()->attempt($credentials);
            if($user){
                return redirect("dashboard")->with("success","");
            }else{
                return back();
            }
    }
    public function logout(){
        auth()->logout();
        return redirect()->route("home");
    }

}
