<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка валидации данных'
            ], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'payer_code' => $request->payer_code,
            'password' => Hash::make($request->string('password')),
        ]);

        if (!$user) {
            return response()->json(['message' => 'Не удалось создать пользователя'], 400);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
		
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ],200);
    }
}
