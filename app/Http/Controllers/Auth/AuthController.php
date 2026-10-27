<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Mail\WelcomeUserMail;
use App\Models\User;
use App\Support\IdentityHasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function __construct(private readonly IdentityHasher $hasher) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $email = (string) $request->validated('email');

        $user = User::query()->create([
            'name' => $request->validated('nick'),
            'nick' => $request->validated('nick'),
            'email' => $email,
            'email_hash' => $this->hasher->hashEmail($email),
            'password' => $request->validated('password'),
            'department' => $request->validated('department'),
            'is_admin' => false,
        ]);

        Mail::to($user)->queue(new WelcomeUserMail($user));

        return $this->tokenResponse($user, 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $login = $request->validated('username');

        $user = User::query()
            ->where(function ($query) use ($login): void {
                $query->where('email', $login)->orWhere('nick', $login);
            })
            ->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Credenciales incorrectas.'],
            ]);
        }

        return $this->tokenResponse($user);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $request->user()->update($request->validated());

        return response()->json($request->user()->fresh()?->toAuthArray());
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    private function tokenResponse(User $user, int $status = 200): JsonResponse
    {
        $token = $user->createToken('spa')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->toAuthArray(),
        ], $status);
    }
}
