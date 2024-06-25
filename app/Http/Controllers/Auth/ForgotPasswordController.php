<?php
namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;

/*class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
 
        $status = Password::sendResetLink(
            $request->only('email')
        );
    
        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message'=>'Correo enviado correctamente!'],200);
        } else if ($status === Password::INVALID_USER) {
            return response()->json(['message'=>'Usuario no existe!'],404);
        } else if ($status === Password::RESET_THROTTLED) {
            return response()->json(['message'=>'Demasiados intentos!'],404);
        } else {
            return response()->json(['message'=>$status],404);
        }
    }
}
    */


class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)], 200)
            : response()->json(['email' => __($status)], 400);
    }
}
