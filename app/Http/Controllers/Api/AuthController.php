<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    //
    public function ssoLogin(Request $request)
    {
        $accessToken = $request->token;

        //Ambil user info dari Microsoft
        $response = Http::withToken($accessToken)
            ->get('https://graph.microsoft.com/v1.0/me');

        if ($response->failed()) {
            return response()->json(['message' => 'Invalid Token', 401]);
        }

        $msUser = $response->json();

        // Ambil data user
        $email = $msUser['mail'] ?? $msUser['userPrincipalName'];
        $name = $msUser['displayName'];

        // Cek/create user
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => bcrypt('random123')]
        );

        // generate Token Laravel (Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }
}
