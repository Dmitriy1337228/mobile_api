<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        // Валидация данных запроса
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Находим пользователя по email
        $user = User::where('email', $request->email)->first();

        // Если пользователь не найден или пароль неверный
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Увеличиваем лимит запросов при неправильных данных
            RateLimiter::hit($this->throttleKey());

            return response()->json(['message' => 'Invalid login credentials'], 401);
        }

        // Очищаем лимит запросов, если аутентификация прошла успешно
        RateLimiter::clear($this->throttleKey());

        // Создаём токен для пользователя
        $token = $user->createToken('auth_token')->plainTextToken;

        // Возвращаем токен и информацию о пользователе
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'status' => 'Login successful',
        ]);
    }

    // Генерация ключа для ограничения запросов
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower(request('email')).'|'.request()->ip());
    }

    /**
     * Logout the user (invalidate token).
     */
    public function destroy(Request $request): Response
    {
        // Удаляем все токены пользователя
        $request->user()->tokens->each(function ($token) {
            $token->delete();
        });

        // Возвращаем успешный ответ
        return response()->noContent();
    }
}
