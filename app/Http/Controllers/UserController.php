<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use RefreshDatabase;

    public function create(Request $request): JsonResource
    {
        $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'max:20', 'confirmed']
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
        Auth::attempt(['email' => $user->email, 'password' => $user->password]);
        return UserResource::make($user);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'max:20']
        ]);
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return UserResource::make(Auth::user());
        } else {
            return response()->json(['message' => 'User not found.'], Response::HTTP_NOT_FOUND);
        }
    }
}
