<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request){

        $validate = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'email' => $validate['email'],
            'password' => Hash::make($validate['password']),
        ]);


        return response()->json(['message'=>"User created successfully"], 201);
    }


    public function login(Request $request){
       $validate = $request->validate(
        [
            'email' => "required|email",
            'password' => 'required'
        ]);

        $user = User::where('email', $validate['email'])->first();

        if(!$user || !Hash::check($validate['password'], $user->password)){
            return response()->json(['message'=>"Invalid credintials"], 401);
        }

        return response()->json(['message' => "Login done successfully"]);
        
    }
}


