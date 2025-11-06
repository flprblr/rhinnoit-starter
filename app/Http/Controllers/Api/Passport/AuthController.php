<?php

namespace App\Http\Controllers\Api\Passport;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\TokenRepository;
use Psr\Http\Message\ServerRequestInterface;

class AuthController extends Controller
{
    /**
     * Emitir un token de acceso Passport para empresas externas
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
            'scope' => $tokenData['scope'] ?? null,
        ], 200);
    }

    /**
     * Obtener el usuario autenticado actual
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
     * Verificar si el token actual es válido
     */
    public function verify(Request $request): JsonResponse
    {
        $token = $request->user()->token();

        if (! $token) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'No se encontró un token válido.',
            ], 401);
        }

        $isValid = ! $token->revoked && ($token->expires_at === null || $token->expires_at > now());

        return response()->json([
            'success' => true,
            'valid' => $isValid,
            'token' => [
                'id' => $token->id,
                'client_id' => $token->client_id,
                'scopes' => $token->scopes,
                'expires_at' => $token->expires_at?->toDateTimeString(),
                'created_at' => $token->created_at->toDateTimeString(),
            ],
        ], 200);
    }

    /**
     * Listar tokens del usuario autenticado
     */
    public function listTokens(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()
            ->select(['id', 'client_id', 'scopes', 'revoked', 'expires_at', 'created_at'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($token) {
                return [
                    'id' => $token->id,
                    'client_id' => $token->client_id,
                    'scopes' => $token->scopes,
                    'revoked' => $token->revoked,
                    'expires_at' => $token->expires_at?->toDateTimeString(),
                    'created_at' => $token->created_at->toDateTimeString(),
                ];
            });

        return response()->json([
            'success' => true,
            'tokens' => $tokens,
        ], 200);
    }

    /**
     * Obtener información de un token específico
     */
    public function showToken(Request $request, int $tokenId): JsonResponse
    {
        $token = $request->user()->tokens()->find($tokenId);

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Token no encontrado o no pertenece al usuario.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'token' => [
                'id' => $token->id,
                'client_id' => $token->client_id,
                'scopes' => $token->scopes,
                'revoked' => $token->revoked,
                'expires_at' => $token->expires_at?->toDateTimeString(),
                'created_at' => $token->created_at->toDateTimeString(),
                'updated_at' => $token->updated_at->toDateTimeString(),
            ],
        ], 200);
    }

    /**
     * Revocar el token actual del usuario
     */
    public function revokeToken(Request $request): JsonResponse
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);
        $token = $request->user()->token();

        if ($token) {
            $tokenRepository->revokeAccessToken($token->id);
            $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token revocado exitosamente.',
        ], 200);
    }

    /**
     * Revocar un token por ID (del usuario autenticado)
     */
    public function revokeById(Request $request, int $tokenId): JsonResponse
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);
        $token = $request->user()->tokens()->find($tokenId);

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Token no encontrado o no pertenece al usuario.',
            ], 404);
        }

        $tokenRepository->revokeAccessToken($token->id);
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);

        return response()->json([
            'success' => true,
            'message' => 'Token revocado exitosamente.',
        ], 200);
    }

    /**
     * Revocar todos los tokens del usuario
     */
    public function revokeAllTokens(Request $request): JsonResponse
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);
        $tokens = $request->user()->tokens;

        foreach ($tokens as $token) {
            $tokenRepository->revokeAccessToken($token->id);
            $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Todos los tokens han sido revocados exitosamente.',
        ], 200);
    }

    /**
     * Revocar todos los tokens excepto el actual
     */
    public function revokeOthers(Request $request): JsonResponse
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);
        $currentToken = $request->user()->token();
        $tokens = $request->user()->tokens;

        $deleted = 0;
        foreach ($tokens as $token) {
            if ($currentToken && $token->id !== $currentToken->id) {
                $tokenRepository->revokeAccessToken($token->id);
                $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
                $deleted++;
            }
        }

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => 'Se han revocado los demás tokens.',
        ], 200);
    }

    /**
     * Revocar tokens expirados del usuario autenticado
     */
    public function revokeExpired(Request $request): JsonResponse
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);
        $tokens = $request->user()->tokens()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->where('revoked', false)
            ->get();

        $deleted = 0;
        foreach ($tokens as $token) {
            $tokenRepository->revokeAccessToken($token->id);
            $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
            $deleted++;
        }

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => 'Tokens expirados revocados.',
        ], 200);
    }

    /**
     * Revocar refresh token
     */
    public function revokeRefreshToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);

        $refreshTokenRepository = app(RefreshTokenRepository::class);
        $refreshToken = $refreshTokenRepository->find($validated['refresh_token']);

        if ($refreshToken) {
            $refreshTokenRepository->revokeRefreshToken($validated['refresh_token']);
            $tokenRepository = app(TokenRepository::class);
            $tokenRepository->revokeAccessToken($refreshToken->access_token_id);

            return response()->json([
                'success' => true,
                'message' => 'Refresh token revocado exitosamente.',
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Refresh token no encontrado.',
        ], 404);
    }
}
