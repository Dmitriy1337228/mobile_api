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
use App\Models\Readings;

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

        $user_id = $user->id;
        $this->randomReadings($user_id);

        $token = $user->createToken('auth_token')->plainTextToken;
		
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ],200);
    }

    //Randomize readings for created user
    private function randomReadings($user_id) {

        $readingData = [
            ['reading_type' => 1], // Электричество
            ['reading_type' => 2], // Холодная вода
            ['reading_type' => 3], // Горячая вода
            ['reading_type' => 4], // Общедомовые услуги
            ['reading_type' => 5] // Отопление
        ];

        foreach ($readingData as $data) {

            $readingValue = match ($data['reading_type']) {
                1 => rand(50, 500) + rand(0, 99) / 100, // Электричество, квт·ч
                2 => rand(1, 15) + rand(0, 99) / 100,   // Холодная вода, м³
                3 => rand(1, 10) + rand(0, 99) / 100,   // Горячая вода, м³
                4 => rand(1, 5),    // Общедомовые услуги, человек
                5 => rand(50, 500) / 100 // Отопление, Гкал
            };

            Readings::create([
                'user_id' => $user_id, // ID авторизованного пользователя
                'reading_type' => $data['reading_type'],
                'reading_value' => $readingValue
            ]);

        }

    }

}
