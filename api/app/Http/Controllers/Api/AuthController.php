<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, CartService $cartService): JsonResponse
    {
        $data = $request->validated();
        $user = User::create($data); // is_admin não é preenchível; password tem cast hashed no Model.

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return $this->afterAuthentication($request, $cartService, 'Cadastro realizado com sucesso.', 201);
    }

    public function login(LoginRequest $request, AuthService $authService, CartService $cartService): JsonResponse
    {
        if (!$authService->login($request->validated())) {
            throw ValidationException::withMessages([
                'email' => 'Credenciais inválidas.',
            ]);
        }

        $request->session()->regenerate();
        return $this->afterAuthentication($request, $cartService, 'Login realizado com sucesso.');
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

    private function afterAuthentication(Request $request, CartService $cartService, string $message, int $status = 200): JsonResponse
    {
        $guestToken = $request->cookie('guest_token');
        $warnings = [];
        $merged = false;

        if ($guestToken) {
            try {
                $warnings = $cartService->mergeGuestCart(Auth::guard('web')->user(), $guestToken);
                $merged = true;
            } catch (\Throwable $exception) {
                // Falhas imprevistas são registradas. Login/cadastro continuam válidos;
                // o cookie é preservado para nova tentativa de fusão.
                report($exception);
                $warnings[] = [
                    'message' => 'Não foi possível unir os carrinhos. Tente novamente ao entrar na conta.',
                ];
            }
        }

        $response = response()->json([
            'message' => $message,
            'user' => new UserResource(Auth::guard('web')->user()),
            'warnings' => $warnings,
            'cart_merged' => $merged,
        ], $status);

        if ($merged) {
            $response->withoutCookie('guest_token', '/');
        }

        return $response;
    }
}
