<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use App\Models\Readings;
use App\Models\User;
use Carbon\Carbon;

class ReadingsController extends Controller
{
    public function store (Request $request) {

        $readings = $request->json()->all();
        $user_id = Auth::id();

        $validationResponse = $this->validateReadings($readings, $user_id);

        if ($validationResponse instanceof JsonResponse) {
            return $validationResponse;
        }

        foreach ($readings as $reading) {

            try {

                Readings::create([
                    'user_id' => $user_id,
                    'reading_type' => $reading['reading_type'],
                    'reading_value' => $reading['reading_value']
                ]);

            } catch (\Exception $e) {
                return response()->json(['message' => 'Что-то пошло не так'], 400);
            }

        }

        $debtor = User::where('id', $user_id)
                ->first();
        $debtor->CalculatedInDebt=false;
        $debtor->save();

        Artisan::call('app:calculate-debts', ['user_id' => $user_id]);
        

    }

    private function validateReadings($readings, $user_id) {

        if (count($readings) !== 5) {
            return response()->json(['message' => 'Не все показаний переданы'], 422);
        }

        $expectedTypes = [1, 2, 3, 4, 5];
        $receivedTypes = array_map(fn($item) => $item['reading_type'], $readings);

        sort($receivedTypes);
        if ($receivedTypes !== $expectedTypes) {
            return response()->json(['message' => 'Невалидные данные'], 422);
        }

        $validRanges = [
            1 => function ($value) {
                return $value >= 50;
            },
            2 => function ($value) {
                return $value >= 1;
            },
            3 => function ($value) {
                return $value >= 1;
            },
            4 => function ($value) {
                return $value >= 1;
            },
            5 => function ($value) {
                return $value >= 0.5;
            },
        ];

        foreach ($readings as $reading) {
            $type = $reading['reading_type'];
            $value = $reading['reading_value'];

            // Проверка, что reading_value - число 
            if (!is_numeric($value)) {
                return response()->json([
                    'message' => "Показание должно быть числом"
                ], 422);
            }

            // Проверка диапазона для каждого типа
            if (!isset($validRanges[$type]) || !$validRanges[$type]($value)) {
                return response()->json([
                    'message' => "Невалидные данные"
                ], 422);
            }

            $previousReading = Readings::where('user_id', $user_id)
                                ->where('reading_type', $type)
                                ->where('created_at', '<', Carbon::now())
                                ->orderBy('created_at', 'desc') 
                                ->first();

            if ($type != 4) { //для кол-ва человек эта проверка не нужна
                if ($previousReading->reading_value >= $reading['reading_value']) {
                    return response()->json([
                        'message' => "Показания не могут быть меньше предыдущих или такими же"
                    ], 422);

                }
            }

        }

    }
}
