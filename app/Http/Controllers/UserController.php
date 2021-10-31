<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Validator;

class UserController extends Controller
{

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'max:20', 'confirmed']
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
        return response()->json([
            'user' => \Arr::only($user->toArray(), ['name', 'email']),
            'token' => $user->createToken('buyer', ['product.view', 'order.view', 'order.create', 'order.edit'])->plainTextToken
        ], Response::HTTP_OK);
    }

    public function logout(): JsonResponse
    {
        auth()->user()->tokens()->delete();
        return \response()->json(['message' => 'logged out.'], Response::HTTP_OK);
    }

    public function login(Request $request): JsonResponse
    {
        $attributes = validator($request->all(), [
            'email' => ['required', 'email', Rule::exists('users', 'email')],
            'password' => ['required', 'min:8', 'max:20']
        ])->validate();

        $user = User::where('email', $attributes['email'])->first();
        abort_if((!$user || !Hash::check($attributes['password'], $user->password)), Response::HTTP_UNAUTHORIZED, 'Credentials are invalid.');

        return \response()->json([
            'user' => \Arr::only($user->toArray(), ['name', 'email']),
            'token' => $user->createToken('buyer', ['product.view', 'order.view', 'order.create', 'order.edit'])->plainTextToken
        ], Response::HTTP_OK);
    }
}
