<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthService $authService)
    {
        $login = $authService->login($request->validated());

        if ($login){
            $request->session()->regenerate();
            
            return response()->json([
                'message' => 'Login realizado com sucesso.'
            ], 200);
        }
        
        return response()->json([
            'message' => 'Usuário ou senha inválidos'
        ], 422);
    }
}
