<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Controllers\Api\LinkApiController;
// routes/api.php (fuera del grupo auth:sanctum)
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::post('/login-token', [AuthenticatedSessionController::class, 'loginToken']);



// Si no lo tienes ya:
Route::middleware(['auth:sanctum'])->get('/user', fn (Request $r) => $r->user());

// --- Auth API mínimos ---

// REGISTER: POST /api/register
Route::post('/register', function (Request $r) {
    $r->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
    ]);

    $user = User::create([
        'name' => $r->name,
        'email' => $r->email,
        'password' => Hash::make($r->password),
    ]);

    return response()->json($user, 201);
});

// LOGIN: POST /api/login  → devuelve Bearer token (Sanctum)
Route::post('/login', function (Request $r) {
    $r->validate(['email' => 'required|email', 'password' => 'required']);

    if (! Auth::attempt($r->only('email', 'password'))) {
        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }

    /** @var \App\Models\User $user */
    $user = $r->user();
    $token = $user->createToken('postman')->plainTextToken;

    return response()->json([
        'user'  => $user,
        'token' => $token,
    ], 200);
});
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('links', [LinkApiController::class, 'index']);
    Route::post('links', [LinkApiController::class, 'store']);
    Route::get('links/{link}', [LinkApiController::class, 'show'])->can('view','link');
    Route::put('links/{link}', [LinkApiController::class, 'update'])->can('update','link');
    Route::delete('links/{link}', [LinkApiController::class, 'destroy'])->can('delete','link');
    Route::get('links/{link}/stats', [LinkApiController::class, 'stats'])->can('view','link');
});