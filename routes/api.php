<?php

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Models\History;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post("/login", [UserController::class, "login"]);
Route::post("/signup", [UserController::class, "store"]);

Route::get("/email/verify/{id}/{hash}", function (Request $request) {
    $user = User::find($request->route("id"));

    if (!hash_equals((string) $request->route("hash"), sha1($user->getEmailForVerification()))) {
        throw new AuthorizationException;
    }

    if ($user->markEmailAsVerified()) event(new Verified($user));

    return response()->json(["message" => "Email successfully verified"], 200);
})->name("verification.verify");

Route::middleware("auth:sanctum")->group(function () {
    Route::get("/user", function (Request $request) {
        return response()->json(["data" => $request->user()], 200);
    });

    Route::get("/transaction", function (Request $request) {
        $transaction = Transaction::all()->where("user_id", $request->user()->id);

        return response()->json(["data" => $transaction], 200);
    });

    Route::post("/transaction", [TransactionController::class, "store"]);

    Route::post("/statistics", function (Request $request) {
        $start_time = date("Y-m-d H:i:s", substr($request->start_time, 0, 10));
        $end_time = date("Y-m-d H:i:s", substr($request->end_time, 0, 10));

        $history = History::all()->whereBetween("created_at", [$start_time, $end_time]);

        return response()->json(["data" => $history], 200);
    });

    Route::post("/email/verify", function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return response()->json(["message" => "Email successfully sent"], 200);
    })->name("verification.send");
});
