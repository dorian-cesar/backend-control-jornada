<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request) {
        $users = User::all(['id','name','email','level']);

        if($request->has('searchqry')){
            $query = $request->searchqry;

            $users = User::where('name', 'LIKE', "%{$query}%");
        } 

        $sortCol = $request->input('sort.col');
        $sortDir = $request->input('sort.dir');

        if ($sortCol && $sortDir) {
            $users->orderBy($sortCol, $sortDir);
        }

        return $request->has('page') ? $users->paginate(12) : $users;
    }

    public function getAll(){
        $users = User::all(['id','name','email','level']);
        return response()->json($users, 200);
    }

    public function show($id){
        return User::findOrFail($id);
    }

    public function register(Request $request) {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'level' => 'required|integer|max:10'
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'level' => $validatedData['level'],
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

    public function adminUpdate(Request $request, $id){
        $user = User::findOrFail($id);

        $rules = [
            'email' => [
                'required',
                Rule::unique('users')->ignore($user->id)->where(function ($query) use ($request, $user) {
                    return $query->where('email', $request->email)
                                 ->where('id', '!=', $user->id);
                }),
            ],
            'name' => 'required|string|max:255',
            'level' => 'required|integer|max:10'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update($request->all());

        return $user;
    }
    public function destroy($id)
    {
        User::where('id',$id)->first()->delete();
        return response()->json(null, 204);
    }
}
