<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Controllers\Api\LinkApiController;

/*
|--------------------------------------------------------------------------
| API (Sanctum tokens / Bearer)
|--------------------------------------------------------------------------
| El frontend guarda el token y lo envía como:
|   Authorization: Bearer <token>
| No se usan cookies ni /sanctum/csrf-cookie aquí.
| Siempre responder JSON (el FE manda Accept: application/json).
*/

// REGISTER: POST /api/register (opcional)
Route::post('/register', function (Request $r) {
    $r->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
    ]);

    $user = User::create([
        'name'     => $r->name,
        'email'    => $r->email,
        'password' => Hash::make($r->password),
    ]);

    return response()->json($user, 201);
});

// LOGIN: POST /api/login → genera token Sanctum
Route::post('/login', function (Request $r) {
    $r->validate(['email' => 'required|email', 'password' => 'required']);

    if (! Auth::attempt($r->only('email', 'password'))) {
        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }

    /** @var \App\Models\User $user */
    $user = $r->user();

    // (Opcional) Revocar tokens anteriores:
    // $user->tokens()->delete();

    $token = $user->createToken('frontend')->plainTextToken;

    return response()->json([
        'user'  => $user,
        'token' => $token,
    ], 200);
});

// LOGOUT: POST /api/logout → revoca token actual
Route::post('/logout', function (Request $r) {
    $r->user()->currentAccessToken()?->delete();
    return response()->noContent();
})->middleware('auth:sanctum');

// Rutas protegidas con token
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // <- ESTA ES LA QUE NECESITA TU FRONT
    Route::get('/user', fn (Request $r) => $r->user());

    Route::get('links', [LinkApiController::class, 'index']);
    Route::post('links', [LinkApiController::class, 'store']);
    Route::get('links/{link}', [LinkApiController::class, 'show'])->can('view', 'link');
    Route::put('links/{link}', [LinkApiController::class, 'update'])->can('update', 'link');
    Route::delete('links/{link}', [LinkApiController::class, 'destroy'])->can('delete', 'link');
    Route::get('links/{link}/stats', [LinkApiController::class, 'stats'])->can('view', 'link');
});
