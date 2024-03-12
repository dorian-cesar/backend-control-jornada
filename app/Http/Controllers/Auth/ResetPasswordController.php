<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;

class ResetPasswordController extends Controller
{
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );


        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Contraseña cambiada.'], 200);
        } else if($status === Password::INVALID_USER){
            return response()->json(['message' => 'Usuario incorrecto.'], 404);
        } else if($status === Password::INVALID_TOKEN){
            return response()->json(['message' => 'Token expirado, ingrese una nueva solicitud.'], 404);
        } else {
            return response()->json(['message'=>$status],404);
        }
    }
}