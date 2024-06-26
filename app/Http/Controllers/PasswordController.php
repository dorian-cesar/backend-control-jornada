<?php

// app/Http/Controllers/Api/PasswordController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User; 

class PasswordController extends Controller
{
    public function update(Request $request)
    {
        // Validación de la solicitud
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificación de la contraseña actual
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return response()->json(['error' => 'La contraseña actual no es correcta'], 403);
        }
          // Debugging: Verifica el objeto $user
        //  dd($user);
        // Actualización de la contraseña
        $user = Auth::user();
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'La contraseña ha sido actualizada correctamente'], 200);
    }
}
