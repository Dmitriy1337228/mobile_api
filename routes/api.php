<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ReadingsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Balances;
use App\Models\OperationsHistory;
use App\Models\Readings;
use Carbon\Carbon;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();    
});
Route::middleware(['auth:sanctum'])->get('/balance', function (Request $request) {
    return response()->json(strval((Balances::where('user_id',Auth::id())->first())->balance_value));
});
Route::middleware(['auth:sanctum'])->get('/lastreadings', function (Request $request) {

    $previousReadings = Readings::where('user_id', Auth::id())
                                ->where('created_at', '<', Carbon::now())
                                ->orderBy('created_at', 'desc')
                                ->limit(5) 
                                ->get();

    return response()->json($previousReadings);
});
Route::middleware(['auth:sanctum'])->get('/operationshistory', function (Request $request) {
    return response()->json(OperationsHistory::where('user_id',Auth::id())->get());
});
Route::middleware(['auth:sanctum'])->post('/topupbalance', function (Request $request) {

    $new_balance = $request->json()->all();

    OperationsHistory::create([
                    'user_id' => Auth::id(),
                    'Description'=>'Пополнение баланса на сумму: '. $new_balance['value'],
                    'DateTime'=>(new \DateTime())->format('Y-m-d H:i:s')
                ]);

    $userBalance = Balances::where('user_id',Auth::id())->first();
    $userBalance->balance_value += $new_balance['value'];
    $userBalance->save();

});
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::middleware('auth:sanctum')->post('/storereadings', [ReadingsController::class, 'store']);
	
Route::get('/test', function () {
    return response()->json(['message' => 'Test route works']);
});	
