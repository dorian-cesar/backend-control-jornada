<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            /*if($user->created_at==$user->updated_at){
                return response()->json(['message' => 'Debe solicitar cambio de contaseña!'], 404);
            }*/

            $token = $request->user()->createToken('authToken')->plainTextToken;

            return response()->json(['token' => $token, 'level' => $user->level, 'name' => $user->name, 'id' => $user->id]);
        }

        return response()->json(['message' => 'Los datos ingresados no son correctos!'], 401);
    }

    public function logout(Request $request)
    {
        // Get bearer token from the request
        $accessToken = $request->bearerToken();
        
        // Get access token from database
        $token = PersonalAccessToken::findToken($accessToken);

        // Revoke token
        $token->delete();

        return response()->json(['message' => 'Logout completado']);
    }
}
