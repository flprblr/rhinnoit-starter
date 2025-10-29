<?php

namespace App\Http\Controllers\Api\Passport;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passport\TokenRepository;
use Psr\Http\Message\ServerRequestInterface;

class AuthController extends Controller
{
    /**
     * Emitir un token de acceso Passport para empresas externas
     *
     * @param  ServerRequestInterface  $request
     * @return JsonResponse
     */
    public function issueToken(ServerRequestInterface $request): JsonResponse
    {
        $accessTokenController = app(\Laravel\Passport\Http\Controllers\AccessTokenController::class);
        $response = $accessTokenController->issueToken($request);

        // Si hay un error, devolverlo directamente
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        // Decodificar la respuesta para agregar formato consistente
        $tokenData = json_decode($response->getContent(), true);

        return response()->json([
            'success' => true,
            'token' => $tokenData['access_token'] ?? null,
            'token_type' => $tokenData['token_type'] ?? 'Bearer',
            'expires_in' => $tokenData['expires_in'] ?? null,
            'refresh_token' => $tokenData['refresh_token'] ?? null,
        ], 200);
    }

    /**
     * Obtener el usuario autenticado actual
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ], 200);
    }

    /**
     * Revocar el token actual del usuario
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function revokeToken(Request $request): JsonResponse
    {
        $tokenRepository = app(TokenRepository::class);
        $token = $request->user()->token();

        if ($token) {
            $tokenRepository->revokeAccessToken($token->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token revocado exitosamente.',
        ], 200);
    }

    /**
     * Revocar todos los tokens del usuario
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function revokeAllTokens(Request $request): JsonResponse
    {
        $tokenRepository = app(TokenRepository::class);
        $tokens = $request->user()->tokens;

        foreach ($tokens as $token) {
            $tokenRepository->revokeAccessToken($token->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Todos los tokens han sido revocados exitosamente.',
        ], 200);
    }
}
