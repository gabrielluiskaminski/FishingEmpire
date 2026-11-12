<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthService $authService, CartService $cartService)
    {
        $login = $authService->login($request->validated());

        if (!$login){
            return response()->json([
                'message' => 'Usuário ou senha inválidos'
            ], 422);
        }

        $request->session()->regenerate();

        $user = Auth::guard('web')->user();
        $guestToken = $request->cookie('guest_token');


        try {
            $warnings = $cartService->mergeGuestCart($user, $guestToken);
        } catch (ValidationException | ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Login  realizado com sucesso.',
                'warning' => 'Não foi possivel unir os carrinhos.'
            ], 200);
        }

        $response = response()->json([
            'message'  => 'Login realizado com sucesso',
            'warnings' => $warnings
        ], 200);

        if ($guestToken) {
            $response->withoutCookie('guest_token');
        }

        return $response;
    }
}
